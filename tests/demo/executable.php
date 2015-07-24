#!/usr/bin/env php
<?php
date_default_timezone_set('UTC');
require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use WhatTheField\Feed;
use WhatTheField\QueryUtils;
use WhatTheField\Discovery\CollectionDiscovery;
use WhatTheField\Discovery\ValueDiscovery;

use Cli\Helpers\DocumentedScript;
use Cli\Helpers\Parameter;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

$log = new Logger('wtf');
$handler = new StreamHandler(STDERR, Logger::DEBUG);
$handler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n"));
$log->pushHandler($handler);

$script = new DocumentedScript();
$script
    ->setName('What The Field')
    ->setVersion('0.1')
    ->setDescription('')
    ->addParameter(new Parameter('f', 'file', Parameter::VALUE_REQUIRED), 'xml file')
    ->addParameter(new Parameter('c', 'config', Parameter::VALUE_REQUIRED), 'config file')
    ->addParameter(new Parameter('k', 'keys', '3'), 'top n keys to show')
    ->addParameter(new Parameter('s', 'samples', '3'), 'number of value samples pr. key')
    ->addParameter(new Parameter('t', 'truncate', '50'), 'truncate sample values above this length')
    ->setProgram(function ($options, $arguments) use ($log) {

        $feedPath = $options['file'];
        $configPath = $options['config'];
        $topN = (int)$options['keys'];
        $sampleN = (int)$options['samples'];
        $truncateN = (int)$options['truncate'];


        $fieldScorers = require $configPath;
        $valuesDiscoveries = [];
        foreach ($fieldScorers as $fieldName => $scoreObj) {
            $valuesDiscoveries[$fieldName] = new ValueDiscovery($scoreObj);
        }

        $feed = new Feed($feedPath, new CollectionDiscovery(), $valuesDiscoveries, $log);
        $collectionPath = $feed->discoverCollectionXPath();
        $mapping = $feed->getAllFieldXPathScores();


        echo "COLLECTION\t$collectionPath\n";
        foreach ($mapping as $name => $scores) {
            $keys = array_keys(array_filter($scores, function ($item) {
                return $item > 0;
            }));
            $strKeys = implode("  ", array_slice($keys, 0, $topN));
            $name = str_pad($name, 10);
            echo "KEY\t$name\t$strKeys\n";
            if ($sampleN > 0) {
                // sample mode
                $c = count($keys);
                for ($i=1; $i <= $c; $i++) { 
                    if ($i > $topN) break;
                    $key = $keys[$i-1];
                    echo "    SECTION ($i/$c)\t$name\t$key\n";

                    $samples = $feed->getSampleValues($sampleN, $key);
                    foreach ($samples as $sample) {
                        $length = mb_strlen($sample);
                        if ($length > $truncateN && $truncateN > 0) {
                            $sample = "'" . substr($sample, 0, $truncateN) . "' ...";
                        }
                        $sample = str_replace(["\n", "\t"], ['\n', '\t'], $sample);
                        $lengthStr = str_pad($length, 4);
                        echo "        SAMPLE\t$key\t$sample\n";
                    }
                }
                echo "\n";
            } else {
                // compact mode
                $strKeys = implode("  ", array_slice($keys, 0, $topN));
                $name = str_pad($name, 10);
                echo "KEY\t$name\t$strKeys\n";
            }

        }
    })
    ->start();