#!/usr/bin/env php
<?php

require_once 'rabrux/Schema/Schema.php';

use Schema\Schema;

// Get first argv, name of script
array_shift( $argv );

$app = new Schema($argv, __DIR__);

$app->run();
