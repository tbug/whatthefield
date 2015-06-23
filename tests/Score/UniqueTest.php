<?php

namespace WhatTheField\Tests\Score;

use WhatTheField\Tests\TestCase;
use WhatTheField\Provider\XMLProvider;
use WhatTheField\Score\IsUnique;

use WhatTheField\Discovery\CollectionDiscovery;
use WhatTheField\Discovery\FieldDiscovery;

class UniqueTest extends TestCase
{
    public function testCollectionPath()
    {
        $feedPath = $this->getFoodieMultilevelPath();
        $provider = new XMLProvider($feedPath);

        // id nodes should score 1
        $scorer = new IsUnique();
        $this->assertEquals(
            1,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/id')[0])
        );

        // city should score 0 (all city fields are the same)
        $scorer = new IsUnique();
        $this->assertEquals(
            0,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/city')[0])
        );

        // score with an exponent
        $scorer = new IsUnique(2);
        $this->assertEquals(
            1,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/id')[0])
        );
        $scorer = new IsUnique(2);
        $this->assertEquals(
            0,
            $scorer->__invoke($provider->getQuery()->find('/items/item[position()=1]/city')[0])
        );



    }
}
