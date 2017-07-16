<?php
set_error_handler("_err_handle");
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
$GLOBALS = require(APP_DIR . DS . 'protected' . DS . 'config.php');
Date_default_timezone_set("PRC");

if ($GLOBALS['debug']) {
    error_reporting(-1);
    ini_set("display_errors", "On");
} else {
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
    ini_set("display_errors", "Off");
    ini_set("log_errors", "On");
}

session_start();
if (!empty($GLOBALS['rewrite'])) {
    if (($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false)
        parse_str(substr($_SERVER['REQUEST_URI'], $pos + 1), $_GET);
    foreach ($GLOBALS['rewrite'] as $rule => $mapper) {
        if ('/' == $rule) $rule = '';
        if (0 !== stripos($rule, 'http://'))
            $rule = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') . '/' . $rule;
        $rule = '/' . str_ireplace(array('\\\\', 'http://', '/', '<', '>', '.'),
                                   array('', '', '\/', '(?<', '>\w+)', '\.'), $rule) . '/i';
        if (preg_match($rule, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $matchs)) {
            $route = explode("/", $mapper);

            if (isset($route[2])) {
                list($_GET['m'], $_GET['c'], $_GET['a']) = $route;
            } else {
                list($_GET['c'], $_GET['a']) = $route;
            }
            foreach ($matchs as $matchkey => $matchval) {
                if (!is_int($matchkey)) $_GET[$matchkey] = $matchval;
            }
            break;
        }
    }
}

$_REQUEST = array_merge($_POST, $_GET);
$__module = isset($_REQUEST['m']) ? strtolower($_REQUEST['m']) : 'web';
$__controller = isset($_REQUEST['c']) ? strtolower($_REQUEST['c']) : 'main';
$__action = isset($_REQUEST['a']) ? strtolower($_REQUEST['a']) : 'index';

if (!empty($__module)) {
    if (!is_available_classname($__module)) err("Err: Module name '$__module' is not correct!");
    if (!is_dir(APP_DIR . DS . 'protected' . DS . 'controller' . DS . $__module)) err("Err: Module '$__module' is not exists!");
}

if (!is_available_classname($__controller)) err("Err: Controller name '$__controller' is not correct!");
spl_autoload_register('inner_autoload');
function inner_autoload ($class) {
    GLOBAL $__module;
    foreach (array('model', 'include', 'controller' . (empty($__module) ? '' : DS . $__module)) as $dir) {
        $file = APP_DIR . DS . 'protected' . DS . $dir . DS . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
        $lowerfile = strtolower($file);
        foreach (glob(APP_DIR . DS . 'protected' . DS . $dir . DS . '*.php') as $file) {
            if (strtolower($file) === $lowerfile) {
                include $file;
                return;
            }
        }
    }
}

$controller_name = $__controller . 'Controller';
$httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
$action_name = $httpMethod . ucfirst($__action);
//if(!class_exists($controller_name, true)) err("Err: Controller '$controller_name' is not exists!");

$controller_obj = new $controller_name();

if (!method_exists($controller_obj, $action_name)) {
    $action_name = 'action' . $__action;
    if (!method_exists($controller_obj, $action_name)) err("Err: Method '$action_name' of '$controller_name' is not exists!");
};

$controller_obj->$action_name();
if ($controller_obj->_auto_display) {
    $auto_tpl_name = (empty($__module) ? '' : $__module . DS) . $__controller . '_' . $__action . '.html';
    if (file_exists(APP_DIR . DS . 'protected' . DS . 'view' . DS . $auto_tpl_name)) $controller_obj->display($auto_tpl_name);
}

function url ($c = 'main', $a = 'index', $param = array()) {
    GLOBAL $__module;
    if (is_array($c)) {
        $param = $c;
        $c = $param['c'];
        unset($param['c']);
        $a = $param['a'];
        unset($param['a']);
    }
    if ($__module != '' && strpos($c,"/") == false) {
        $c = $__module . '/' . $c;
    }
    $params = empty($param) ? '' : '&' . http_build_query($param);
    if (strpos($c, '/') !== false) {
        list($m, $c) = explode('/', $c);
        $route = "$m/$c/$a";
        $url = $_SERVER["SCRIPT_NAME"] . "?m=$m&c=$c&a=$a$params";
    } else {
        $m = '';
        $route = "$c/$a";
        $url = $_SERVER["SCRIPT_NAME"] . "?c=$c&a=$a$params";
    }
    if (!empty($GLOBALS['rewrite'])) {
        static $urlArray = array();
        if (!isset($urlArray[$url])) {
            foreach ($GLOBALS['rewrite'] as $rule => $mapper) {
                $mapper = '/' . str_ireplace(array('/', '<a>', '<c>', '<m>'),
                                             array('\/', '(?<a>\w+)', '(?<c>\w+)', '(?<m>\w+)'), $mapper) . '/i';

                if (preg_match($mapper, $route, $matchs)) {
                    $urlArray[$url] = str_ireplace(array('<a>', '<c>', '<m>'), array($a, $c, $m), $rule);
                    if (!empty($param)) {
                        $_args = array();
                        foreach ($param as $argkey => $arg) {
                            $count = 0;
                            $urlArray[$url] = str_ireplace('<' . $argkey . '>', $arg, $urlArray[$url], $count);
                            if (!$count) $_args[$argkey] = $arg;
                        }
                        $urlArray[$url] = preg_replace('/<\w+>/', '', $urlArray[$url]) .
                            (!empty($_args) ? '?' . http_build_query($_args) : '');
                    }

                    if (0 !== stripos($urlArray[$url], 'http://'))
                        $urlArray[$url] = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') . '/' . $urlArray[$url];
                    $rule = str_ireplace(array('<m>', '<c>', '<a>'), '', $rule);
                    if (count($param) == preg_match_all('/<\w+>/is', $rule, $_match)) {
                        return $urlArray[$url];
                    }
                }
            }
            return isset($urlArray[$url]) ? $urlArray[$url] : $url;
        }
        return $urlArray[$url];
    }
    return $url;
}

function is_available_classname ($name) {
    return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name);
}

