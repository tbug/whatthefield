<?php

namespace WhatTheField\Reader;

use WhatTheField\Node\Node;


class XMLReader implements IReader
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function read()
    {
        if (strlen($this->data) < 1024 && is_file($this->data)) {
            $root = simplexml_load_file($this->data);
        } else {
            $root = simplexml_load_string($this->data);
        }
        return $this->convert($root);
    }


    protected function convert($element)
    {
        $attrs = [];
        foreach ($element->attributes() as $attrKey => $attrValue) {
            $attrs[$attrKey] = (string)$attrValue;
        }

        $value = $element->__toString();
        if ($element->count() > 0) {
            // non-leaf, trim string value down, if empty, return null (no value)
            $trimmed = trim($value);
            if (strlen($trimmed) === 0) {
                $value = null;;
            } else {
                $value = $trimmed;
            }
        }

        $children = [];
        foreach ($element->children() as $child) {
            $children[] = $this->convert($child);
        }
        $node = new Node($element->getName(), $value, $attrs, $children);
        return $node;
    }

}
