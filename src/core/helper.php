<?php

Class Helper
{
    /**
     * 检查参数合法性
     * @param $name
     * @return int
     */
    public static function is_available_classname($name)
    {
        return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name);
    }

    /**
     * 获取规则的url
     * @param $c
     * @param $a
     * @param array $param
     * @return string
     */
    public static function url($c, $a, $param = array())
    {
        GLOBAL $__module;
        GLOBAL $GLOBALS;
        $rewrite = $GLOBALS['rewrite'];
        $c = empty($c) ? $rewrite['c'] : $c;
        $a = empty($a) ? $rewrite['a'] : $a;
        $params = empty($param) ? '' : http_build_query($param);
        if ($rewrite['isRewrite']) {
            if (!empty($params)) {
                $params = "?$params";
            }
            if ($__module == $rewrite['m'][0]) {
                $url = "http://" . $_SERVER["HTTP_HOST"] . '/' . $c . '/' . $a . $params;
            } else {
                $url = "http://" . $_SERVER["HTTP_HOST"] . '/' . $__module . '/' . $c . '/' . $a . $params;
            }
        } else {
            if ($__module != $rewrite['m'][0]) {
                $url = "http://" . $_SERVER["SCRIPT_NAME"] . "?m=$__module&c=$c&a=$a$params";
            } else {
                $url = "http://" . $_SERVER["SCRIPT_NAME"] . "?c=$c&a=$a$params";
            }
        }
        return $url;
    }

    /**
     * 设置路由
     */
    public static function setRoute()
    {
        $rewrite = $GLOBALS['rewrite'];
        $list_route = [$rewrite['m'][0], $rewrite['c'], $rewrite['a']];
        if ($rewrite['isRewrite'] && isset($_SERVER['REQUEST_URI'])) {
            $requestURI = $_SERVER['REQUEST_URI'];
            $requestURI = str_replace('?' . $_SERVER["QUERY_STRING"], '', $requestURI);
            $route = explode("/", $requestURI);
            if (in_array($route[1], $rewrite['m'])) {
                $list_route[0] = $route[1];
                $route = array_slice($route, 1, count($route));
            }
            $list_route[1] = empty($route[1]) ? $list_route[1] : $route[1];
            $list_route[2] = empty($route[2]) ? $list_route[2] : $route[2];
        }
        $_REQUEST['m'] = strtolower(self::request("m", $list_route[0]));
        $_REQUEST['c'] = strtolower(self::request("c", $list_route[1]));
        $_REQUEST['a'] = strtolower(self::request("a", $list_route[2]));
    }

    /**
     * 启动程序
     */
    public static function start()
    {
        GLOBAL $__module, $__action, $__controller;

        //模块对应目录
        if (!self::is_available_classname($__module)) {
            die("Err: Module name '$__module' is not correct!");
        }
        if (!is_dir(APP_PATH . '../controller' . DS . $__module)) {
            die("Err: Module '$__module' is not exists!");
        }

        if (!self::is_available_classname($__controller)) {
            die("Err: Controller name '$__controller' is not correct!");
        }
        $controller_name = $__controller . 'Controller';
        //处理restful
        $httpMethod = strtolower(empty($_SERVER['REQUEST_METHOD']) ? 'get' : $_SERVER['REQUEST_METHOD']);
        $action_name = $httpMethod . ucfirst($__action);

        if (!class_exists(ucfirst($controller_name), true)) {
            die("Err: Controller '$controller_name' is not exists!");
        }
        $controller_obj = new $controller_name();

        if (!method_exists($controller_obj, $action_name)) {
            $action_name = 'action' . $__action;
            if (!method_exists($controller_obj, $action_name)) {
                die("Err: Method '$action_name' of '$controller_name' is not exists!");
            }
        };
        $controller_obj->$action_name();
    }

    /**
     * 所有的输出格式统一
     * @param $message 输出对象
     * @param $code 输出错误码
     */
    public static function responseJson($message, $code = 0)
    {
        header('Content-type: application/json');
        exit(json_encode(['code' => $code, 'message' => $message]));
    }

    /**
     * @param $msg
     * @param string $url
     * @param int $code 非0 错误提示
     */
    public static function redirect($msg, $url = '', $code = 0)
    {

        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            if (is_array($msg)) {
                exit(json_encode($msg));
            } else {
                self::responseJson(['alertStr' => $msg, 'redirect' => $url], $code);
            }
        } else {
            $strAlert = "";
            if (!empty($msg)) {
                $strAlert = "alert(\"{$msg}\");";
            }
            if ($url == "") {
                exit("<script>alert('$msg');window.history.go(-1);</script>");
            } else {
            }
            exit("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){  {$strAlert} location.href=\"{$url}\";}</script></head><body onload=\"sptips()\"></body></html>");
        }
    }

    /**
     * 获取客户端ip
     * @return array|false|string
     */
    public static function userIp()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("REMOTE_ADDR")) {
                    $ip = getenv("REMOTE_ADDR");
                } else {
                    $ip = "Unknow";
                }
            }
        }
        return $ip;
    }

    /** 日志记录
     * @param $errMsg
     * @param $level (debug, info, error)
     */
    public static function log($errMsg, $level = 'info')
    {
        $logPath = APP_DIR . DS . $GLOBALS['logPath'] . DS . $level . "_" . date('Ymd') . ".log";
        error_log(date('Ymd H:i:s') . "  " . $errMsg . "\r\n", 3, $logPath);
        if (strtolower(trim($level)) === 'error') {
            if ($GLOBALS['debug']) {
                Helper::responseJson($errMsg, -1);
            } else {
                Helper::responseJson('异常查看系统日志', -1);
            }
        }
    }

    /** 自定义错误
     * @param $errNo (错误码)
     * @param $errStr (错误说明)
     * @param $errFile 错误文件
     * @param $errLine 错误行号
     */
    public static function customError($errNo, $errStr, $errFile, $errLine)
    {
        $errMsg = "[{$errNo}] {$errStr} {$errFile} {$errLine} ";
        self::log($errMsg, $errNo);
        if ($GLOBALS["debug"]) {
            echo $errMsg;
        }
        if ($errNo == E_ERROR) {
            die();
        }
    }

    /**request获取信息设置默认值
     * @param $name
     * @param $default
     * @param bool $isSafe
     * @return mixed
     */
    public static function request($name, $default, $isSafe = true)
    {
        if (!isset($_REQUEST[$name])) {
            return $default;
        } else {
            return $isSafe ? str_replace("''", "", $_REQUEST[$name]) : $_REQUEST[$name];
        }
    }

    /** 字段过滤
     * @param array $input
     * @param $fields
     */
    public static function filterFields(array &$input, $fields)
    {
        $operator = ['*', '+', '-', '/', '#'];
        if (empty($fields)) {
            return;
        }
        foreach ($input as $k => $v) {
            $key = $k;
            if (in_array(substr($k, 0, 1), $operator)) {
                $key = substr($k, 1);
            }
            if (!in_array($key, $fields)) {
                unset($input[$k]);
            }
        }
    }

}