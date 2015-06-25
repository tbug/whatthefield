<?php

namespace WhatTheField\Discovery;

use FluentDOM\Nodes;
use Psr\Log\LoggerInterface;


interface IDiscovery
{
    /**
     * Discover a list of possible xpaths that matches what the IDiscovery
     * implementation is supposed to find sorted by most important first.
     * The returned value is a map where key is the xpath and value is a score.
     * NOTE the list MUST be sorted by score.
     * @return array[string, float]
     */
    public function discoverScores(Nodes $query);

    /**
     * return 2-element array where first key is the xpath and second key is the score.
     */
    public function discoverScore(Nodes $query);

    /**
     * Discover the most likely xpath for the IDiscovery impl.
     * @return string
     */
    public function discover(Nodes $query);
}