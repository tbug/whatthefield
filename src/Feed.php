<?php

namespace WhatTheField;

use Provider\IProvider;
use Specification\ISpecification;
use Specification\DynamicSpecification;

class Feed
{
    protected $provider;
    protected $specification;

    public function __construct(IProvider $provider, ISpecification $specification=null)
    {
        $this->provider = $provider;
        $this->specification = $specification ?: new DynamicSpecification($provider);
    }

    /**
     * return the xpath used to select all items in the feed collection
     */
    public function getCollectionXPathExpr()
    {
        $this->specification->getCollectionXPathExpr();
    }


}