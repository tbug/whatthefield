<?php

namespace WhatTheField\Score;

use \DOMNode;
use WhatTheField\Utils;

class IsUnique implements IScore
{
    static protected $pathCache = [];

    /**
     * Check if DOMNode is unique (compared to it's siblings)
     */
    public function __invoke(DOMNode $node)
    {
        $nodePath = (new Utils)->toXPath($node);

        // as all nodes with the same path will have the same score,
        // we cache pr. path name
        if (!isset(self::$pathCache[$nodePath])) {
            $collection = FluentDOM($node->ownerDocument)->find($nodePath);
            $grouped = (new Utils)->groupByContentHash($collection);
            
            $totalCount = count($collection);
            $uniqueCount = count($grouped);
            // special case for uniqueCount 1:
            // not unique at all, return 0
            if ($uniqueCount === 1) {
                $score = 0;
            } else {
                $score = $uniqueCount / $totalCount;
            }
            self::$pathCache[$nodePath] = $score;
        }
        return self::$pathCache[$nodePath];

    }
}
