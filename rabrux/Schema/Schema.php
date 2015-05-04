<?php

namespace Schema;

use Schema\App\Cli;

/**
 *
 */
class Schema
{

  public static $cli;

  function __construct($argv = [], $directory = '')
  {
    spl_autoload_register(__NAMESPACE__ . "\\Schema::autoload");
    $this->cli = new Cli($argv, $directory);
  }

  /**
   * Run Commands
   */
  public function run() {
    $this->cli->run();
  }

  /**
   * autoload class files
   * @param string classname
   */
  public static function autoload($classname) {

    $pathFile = str_replace('\\', '/', str_replace(__NAMESPACE__, '', __DIR__) . $classname . '.php' );

    if (is_readable($pathFile)) {
      require_once $pathFile;
    } else {
      var_dump($classname); exit;
    }
  }

}
