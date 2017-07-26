<?php

class MainController extends BaseController
{

    public function getLogin () {
        $this->layout = null;
    }

    public function getIndex () {
        $this->display('default.php');
    }

    public function getTest()
    {
        echo url('main','user', ['x'=>1]);
    }


}