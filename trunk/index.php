<?php
define('APP_NAME','Admin');				// 定义项目名称
define('APP_PATH','./Admin/');			// 定义项目目录
define('APP_DEBUG',false);				// 定义ThinkPHP目录 
define('THINK_PATH','./ThinkPHP/');		// 定义ThinkPHP目录 
define('THEME_NAME', 'default');
require('ThinkPHP/ThinkPHP.php');		// 加载入口文件
$App = new App();						// 实例化项目