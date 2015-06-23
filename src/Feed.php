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
    protected $log;

    protected $cache;

    public function __construct(IProvider $provider, IDiscovery $collectionDiscovery, array $fieldDiscoveries, $log)
    {
        $this->log = $log;
        $this->provider = $provider;
        $this->utils = new QueryUtils($log);
        $this->collectionDiscovery = $collectionDiscovery;

        $this->collectionDiscovery->setLogger($log);

        // assert the field discoveries are correct
        foreach ($fieldDiscoveries as $fieldName => $discovery) {
            if (!($discovery instanceof IDiscovery)) {
                throw new Exception("$fieldName value not instance of IDiscovery");
            }
        }
        $this->fieldDiscoveries = $fieldDiscoveries;
        foreach ($fieldDiscoveries as $d) {
            $d->setLogger($log);
        }
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
        return $discovery->discover($this->findAllCollectionItemValueNodes());
    }

    protected function discoverCollectionXPath()
    {
        $log = $this->log;
        $log->debug("Beginning collection discovery");
        $result = $this->collectionDiscovery->discover($this->provider->getQuery());
        $log->debug("Ended collection discovery. got: '{$result}'", [
            'collection_path' => $result
        ]);
        return $result;
    }

    public function getCollectionXPath()
    {
        return $this->discoverCollectionXPath();
    }

    public function discoverRelativeFieldXPaths()
    {
        $collectionXPath = $this->discoverCollectionXPath();
        return $this->discoverFieldXPaths($collectionXPath);
    }

    public function discoverFieldXPaths($relativeTo='')
    {
        $log = $this->log;
        $valueNodes = $this->findAllCollectionItemValueNodes();
        $result = [];

        foreach ($this->fieldDiscoveries as $fieldName => $discovery) {

            $log->debug("Beginning field discovery of field: '{$fieldName}'", [
                'field_name' => $fieldName
            ]);
            $result[$fieldName] = $discovery->discover($valueNodes);

            $log->debug("Ended field discovery of field: '{$fieldName}', got: '{$result[$fieldName]}'", [
                'field_name' => $fieldName,
                'field_path' => $result[$fieldName]
            ]);
        }

        if (strlen($relativeTo) > 0) {
            $log->debug("Let paths be relative to: '$relativeTo'");
            $relativeToLength = strlen($relativeTo);
            $out = [];
            foreach ($result as $key => $value) {
                if (strpos($value, $relativeTo) === 0) {
                    $out[$key] = substr($value, $relativeToLength);
                }
            }
            $log->debug("Completed relative remapping", [
                'relative_to' => $relativeTo,
                'relative_to_original' => $result,
                'relative_to_remapped' => $out,
            ]);
            return $out;
        } else {
            return $result;
        }
    }

    protected function findAllCollectionItemValueNodes()
    {
        $collectionExpr = $this->getCollectionXPath();
        $valueNodeExpr = $collectionExpr.'//*[text()]';        
        return $this->provider->getQuery()->find($valueNodeExpr);
    }


}