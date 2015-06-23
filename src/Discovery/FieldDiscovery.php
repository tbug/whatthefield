<?php

namespace WhatTheField\Discovery;

use FluentDOM\Query;
use FluentDOM\Nodes;

use WhatTheField\QueryUtils;

class FieldDiscovery implements IDiscovery
{
    protected $scoreObjects;
    protected $filterObjects;
    protected $utils;

    public function __construct (array $filterObjects, array $scoreObjects)
    {

        $this->utils = new QueryUtils;
        $this->filterObjects = $filterObjects;
        $this->scoreObjects = $scoreObjects;
    }



    /**
     * Discover a list of possible xpaths that are collection items
     * in sorted order where the first element is the most likely.
     * @return array[string]
     */
    public function discoverAll(Nodes $originalNodes)
    {
        $utils = $this->utils;
        $nodes = $originalNodes;
        $scoreObjects = $this->scoreObjects;
        // apply filter to limit what we need to score
        foreach ($this->filterObjects as $filterObject) {
            $nodes = $filterObject->filter($nodes);
        }

        // then for each resulting node, score it
        $xPathScores = [];
        foreach ($nodes as $node) {
            $nodeXPath = $utils->toXpath($node);
            $scores = [];    
            foreach ($scoreObjects as $key => $scoreObject) {
                $scoreKey = "$key:".get_class($scoreObject);
                $scores[$scoreKey] = $scoreObject->__invoke($node);
            }
            $sum = array_sum($scores);
            if (!isset($xPathScores[$nodeXPath])) {
                $xPathScores[$nodeXPath] = [$sum];
            } else {
                $xPathScores[$nodeXPath][] = $sum;
            }
        }

        $averagedOutXpaths = [];
        foreach ($xPathScores as $key => $values) {
            $averagedOutXpaths[$key] = array_sum($values) / count($values);
        }

        arsort($averagedOutXpaths);
        return array_keys($averagedOutXpaths);
    }

    /**
     * Discover the most likely xpath for the collection in query.
     * @return string
     */
    public function discover(Nodes $query)
    {
        $possibles = $this->discoverAll($query);
        if (count($possibles) > 0) {
            return $possibles[0];
        } else {
            throw new DiscoveryException('Could not find field');
        }
    }
}