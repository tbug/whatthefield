<?php

namespace WhatTheField\Score;

use \DOMNode;

class IsGreaterThan implements IScore
{
    protected $value;
    protected $isValueCallable;

    public function __construct($value)
    {
        $this->value = $value;
        $this->isValueCallable = is_callable($value);

    }

    public function __invoke(DOMNode $node)
    {
        if (!is_numeric($node->textContent)) {
            return 0;
        }
        $nodeValue = (float)$node->textContent;
        $ownValue = $this->isValueCallable ? $this->value($node) : $this->value;
        if (!is_numeric($ownValue)) {
            return 0;
        }
        return $nodeValue > $ownValue ? 1 : 0;
    }
}
