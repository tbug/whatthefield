<?php

namespace WhatTheField\Discovery;


use WhatTheField\QueryUtils;
use FluentDOM\Query;
use FluentDOM\Nodes;

class CollectionDiscovery implements IDiscovery
{
    protected $utils;

    public function __construct ()
    {
        $this->utils = new QueryUtils;
    }

    /**
     * Discover a list of possible xpaths that are collection items
     * in sorted order where the first element is the most likely.
     * @return array[string]
     */
    public function discoverAll(Nodes $query)
    {
        $nonContentNodes = $query->find('//*[not(text())]');
        $maxSibs = $this->utils->getMaxSibCount($nonContentNodes);
        arsort($maxSibs);
        return array_keys($maxSibs);
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
            throw new DiscoveryException('Could not find a collection');
        }
    }
}