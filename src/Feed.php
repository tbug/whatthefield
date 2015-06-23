<?php

namespace WhatTheField;

use WhatTheField\Provider\IProvider;
use WhatTheField\Discovery\IDiscovery;


class Feed
{
    protected $provider;
    protected $utils;
    protected $collectionDiscovery;
    protected $fieldDiscoveries;

    public function __construct(IProvider $provider, IDiscovery $collectionDiscovery, array $fieldDiscoveries)
    {
        $this->provider = $provider;
        $this->utils = new QueryUtils;
        $this->collectionDiscovery = $collectionDiscovery;

        // assert the field discoveries are correct
        foreach ($fieldDiscoveries as $fieldName => $discovery) {
            if (!($discovery instanceof IDiscovery)) {
                throw new Exception("$fieldName value not instance of IDiscovery");
            }
        }
        $this->fieldDiscoveries = $fieldDiscoveries;
    }

    /**
     * return the xpath used to select all items in the feed collection
     */
    public function getCollectionXPathExpr()
    {
        return $this->collectionDiscovery->discover($this->provider->getQuery());
    }

    /**
     * Return the xpath (grouped by name) if the field called $fieldName
     * $fieldName must be registered as a valid field name in fieldDiscoveries
     */
    public function discoverFieldXPath($fieldName)
    {
        if (!isset($this->fieldDiscoveries[$fieldName])) {
            throw new Exception("Field named '$fieldName' not registered");
        }
        $discovery = $this->fieldDiscoveries[$fieldName];
        return $discovery->discover($this->findAllCollectionValueNodes());
    }

    protected function findAllCollectionValueNodes()
    {
        $collectionExpr = $this->getCollectionXPathExpr();
        $valueNodeExpr = $collectionExpr.'//*[text()]';        
        return $this->provider->getQuery()->find($valueNodeExpr);
    }


}