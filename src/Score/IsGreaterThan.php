<?php

namespace WhatTheField\Score;

use \DOMNode;

class IsGreaterThan extends AbstractScore implements IScore
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke(DOMNode $node)
    {
        if (!is_numeric($node->textContent)) {
            return 0;
        }
        $nodeValue = (float)$node->textContent;
        $ownValue = $this->callOrValue($this->value, $node);
        if (!is_numeric($ownValue)) {
            return 0;
        }
        return $nodeValue > $ownValue ? 1 : 0;
    }
}
