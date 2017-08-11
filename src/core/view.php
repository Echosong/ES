<?php

class View
{
    private $template_vals = array();

    public function render($tempalte_name)
    {
        @ob_start();
        //核心作用语句
        extract($this->template_vals, EXTR_SKIP);
        include $tempalte_name;
        return ob_get_clean();
    }

    public function assign($mixed, $val = '')
    {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v) {
                if ($k != '') {
                    $this->template_vals[$k] = $v;
                }
            }
        } else {
            if ($mixed != '') {
                $this->template_vals[$mixed] = $val;
            }
        }
    }
}