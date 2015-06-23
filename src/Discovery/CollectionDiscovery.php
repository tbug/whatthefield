<?php

namespace WhatTheField\Discovery;

use FluentDOM\Query;
use FluentDOM\Nodes;

class CollectionDiscovery extends AbstractDiscovery implements IDiscovery
{
    /**
     * Discover a list of possible xpaths that are collection items
     * in sorted order where the first element is the most likely.
     * @return array[string]
     */
    public function discoverScores(Nodes $nodes)
    {
        $nonContentNodes = $nodes->find('//*[not(text())]');
        $maxSibs = $this->getUtils()->getMaxSibCount($nonContentNodes);
        arsort($maxSibs);
        return $maxSibs;
    }

    public function discoverScore(Nodes $nodes)
    {
        $possibles = $this->discoverScores($nodes);
        if (count($possibles) === 0) {
            throw new DiscoveryException('Could not find collection');
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