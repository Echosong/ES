<?php


class MainController extends BaseController
{
    public function getLogin()
    {

    }

    private function uplod()
    {

    }

    /**
     * 测试分页
     */
    public function getPage(){
        $userDb = new Model('category');
        $page = Helper::request('page', 0);
        $users = $userDb->findAll('', 'id desc',  'id,name', [$page, 20]);
        Helper::responseJson([$users, $userDb->page ]);
    }

    public function getIndex()
    {

        $this->display('default.php');
    }

    public function postIndex()
    {
        //sleep(6);
       var_dump($_REQUEST);
    }

    public function getTest()
    {
        $userDb = new Model('user');
        $id = $userDb->create([
            [
                'username' => 'admin6',
                'password' => 'password1',
                'login_count' => 1,
                '#last_time' => 'CURRENT_TIMESTAMP()'
            ],
            [
                'username' => 'admin8',
                'password' => 'password1',
                'login_count' => 1,
                '#last_time' => 'CURRENT_TIMESTAMP()'
            ]
        ]);
        echo $userDb->update(['id' => 1],
            ['+login_count' => 10, "#email" => 'uuid()']);

    }


}