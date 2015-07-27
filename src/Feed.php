<?php

namespace WhatTheField;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use WhatTheField\Discovery\IDiscovery;
use FluentDOM;
use FluentDOM\Nodes;

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

        $contentType = $this->guessContentType($path);
        $this->document = FluentDOM::load($path, $contentType);


        $this->document->registerNamespace('#default', 'urn:default');
        // register all found namespaces
        foreach ($this->document->find('namespace::*') as $node) {
            if (!in_array($node->localName, ['xml', 'xmlns'])) {
                $this->document->registerNamespace($node->localName, $node->nodeValue);
            }
        }

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
     * Sample $n nodes of the value found on $path
     * If you ask for 3 samples and 100 nodes exists matching path,
     * you will get node number 33, 66 and 99
     */
    public function getSampleNodes($n, $path)
    {
        $nodes = [];
        $result = $this->getDocument()->find($path);
        $total = count($result);
        $step = floor($total/$n);

        if ($step > 1) {
            for ($i=0; $i < $n; $i++) { 
                $nodes[] = $result[$i*$step];
            }
        } else {
            foreach ($result as $node) {
                $nodes[] = $node;
            }
        }
        return $nodes;
    }

    public function getSampleValues($n, $path)
    {
        $nodes = $this->getSampleNodes($n, $path);
        $samples = [];
        foreach ($nodes as $node) {
            $samples[] = $node->textContent;
        }
        return $samples;
    }

    /**
     * Return the xpath (grouped by name) if the field called $fieldName
     * $fieldName must be registered as a valid field name in valueDiscoveryObjects
     */
    public function getFieldXPathScores($fieldName, Nodes $nodes)
    {
        if (!isset($this->valueDiscoveryObjects[$fieldName])) {
            throw new Exception("Field named '$fieldName' not registered");
        }

        $this->debug("Beginning field discovery of field: '{$fieldName}'", [
            'field_name' => $fieldName
        ]);

        $results = $this->valueDiscoveryObjects[$fieldName]->discoverScores($nodes);

        $logResults = implode(', ', array_slice(array_map(function ($key, $value) {
            return "$key=$value";
        }, array_keys($results), array_values($results)), 0, 3));

        $this->debug("Ended field discovery of field: '{$fieldName}' top-3 maches: $logResults", [
            'field_name' => $fieldName,
            'field_paths' => $results
        ]);
        return $results;
    }

    public function discoverCollectionXPath()
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

    public function getAllFieldXPathScores($relativeTo='')
    {
        // populate cache before starting to iterate fields
        $nodes = $this->findAllCollectionItemValueNodes();
        $fieldScores = [];
        foreach ($this->valueDiscoveryObjects as $fieldName => $_) {
            $fieldScores[$fieldName] = $this->getFieldXPathScores($fieldName, $nodes);
        }
        return $fieldScores;
    }

    protected function findAllCollectionItemValueNodes()
    {
        $key = 'findAllCollectionItemValueNodes';
        if (!isset($this->cache[$key])) {
            $this->debug('Finding collection value nodes');
            $collectionExpr = $this->getCollectionXPath();
            $valueOrAttributeNodeExpr = "{$collectionExpr}//@*|{$collectionExpr}//*[text()]";
            $this->cache[$key] = FluentDOM($this->getDocument())->find($valueOrAttributeNodeExpr);
            $count = count($this->cache[$key]);
            $this->debug("Found {$count} value/attribute nodes");
        }
        return $this->cache[$key];
    }

    protected function guessContentType($filename)
    {
        $choices = [
            '/\.(xml|xslt)$/' => 'text/xml',
            '/\.(js|json)$/' => 'application/json',
        ];
        foreach ($choices as $pattern => $type) {
            if (preg_match($pattern, $filename)) {
                return $type;
            }
        }
        return 'text/xml';
    }

}