<?php

namespace WhatTheField\Discovery;


use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use WhatTheField\QueryUtils;

abstract class AbstractDiscovery implements IDiscovery
{

    protected $_utils;
    protected $_log;

    public function __construct ()
    {
        $this->_utils = new QueryUtils;
        $this->_log = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger=null)
    {
        $this->log = $logger;
        $this->_utils = new QueryUtils($logger);
    }

    public function getLogger()
    {
        if (!$this->log) {
            $this->setLogger(new NullLogger());
        }
        return $this->log;
    }

    public function getUtils()
    {
        return $this->_utils;
    }

}
