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

        // try some rules that apply to the id field
        $field = new FieldDiscovery([], [
            new Score\IsUnique(),
            new Score\Boost(-1, [
                new Score\MatchFilterValidate(FILTER_VALIDATE_URL),
                new Score\Max([
                    new Score\Constant(0),
                    new Score\IsDecimal(),
                ]),
            ]),
            // // tie breaker on ancestor level 
            new Score\Boost(-0.001, [
                new Score\AncestorCount(),
            ]),
            // // tie breaker, by word count. More words == less likely to be the id
            new Score\Boost(-0.001, [
                new Score\MatchCount('/\s+/S'),
            ]),
            // // tie breaker, not a number
            new Score\Boost(-0.01, [
                new Score\IsMatch('/[^\d]+/S'),
            ]),
            // tie breaker, greater than common max numeric postal code
            new Score\Boost(0.1, [
                new Score\IsGreaterThan(99999)
            ]),
        ]);

        $valueNodes = $provider->getQuery()->find('/items/item//*[text()]');
        
        $discovered = $field->discover($valueNodes);

        $this->assertEquals('/items/item/id', $discovered);
    }
}