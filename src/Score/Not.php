<?php

namespace WhatTheField\Score;

use \DOMNode;

class Not implements IScore
{
    protected $scorer;

    public function __construct(IScore $scorer)
    {
        $this->scorer = $scorer;
    }

    public function __invoke(DOMNode $node)
    {
        return -$this->scorer->__invoke($node);
    }
}
