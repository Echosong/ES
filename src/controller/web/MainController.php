<?php



class MainController extends BaseController
{
    public function getLogin () {

    }
    private  function  uplod()
    {


    }

    public function getIndex () {
        
        $this->display('default.php');
    }

    public function getTest()
    {
  
        echo Helper::url('main','user', ['x'=>1]);
    }



}