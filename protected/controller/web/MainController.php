<?php

class MainController extends BaseController
{

    public function getLogin () {
        $this->layout = null;
        $this->display("login.php");
    }

    public function getIndex () {

        //$this->layout = null;
        $carDb = new DB('class');
        $newDb = new DB('news');
        $this->info = $carDb->find(array('id' => 1));
        $this->item = $newDb->findAll(array('n_type' => 4),"id desc");
        $this->items = $newDb->findAll(array('n_type' => 10),"id desc");
        $this->display('default.php');
    }


    public function getAbout () {
        $this->carData();
        $this->display('about.php');
    }

    public function getNews () {
        $this->carData();
        $newDb = new DB('news');
        $lists = $newDb->findAll(array('n_type' => $_GET['cid']),"id desc");
        $this->lists = $lists;
        $this->display('news.php');
    }


    public function getInfo () {
        $id = $_GET['id'];
        $newDb = new DB('news');
        $new = $newDb->find(array('id' => $id));
        $_GET['cid'] = $new['n_type'];
        $this->carData();
        $this->new = $new;

        $this->display('info.php');
    }


    private function carData () {
        $cid = $_GET['cid'];
        $this->cid = $cid;
        if (empty($cid)) {
            $this->history('参数错误');
        }
        $carDb = new DB('class');
        $car = $carDb->find(array('id' => $cid));
        if ($car['class_pid'] == 0) {
            $this->path = $car['class_name'];
            $pcar = $car;
        } else {
            $pcar = $carDb->find(array('id' => $car['class_pid']));
            $this->path = $pcar['class_name'] . ">>" . $car['class_name'];
        }
        $this->sonlist = $carDb->findAll(array('class_pid' => $pcar['id']), "class_orderid desc");
        if (strlen($car['class_img']) < 5) {
            $this->img = $pcar['class_img'];
        } else {
            $this->img = $car['class_img'];
        }
        $this->car = $car;
        $this->pcar = $pcar;
    }


    public function getCode () {
        $code = new ValidateCode();
        $code->doimg();
        $_SESSION['rndCode'] = $code->getCode();
    }

    /**
     * 登陆测试
     */
    public function postLogin () {
        $account = trim($_POST['account']);
        $password = trim($_POST['pass']);
        $code = strtolower(trim($_POST['code']));
        if (strlen($code) != 4) {
            exit("请输入验证码");
        }
        if ($code != strtolower($_SESSION['rndCode'])) {
            exit("验证码错误");
        }
        $_SESSION['rndCode'] = null;
        if (strlen($account) < 3) {
            exit("输入账号密码有误");
        }
        $userDb = new DB("user");
        $user = $userDb->find(array('u_account' => $account));
        if ($user) {
            if ($user['u_password'] != md5($password)) {
                exit("密码错误" . md5($password));
            } else {
                $_SESSION['userinfo'] = $user;
                $userDb->update(array('id' => $user['id']), array('u_IP' => $this->getip(), 'u_lasttime' => date('Y-m-d H:i:s', time())));
                exit("成功");
            }
        } else {
            exit('账号不存在!');
        }
    }

    /**
     * 商品列表
     */
    public function getProducts () {
        $_GET['cid'] = 8;
        $this->carData();
        $productDb = new DB('good');
        $param = "";
        $where = " 1=1 ";
        $catDb = new DB('cat');
        if (!empty($_GET['catid'])) {
            $where = "catid=" . $_GET['catid'];
            $this->catname = $catDb->find(array('id' => $_GET['catid']));
        }
        $this->cats = $catDb->findAll("1=1");
        $page = $_GET['page'] ? $_GET['page'] : 1;
        $this->products = $productDb->findAll($where);
        $this->page = $this->pager($productDb->page, $param);
        $this->display('products.php');
    }

    /*
     * 商品详情
     */
    public function getDetail () {
        $id = $_GET['id'];
        $goodDb = new DB('good');
        $this->good = $goodDb->find(array('id' => $id));
        $evaluateDb = new DB('evaluate');
        $this->evaluates = $evaluateDb->findAll(array('goodid' => $id), 'id desc');
        $this->display('detail.php');
    }

    /**
     * 评价
     */
    public function postEval () {
        if (!$_SESSION['userinfo']) {
            $this->history('未登录');
        }
        $evaluateDb = new DB('evaluate');
        $_POST['user'] = $_SESSION['userinfo']['u_account'];
        $_POST['created'] = date('Y-m-d H:i:s');
        $evaluateDb->create($_POST);
        $this->success('', url('main', 'detail') . "?id=" . $_POST['goodid'] . "&t=1");
    }

}