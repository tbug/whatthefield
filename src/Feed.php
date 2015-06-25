<?php

namespace WhatTheField;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use WhatTheField\Discovery\IDiscovery;
use WhatTheField\Fluent\Utils;

use FluentDOM;

class Feed implements LoggerAwareInterface
{
    use LoggerTrait;
    use LoggerAwareTrait;


    protected $document;
    protected $collectionDiscoveryObject;
    protected $valueDiscoveryObjects;

    protected $cache;

    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

    public function __construct($path, IDiscovery $collectionDiscoveryObject, array $valueDiscoveryObjects, LoggerInterface $logger)
    {
        $this->setLogger($logger);


        if (!is_file($path)) {
            throw new Exception("'$path' is not a valid file");
        }
        // we have custom logic. Make FluentDOM use our custom loader.
        Utils::init();

        $this->document = FluentDOM::load($path);
        if (!$this->document) {
            throw new Exception("'$path' could not be loaded.");
        }

        $this->valueDiscoveryObjects = $valueDiscoveryObjects;
        $this->collectionDiscoveryObject = $collectionDiscoveryObject;

        // apply loggers
        if ($this->collectionDiscoveryObject instanceof LoggerAwareInterface) {
            $this->collectionDiscoveryObject->setLogger($logger);
        }
        foreach ($this->valueDiscoveryObjects as $obj) {
            if ($obj instanceof LoggerAwareInterface) {
                $obj->setLogger($logger);
            }
        }

        // assert the field discoveries are correct
        foreach ($this->valueDiscoveryObjects as $fieldName => $discovery) {
            if (!($discovery instanceof IDiscovery)) {
                throw new Exception("'$fieldName' value not instance of IDiscovery");
            }
        }

    }

    public function getDocument()
    {
        return $this->document;
    }


    /**
     * Return the xpath (grouped by name) if the field called $fieldName
     * $fieldName must be registered as a valid field name in valueDiscoveryObjects
     */
    public function discoverFieldXPath($fieldName)
    {
        if (!isset($this->valueDiscoveryObjects[$fieldName])) {
            throw new Exception("Field named '$fieldName' not registered");
        }
        $discovery = $this->valueDiscoveryObjects[$fieldName];
        return $discovery->discover($this->findAllCollectionItemValueNodes());
    }

    protected function discoverCollectionXPath()
    {
        $this->debug("Beginning collection discovery");
        $result = $this->collectionDiscoveryObject->discover(FluentDOM($this->getDocument()));
        $this->debug("Ended collection discovery. got: '{$result}'", [
            'collection_path' => $result
        ]);
        return $result;
    }

    public function getCollectionXPath()
    {
        $key = 'collectionXPath';
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->discoverCollectionXPath();
        }
        return $this->cache[$key];
    }

    public function discoverRelativeFieldXPaths()
    {
        $collectionXPath = $this->getCollectionXPath();
        return $this->discoverFieldXPaths($collectionXPath);
    }

    public function discoverFieldXPaths($relativeTo='')
    {
        $valueNodes = $this->findAllCollectionItemValueNodes();
        $result = [];

        foreach ($this->valueDiscoveryObjects as $fieldName => $discovery) {

            $this->debug("Beginning field discovery of field: '{$fieldName}'", [
                'field_name' => $fieldName
            ]);
            $result[$fieldName] = $discovery->discover($valueNodes);

            $this->debug("Ended field discovery of field: '{$fieldName}', got: '{$result[$fieldName]}'", [
                'field_name' => $fieldName,
                'field_path' => $result[$fieldName]
            ]);
        }

        if (strlen($relativeTo) > 0) {
            $this->debug("Let paths be relative to: '$relativeTo'");
            $relativeToLength = strlen($relativeTo);
            $out = [];
            foreach ($result as $key => $value) {
                if (strpos($value, $relativeTo) === 0) {
                    $out[$key] = substr($value, $relativeToLength);
                }
            }
            $this->debug("Completed relative remapping", [
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
        $this->debug('Finding collection value nodes');
        $collectionExpr = $this->getCollectionXPath();
        $valueOrAttributeNodeExpr = "{$collectionExpr}//@*|{$collectionExpr}//*[text()]";
        $valueOrAttributeNodes = FluentDOM($this->getDocument())->find($valueOrAttributeNodeExpr);
        $valueOrAttributeNodeCount = count($valueOrAttributeNodes);
        $this->debug("Found {$valueOrAttributeNodeCount} value/attribute nodes");
        return $valueOrAttributeNodes;
    }


}