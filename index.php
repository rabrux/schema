<?php

header('Content-type: text/plain');
header('Content-type: application/json');

require_once __DIR__ . '/rabrux/Schema/Schema.php';

use Schema\Schema;

$obj = new Schema();

// $obj->lorem()->where('id', 1);
// $obj->lorem()->where('id', 1);

$obj->algo = array(
  'name' => 'rsalvador'
);

$conf = file_get_contents('schemas/.conf.json');
$schema = file_get_contents('schemas/dentu.json');

$obj = json_decode($conf);
$schema = json_decode( $schema );

// var_dump($schema->database);
$sql = "";
foreach ($schema->tables as $table => $fields) {
  $database = $schema->database;
  $sql .= "\nCREATE TABLE IF NOT EXISTS $database->tablePrefix$table (";
  foreach ($fields->fields as $field => $attributes) {
    if ($attributes == "pk")
      $attributes = "int(11) not null AUTO_INCREMENT";
    $sql .= "\n $field $attributes,";
  }
  $sql = trim($sql, ',');
  $sql .= ") ENGINE=$database->engine DEFAULT CHARSET=$database->collation;";
}

echo $sql;

// echo json_encode($obj->servers);
