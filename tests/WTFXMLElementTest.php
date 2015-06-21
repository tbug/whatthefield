<?php

namespace WhatTheField;

use WhatTheField\Reader\XMLReader;
use WhatTheField\WTFXMLElement;
use WhatTheField\Guesser\Guesser;


class WTFXMLElementTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        #$fd = FluentDOM(__DIR__.'/feed.xml');
#        $fd = FluentDOM($this->testXML);



        // function getAllPaths($document)
        // {
        //     $fd = FluentDOM($document);
        //     $namePaths = [];
        //     foreach ($fd->find('//*') as $node) {
        //         $parents = FluentDOM($node)->parents()->map(function ($el) {
        //             return $el->nodeName;
        //         });
        //         $parents = array_reverse($parents);
        //         $parents[] = $node->nodeName;
        //         $xpath = '/'.implode('/', $parents);
        //         $namePaths[$xpath] = true;
        //     }
        //     return array_keys($namePaths);            
        // }

        // function getMaxSibCount($document) {
        //     $maxSibCount = [];
        //     foreach (getAllPaths($document) as $path) {
        //         $fd = FluentDOM($document);
        //         foreach ($fd->find($path) as $par) {
        //             $nameCount = [];
        //             foreach (FluentDOM($par)->children() as $child) {
        //                 $n = $path.'/'.$child->nodeName;
        //                 $nameCount[$n] = isset($nameCount[$n]) ? $nameCount[$n] + 1 : 1;
        //             }
        //             foreach ($nameCount as $n => $count) {
        //                 if (!isset($maxSibCount[$n])) {
        //                     $maxSibCount[$n] = 0;
        //                 }
        //                 $maxSibCount[$n] = max($maxSibCount[$n], $count);
        //             }
        //         }
        //     }
        //     return $maxSibCount;
        // }

        // var_dump(getMaxSibCount(__DIR__.'/feed.xml'));

    }



 
}
