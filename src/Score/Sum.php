<?php

namespace WhatTheField\Score;

use \DOMNode;

/**
 * Will multiply all $scorers values with $factor,
 * then sum and return the score.
 */
class Sum implements IScore
{
    protected $scorers;

    /**
     * @param array[callable] $scorers
     */
    public function __construct(array $scorers)
    {
        $this->scorers = $scorers;
    }

    public function __invoke(DOMNode $node)
    {
        $score = 0;
        foreach ($this->scorers as $scorer) {
            $score += $scorer($node);
        }
        return $score;
    }
}
