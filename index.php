<?php

define('APP_DIR', realpath('./'));
//加载composer 信息
if(file_exists(__DIR__.'/vendor/autoload.php')){
    require_once __DIR__.'/vendor/autoload.php';
}
//能处理shell 请求
if (!empty($argc)) {
    $_REQUEST['m'] = $argv[1];
    $_REQUEST['c'] = $argv[2];
    $_REQUEST['a'] = $argv[3];
    $_REQUEST['p'] = empty($argv[4])? '': $argv[4];
}

require(APP_DIR . '/src/core/es.php');
