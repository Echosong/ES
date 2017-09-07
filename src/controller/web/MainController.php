<?php


class MainController extends BaseController
{
    public function getLogin()
    {

    }

    private function uplod()
    {


    }

    public function getIndex()
    {

        $this->display('default.php');
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