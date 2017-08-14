<?php


class Controller
{
    public $layout;
    private $_v;
    private $_data = array();
    public $routes;
    public $template_dir ;

    public function init() { }

    public function __construct()
    {
        global $__module, $__controller, $__action;
        $this->routes = ['m' => $__module, 'c' => $__controller, 'a' => $__action];
        $this->template_dir =  APP_DIR . DS . "src" . DS . 'view' . DS . $this->routes['m'];
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
        if (!$this->_v) {
            $this->_v = new View();
        }
        //controller 成员对模板外公开
        $this->_v->assign(get_object_vars($this));
        $this->_v->assign($this->_data);

        if ($this->layout) {
            $this->_v->assign('__render_body',  $this->template_dir . DS . $tpl_name);
            $tpl_name = $this->layout;
        }
        if ($return) {
            //此方式保留方便action里面直接生成静态文件
            return $this->_v->render( $this->template_dir . DS . $tpl_name);
        } else {
            echo $this->_v->render( $this->template_dir . DS . $tpl_name);
        }
    }

}