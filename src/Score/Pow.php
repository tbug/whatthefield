<?php

namespace WhatTheField\Score;

use \DOMNode;

class Pow extends AbstractScore implements IScore
{

    protected $base;
    protected $exponent;

    public function __construct($base, $exponent)
    {
        $this->base = $base;
        $this->exponent = $exponent;
    }

    public function __invoke(DOMNode $node)
    {
        return pow(
            $this->callOrValue($this->base, $node),
            $this->callOrValue($this->exponent, $node)
        );
    }
}
