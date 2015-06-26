<?php

namespace WhatTheField\Score;

use \DOMNode;

class Pow implements IScore
{

    protected $base;
    protected $exponent;
    protected $isBaseCallable;
    protected $isExponentCallable;


    public function __construct($base, $exponent)
    {
        $this->base = $base;
        $this->isBaseCallable = is_callable($base);
        $this->exponent = $exponent;
        $this->isExponentCallable = is_callable($exponent);
    }

    public function __invoke(DOMNode $node)
    {
        return pow(
            $this->isBaseCallable ? $this->base($node) : $this->base,
            $this->isExponentCallable ? $this->exponent($node) : $this->exponent
        );
    }
}
