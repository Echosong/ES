<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/10
 * Time: 12:21
 */

class MainController extends Controller
{
    public function actionIndex()
    {
        while (true){
            echo "todo 业务参数：".Helper::request("p", "");
            sleep(1);
        }
    }
}