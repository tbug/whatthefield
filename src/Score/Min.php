<?php

namespace WhatTheField\Score;

use \DOMNode;

class Min implements IScore
{
    public function __construct(array $scorers)
    {
        $this->scorers = $scorers;
    }

    public function __invoke(DOMNode $node)
    {
        $score = 0;
        foreach ($this->scorers as $scorer) {
            $score = min($scorer->__invoke($node), $score);
        }
        return $score;
    }
}
