<?php

define('BASE_DIR', getcwd());

require_once(BASE_DIR . '/appFunctions.php');

$result = myMain();

echo "\n\nProgram ended with result $result\n\n";
