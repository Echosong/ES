<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="<?=$config->site_dis?>" />
	<meta name="description" content="<?=$config->site_dis?>" />
	<title><?php echo $config['site_CompanyName']?>-<?php echo $car['class_name'] ?> </title>
	<link media="all" type="text/css" rel="stylesheet" href="/i/web/css/lanrenzhijia.css">
	<link media="all" type="text/css" rel="stylesheet" href="/i/web/css/style.css">
	<link media="all" type="text/css" rel="stylesheet" href="/i/web/css/zdy1.css">
	<link media="all" type="text/css" rel="stylesheet" href="/i/web/css/zdy2.css">
	<script src="/i/web/js/jquery-1.4.4.min.js"></script>
	<script src="/i/web/js/alixixi_jquery.min.js"></script>
	<script src="/i/web/js/script.js"></script>
	<script src="/i/web/png/pngtm.js"></script>
</head>
<body class="page page-id-104 page-template-default">
<div id="page-wrap">
	<div id="header">
		<div class="logo">
			<!--否定语句-->
			<img src="/i/web/images/logo.png" />
		</div>
		<div id="navigation">
			<div class="menu-%e8%8f%9c%e5%8d%951-container">
				<ul id="menu-%e8%8f%9c%e5%8d%951" class="menu">
					<li id="menu-item-59" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-59">
						<a href="<?= url("main", "index") ?>">首页</a>
					</li>
					<li id="menu-item-127" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-127">
						<a href="<?= url('main', 'products') ?>">会员商城</a>
					</li>
					<li id="menu-item-206" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-206">
						<a href="<?= url('main', 'news', array('cid'=>4)) ?>">行业资讯</a>
					</li>
					<li id="menu-item-105" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-206">
						<a href="<?= url('main','about', ['cid'=>1]) ?>">关于我们</a>
					</li>
					<li id="menu-item-102" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-102">
						<a href="<?= url('main','about', ['cid'=>7]) ?>">业务领域</a>
					</li>
					<li id="menu-item-280" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-280">
						<a href="<?= url('main','login') ?>">会员中心</a>
					</li>

				</ul>
			</div>
		</div>
	</div>
</div>
<?php include $_view_obj->compile($__template_file); ?>
<div id="footer">
	<div class="footer1 f_bq">Copyright &copy; 2014 All Rights Reserved 版权所有 &copy; <?=$config['site_CompanyName'] ?>
		备案：<?=$config["site_beian"] ?>
		联系电话： <?=$config["site_tel"] ?>　 联系人：<?=$config["site_Person"] ?>
	</div>
	<div style=" text-align:center;"></div>
</div>
</html>