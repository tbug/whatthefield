<?php

namespace WhatTheField\Score;

use \DOMNode;

class Constant implements IScore
{
    protected $score;

    public function __construct($score)
    {
        $this->score = $score;
    }

    public function __invoke(DOMNode $node)
    {
        if (is_callable($this->score)) {
            return $this->score($node);
        } else {
            return $this->score;
        }
    }
}
