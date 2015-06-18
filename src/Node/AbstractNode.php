<?php

namespace WhatTheField\Node;

/**
 * Adds key=>value [attributes] to a node
 * (XML node-ish)
 * It also provides for a semi-decent __toString to help debugging a bit.
 * This is the node class you'd probably want to extend for very basic functionality.
 */
abstract class AbstractNode extends Collection
{
    protected $key;
    protected $value;
    protected $attributes;

    public function __construct ($key, $value=null, array $attributes=[], array $children=[])
    {
        $this->key = $key;
        $this->value = $value;
        $this->attributes = $attributes;
        parent::__construct($children);
    }

    /**
     * This function returns a new copy of the collection object,
     * but with new children.
     */
    protected function copyWithNewChildren($children)
    {
        $me = get_class($this);
        return new $me($this->key, $this->value, $this->attributes, $children);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttr($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    public function hasValue()
    {
        return $this->value !== null;
    }

    public function hasAttr($key)
    {
        return isset($this->attributes[$key]);
    }

    public function __toString()
    {
        return $this->___toString();
    }

    public function getKeys()
    {
        $keys = [];
        foreach ($this as $child) {
            $keys[] = $child->getKey();
        }
        return $keys;
    }

    protected function ___toString($indent='')
    {
        $key = $this->getKey();
        $value = $this->getValue();
        if ($value === null) {
            $dataString = "$key";
        } else {
            $dataString = "$key=\"$value\"";
        }

        $attributes = $this->getAttributes();
        $attrPres = [];
        foreach ($this->attributes as $attrKey => $attrValue) {
            $attrPres[] = "$attrKey=\"$attrValue\"";
        }
        $attrString = implode(', ', $attrPres);

        $out[] = sprintf('%s<(%s) [%s]', $indent, $dataString, $attrString);
        if (count($this)) {
            foreach ($this as $child) {
                $out[] = $child->___toString($indent.' |  ');
            }
            $out[] = sprintf('%s \>', $indent);
            return implode("\n", $out);
        } else {
            $out[] = sprintf('>');
            return implode('', $out);
        }
    }

}
