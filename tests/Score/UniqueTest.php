<?php

namespace WhatTheField\Tests\Score;

use WhatTheField\Tests\TestCase;
use WhatTheField\Provider\XMLProvider;
use WhatTheField\Score\IsUnique;

use WhatTheField\Discovery\CollectionDiscovery;
use WhatTheField\Discovery\ValueDiscovery;

class UniqueTest extends TestCase
{
    public function testCollectionPath()
    {
        $feedPath = $this->getFoodieMultilevelPath();

        // id nodes should score 1
        $scorer = new IsUnique();
        $this->assertEquals(
            1,
            $scorer->__invoke(FluentDOM($feedPath)->find('/items/item[position()=1]/id')[0])
        );

        // city should score 0 (all city fields are the same)
        $scorer = new IsUnique();
        $this->assertEquals(
            0,
            $scorer->__invoke(FluentDOM($feedPath)->find('/items/item[position()=1]/city')[0])
        );

        // score with an exponent
        $scorer = new IsUnique(2);
        $this->assertEquals(
            1,
            $scorer->__invoke(FluentDOM($feedPath)->find('/items/item[position()=1]/id')[0])
        );
        $scorer = new IsUnique(2);
        $this->assertEquals(
            0,
            $scorer->__invoke(FluentDOM($feedPath)->find('/items/item[position()=1]/city')[0])
        );



    }
}
