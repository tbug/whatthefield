<?php

namespace WhatTheField\Score;

use \DOMNode;

class MatchCount implements IScore
{
    protected $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function __invoke(DOMNode $node)
    {
        return preg_match_all($this->pattern, $node->textContent);
    }
}
