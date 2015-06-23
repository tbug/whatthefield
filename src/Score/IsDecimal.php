<?php

namespace WhatTheField\Score;

use \DOMNode;

class IsDecimal implements IScore
{
    public function __construct()
    {
    }

    public function __invoke(DOMNode $node)
    {
        return (int)(bool)preg_match('/^\d+\.\d+$/', $node->textContent);
    }
}
