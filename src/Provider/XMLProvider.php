<?php

namespace WhatTheField\Provider;

use FluentDOM;

class XMLProvider extends AbstractProvider implements IProvider
{
    protected $document;

    public function __construct($xmlPath)
    {
        parent::__construct();
        $this->document = FluentDOM::load($xmlPath);
    }

    public function getDocument()
    {
        return $this->document;
    }
}
