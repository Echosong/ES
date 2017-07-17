<?php

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