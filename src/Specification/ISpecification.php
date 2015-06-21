<?php

namespace WhatTheField\Specification;


interface ISpecification
{
    /**
     * Get an xpath that selects all collection elements in the feed
     * @return string
     */
    public function getCollectionXPathExpr();
}
