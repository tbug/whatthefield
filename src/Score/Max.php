<?php

namespace WhatTheField\Score;

use \DOMNode;

class Max implements IScore
{
    public function __construct(array $scorers)
    {
        $this->scorers = $scorers;
    }

    public function __invoke(DOMNode $node)
    {
        $score = 0;
        foreach ($this->scorers as $scorer) {
            if (is_numeric($scorer)) {
                $score = max($scorer, $score);
            } else {
                $score = max($scorer($node), $score);
            }
        }
        return $score;
    }
}
