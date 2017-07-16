<?php

date_default_timezone_set('PRC');

$config = array(
    'rewrite' => array(
        'admin/index.html' => 'admin/main/index',
        '<m>/<c>_<a>.html' => '<m>/<c>/<a>',
        '<c>/<a>' => '<c>/<a>',
        '/' => 'main/index',
    ),

    'fundType' => array(
        1 => '账户充值',
        2 => '账户提现 ',
        3 => '账户转入',
        4 => '账户转出 ',
        5 => '销售奖金',
        6 => '销售分红 ',
        7 => "领导奖",
        8 => "领导提成",
        9 => "报单奖",
        10 => '循环互助奖',
        11 => '报单扣款',
        12 => '管理员加款',
        13 => '管理员扣款',
        14=> '平摊风险',
        15=> '购物币充值'
    ),



    'orderState' => array('请选择', '新订单', '已经付款', '已发货', '订单完成', '订单取消'),

    'evaluatesType' => array('差评', '中评', '好评'),

    'fundName' => array(
        5 => 'sale',
        6 => 'lucky',
        7 => "team",
        8 => "wealth",
        9 => "center",
        10 => 'agent',
        14 =>'lottery',
    ),

    'regMoney' => 20000,

    "priceArr" => array(
        5 => 0.2,
        6 => 0.6,
        7 => 0.01,
        8 => 0.05,
        9 => 0.02,
        10 => 0.03
    ),

    //相关资金统一明细 'team' => ["领导提成",8],
    'fund'=>array('addmoney'=>["账户充值", '1'], 'cash' => ['账户提现',2],
        'inmoney' => ['账户转入', 3], 'outmoney' => ['账户转出',4],
        'sale' => ['销售奖',5], 'lucky' => ['销售分红', 6], 'team' => ["领导奖",7],
         'center' => ["报单奖",9], 'centerout'=>['报单扣款', 11],
        'agent'=>['循环互助奖', 10], 'money'=> ['总奖金',16], 'fund'=> ['期权',17], 'tax'=>['个人所得税', 18],
        'shop'=>['购物币',19],'currency'=>['现金币',20],
        'adminadd'=>['平台加款',12], 'adminout' => ['平台扣款', 13], 'lottery'=> ['比例控制', 14],
    ),
    //会员等级
    'userStar' => array("初级", '中级', '高级'),
    //会员金额
    'userRegMoney' => array(1000, 5000, 10000),

    //申请相应权限
    'applyType' => array('报单中心','合作股东','合作股东'),

    //代理字段
    'applyName' => array('u_iscenter', 'u_isagent','u_isagent'),

    "agentLevel" => array("无",'股东会员'),
    
    'lottery'=>[10,11,12,16,17]
);

$domain = array(
    "tianyi.com" => array(
        'debug' => 1,
        'mysql' => array(
            'MYSQL_HOST' => '119.23.225.64',
            'MYSQL_PORT' => '3306',
            'MYSQL_USER' => 'root',
            'MYSQL_DB' => 'db_tianyi',
            'MYSQL_PASS' => 'songfeiok',
            'MYSQL_CHARSET' => 'utf8',
        ),
        'sae' => TRUE,
        'prefix' => 'mo_',
    )
);


return $domain['tianyi.com'] + $config;