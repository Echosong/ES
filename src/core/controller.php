<?php


class Controller
{
    public $layout;
    private $_v;
    private $_data = array();
    public $routes;

    public function init() { }

    public function __construct()
    {
        global $__module, $__controller, $__action;
        $this->routes = ['m' => $__module, 'c' => $__controller, 'a' => $__action];
        $this->init();
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function display($tpl_name, $return = false)
    {
        $view_path = APP_DIR . DS . "src" . DS . 'view' . DS . $this->routes['m'];
        if (!$this->_v) {
            $this->_v = new View();
        }
        //controller 成员对模板外公开
        $this->_v->assign(get_object_vars($this));
        $this->_v->assign($this->_data);

        if ($this->layout) {
            $this->_v->assign('$__render_body', $view_path . DS . $tpl_name);
            $tplName = $this->layout;
        }
        if ($return) {
            //此方式保留方便action里面直接生成静态文件
            return $this->_v->render($view_path . DS . $tpl_name);
        } else {
            echo $this->_v->render($view_path . DS . $tpl_name);
        }
    }

}