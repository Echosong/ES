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
            if(!empty($params)){
                $params = "?$params";
            }
            if ($__module == $rewrite['m'][0]) {
                $url = "http://" . $_SERVER["HTTP_HOST"] . '/' . $c . '/' . $a . $params;
            } else {
                $url = "http://" . $_SERVER["HTTP_HOST"] . '/' . $__module . '/' . $c . '/' . $a .$params;
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
        $requestURI = $_SERVER['REQUEST_URI'];
        $requestURI = str_replace('?'.$_SERVER["QUERY_STRING"], '',$requestURI);
        if ($rewrite['isRewrite'] && !strpos($requestURI, '.php')) {
            $route = explode("/", $requestURI);
            if (!empty($route[1])) {
                if (in_array($route[1], $rewrite['m'])) {
                    $_GET['m'] = $route[1];
                    list($_GET['c'], $_GET['a']) = array_slice($route, 2, 3);
                } else {
                    $_GET['m'] = $rewrite['m'][0];
                    list($_GET['c'], $_GET['a']) = array_slice($route, 1, 2);
                }
            }
        }
        $_GET['m'] = strtolower( empty($_GET['m'])? $rewrite['m'][0]:$_GET['m']);
        $_GET['c'] = strtolower(empty($_GET['c'])? $rewrite['c']:$_GET['c'] );
        $_GET['a'] = strtolower(empty($_GET['a'])? $rewrite['a']:$_GET['a']);

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
        $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
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
    public static function redirect($msg,  $url = '',$code= 0)
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
     * @param $errmsg
     * @param $level debug, info, error
     */
    public static function log($errMsg, $level = 'info')
    {
        $logPath = APP_DIR . DS . $GLOBALS['logPath'] . DS . date('Ymd') . "_" . $level . ".log";
        error_log(date('Ymd H:i:s') . "  " . $errMsg . "\r\n", 3, $logPath);
    }


    /**
     * request获取信息设置默认值
     * @param $name
     * @param $defult
     * @return mixed
     */
    public static function request($name, $defult, $isSafe= true)
    {
        if(!isset($_REQUEST[$name])){
            return $defult;
        }else{
            $param = str_replace("''", "",$_REQUEST[$name]);
            return $param;
        }
    }

}