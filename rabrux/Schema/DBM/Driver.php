<?php

namespace Schema\DBM;

/**
 *
 */
class Driver
{

  private $driver = null;

  function __construct($driver)
  {
    $this->driver = $driver;
  }

  protected function create() {
    $this->flushStack();
    return $this;
  }

  protected function exists() {
    $this->flushStack();
    return $this;
  }
}
