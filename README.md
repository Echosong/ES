# ES

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
    'mysql' => array(
        'MYSQL_HOST' => '127.0.0.1', 
        'MYSQL_PORT' => '3306',
        'MYSQL_USER' => 'root',
        'MYSQL_DB' => 'db_demo',
        'MYSQL_PASS' => '123456',
        'MYSQL_CHARSET' => 'utf8',
    ),
    'prefix' => 'mo_', //表前缀
);
```

#### 业务自定义配置
 
 自定义的业务方面的配置，可以自己定义个配置文件，在config.php 进行引入，也可以直接在config.php 进行修改配置，后面使用全部用
 
 $GLOBALS = require(APP_PATH . '../config.php');
$GLOBALS 全局 数组配置进行获取相应的配置项。

### 控制器

#### Controller

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

### 数据模型

1. 查询

2. 新增

3. 更新

4. 删除

### 视图

1. 模板引擎

2. 母版

## 扩展引用

## 案列

crm系统 https://github.com/Echosong/es_crm
