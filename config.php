<?php
if(!defined('THINK_PATH')) exit();
return array(
	'DB_TYPE'		=>	'mysql',// 数据库类型	
	'DB_HOST'		=>	'localhost',// 数据库服务器地址
	'DB_NAME'		=>	'lvfoadb',// 数据库名称
	'DB_USER'		=>	'root',// 数据库用户名
	'DB_PWD'		=>	'root',// 数据库密码
	'DB_PREFIX'		=>	'tb_',// 数据表前缀
	'DB_CHARSET'	=>	'utf8',// 网站编码
	'DB_PORT'		=>	'3306',// 数据库端口
	'APP_AUTOLOAD_PATH' => 'ORG.Util',	//设置自动加载路径
	'THEME_NAME'    =>  'default',
	
	//网站系统设置
	'SITE_NAME'			=>  '绿风OA管理系统',
	'SITE_KEYWORDS'		=>  'ysg',
	'SITE_DESCRIPTION'	=>  'ysg',
	'EMAIL'				=>	'924811784@qq.com',
	'OFFLINEMESSAGE'	=>	'',
	'XPCMS_VERSION'		=>	'1.0'
);
?>