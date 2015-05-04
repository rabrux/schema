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

/**
 *
 */
class Messages
{

  // Define colors to application messages
  const SUCCESS   = "SUCCESS";
  const ERROR     = "ERROR";
  const WARNING   = "WARNING";
  const QUESTION  = "QUESTION";
  const OPTION  = "OPTION";
  const UNKNOWN   = "UNKNOWN";
  const CONSOLE   = "CONSOLE";
  const PROMPT    = "PROMPT";
  const OUT       = "OUT";
  const NULL      = "NULL";

  const COLORS = array(
    'SUCCESS'   => "\033[0;32m",
    'ERROR'     => "\033[1;31m",
    'WARNING'   => "\033[0;33m",
    'UNKNOWN'   => "\033[1;32m",
    'CONSOLE'   => "\033[0m",
    'PROMPT'    => "\033[1;34m",
    'OUT'       => "\033[0;37m"
  );

  /**
   * Return formated message string
   * @return string
   */
  public function buildMessage($type = null, $message = null,  $default = null) {

    switch (strtolower($type)) {

      case 'success':
        return Messages::COLORS['SUCCESS'] . "success: $message.\n" . Messages::COLORS['CONSOLE'];
        break;

      case 'error':
        return Messages::COLORS['ERROR'] . "error: $message.\n" . Messages::COLORS['CONSOLE'];
        break;

      case 'warning':
        return Messages::COLORS['WARNING'] . "warning: $message.\n" . Messages::COLORS['CONSOLE'];
        break;

      case 'question':
        return Messages::COLORS['CONSOLE'] . "$message" . Messages::COLORS['SUCCESS'] . '? ' . Messages::COLORS['CONSOLE'] . ($default ? '(' . Messages::COLORS['WARNING'] . $default . Messages::COLORS['CONSOLE'] . '): ' : ': ' ) . Messages::COLORS['PROMPT'];
        break;

      case 'option':
        return Messages::COLORS['CONSOLE'] . "[ " . Messages::COLORS['WARNING'] . $default . Messages::COLORS['CONSOLE'] . " ] $message\n";
        break;

      case 'unknown':
        return Messages::COLORS['UNKNOWN'] . "unknown command $message.\n" . Messages::COLORS['CONSOLE'];
        break;

      case 'prompt':
        return Messages::COLORS['SUCCESS'] . '? ' . Messages::COLORS['CONSOLE'] . "$message" . ($default ? ' (' . Messages::COLORS['WARNING'] . $default . Messages::COLORS['CONSOLE'] . ')' : '') . ": " . Messages::COLORS['PROMPT'];
        break;

      case 'out':
        return Messages::COLORS['SUCCESS'] . "- " . Messages::COLORS['CONSOLE'] . "$message\n";
        break;

      case 'null':
        return Messages::COLORS['CONSOLE'];
        break;

      default:
        return Messages::COLORS['SUCCESS'] . "$message\n" . Messages::COLORS['CONSOLE'];
        break;

    }

  }

}
