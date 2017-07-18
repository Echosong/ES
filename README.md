# ![运营商][1] ES


## 框架简介
ES 是一款 极简，灵活， 高性能，扩建性强 的php 框架。 未开源之前在商业公司 经历数年,数个高并发网站 实践使用！


## 框架结构

整个框架核心四个文件，所有文件加起来放在一起总行数不超过400 行 

```
|--src //受保护代码文件夹
  |--lib
    |--es.php //启动文件
    |--controller.php //控制器文件
    |--model.php //模型文件
    |--view.php //视图引擎
  |--controller //控制器业务文件
  |--view //视图文件
  |--model //模型一般小型业务可以省略，数据操作直接放到controller 
  |--config.php //全局配置文件，业务相关的配置也可以放这里，或者自己建立一个独立的配置文件index.php 文件引用
|--res //静态资源
|--index.php //入口文件

```

## 具体实现功能

### 配置文件

#### 配置路由规则

ES 没有像些重型框架单独有 Route 配置， ES的想法很简单，主要分为 模块[m]，控制器[c]，动作[c] 来路由

```php
 'rewrite' => array(
        //设置模块 碰到 http://{host}/admin/ 认为进入了后台模块 数组 0 标识默认 m
       'm'=>['web','admin','app','api'], 
       'c'=>'main', //controller 默认值
       'a'=>'index', //action 默认值,
       'isRewrite'=> TRUE //是否开启伪静态 .htaccess 文件配置
    ),
```
其中 m 为模块，一般我们开发小型web系统时候，后台（admin）、前端（web）、接口(api) 来划分结构； 大型一点的web系统，常根据业务进行模块划分，比如
shop、order、user 等等模块划分。 实际划分就对应着 controller view 里面的文件夹的安排，一般的 一个模块对应其下面的一个文件夹，这样清晰的管理模块
方便协作开发和解耦

另外 配置中 m=>[api..] 数组就是划分的模块，对应地址栏会去选择 www.baidu.com/admin/con/index  域名部分后面的第一个 /admin/ 如果在配置中就表示为识别到的模块， 否者将模块默认为 m[0]
实现代码可以参考 es：

```php
$rewrite = $GLOBALS['rewrite'];
if ($rewrite['isRewrite']) {
    $route = explode("/", $_SERVER['PHP_SELF']);
    if (!empty($rule[1])) {
        if (in_array($rule[1], $rewrite['m'])) {
            $_GET['m'] = $route[1];
            list($_GET['c'], $_GET['a']) = array_slice($route, 2, 3);
        } else {
            $_GET['m'] = $rewrite['m'][0];
            list($_GET['c'], $_GET['a']) = array_slice($route, 1, 2);
        }
    }
}
```

#### 数据库配置

数据库目前支持mysql

```php
$dbb = array(
    'mysql' => [
        //主库
        'master'=>[
            'MYSQL_HOST' => '127.0.0.1',
            'MYSQL_PORT' => '3306',
            'MYSQL_USER' => 'root',
            'MYSQL_DB' => 'db_demo',
            'MYSQL_PASS' => '123456',
            'MYSQL_CHARSET' => 'utf8',
        ],
        //从库可以加入多个实例
        'slave'=>[
            'MYSQL_HOST' => '127.0.0.1',
            'MYSQL_PORT' => '3306',
            'MYSQL_USER' => 'root',
            'MYSQL_DB' => 'db_demo',
            'MYSQL_PASS' => '123456',
            'MYSQL_CHARSET' => 'utf8',
        ]
    ],
    'prefix' => 'mo_',
);
```

#### 业务自定义配置
 
 自定义的业务方面的配置，可以自己定义个配置文件，在config.php 进行引入，也可以直接在config.php 进行修改配置，后面使用全部用
 
 $GLOBALS = require(APP_PATH . '../config.php');
$GLOBALS 全局 数组配置进行获取相应的配置项。

### 控制器

#### Controller

控制器 （Get, Post , Head , Put http）+$_Get['a'] 的函数，直接暴露给了请求，请求可以通过
$_GET['a'] 直接调用，如果么没有指定相应http 动作时间，直接调用 public action+$_GET['a'] 的函数
比如 客户端get请求
    
    /api/main/index
    会路由到 controller/api/mainController.php 文件 下面 的 
    public function getIndex(){} 函数，如果此函数不存在，会找个 
    public function actionIndex(){} 函数

另外在写http 接口时候我们可以直接 Restful api 格式用http 请求自动对应
比如

    /api/good
    会路由到 controller/api/goodController.php 文件 下面 的 
    public function get(){} 函数
    public function post(){} 函数
    ....已至restful Api接口写法

