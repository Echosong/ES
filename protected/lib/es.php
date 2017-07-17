<?php

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(__FILE__) . DS);
$GLOBALS = require(APP_PATH.'../config.php');

require_once(APP_PATH."controller.php");
require_once(APP_PATH."model.php");
require_once(APP_PATH."view.php");

Date_default_timezone_set("PRC");
set_error_handler("_err_handle");

if ($GLOBALS['debug']) {
    error_reporting(-1);
    ini_set("display_errors", "On");
} else {
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
    ini_set("display_errors", "Off");
    ini_set("log_errors", "On");
}

session_start();

$rewrite = $GLOBALS['rewrite'];
if($rewrite['isRewrite']){
     $route = explode("/", $_SERVER['PHP_SELF']);
     if(!empty($rule[1])){    
         if(in_array($rule[1], $rewrite['m'])){
             $_GET['m'] = $route[1];
             list($_GET['c'], $_GET['a']) = array_slice($route,2,3);
         }else{
             $_GET['m'] = $rewrite['m'][0];
             list($_GET['c'], $_GET['a']) = array_slice($route,1,2);
         }
     }
}

$_REQUEST = array_merge($_POST, $_GET);
$__module =  strtolower($_REQUEST['m']) ;
$__controller = strtolower($_REQUEST['c']). 'Controller'; ;
$__action =  strtolower($_REQUEST['a']) ;

//模块对应目录
if (!is_available_classname($__module)) err("Err: Module name '$__module' is not correct!");
if (!is_dir(APP_PATH. '../controller'.DS.$__module)) err("Err: Module '$__module' is not exists!");

if (!is_available_classname($__controller)) err("Err: Controller name '$__controller' is not correct!");

spl_autoload_register('inner_autoload');
function inner_autoload ($class) {
    GLOBAL $__module;
    foreach (array('model', 'include', 'controller'.DS. $__module)) as $dir) {
        $file = APP_PATH .'../' $dir .DS . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
}

$controller_name = $__controller 
//处理restful
$httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
$action_name = $httpMethod . ucfirst($__action);

$controller_obj = new $controller_name();

if (!method_exists($controller_obj, $action_name)) {
    $action_name = 'action' . $__action;
    if (!method_exists($controller_obj, $action_name)) err("Err: Method '$action_name' of '$controller_name' is not exists!");
};
$controller_obj->$action_name();

//自动模板渲染
if ($controller_obj->_auto_display) {
    $auto_tpl_name = (empty($__module) ? '' : $__module . DS) . $__controller . '_' . $__action . '.html';
    if (file_exists(APP_DIR . DS . 'protected' . DS . 'view' . DS . $auto_tpl_name)) $controller_obj->display($auto_tpl_name);
}


function url ($c, $a, $param = array()) {
    GLOBAL $__module;
    $c = empty($c)? $rewrite['c']: $c;
    $a = empty($a)? $rewrite['a']: $a;
    $params = empty($param) ? '' :http_build_query($param);
    if($GLOBALS['isRewrite']){
          $url = $_SERVER["SCRIPT_NAME"].DS.$__module.DS.$c.DS.$a."?".$param; 
    }else{
         $url = $_SERVER["SCRIPT_NAME"] . "?m=$__module&c=$c&a=$a$params";
    }
    return $url;
}

function is_available_classname ($name) {
    return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name);
}

function _err_handle ($errno, $errstr, $errfile, $errline) {
    $msg = "ERROR";
    if ($errno == E_WARNING) $msg = "WARNING";
    if ($errno == E_NOTICE) {
        $msg = "NOTICE";
        return;
    };
    if ($errno == E_STRICT) $msg = "STRICT";
    if ($errno == 8192) $msg = "DEPRECATED";
    if (ob_get_contents()) ob_end_clean();
    die("$msg: $errstr in $errfile on line $errline");
}