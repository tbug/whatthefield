<?php
namespace WhatTheField\Fluent;


use FluentDOM\Nodes;
use DOMNodeList;
use DOMDocument;
use DOMElement;
use DOMAttr;
use DOMNode;


trait NodeTrait
{
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

    public function getMaxSibCount(Nodes $document)
    {
        if ($this instanceof DOMDocument) {
            $root = $this->documentElement;
        } else {
            $root = $this->ownerDocument->documentElement;
        }
        $maxSibCount = $this->maxSibRecursive($root->childNodes, '/'.$root->nodeName);
        return $maxSibCount;
    }

    public function toXPathSegmentName($guessGrouping=false)
    {
        return Utils::toXPathSegmentName($this, $guessGrouping);
    }

    public function toXPath($guessGrouping=false)
    {
        return Utils::toXPath($this, $guessGrouping);
    }

    public function getParents()
    {
        return Utils::getParents($this);
    }


}
