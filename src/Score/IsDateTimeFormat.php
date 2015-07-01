<?php

namespace WhatTheField\Score;

use DateTime;
use DOMNode;

class IsDateTimeFormat implements IScore
{
    protected $format;

    public function __construct($format)
    {
        $this->format = $format;
    }

    public function __invoke(DOMNode $node)
    {
        $parsed = DateTime::createFromFormat($this->format, $node->textContent);
        return (int)($parsed !== false);
    }
}
