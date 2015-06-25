<?php

namespace WhatTheField\Discovery;


use WhatTheField\QueryUtils;

use Psr\Log\NullLogger;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

abstract class AbstractDiscovery implements IDiscovery, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function getLogger()
    {
        return $this->logger ?: new NullLogger();
    }

}
