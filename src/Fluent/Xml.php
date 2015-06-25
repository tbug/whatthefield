<?php
namespace WhatTheField\Fluent;

use FluentDOM\Loadable;
use FluentDOM\Loader\Supports;

/**
 * Load a DOM document from a xml file or string
 */
class Xml implements Loadable
{

    use Supports;

    /**
     * @return string[]
     */
    public function getSupported()
    {
        return array('xml', 'application/xml', 'text/xml');
    }

    /**
     * @see Loadable::load
     * @param string $source
     * @param string $contentType
     * @param array $options
     * @return Document|NULL
     */
    public function load($source, $contentType, array $options = [])
    {
        if ($this->supports($contentType)) {
            $dom = new Document();
            $dom->preserveWhiteSpace = FALSE;
            if ($this->startsWith($source, '<')) {
                $dom->loadXml($source);
            } else {
                $dom->load($source);
            }
            return $dom;
        }
        return NULL;
    }

}