class Controller
{
    public $layout;
    public $_auto_display = true;
    private $_v;
    private $_data = array();
    public $tep_dir = "";
    public function init () {
    }

    public function __construct () {
        $this->init();
    }

    public function __get ($name) {
        return $this->_data[$name];
    }

    public function __set ($name, $value) {
        $this->_data[$name] = $value;
    }

    public function success ($msg, $url) {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            if (is_array($msg)) {
                exit(json_encode($msg));
            } else {
                exit(json_encode(array("code" => 0, 'message' => $msg, 'redirect' => $url)));
            }
        } else {
            $strAlert = "";
            if (!empty($msg)) {
                $strAlert = "alert(\"{$msg}\");";
            }
            echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){  {$strAlert} location.href=\"{$url}\";}</script></head><body onload=\"sptips()\"></body></html>";
            exit;
        }
    }

    public function history ($msg) {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            if (is_array($msg)) {
                exit(json_encode($msg));
            } else {
                exit(json_encode(array("code" => 1, 'message' => $msg)));
            }
        } else {//非ajax提交
            $type = $_SERVER['HTTP_X_REQUESTED_WITH'];
            exit("<script>alert('$msg');window.history.go(-1);</script>");
        }
    }

    public function display ($tpl_name, $return = false) {

        if (!$this->_v) $this->_v = new View(APP_DIR . DS . 'protected' . DS . 'view', APP_DIR . DS . 'protected' . DS . 'tmp');
        $this->_v->assign(get_object_vars($this));
        $this->_v->assign($this->_data);
        if ($this->tep_dir != "") {
            $tpl_name = $this->tep_dir . DS . $tpl_name;
        }
        if ($this->layout) {
            $this->_v->assign('__template_file', $tpl_name);
            $tpl_name = $this->layout;
        }
        $this->_auto_display = false;

        if ($return) {
            return $this->_v->render($tpl_name);
        } else {
            echo $this->_v->render($tpl_name);
        }
    }

}

class Model
{
    public $page;
    public $table_name;

    private $_master_db;
    private $_slave_db;
    private $sql = array();

    public function __construct ($table_name = null) {
        global $GLOBALS;
        if ($table_name) $this->table_name = $GLOBALS['prefix'] . $table_name;
    }

