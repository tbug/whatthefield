<?php

namespace WhatTheField\Score;

use \DOMNode;

class WordCount implements IScore
{
    protected $goal;
    protected $baseScore;
    protected $factor;

    public function __construct($idealNumberOfNodes, $baseScore=1, $factor=0.5)
    {
        $this->goal = $idealNumberOfNodes;
        $this->baseScore = $baseScore;
        $this->factor = $factor;
    }

    public function __invoke(DOMNode $node)
    {
        $actual = preg_match_all('/\s+/', $node->textContent);

        $distance = abs($this->goal - $actual);
        return $this->baseScore * pow($this->factor, $distance);
    }
}
