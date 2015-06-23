<?php

namespace WhatTheField\Tests\Score;

use WhatTheField\Tests\TestCase;
use WhatTheField\Provider\XMLProvider;
use WhatTheField\Score;

use WhatTheField\Discovery\FieldDiscovery;

class FieldDiscoveryTest extends TestCase
{
    public function testCollectionPath()
    {
        $feedPath = $this->getFoodieMultilevelPath();
        $provider = new XMLProvider($feedPath);

        $field = new FieldDiscovery([], [
            new Score\Unique(),
            new Score\Boost(0.1, new Score\Named(['id'])),
        ]);

        $valueNodes = $provider->getQuery()->find('/items/item//*[text()]');
        
        $discovered = $field->discover($valueNodes);

        $this->assertEquals('/items/item/id', $discovered);
    }
}
