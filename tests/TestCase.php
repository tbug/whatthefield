<?php

namespace WhatTheField\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    const DS = DIRECTORY_SEPARATOR;

    public function getFoodiePath()
    {
        return __DIR__.self::DS.'testfeeds'.self::DS.'foodie.xml';
    }

 
}
