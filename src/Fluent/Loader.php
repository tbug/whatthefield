<?php

namespace WhatTheField\Fluent;

use FluentDOM\Loaders;


class Loader extends Loaders
{

  public function __construct()
  {
    parent::__construct(
      [
        new Xml(),
      ]
    );
  }
}