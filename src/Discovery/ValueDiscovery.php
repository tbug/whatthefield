<?php

namespace WhatTheField\Discovery;

use FluentDOM\Nodes;
use WhatTheField\Utils;
use WhatTheField\Score\IScore;

class ValueDiscovery extends AbstractDiscovery implements IDiscovery
{
    protected $scorer;

    public function __construct (IScore $scoreObj)
    {
        $this->scorer = $scoreObj;
    }

    /**
     * Discover a list of possible xpaths that are collection items
     * in sorted order where the first element is the most likely.
     * @return array[string]
     */
    public function discoverScores(Nodes $originalNodes)
    {
        $nodes = $originalNodes;

        $scorer = $this->scorer; // grab the scorer callable
        $xPathScores = [];       // stores scoring per xpath

        foreach ($nodes as $node) {
            $nodeXPath = (new Utils)->toXPath($node);
            $score = $scorer($node);

            if (!isset($xPathScores[$nodeXPath])) {
                $xPathScores[$nodeXPath] = [$score];
            } else {
                $xPathScores[$nodeXPath][] = $score;
            }
        }

        // TODO cut off the bottom 5 percent and top 5 percent ? 

        $averagedOutXpaths = [];
        foreach ($xPathScores as $key => $values) {
            $averagedOutXpaths[$key] = array_sum($values) / count($values);
        }

        arsort($averagedOutXpaths);
        return $averagedOutXpaths;
    }

    public function discoverBestScore(Nodes $nodes)
    {
        $possibles = $this->discoverScores($nodes);
        if (count($possibles) === 0) {
            throw new DiscoveryException('Could not find any value that matches rules');
        }
        reset($possibles);
        return [key($possibles), current($possibles)];
    }

    /**
     * Discover the most likely xpath for the collection in nodes.
     * @return string
     */
    public function discover(Nodes $nodes)
    {
        list($path, $score) = $this->discoverBestScore($nodes);
        return $path;
    }
}