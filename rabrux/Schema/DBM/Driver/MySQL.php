<?php

namespace Schema\DBM\Driver;
use Schema\DBM\Driver;

/**
 *
 */
class MySQL extends Driver
{

  private $driver;
  private $database;
  private $table;
  private $stack = array();

  private $func = [];

  function __construct($driver)
  {
    $this->driver = $driver;
  }

  /**
   * Interceptor to push in stack
   */
  public function __call($method, $params) {
    if (method_exists($this, $method)) {
      array_push($this->stack, $method);
      return call_user_func_array(array($this, $method), $params);
    }
    return $this;
  }

  protected function database($database) {
    $this->database = $database;
    return $this;
  }

  protected function create() {

    $action = array_shift($this->stack);

    if ( parent::create() ) {
      switch ($action) {
        case 'database':
          if ( $this->driver->query("CREATE DATABASE IF NOT EXISTS $this->database; USE $this->database;") )
            return true;
          break;
      }
    }

    return false;

  }

  protected function exists() {

    $action = array_shift($this->stack);

    if ( parent::exists() ) {

      switch ($action) {
        case 'database':
          $databases = $this->driver->query("SHOW DATABASES")->fetchAll();
          foreach ($databases as $item) {
            if ( $this->database == $item['Database'] )
              return true;
          }
          break;
      }

    }

    return false;
  }

  protected function migrate($data) {
    // Create table script
    foreach ($data->tables as $table => $fields) {
      $database = $data->database;
      // Table structure
      $sql .= "\nCREATE TABLE $database->tablePrefix$table (";
      foreach ($fields->fields as $field => $attributes) {
        if ($attributes == "pk") {
          $attributes = "int(11) not null";
          $sql_keys .= "\nALTER TABLE $database->tablePrefix$table ADD PRIMARY KEY ($field);";
          $sql_keys .= "\nALTER TABLE $database->tablePrefix$table CHANGE $field $field INT(11) NOT NULL AUTO_INCREMENT;";
        }
        $sql .= "\n $field $attributes,";
      }
      $sql = trim($sql, ',');
      $sql .= ") ENGINE=$database->engine DEFAULT CHARSET=utf8 COLLATE=$database->collation;";
      // keys
      if ($fields->keys) {
        foreach ($fields->keys as $key => $column) {
          if ($key === 'unique')
            $sql_keys .= "\nALTER TABLE $database->tablePrefix$table ADD UNIQUE($column);";
        }
      }
    }

    foreach ($data->foreignKeys as $key => $attrs) {
      $database = $data->database;
      $to = $attrs->to;
      $references = $attrs->references;
      $sql_keys .= "\nALTER TABLE {$database->tablePrefix}{$to->table} ADD CONSTRAINT $key FOREIGN KEY ({$to->column}) REFERENCES {$database->tablePrefix}{$references->table} ({$references->column}) ON UPDATE {$attrs->onUpdate} ON DELETE {$attrs->onDelete};";
    }

    // Execute statements
    $query = $this->driver->prepare($sql . $sql_keys);
    if ( $query->execute() )
      return true;
    else
      return false;

  }

  protected function flushStack() {
    $this->stack = array();
  }

}
