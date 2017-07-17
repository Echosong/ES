# ES

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
|--res //静态资源
|--index.php //入口文件

```

## 具体实现功能

### 配置

1. 配置路由规则

2. 数据库配置

3. 业务自定义配置

### controller

1. Controller

2. basController

### model

1. 查询

2. 新增

3. 更新

4. 删除

### view

1. 模板引擎

2. 母版

## 扩展引用

## 案列

crm系统 https://github.com/Echosong/es_crm
