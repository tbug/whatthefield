<?php

namespace WhatTheField\Specification;

use WhatTheField\Provider\IProvider;
use WhatTheField\QueryUtils;

class DynamicSpecification implements ISpecification
{
    protected $provider;
    protected $utils;

    public function __construct(IProvider $provider)
    {
        $this->provider = $provider;
        $this->utils = new QueryUtils;
    }

    public function getCollectionXPathExpr()
    {
        $fd = $this->provider->getQuery();
        $utils = $this->utils;
        // try to guess at what the collection path might be.

        $maxSibCount = $utils->getMaxSibCount($fd);
        return array_keys($maxSibCount)[0];
    }



}