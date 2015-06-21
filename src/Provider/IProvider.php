<?php

namespace WhatTheField\Provider;

interface IProvider
{
    /**
     * Get the provider document as a FluentDOM\Query
     * @return FluentDOM\Query
     */
    public function getQuery();
}