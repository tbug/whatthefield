<?php

namespace WhatTheField;

use FluentDOM;
use FluentDOM\Query;
use FluentDOM\Nodes;
use DOMNode;
use DOMDocument;

class QueryUtils
{
    static protected $singleton;
    static public function instance()
    {
        if (!self::$singleton) {
            self::$singleton = new self; 
        }
        return self::$singleton;
    }

    /**
     * Return a map of all named xpaths and the number of siblings they have with the same name, at max.
     * This is useful to detect collections of things.
     */
    public function getMaxSibCount(Query $document) {
        $maxSibCount = [];
        foreach (array_keys($this->toXpathCounts($document->find('.//*|.'))) as $path) {
            foreach ($document->find($path) as $par) {
                $nameCount = [];
                foreach ($par as $child) {
                    $n = $path.'/'.$child->nodeName;
                    $nameCount[$n] = isset($nameCount[$n]) ? $nameCount[$n] + 1 : 1;
                }
                foreach ($nameCount as $n => $count) {
                    if (!isset($maxSibCount[$n])) {
                        $maxSibCount[$n] = 0;
                    }
                    $maxSibCount[$n] = max($maxSibCount[$n], $count);
                }
            }
        }
        return $maxSibCount;
    }

    public function toXpathCounts(Nodes $nodes)
    {
        $xpaths = [];
        foreach ($nodes as $node) {
            $key = $this->toXPath($node);
            if (!isset($xpaths[$key])) {
                $xpaths[$key] = 1;
            } else {
                $xpaths[$key] += 1;
            }
        }
        return $xpaths;
    }

    public function toXPath(DOMNode $node)
    {
        $ancestors = [];
        do {
            if ($node instanceof DOMDocument) {
                break;
            }
            $ancestors[] = $node->nodeName;
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

}