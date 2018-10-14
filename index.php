<?php


// 应用入口文件
// 检测是否是新安装
//if(file_exists("./Public/install") && !file_exists("./Public/install/install.lock")){
//	// 组装安装url
//	$url=$_SERVER['HTTP_HOST'].trim($_SERVER['SCRIPT_NAME'],'index.php').'Public/install/index.php';
//	// 使用http://域名方式访问；避免./Public/install 路径方式的兼容性和其他出错问题
//	header("Location:http://$url");
//	die;
//}


// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',false);

// 定义应用目录
define('APP_PATH','./Application/');

// 定义缓存目录
define('RUNTIME_PATH','./Runtime/');

// 定义模板主题
define("DEFAULT_THEME","default");

// 定义模板文件默认目录
define("TMPL_PATH","./Template/".DEFAULT_THEME."/");

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
