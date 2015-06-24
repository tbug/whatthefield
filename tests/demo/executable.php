#!/usr/bin/env php
<?php
date_default_timezone_set('UTC');
require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use WhatTheField\Provider\XMLProvider;
use WhatTheField\Feed;
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
        $provider = new XMLProvider($feedPath);

        $feed = new Feed($provider, new CollectionDiscovery(), $fieldConfig, $log);
        $mapping = $feed->discoverRelativeFieldXPaths();

        var_dump($mapping);
    })
    ->start();