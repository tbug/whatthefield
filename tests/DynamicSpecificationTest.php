<?php

namespace WhatTheField\Tests;

use WhatTheField\Provider\XMLProvider;
use WhatTheField\Specification\DynamicSpecification;

class DynamicSpecificationTest extends TestCase
{
    public function testCollectionPath()
    {
        $feedPath = $this->getFoodiePath();
        $provider = new XMLProvider($feedPath);
        $ds = new DynamicSpecification($provider);

        $this->assertEquals('/items/item', $ds->getCollectionXPathExpr());
    }
}
