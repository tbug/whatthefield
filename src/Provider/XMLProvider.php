<?php

namespace WhatTheField\Provider;

use FluentDOM;

class XMLProvider implements IProvider
{
    protected $docQuery;

    public function __construct($xmlPath)
    {
        $this->docQuery = new FluentDOM\Query($xmlPath);
    }

    public function getQuery()
    {
        return $this->docQuery;
    }
}
