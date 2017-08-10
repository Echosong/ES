<?php



class MainController extends BaseController
{

    public function __construct(){

    }

    public function getLogin () {
        $this->layout = null;
    }

    public function getIndex () {
        $this->display('default.php');
    }

    public function getTest()
    {
        $this->logger->info("写个日志");
        //Helper::log("记录下");
        //Helper::responseJson('成功了');
        echo Helper::url('main','user', ['x'=>1]);
    }


}