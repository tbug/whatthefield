<?php

namespace WhatTheField\Score;

use \DOMNode;

/**
 * Will multiply all $scorers values with $factor,
 * then sum and return the score.
 */
class Boost implements IScore
{
    protected $scorers;
    protected $factor;
    protected $isFactorCallable;

    /**
     * @param mixed $factor can be a number or a callable (e.g, IScore)
     * @param array[callable] $scorers
     */
    public function __construct($factor, array $scorers)
    {
        $this->scorers = $scorers;
        $this->factor = $factor;
        $this->isFactorCallable = is_callable($factor);
    }

    public function __invoke(DOMNode $node)
    {
        if ($this->isFactorCallable) {
            $factor = $this->factor($node);
        } else {
            $factor = $this->factor;
        }
        $score = 0;
        foreach ($this->scorers as $scorer) {
            $score += $scorer($node) * $factor;
        }
        return $score;
    }
}
