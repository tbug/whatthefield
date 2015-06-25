<?php

namespace WhatTheField\Provider;

use WhatTheField\Fluent\Loader;
use FluentDOM;

abstract class AbstractProvider implements IProvider
{
    public function __construct()
    {
        FluentDOM::setLoader(new Loader);
    }
}
