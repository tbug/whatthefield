<?php

namespace WhatTheField;

use FluentDOM;
use FluentDOM\Query;
use FluentDOM\Nodes;
use DOMAttr;
use DOMNode;
use DOMElement;
use DOMDocument;
use DOMNodeList;
use Psr\Log\NullLogger;

class QueryUtils
{
    public function __construct($logger=null)
    {
        $this->log = $logger ?: new NullLogger();
    }

    static protected $singleton;
    static public function instance()
    {
        if (!self::$singleton) {
            self::$singleton = new self(new NullLogger); 
        }
        return self::$singleton;
    }

    protected function maxSibRecursive(DOMNodeList $nodes, $path='')
    {
        $result = [
            $path => 1
        ];
        $childAggr = [];
        foreach ($nodes as $child) {
            if (!($child instanceof \DOMElement)) {
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


    /**
     * Return a map of all named xpaths and the number of siblings they have with the same name, at max.
     * This is useful to detect collections of things.
     */
    public function getMaxSibCount(Nodes $document) {
        $log = $this->log;
        $log->debug("    (utils) Starting getMaxSibCount");
        $docElement = $document->getDocument()->documentElement;
        $maxSibCount = $this->maxSibRecursive($docElement->childNodes, '/'.$docElement->nodeName);
        $log->debug("    (utils) Ending getMaxSibCount");
        return $maxSibCount;
    }

    public function toXpathCounts(Nodes $nodes)
    {
        $log = $this->log;
        $log->debug("    (utils) Starting toXpathCounts");
        $xpaths = [];
        $total = count($nodes);
        $log->debug(" | total of $total node(s)");
        foreach ($nodes as $node) {
            $key = $this->toXPath($node);
            if (!isset($xpaths[$key])) {
                $xpaths[$key] = 1;
            } else {
                $xpaths[$key] += 1;
            }
        }
        $log->debug("    (utils) Ending toXpathCounts");
        return $xpaths;
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
        $attributes = $el->attributes;
        if ($attributes === null) {
            return ''; // easy, no attributes, no conditions
        }
        $conditions=[];
        foreach ($attributes as $attrName => $attrObj) {
            if ((bool)round($this->scoreGroupAttribute($attrObj))) {
                $conditions[] = '@' . $attrName . '="' . (string)$attrObj . '"';
            }
        }
        if (count($conditions) > 0) {
            return '[' . implode(' and ', $conditions) . ']';
        } else {
            return '';
        }
    }


    private $groupCache = [];
    /**
     * @return float number from 0 to 1 where 1 is most likely to be a grouping attribute 
     */
    protected function scoreGroupAttribute(DOMAttr $attr)
    {
        $path = $this->toXPath($attr, false);
        $attrName = $attr->nodeName;

        // get stats on how attribute is used in document.
        if (!isset($this->groupCache[$path])) {
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
            $this->groupCache[$path] = [$parentNodeCount, $totalCount, $valueDistribution];
        } else {
            list($parentNodeCount, $totalCount, $valueDistribution) = $this->groupCache[$path];
        }

        // score indicating on how many possible nodes do we see the attr
        $availability = $totalCount / $parentNodeCount;
        // score for how similar all seen values are (10 is determined by dice roll)
        $sameness = pow(1 - (count($valueDistribution) / $totalCount), 10);

        // now a good sameness and good availability probably means grouping:
        $score = ($availability*$sameness);
        return $score;
    }
    private $seen=[];

    protected function getParents(DOMNode $node)
    {
        $ancestors = [];
        do {
            $ancestors[] = $node;
        } while ($node = $node->parentNode);
        return $ancestors;
    }

    public function toXPath(DOMNode $node, $guessGroupingAttributes=true)
    {
        $segments = [];
        foreach (array_reverse($this->getParents($node)) as $node) {
            $segments[] = $this->toXPathSegmentName($node, $guessGroupingAttributes);
        }
        return implode('/', $segments);
    }

    protected function toXPathSegmentName(DOMNode $node, $guessGroupingAttributes=true)
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
        $log = $this->log;
        return $this->groupByCallable($nodes, function ($node) {
            return hash('adler32', $node->textContent);
        });
    }

    public function groupByNodeXPath(Nodes $nodes)
    {
        $log = $this->log;
        return $this->groupByCallable($nodes, function ($node) {
            return $this->toXPath($node);
        });
    }

}