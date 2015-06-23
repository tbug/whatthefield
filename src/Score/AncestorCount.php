<?php

namespace WhatTheField\Score;

use \DOMNode;

class AncestorCount implements IScore
{
    public function __invoke(DOMNode $node)
    {
        $n = 0;
        $parent = $node;
        while ($parent = $parent->parentNode) {
            if ($parent instanceof \DOMDocument) {
                break;
            }
            $n += 1;
        }
        return $n;
    }
}
