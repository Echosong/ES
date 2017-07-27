<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', '/' );
define('APP_PATH', dirname(__FILE__) . DS);

Date_default_timezone_set("PRC");
session_start();

$GLOBALS = require(APP_PATH . '../config.php');
require_once(APP_PATH . "helper.php");
require_once(APP_PATH . "controller.php");
require_once(APP_PATH . "model.php");
require_once(APP_PATH . "view.php");

//设置路由规范
Helper::setRoute();

//定义全局变量
$_REQUEST = array_merge($_POST, $_GET);
$__module = strtolower($_REQUEST['m']);
$__controller = strtolower($_REQUEST['c']);
$__action = strtolower($_REQUEST['a']);

spl_autoload_register(function ($class) use ($__module){
    foreach (array('model', 'include', 'controller' . DS . $__module, './') as $dir) {
        $file = APP_PATH . '../'. $dir . DS . $class . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});

//开始运行
Helper::start();


