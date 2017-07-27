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
        Helper::log("记录下");
        Helper::responseJson('成功了');
        echo url('main','user', ['x'=>1]);
    }


}