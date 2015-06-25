<?php


namespace WhatTheField\Fluent;

class Document extends \FluentDOM\Document
{
    use NodeTrait;

    /**
    * Map dom node classes to extended descendants.
    * @var array
    */
    private $_classes = [
        'DOMDocument' => '\\Document',
        'DOMAttr' => '\\Attribute',
        'DOMCdataSection'=> '\\CdataSection',
        'DOMComment'=> '\\Comment',
        'DOMElement'=> '\\Element',
        'DOMProcessingInstruction'=> '\\ProcessingInstruction',
        'DOMText'=> '\\Text',
        'DOMDocumentFragment'=> '\\DocumentFragment'
    ];

    public function __construct($version = '1.0', $encoding = 'UTF-8') {
        parent::__construct($version, $encoding);
        foreach ($this->_classes as $superClass => $className) {
            $this->registerNodeClass($superClass, __NAMESPACE__.$className);
        }
    }
}
