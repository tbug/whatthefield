<?php

namespace WhatTheField\Score;

use \DOMNode;

/**
 * Will multiply all $scorers values with $factor,
 * then sum and return the score.
 */
class Boost extends AbstractScore implements IScore
{
    protected $scorers;
    protected $factor;

    /**
     * @param mixed $factor can be a number or a callable (e.g, IScore)
     * @param array[callable] $scorers
     */
    public function __construct($factor, array $scorers)
    {
        $this->scorers = $scorers;
        $this->factor = $factor;
    }

    public function __invoke(DOMNode $node)
    {
        $factor = $this->callOrValue($this->factor, $node);
        $score = 0;
        foreach ($this->scorers as $scorer) {
            $score += $scorer($node) * $factor;
        }
        return $score;
    }
}