    public function findAll ($conditions = array(), $sort = null, $fields = '*', $limit = null) {
        $sort = !empty($sort) ? ' ORDER BY ' . $sort : '';
        $conditions = $this->_where($conditions);
        $sql = ' FROM ' . $this->table_name . $conditions["_where"];
        if (is_array($limit)) {
            $total = $this->query('SELECT COUNT(*) as M_COUNTER ' . $sql, $conditions["_bindParams"]);
            $limit = $limit + array(1, 20, 10);
            $limit = $this->pager($limit[0], $limit[1], $limit[2], $total[0]['M_COUNTER']);
            $limit = empty($limit) ? '' : ' LIMIT ' . $limit['offset'] . ',' . $limit['limit'];
        } else {
            $limit = !empty($limit) ? ' LIMIT ' . $limit : '';
        }
        return $this->query('SELECT ' . $fields . $sql . $sort . $limit, $conditions["_bindParams"]);
    }

    public function find ($conditions = array(), $sort = null, $fields = '*') {
        $res = $this->findAll($conditions, $sort, $fields, 1);
        return !empty($res) ? array_pop($res) : false;
    }

    public function update ($conditions, $row) {
        $values = array();
        foreach ($row as $k => $v) {
            $values[":M_UPDATE_" . $k] = $v;
            $setstr[] = '`' . $k . "`=" . ":M_UPDATE_" . $k;
        }
        $conditions = $this->_where($conditions);
        return $this->execute("UPDATE " . $this->table_name . " SET " . implode(', ', $setstr) . $conditions["_where"], $conditions["_bindParams"] + $values);
    }

    public function delete ($conditions) {
        $conditions = $this->_where($conditions);
        return $this->execute("DELETE FROM " . $this->table_name . $conditions["_where"], $conditions["_bindParams"]);
    }

