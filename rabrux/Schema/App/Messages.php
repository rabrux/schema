<?php


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
