<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP > 5.3.0 !');
}

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

// 绑定Admin模块到当前入口文件
//define('BIND_MODULE','Admin');

//define('BIND_CONTROLLER','Login'); // 绑定Index控制器到当前入口文件

//默认模块
//define('BIND_MODULE','Home');
// 默认控制器名称
// define('BIND_CONTROLLER','Index');


// 定义应用目录
define('APP_PATH', './Application/');

// 定义缓存目录
define('RUNTIME_PATH','./Runtime/');

// 定义模板文件默认目录
define("TMPL_PATH","./tpl/");

// 定义oss的url
define("OSS_URL","");


require  __DIR__ .'/vendor/autoload.php';
// require  __DIR__ .'/vendor/phpoffice/phpword/bootstrap.php';
// 引入ThinkPHP入口文件
require './Think/ThinkPHP.php';


