<?php

namespace WhatTheField\Tests;

use WhatTheField\Fluent\Utils;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    const DS = DIRECTORY_SEPARATOR;

    public function getFoodiePath()
    {
        return __DIR__.self::DS.'testfeeds'.self::DS.'foodie.xml';
    }
    public function getFoodieMultilevelPath()
    {
        return __DIR__.self::DS.'testfeeds'.self::DS.'foodieMultilevel.xml';
    }

    public function setUp()
    {
        Utils::init();
    }

}
