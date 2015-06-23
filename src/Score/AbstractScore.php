<?php

namespace WhatTheField\Score;

use \DOMNode;

abstract class AbstractScore implements IScore
{
    protected function callOrValue($mixed, DOMNode $node)
    {
        if (is_callable($mixed)) {
            return $mixed($node);
        } else {
            return $mixed;
        }
    }
}
