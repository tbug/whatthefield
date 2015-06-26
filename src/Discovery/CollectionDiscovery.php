<?php

namespace WhatTheField\Discovery;

use FluentDOM\Query;
use FluentDOM\Nodes;
use WhatTheField\Utils;

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
        $maxSibs = (new Utils)->getMaxSibCount($nodes->getDocument(), $nonContentNodes);
        arsort($maxSibs);

        $ancestorCountGrouping = [];
        foreach ($maxSibs as $path => $count) {
            if ($count < 2) {
                continue;
            }
            $ancestorCount = substr_count($path, '/');
            if(!isset($ancestorCountGrouping[$ancestorCount])) {
                $ancestorCountGrouping[$ancestorCount] = [$path => $count];
            } else {
                $ancestorCountGrouping[$ancestorCount][$path] = $count;
            }
        }
        ksort($ancestorCountGrouping);

        $result = [];
        foreach ($ancestorCountGrouping as $_ => $collectionScores) {
            foreach ($collectionScores as $path => $score) {
                $result[$path] = $score;
            }
        }
        return $result;
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