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
        return $root;
    }

}
