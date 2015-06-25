<?php

namespace WhatTheField\Provider;

interface IProvider
{
    /**
     * Get the provider document as a \DOMDocument
     * @return \DOMDocument
     */
    public function getDocument();
}