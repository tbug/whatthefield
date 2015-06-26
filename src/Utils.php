<?php
namespace WhatTheField;

use FluentDOM\Nodes;
use DOMNodeList;
use DOMDocument;
use DOMElement;
use DOMAttr;
use DOMNode;

use FluentDOM;

class Utils
{
    protected function getCacheObject($reference)
    {
        return UtilsDocumentCache::instance($reference);
    }

    protected function cached($reference, $key, callable $valueCreator, $args=[])
    {
        return $this->getCacheObject($reference)->cached($key, $valueCreator, $args);
    }


    /**
     * Given an element, return an xpath condition that will select
     * "similar" nodes. Usually this will be empty string, but cases exist
     * where a property is used to group nodes by the same name:
     * <properties>
     *   <property name="foo">bar</property>
     *   <property name="bar">baz</property>
     * </properties>
     * 
     */
    public function getElementGroupingConditions(DOMElement $el)
    {
        $cacheObject = $this->getCacheObject($el);
        $attributes = $el->attributes;
        if ($attributes === null) {
            return ''; // easy, no attributes, no conditions
        }
        $conditions=[];
        foreach ($attributes as $attrName => $attrObj) {
            if ((bool)round($this->scoreGroupAttribute($attrObj, $cacheObject))) {
                $conditions[] = '@' . $attrName . '="' . (string)$attrObj . '"';
            }
        }
        if (count($conditions) > 0) {
            return '[' . implode(' and ', $conditions) . ']';
        } else {
            return '';
        }
    }

    /**
     * @return float number from 0 to 1 where 1 is most likely to be a grouping attribute 
     */
    protected function scoreGroupAttribute(DOMAttr $attr, UtilsDocumentCache $cache)
    {
        $path = $this->toXPath($attr, false);

        // wrap value in cache
        $result = $cache->cached('scoreGroupAttribute|'.$path, function () use ($attr, $path) {
            $document = $attr->ownerDocument;
            $samePathNodes = FluentDOM($document)->find($path);
            $parentNodeCount = count(FluentDOM($document)->find($this->toXPath($attr->parentNode, false)));

            $valueDistribution = [];
            $totalCount = 0;
            foreach ($samePathNodes as $node) {
                if (! ($node instanceof DOMAttr)) {
                    throw new Exception('sanity check, something is wrong');
                }
                $totalCount += 1;

                $value = (string)$node;
                if (!isset($valueDistribution[$value])) {
                    $valueDistribution[$value] = 1;
                } else {
                    $valueDistribution[$value] += 1;
                }
            }
            return [$parentNodeCount, $totalCount, $valueDistribution];
        });
        list($parentNodeCount, $totalCount, $valueDistribution) = $result;



        // score indicating on how many possible nodes do we see the attr
        $availability = $totalCount / $parentNodeCount;
        // score for how similar all seen values are (10 is determined by dice roll)
        $sameness = pow(1 - (count($valueDistribution) / $totalCount), 10);

        // now a good sameness and good availability probably means grouping:
        $score = ($availability*$sameness);
        return $score;
    }

    private $seen=[];
    public function getParents(DOMNode $node)
    {
        $ancestors = [];
        do {
            $ancestors[] = $node;
        } while ($node = $node->parentNode);
        return $ancestors;
    }

    public function toXPath(DOMNode $node, $guessGroupingAttributes=true)
    {
        if (!isset($node->toXPathCache)) {
            $segments = [];
            foreach (array_reverse($this->getParents($node)) as $node) {
                $segments[] = $this->toXPathSegmentName($node, $guessGroupingAttributes);
            }
            $node->toXPathCache = implode('/', $segments);
        }
        return $node->toXPathCache;
    }

    public function toXPathSegmentName(DOMNode $node, $guessGroupingAttributes=true)
    {
        if ($node instanceof DOMDocument) {
            $segment = ''; // will be joined by /, so correctly be the root of the document
        } elseif ($node instanceof DOMElement) {
            if ($guessGroupingAttributes) {
                $elementGroupingConditionString = $this->getElementGroupingConditions($node);
            } else {
                $elementGroupingConditionString = '';
            }
            $segment = $node->nodeName.$elementGroupingConditionString;
        } elseif ($node instanceof DOMAttr) {
            $segment = '@'.$node->nodeName;
        } else {
            throw new Exception('Node type not known: '.$node->nodeType);
        }
        return $segment;
    }

    public function groupByCallable(Nodes $nodes, callable $fn)
    {
        $grouping = [];
        foreach ($nodes as $node) {
            $key = $fn($node);
            if (!isset($grouping[$key])) {
                $grouping[$key] = [];
            }
            $grouping[$key][] = $node;
        }

        foreach ($grouping as $key => &$nodeList) {
            $nodeList = $nodes->spawn($nodeList);
        }
        return $grouping;
    }

    public function groupByContentHash(Nodes $nodes)
    {
        return $this->groupByCallable($nodes, function ($node) {
            return hash('adler32', $node->textContent);
        });
    }

    public function groupByNodeXPath(Nodes $nodes)
    {
        return $this->groupByCallable($nodes, function ($node) {
            return $this->toXPath($node);
        });
    }




    protected function maxSibRecursive(DOMNodeList $nodes, $path='')
    {
        $result = [
            $path => 1
        ];
        $childAggr = [];
        foreach ($nodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }

            $childNodeName = $child->nodeName;
            $childNodePath = $path.'/'.$childNodeName;

            if (!isset($childAggr[$childNodePath])) {
                $childAggr[$childNodePath] = 1;
            } else {
                $childAggr[$childNodePath] += 1;
            }

            foreach ($this->maxSibRecursive($child->childNodes, $childNodePath) as $key => $value) {
                if (isset($result[$key])) {
                    $result[$key] = max($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }
        foreach ($childAggr as $key => $value) {
            if (isset($result[$key])) {
                $result[$key] = max($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function getMaxSibCount(DOMNode $document)
    {
        if ($document instanceof DOMDocument) {
            $root = $document->documentElement;
        } else {
            $root = $document->ownerDocument->documentElement;
        }
        $maxSibCount = $this->maxSibRecursive($root->childNodes, '/'.$root->nodeName);
        return $maxSibCount;
    }




}