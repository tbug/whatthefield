<?php

namespace WhatTheField\Discovery;

use FluentDOM\Nodes;

class ValueDiscovery extends AbstractDiscovery implements IDiscovery
{
    protected $scoreObjects;
    protected $filterObjects;

    public function __construct (array $filterObjects, array $scoreObjects)
    {
        $this->filterObjects = $filterObjects;
        $this->scoreObjects = $scoreObjects;
    }

    /**
     * Discover a list of possible xpaths that are collection items
     * in sorted order where the first element is the most likely.
     * @return array[string]
     */
    public function discoverScores(Nodes $originalNodes)
    {
        $nodes = $originalNodes;
        $scoreObjects = $this->scoreObjects;
        // apply filter to limit what we need to score
        foreach ($this->filterObjects as $filterObject) {
            $nodes = $filterObject->filter($nodes);
        }

        // then for each resulting node, score it
        $xPathScores = [];
        $total = count($nodes);
        $n = 0;
        fwrite(STDERR, "Starting discovery ....");
        $printDeltaTime = 0.5;
        $nextPrint = microtime(true) + $printDeltaTime;
        foreach ($nodes as $node) {
            $nodeXPath = $node->toXPath();
            $scores = [];
            foreach ($scoreObjects as $key => $scoreObject) {
                $scoreKey = "$key:".get_class($scoreObject);
                $scores[$scoreKey] = $scoreObject($node);
            }
            $sum = array_sum($scores);
            if (!isset($xPathScores[$nodeXPath])) {
                $xPathScores[$nodeXPath] = [$sum];
            } else {
                $xPathScores[$nodeXPath][] = $sum;
            }

            $n += 1;
            if (microtime(true) > $nextPrint || $n === $total) {
                $percentage = number_format($n / $total * 100, 1);
                fwrite(STDERR, "\rProcessed {$n} of {$total} ({$percentage}%)");
                $nextPrint = microtime(true) + $printDeltaTime;
            }
        }
        fwrite(STDERR, "\n");

        $averagedOutXpaths = [];
        foreach ($xPathScores as $key => $values) {
            $averagedOutXpaths[$key] = array_sum($values) / count($values);
        }

        arsort($averagedOutXpaths);
        return $averagedOutXpaths;
    }

    public function discoverScore(Nodes $nodes)
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
        list($path, $score) = $this->discoverScore($nodes);
        return $path;
    }
}