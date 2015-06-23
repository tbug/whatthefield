<?php

namespace WhatTheField\Score;

use \DOMNode;

class Named implements IScore
{
    protected $names;
    protected $sanitizer;

    public function __construct(array $names, callable $sanitizer=null)
    {
        $this->names = $names;
        $this->sanitizer = $sanitizer;
    }

    public function __invoke(DOMNode $node)
    {
        $sanitizer = $this->sanitizer;
        if ($sanitizer !== null) {
            $name = $sanitizer($node->nodeName);
        } else {
            $name = $node->nodeName;
        }
        return (bool)in_array($name, $this->names);
    }
}
