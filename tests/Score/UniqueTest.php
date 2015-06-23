<?php

namespace WhatTheField\Tests\Score;

use WhatTheField\Tests\TestCase;
use WhatTheField\Provider\XMLProvider;
use WhatTheField\Score\Unique;

use WhatTheField\Discovery\CollectionDiscovery;
use WhatTheField\Discovery\FieldDiscovery;

class UniqueTest extends TestCase
{
    public function testCollectionPath()
    {
        $feedPath = $this->getFoodieMultilevelPath();
        $provider = new XMLProvider($feedPath);

        // id nodes should score 1
        $scorer = new Unique();
        $this->assertEquals(
            1,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/id')[0])
        );

        // city should score 0 (all city fields are the same)
        $scorer = new Unique();
        $this->assertEquals(
            0,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/city')[0])
        );

        // score with an exponent
        $scorer = new Unique(2);
        $this->assertEquals(
            1,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/id')[0])
        );
        $scorer = new Unique(2);
        $this->assertEquals(
            0,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/city')[0])
        );



    }
}