    public function create ($row) {
        $values = array();
        foreach ($row as $k => $v) {
            $keys[] = "`{$k}`";
            $values[":" . $k] = $v;
            $marks[] = ":" . $k;
        }
        $this->execute("INSERT INTO " . $this->table_name . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $marks) . ")", $values);
        return $this->_master_db->lastInsertId();
    }

    public function findCount ($conditions) {
        $conditions = $this->_where($conditions);
        $count = $this->query("SELECT COUNT(*) AS M_COUNTER FROM " . $this->table_name . $conditions["_where"], $conditions["_bindParams"]);
        return isset($count[0]['M_COUNTER']) && $count[0]['M_COUNTER'] ? $count[0]['M_COUNTER'] : 0;
    }

    public function findSum ($conditions, $field) {
        $conditions = $this->_where($conditions);
        $sum = $this->query("SELECT sum({$field}) AS M_COUNTER FROM " . $this->table_name . $conditions["_where"], $conditions["_bindParams"]);
        return isset($sum[0]['M_COUNTER']) && $sum[0]['M_COUNTER'] ? $sum[0]['M_COUNTER'] : 0;
    }

    public function dumpSql () {
        return $this->sql;
    }

    public function pager ($page, $pageSize = 10, $scope = 10, $total) {
        $this->page = null;
        if ($total > $pageSize) {
            $total_page = ceil($total / $pageSize);
            $page = min(intval(max($page, 1)), $total);
            $this->page = array(
                'total_count' => $total,
                'page_size' => $pageSize,
                'total_page' => $total_page,
                'first_page' => 1,
                'prev_page' => ((1 == $page) ? 1 : ($page - 1)),
                'next_page' => (($page == $total_page) ? $total_page : ($page + 1)),
                'last_page' => $total_page,
                'current_page' => $page,
                'all_pages' => array(),
                'offset' => ($page - 1) * $pageSize,
                'limit' => $pageSize,
            );
            $scope = (int)$scope;
            if ($total_page <= $scope) {
                $this->page['all_pages'] = range(1, $total_page);
            } elseif ($page <= $scope / 2) {
                $this->page['all_pages'] = range(1, $scope);
            } else {
                $this->page['all_pages'] = range($page - $scope / 2, min($page + $scope / 2 - 1, $total_page));
            }
        }
        return $this->page;
    }

    public function query ($sql, $params = array()) {
        return $this->execute($sql, $params, true);
    }

    public function execute ($sql, $params = array(), $is_query = false) {
        $this->sql[] = $sql;
        if ($is_query && is_object($this->_slave_db)) {
            $sth = $this->_slave_db->prepare($sql);
        } else {
            if (!($this->_master_db)) $this->setDB('default');
            $sth = $this->_master_db->prepare($sql);
        }

        if (is_array($params) && !empty($params)) {
            foreach ($params as $k => &$v) $sth->bindParam($k, $v);
        }
        if ($sth->execute()) return $is_query ? $sth->fetchAll(PDO::FETCH_ASSOC) : $sth->rowCount();
        $err = $sth->errorInfo();
        err('Database SQL: "' . $sql . '", ErrorInfo: ' . $err[2], 1);
    }

    public function setDB ($db_config_key = 'default', $is_readonly = false) {
        if ('default' == $db_config_key) {
            $db_config = $GLOBALS['mysql'];
        } else if (!empty($GLOBALS['mysql'][$db_config_key])) {
            $db_config = $GLOBALS['mysql'][$db_config_key];
        } else {
            err("Database Err: Db config '$db_config_key' is not exists!");
        }
        if ($is_readonly) {
            $this->_slave_db = $this->_db_instance($db_config, $db_config_key);
        } else {
            $this->_master_db = $this->_db_instance($db_config, $db_config_key);
        }
    }

    private function _db_instance ($db_config, $db_config_key) {
        if (empty($GLOBALS['mysql_instances'][$db_config_key])) {
            try {
                $GLOBALS['mysql_instances'][$db_config_key] = new PDO('mysql:dbname=' . $db_config['MYSQL_DB'] . ';host=' . $db_config['MYSQL_HOST'] . ';port=' . $db_config['MYSQL_PORT'], $db_config['MYSQL_USER'], $db_config['MYSQL_PASS'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $db_config['MYSQL_CHARSET'] . '\''));
            } catch (PDOException $e) {
                err('Database Err: ' . $e->getMessage());
            }
        }
        return $GLOBALS['mysql_instances'][$db_config_key];
    }

    private function _where ($conditions) {
        $result = array("_where" => " ", "_bindParams" => array());
        if (!$conditions) {
            return $result;
        }
        if (is_array($conditions) && !empty($conditions)) {
            $fieldss = array();
            $sql = null;
            $join = array();
            if (isset($conditions[0]) && $sql = $conditions[0]) unset($conditions[0]);
            foreach ($conditions as $key => $condition) {
                $optstr = substr($key, strlen($key) - 1, 1);
                if ($optstr == '>' || $optstr == '<') {
                    unset($conditions[$key]);
                    $key = str_replace($optstr, '', $key);
                } else {
                    $optstr = '=';
                }
                if (substr($key, 0, 1) != ":") {
                    unset($conditions[$key]);
                    $conditions[":" . $key] = $condition;
                }
                $join[] = "`{$key}`{$optstr} :{$key}";
            }
            if (!$sql) $sql = join(" AND ", $join);
            $result["_where"] = " WHERE " . $sql;
            $result["_bindParams"] = $conditions;
        } else {
            $result["_where"] = " WHERE " . $conditions;
            $result["_bindParams"] = array();
        }
        return $result;
    }
}

class View
{
    private $template_dir;
    private $template_vals = array();

    public function __construct ($template_dir) {
        $this->template_dir = $template_dir;
    }

    public function render ($tempalte_name) {
        $file = $this->compile($tempalte_name);
        @ob_start();
        extract($this->template_vals, EXTR_SKIP);
        $_view_obj = &$this;
        include $file;

        return ob_get_clean();
    }

    public function compile ($tempalte_name) {
        $file = $this->template_dir . DS . $tempalte_name;
        if (!file_exists($file)) err('Err: "' . $file . '" is not exists!');
        return $file;
    }

    public function assign ($mixed, $val = '') {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v) {
                if ($k != '') $this->template_vals[$k] = $v;
            }
        } else {
            if ($mixed != '') $this->template_vals[$mixed] = $val;
        }
    }
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
    err("$msg: $errstr in $errfile on line $errline");
}

function err ($msg) {
    $traces = debug_backtrace();
    if (false) {
        if (!empty($GLOBALS['err_handler'])) {
            call_user_func($GLOBALS['err_handler'], $msg, $traces);
        } else {
            error_log($msg);
        }
    } else {
        if (ob_get_contents()) ob_end_clean();
        require_once("error.php");
    }
    exit;
}