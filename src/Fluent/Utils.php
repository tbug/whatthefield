<?php
namespace WhatTheField\Fluent;

use FluentDOM\Nodes;
use DOMDocument;
use DOMElement;
use DOMAttr;
use DOMNode;

use FluentDOM;

class Utils
{
    public static function init()
    {
        FluentDOM::setLoader(new Loader);
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
    static public function getElementGroupingConditions(DOMElement $el)
    {
        $attributes = $el->attributes;
        if ($attributes === null) {
            return ''; // easy, no attributes, no conditions
        }
        $conditions=[];
        foreach ($attributes as $attrName => $attrObj) {
            if ((bool)round(self::scoreGroupAttribute($attrObj))) {
                $conditions[] = '@' . $attrName . '="' . (string)$attrObj . '"';
            }
        }
        if (count($conditions) > 0) {
            return '[' . implode(' and ', $conditions) . ']';
        } else {
            return '';
        }
    }


    static private $groupCache = [];
    /**
     * @return float number from 0 to 1 where 1 is most likely to be a grouping attribute 
     */
    static protected function scoreGroupAttribute(DOMAttr $attr)
    {
        $path = self::toXPath($attr, false);
        $attrName = $attr->nodeName;

        // get stats on how attribute is used in document.
        if (!isset(self::$groupCache[$path])) {
            $document = $attr->ownerDocument;
            $samePathNodes = FluentDOM($document)->find($path);
            $parentNodeCount = count(FluentDOM($document)->find(self::toXPath($attr->parentNode, false)));

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
            self::$groupCache[$path] = [$parentNodeCount, $totalCount, $valueDistribution];
        } else {
            list($parentNodeCount, $totalCount, $valueDistribution) = self::$groupCache[$path];
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
    static public function getParents(DOMNode $node)
    {
        $ancestors = [];
        do {
            $ancestors[] = $node;
        } while ($node = $node->parentNode);
        return $ancestors;
    }

    static public function toXPath(DOMNode $node, $guessGroupingAttributes=true)
    {
        $segments = [];
        foreach (array_reverse(self::getParents($node)) as $node) {
            $segments[] = self::toXPathSegmentName($node, $guessGroupingAttributes);
        }
        return implode('/', $segments);
    }

    static public function toXPathSegmentName(DOMNode $node, $guessGroupingAttributes=true)
    {
        if ($node instanceof DOMDocument) {
            $segment = ''; // will be joined by /, so correctly be the root of the document
        } elseif ($node instanceof DOMElement) {
            if ($guessGroupingAttributes) {
                $elementGroupingConditionString = self::getElementGroupingConditions($node);
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

    static public function groupByCallable(Nodes $nodes, callable $fn)
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

    static public function groupByContentHash(Nodes $nodes)
    {
        return self::groupByCallable($nodes, function ($node) {
            return hash('adler32', $node->textContent);
        });
    }

    static public function groupByNodeXPath(Nodes $nodes)
    {
        return self::groupByCallable($nodes, function ($node) {
            return self::toXPath($node);
        });
    }

}