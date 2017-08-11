<?php



class MainController extends BaseController
{

    public function __construct(){
        $this->layout = "layout.php";
    }

    public function getLogin () {

    }

    public function getIndex () {
        $this->display('default.php');
    }

    public function getTest()
    {
  
        echo Helper::url('main','user', ['x'=>1]);
    }


}