<?php

namespace WhatTheField\Score;


use \DOMNode;

class IsFilterVar implements IScore
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function __invoke(DOMNode $node)
    {
        if (filter_var($node->textContent, $this->filter) === false) {
            return 0;
        } else {
            return 1;
        }
    }
}
