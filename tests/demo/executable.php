#!/usr/bin/env php
<?php
date_default_timezone_set('UTC');
require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use WhatTheField\Feed;
use WhatTheField\QueryUtils;
use WhatTheField\Discovery\CollectionDiscovery;

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
    ->addParameter(new Parameter('f', 'feed', Parameter::VALUE_REQUIRED), 'feed')
    ->addParameter(new Parameter('c', 'config', Parameter::VALUE_REQUIRED), 'config')
    ->setProgram(function ($options, $arguments) use ($log) {

        $feedPath = $options['feed'];
        $configPath = $options['config'];
        $fieldConfig = require $configPath;

        $feed = new Feed($feedPath, new CollectionDiscovery(), $fieldConfig, $log);
        $collectionPath = $feed->discoverCollectionXPath();
        $mapping = $feed->discoverFieldXPaths();

        $longestKey = 0;
        foreach ($mapping as $key => $path) {
            $longestKey = max($longestKey, mb_strlen($key));
        }

        echo "COLLECTION\t$collectionPath\n";
        foreach ($mapping as $key => $path) {
            $key = str_pad($key, $longestKey);
            echo "KEY\t$key\t$path\n";
        }
    })
    ->start();