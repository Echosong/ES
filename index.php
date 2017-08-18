<?php

define('APP_DIR', realpath('./'));

//require_once __DIR__.'/vendor/autoload.php';

//能处理shell 请求
if (!empty($argc)) {
    $_GET['m'] = $argv[1];
    $_GET['c'] = $argv[2];
    $_GET['a'] = $argv[3];
}

require(APP_DIR . '/src/core/es.php');