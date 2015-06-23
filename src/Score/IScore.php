<?php

namespace WhatTheField\Score;

use \DOMNode;

interface IScore
{
    /**
     * Given a DOMNode, return a number indicating how much
     * this node is what the IScore implementation provides.
     * The score can be any valid float.
     * A positive number indicates a match, a negative number
     * means a negative match. Usually you don't need to return negative numbers,
     * but only 0 (as in: "we don't know what this field is")
     * @return float
     */
    public function __invoke(DOMNode $node);
}
