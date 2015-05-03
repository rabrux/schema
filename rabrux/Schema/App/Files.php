<?php

namespace Schema\App;

class Files {

  /**
   * App working directory
   * @var string
   */
  public static $directory;

  /**
   * Constructor, set directory
   */
  function __construct($directory)
  {
    $this->directory = $directory;
  }

  /**
   * Get working directory
   * @return string
   */
  public function getDirectory() {
    return $this->directory . DIRECTORY_SEPARATOR;
  }

  /**
   * Save JSON to file
   */
  public function dumpJSON($file, $data) {
    if ( file_put_contents($this->getDirectory() . "schemas/$file", json_encode($data, JSON_PRETTY_PRINT)) )
      return true;
    return false;
  }

  public function readJSON($file) {
    $json = json_decode( file_get_contents($this->getDirectory() . "schemas/$file") );
    if ($json)
      return $json;
    return false;
  }

  /**
   * Return true if schema directory exists
   * @return bool
   */
  public function schemaDirExists() {
    $dir = $this->getDirectory . 'schemas';

    if (!file_exists($dir) && !is_dir($dir))
      return false;

    return true;
  }

  /**
  * Return all migrations in schemas directory
  * @return array
  */
  public function getMigrations() {
    $dir = opendir( $this->getDirectory() . 'schemas' );
    while ($file = readdir($dir)) {
      if (preg_match('/^(?!\.).*\.json$/', $file))
        $schemas[] = pathinfo($file)['filename'];
    }
    return $schemas;
  }

  /**
   * Create schema directory, if it is created returns true
   * @return bool
   */
  public function createSchemaDir() {
    if ( !$this->schemaDirExists() ) {
      mkdir( $this->getDirectory . 'schemas' );
      return true;
    }
    return false;
  }

  /**
   * Return true if config file exists
   * @return bool
   */
  public function configExists() {

    if (!file_exists($this->getDirectory() . 'schemas/.conf.json'))
      return false;

    return true;
  }

  /**
   * Return true if $schema migration exists
   * @param string
   * @return bool
   */
  public function schemaExists($schema) {

    if (!file_exists($this->getDirectory() . "schemas/$schema.json"))
      return false;

    return true;
  }

}
