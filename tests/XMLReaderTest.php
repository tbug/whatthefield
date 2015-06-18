<?php

namespace WhatTheField;

use WhatTheField\Reader\XMLReader;

class XMLReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $testXML;
    public function setUp ()
    {
        $this->testXML = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<items>
    <item id="1">
        <foo>foo1</foo>
        <bar>bar1</bar>
        <baz><foobarbaz lol="1" />baz1</baz>
    </item>
    <item id="2">
        <foo>foo2</foo>
        <bar>bar2</bar>
        <baz><foobarbaz lol="2" />
        baz2</baz>
    </item>
    <item id="3">
        <foo>foo3</foo>
        <bar>bar3</bar>
        <baz><foobarbaz lol="3" />baz3
        </baz>
    </item>
</items>
EOD;
    }

    public function tearDown ()
    {
    }

    public function testNodeBuild()
    {
        $reader = new XMLReader($this->testXML);
        $structure = $reader->read();

        $this->assertEquals('items', $structure->getKey());

        foreach ($structure as $i => $child) {
            $id = (string)($i+1);
            $this->assertEquals('item', $child->getKey());
            $this->assertEquals($id, $child->getAttr('id'));
            $this->assertFalse($child->hasValue());

            $foo = $child->findByKey('foo')[0];
            $bar = $child->findByKey('bar')[0];
            $baz = $child->findByKey('baz')[0];
            $nope = $child->findByKey('nope');

            $this->assertEquals(0, count($nope));

            $this->assertEquals('foo'.$id, $foo->getValue());
            $this->assertEquals('bar'.$id, $bar->getValue());
            $this->assertEquals('baz'.$id, $baz->getValue());

            $this->assertEquals(0, count($foo));
            $this->assertEquals(0, count($bar));
            $this->assertEquals(1, count($baz));

            $this->assertEquals([], $bar->getAttributes());
            $this->assertEquals([], $bar->getAttributes());
            $this->assertEquals([], $baz->getAttributes());


            $foobarbaz = $baz->findByKey('foobarbaz')[0];
            $this->assertTrue($foobarbaz->hasAttr('lol'));
            $this->assertFalse($foobarbaz->hasAttr('nope'));
            $this->assertEquals($id, $foobarbaz->getAttr('lol'));
        }

        $toStringOutput = $structure->__toString();
    }

    public function testGetChildrenByKeyMutli()
    {
        $reader = new XMLReader($this->testXML);
        $root = $reader->read();
        $children = $root->findByKey('item');
        $this->assertEquals(3, count($children));
        $newChildrenCopy = $children->findByKey('item');
        $this->assertEquals(3, count($newChildrenCopy));

        // the children and newChildrenCopy should not be the same
        // objects. Very important. Check that.
        for ($i=0; $i < count($newChildrenCopy); $i++) { 
            $this->assertNotSame($children[0], $newChildrenCopy[0]);
        }
    }
}
