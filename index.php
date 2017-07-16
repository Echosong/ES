<?php

define('APP_DIR', realpath('./'));

if(!empty($argc)) {
    $_POST['m'] = $argv[1];
    $_POST['c'] = $argv[2];
    $_POST['a'] = $argv[3];
}

require(APP_DIR.'/protected/lib/es.php');