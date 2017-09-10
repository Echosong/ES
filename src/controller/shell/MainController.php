<?php
/**
 * Created by PhpStorm.
 * User: echosong
 * Date: 2017/9/10
 * Time: 12:21
 */

class MainController extends Controller
{
    /**
     * php 脚本运行 php index.php shell main index "自定义参数"
     */
    public function actionIndex()
    {
        while (true){
            echo "todo 业务参数：".Helper::request("p", "");
            sleep(1);
        }
    }
}