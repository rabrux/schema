<?php

namespace Schema;

// use Schema\Database\Structure;
use Schema\App\Cli;
// use Schema\App\Files;
// use Schema\App\Messages;

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

  public function run() {
    $this->cli->run();
  }

  // public function __call($table, array $args) {
  //   var_dump( $table );
  //   var_dump( $args );
  //   \Schema\Database\Structure::algo();
  // }
  //
  // public function __set($name, $value) {
  //   $this->$name = $value;
  //   return $this;
  // }
  //
  // public function createTable() {
  //   var_dump( __METHOD__ );
  // }

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
