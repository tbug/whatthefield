<?php

namespace WhatTheField\Tests;

use WhatTheField\Provider\XMLProvider;
use WhatTheField\Specification\DynamicSpecification;
use WhatTheField\QueryUtils;
use WhatTheField\Feed;
use WhatTheField\Score;

use WhatTheField\Discovery\CollectionDiscovery;
use WhatTheField\Discovery\FieldDiscovery;

class DynamicSpecificationTest extends TestCase
{
    public function testCollectionPath()
    {
        return;
        $feedPath = $this->getFoodieMultilevelPath();
        $provider = new XMLProvider($feedPath);
        $feed = new Feed(
            $provider,
            new CollectionDiscovery(),
            [
                // an ID is unique, 1 word, not decimal and not a URL
                'id' => new FieldDiscovery([], [
                    new Score\Unique(),     // IS unique, should preferably not occour twice in the feed
                    // new Score\WordCount(1), // 1 word
                    // new Score\Not(new Score\Decimal()), // not decimal
                    // new Score\Not(new Score\URL()), // not url
                ])
            ]
        );

        var_dump($feed->discoverFieldXPath('id'));
        return;







        $ds = new DynamicSpecification($provider);

        $this->assertEquals('/items/item', $ds->getCollectionXPathExpr());

        list($seen, $missed, $hashes, $bytes, $words) = (new QueryUtils)
            ->getNodeNameStats(
                $provider->getQuery(),
                $ds->getCollectionXPathExpr()
            );


        $potentialIds = [];
        foreach ($seen as $key => $seenCount) {
            if (!isset($missed[$key])) {
                $potentialIds[] = $key;
            }
        }
        $score = [];
        foreach ($potentialIds as $key) {
            $sum = array_sum(array_map(function ($el) {
                return $el-1;
            }, $hashes[$key]));
            $uniquenessScore = ($sum === 0) ? 0 : $sum / count($hashes[$key]);


            $count = count($words[$key]);
            $sum = array_sum($words[$key]);
            $wordScore = $sum / $count;

            $score[$key] = $uniquenessScore + $wordScore;
        }

        asort($score);

        $possible = array_filter($score, function ($score) {
            return $score === 0;
        });
        print "\n";
        print "\n";
        print "\n";
        print "Collection path: {$ds->getCollectionXPathExpr()}\n";
        print "\n";
        print "\nThe following keys might be the item identity:\n";
        foreach ($possible as $key => $value) {
            print " - $key\n";
        }
        print "\n";
        print "\n";



        $idSpec = new FieldSpec([
            new Score\Unique(),     // IS unique
            new Score\WordCount(1), // 1 word
            new Score\Not(new Score\Decimal()), // not decimal
            new Score\Not(new Score\URL()), // not url
        ]);


    }
}
