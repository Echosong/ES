<?php

define('APP_DIR', realpath('./'));

//能处理shell 请求
if (!empty($argc)) {
    $_POST['m'] = $argv[1];
    $_POST['c'] = $argv[2];
    $_POST['a'] = $argv[3];
}

require(APP_DIR . '/src/core/es.php');