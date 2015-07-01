<?php

namespace WhatTheField\Score;

use \DOMNode;

class NumericDistance implements IScore
{
    protected $a;
    protected $b;
    protected $aIsCallable;
    protected $bIsCallable;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
        $this->aIsCallable = is_callable($a);
        $this->bIsCallable = is_callable($b);

    }

    public function __invoke(DOMNode $node)
    {
        $a = $this->a;
        $b = $this->b;

        $aScore = $this->aIsCallable ? $a($node) : $a;
        $bScore = $this->bIsCallable ? $b($node) : $b;
        return abs($aScore-$bScore);
    }
}
