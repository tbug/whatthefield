<?php

namespace WhatTheField\Score;

use \DOMNode;

class IsMatch implements IScore
{
    protected $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function __invoke(DOMNode $node)
    {
        if (preg_match($this->pattern, $node->textContent)) {
            return 1;
        } else {
            return 0;
        }
    }
}
