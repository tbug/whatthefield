<?php

namespace WhatTheField\Guesser;

use WhatTheField\Reader\IReader;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class Guesser
{
    protected $rootNode;
    protected $logger;

    public function __construct(Node $rootNode, LoggerInterface $logger=null)
    {
        $this->rootNode = $rootNode;
        if (!$logger) {
            $logger = new Logger('guesser');
        }
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }


    public function execute()
    {
        



    }
}
