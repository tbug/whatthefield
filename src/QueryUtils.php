<?php

namespace WhatTheField;

use FluentDOM\Query;

class QueryUtils
{
    /**
     * Return a map of all named xpaths and the number of siblings they have with the same name, at max.
     * This is useful to detect collections of things.
     */
    public function getMaxSibCount(Query $document) {
        $maxSibCount = [];
        foreach ($this->getAllNameXPaths($document) as $path) {
            $fd = FluentDOM($document);
            foreach ($fd->find($path) as $par) {
                $nameCount = [];
                foreach (FluentDOM($par)->children() as $child) {
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

    /**
     * Return an array of all available xpaths for each named node.
     * <items><item></item><item></item></items>
     * will return
     * - /items
     * - /items/item
     * @return array[string]
     */
    public function getAllNameXPaths(Query $document)
    {
        $namePaths = [];
        foreach ($document->getDocument()->find('.//*|.') as $node) {
            $parents = FluentDOM($node)->parents()->map(function ($el) {
                return $el->nodeName;
            });
            $parents = array_reverse($parents);
            $parents[] = $node->nodeName;
            $xpath = '/'.implode('/', $parents);
            $namePaths[$xpath] = true;
        }
        return array_keys($namePaths);            
    }

}