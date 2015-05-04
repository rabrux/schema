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