```php
//获取视图数据源的值
public function __get ($name)

//此函数设置的值可以在视图模板里直接使用
public function __set ($name, $value)

//处理，比如修改 添加 数据成功时候需要返回列表页， $msg 弹出的提示内容， $url 为列表页面的地址
public function success ($msg, $url)

//跟success 函数对应，直接返回上次操作页面，比如添加保存成功 再调回到添加页面
public function history ($msg)

//处理 action对应的模板 $tpl_name 模板地址 会自定到view 和controller 同名的文件夹
public function display ($tpl_name, $return = false)

```

#### basController

baseController 在每个模块里面有父类，继承系统核心Controller 主要用来处理

 - 处理模块常用的业务（比如权限验证）
 - 处理比如分页的数据展现样式
 - 公共的业务处理在默认情况下没有model 那么把处理一些公共的数据操作业务也可以放到这里
 - 做数据输出的过滤，比如同一个输入格式 {"code":0,"message":""} 诸如此类函数的处理
 
### 数据模型

es 数据库操作使用PDO pdo 本身对数据库操作，参数化，防治sql诸如， 请在使用元素sql 语句查询的时候，不要直接拼凑字符串。

#### 读写分离（多数库操作）

```php

 public function setDB($db_config_key = 'default', $is_readonly = false)

 $userDb = new Model('user');
 
//设置以下操作使用 sale0 实例
 $userDb->setDb('sale0', true);
 $user->...

```

#### 查询

```php
//查询user表数据
$userDb = new Model("user");

 /** 查询数据返回数据数组集合
     * @param array 查询条件可以是数组，也可以直接是字符串 比如 ['id'=>1] 等效 "and id = 1"
     * @param null 排序 如 " id desc"
     * @param string 查詢表字段
     * @param null $limit 这个参数比较关键，如果这个参数不为空将 可以分页
     * @return mixed
     */
public function findAll ($conditions = array(), $sort = null, $fields = '*', $limit = null) 

//分页查询
$userData = $userDb->findAll('id> 1', 'id desc', '*', 10)

//执行为上面的语句后
$userDb->page 返回一个分页数组，模板里面可以根据这个数组去做一定处理
一般情况BaseController 自定义个函数来拼凑html 显示
或者直接 是前后端分离 json 直接打印给前端处理
 
//查询单条数据
public function find ($conditions = array(), $sort = null, $fields = '*')

//sql 直接查询
public function query($sql, $params = array())
$user = $userDb->query("select * from mo_user where id=? ", ['id'=>1]);

//查询统计量等同于 sum($field) 返回直接返回整型
public function findSum($conditions, $field)

//查询统计量等同于 count(1) 返回直接返回整型
public function findCount($conditions)
```

####  新增

```php
/** 表插入记录
 * @param $row
 * @return mixed
 */
public function create($row);

演示
$userDb = new Model('user');
$userDb->create(
    ['username'=>'es',
      'password'=>'123456',
      'sex'=>1
    ]
);
```
#### 更新
```php
/**
 * @param 查询条件
 * @param 更加的数据
 * @return mixed
 */
public function update($conditions, $row)
$userDb->update(
    ['id'=>1],
    ['username'=>'es',
      'password'=>'123456',
      'sex'=>1
    ]
);
```

#### 删除
```php
//按条件删除数据
public function delete($conditions)

$userDb = new Model('user');
$userDb->delete(['id'=>1]);

```

### 视图
es 采用php原始 脚本作为模板标记语言， 主要好处有
- 不需要额外学习一门新的标识语言
- 速度上也是最快的
- 实现起来简单

#### 模板引擎
    php 原始脚本，只不过模板里面能够使用的变量 只能来源controller __set 所设置的变量
    保证模板使用的数据安全，按需所给。
    
2. 母版
    
   引入母版机制(具体参考案例)
 ```html
<?php include $_view_obj->compile($__template_file); ?>
```
    
## 扩展引用
es 本身最求灵活，极简单，所有没有引入其他重型模板的功能点，比如cache, 链接redis 等等，那么使用者，如果需要
相关功能怎么办能

步骤如下：

- 配置扩展目录
```php
'plugins'=>['include','plugin'] //扩展目录
```
- 扩展目录里面放入扩展类[类文件直拷贝到目录里面]
```php
class P
{
    public function test(){
        return '扩展';
    }
}

```
- 在controller action 里面

```php
$p = new P();
$p->test()
```

## 支持常驻脚本

```php
//能处理shell 请求
if (!empty($argc)) {
    $_POST['m'] = $argv[1];
    $_POST['c'] = $argv[2];
    $_POST['a'] = $argv[3];
}
$ php index.php m c a
```

## 案列

crm系统 https://github.com/Echosong/es_crm

## 联系方式

* bug和建议请发送至：`313690636@qq.com`；
* 技术支持、技术合作或咨询请联系作者QQ:`313690636`、微信：`echosongfeigang`。
* 博客地址 ：http://www.cnblogs.com/echosong/


  [1]:http://dbbsale.oss-cn-shanghai.aliyuncs.com/keqiang/32.ico

