<?php
/**
 * Schema Cli Migration - PHP command line schema migration
 *
 * @author      Raúl Salvador Andrade <rsalvador@wat.mx>
 * @copyright   2015 Raúl Salvador Andrade
 * @link        http://www.wat.mx
 * @license     http://www.wat.mx/license
 * @version     1.0.0
 * @package     Schema
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
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

  public function readJSON($file, $array = false) {
    $json = json_decode( file_get_contents($this->getDirectory() . "schemas/$file"), $array );
    if ($json)
      return $json;
    return false;
  }

  public function removeSchema($schema) {
    if ( unlink($this->getDirectory() . "schemas/$schema.json") )
      return true;
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
