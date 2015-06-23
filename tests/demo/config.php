<?php

use WhatTheField\Discovery\FieldDiscovery;
use WhatTheField\Score;

return [
    // an ID is unique, 1 word, not decimal and not a URL
    'id' => new FieldDiscovery([], [
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
    ])
];