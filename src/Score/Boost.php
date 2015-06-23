<?php

namespace WhatTheField\Score;

use \DOMNode;

class Boost implements IScore
{
    protected $scorer;
    protected $factor;

    public function __construct($factor, IScore $scorer)
    {
        $this->scorer = $scorer;
        $this->factor = $factor;
    }

    public function __invoke(DOMNode $node)
    {
        return $this->factor * $this->scorer->__invoke($node);
    }
}
