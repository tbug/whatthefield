<?php

namespace WhatTheField\Discovery;

use FluentDOM\Nodes;

interface IDiscovery
{
    /**
     * Discover a list of possible xpaths that matches what the IDiscovery
     * implementation is supposed to find sorted by most important first.
     * @return array[string]
     */
    public function discoverAll(Nodes $query);

    /**
     * Discover the most likely xpath for the IDiscovery impl.
     * @return string
     */
    public function discover(Nodes $query);
}