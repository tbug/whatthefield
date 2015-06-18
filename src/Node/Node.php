<?php

namespace WhatTheField\Node;

class Node extends AbstractNode
{
    public function findByKey($key)
    {
        return $this->find(function ($child) use ($key) {
            if ($child->getKey() === $key) {
                return true;
            }
        });
    }

    public function findByValue($value)
    {
        return $this->find(function ($child) use ($value) {
            if ($child->getValue() === $value) {
                return true;
            }
        });
    }

    /**
     * return children where child contain attribute
     * with key $key and value $value.
     * If $value is null, all values match,
     * of $key is null, all keys match
     */
    public function findByAttribute($key, $value=null)
    {
        return $this->find(function ($child) use ($key, $value) {
            $keyMatch = $key === null || $child->hasAttr($key);
            $valueMatch = $value === null;
            if ($value !== null) {
                if ($key === null) {
                    foreach ($child->getAttributes() as $attrKey => $attrValue) {
                        if ($attrValue === $value) {
                            $valueMatch = true;
                            break;
                        }
                    }
                } else {
                    $valueMatch = $child->getAttr($key) === $value;
                }
            }

            return $keyMatch && $valueMatch;
        });
    }

    public function query($query)
    {
        $out = $this;
        $qp = new QueryParser($query);
        foreach ($qp->operations() as $op) {
            list ($method, $args) = $op;
            $out = call_user_func_array([$out, $method], $args);
        }
        return $out;
    }
}

