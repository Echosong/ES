<?php


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

        if (!$this->_v) $this->_v = new View(APP_DIR.DS."src" . DS . 'view');
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