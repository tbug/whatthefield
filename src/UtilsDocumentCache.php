<?php

namespace WhatTheField;

use DOMDocument;
use DOMNode;
use FluentDOM\Nodes;

/**
 * Used for caching information for a DOMDocument.
 * the static method instance() Accepts DOMNode objects
 * and automatically converts the identity to the nodes associated DOMDocument,
 * meaning that you can retrieve the cache for a document via any of it's nodes
 * with UtilsDocumentCache::instance($myNode);
 */
class UtilsDocumentCache
{
    static private $_instances = [];
    static public function instance($identity)
    {
        if (is_object($identity)) {
            if ($identity instanceof DOMNode && !($identity instanceof DOMDocument)) {
                // if we pass a DOMNode, grab the document instead
                $identity = $identity->ownerDocument;
            } elseif ($identity instanceof Nodes) {
                // or if node collection, again, grab document.
                $identity = $identity->getDocument();
            }
            $identity = spl_object_hash($identity);
        }
        if (!isset(self::$_instances[$identity])) {
            self::$_instances[$identity] = new self();
        }
        return self::$_instances[$identity];
    }

    private $cache;

    public function __construct ()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->cache = [];
    }

    public function cached($key, callable $valueCreator, $args=[])
    {
        if (!$this->exists($key)) {
            return $this->set($key, call_user_func_array($valueCreator, $args));
        }
        return $this->get($key);
    }

    public function exists($key)
    {
        return isset($this->cache[$key]);
    }

    public function get($key, $default=null)
    {
        return isset($this->cache[$key]) ? $this->cache[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->cache[$key] = $value;
        return $value;
    }

}
