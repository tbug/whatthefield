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

class QueryUtils
{
    public function __construct($logger=null)
    {
        $this->log = $logger;
    }

    static protected $singleton;
    static public function instance()
    {
        if (!self::$singleton) {
            self::$singleton = new self(); 
        }
        return self::$singleton;
    }


    protected function allPossibleXPaths(DOMNode $node, $path='')
    {
        if (!($node instanceof \DOMElement)) {
            return [];
        }
        $name = $node->nodeName;
        $path = $path.'/'.$name;
        $children = $node->childNodes;
        $result = [
            $path => 1
        ];
        if ($children) {
            foreach ($children as $child) {
                foreach ($this->allPossibleXPaths($child, $path) as $key => $value) {
                    if (isset($result[$key])) {
                        $result[$key] += $value;
                    } else {
                        $result[$key] = $value;
                    }
                }
            }
        }
        return $result;
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

    public function toXPath(DOMNode $node)
    {
        $ancestors = [];
        do {
            if ($node instanceof DOMDocument) {
                break;
            } elseif ($node instanceof DOMElement) {
                $ancestors[] = $node->nodeName;
            } elseif ($node instanceof DOMAttr) {
                $ancestors[] = '@'.$node->nodeName;
            } else {
                throw new Exception('Node type not known: '.$node->nodeType);
            }
        } while ($node = $node->parentNode);
        return '/'.implode('/', array_reverse($ancestors));
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