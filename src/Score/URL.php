<?php

namespace WhatTheField\Score;


use \DOMNode;

class URL implements IScore
{
    public function __invoke(DOMNode $node)
    {
        if (filter_var($node->textContent, FILTER_VALIDATE_URL) === false) {
            return 0;
        } else {
            return 1;
        }
    }
}
