<?php
/**
 * 一对一交友方案应用 api 入口文件
 */

// 调试模式开关
define("APP_DEBUG", false);

// 定义 APP 命名空间
define("APP_NAMESPACE", 'api');

// 定义CMF根目录,可更改此目录
define('CMF_ROOT', __DIR__ . '/../../');

// 定义应用目录
define('APP_PATH', CMF_ROOT . 'api/');

// 定义CMF目录
define('CMF_PATH', CMF_ROOT . 'simplewind/cmf/');

// 定义插件目录
define('PLUGINS_PATH', CMF_ROOT . 'plugins/');

// 定义扩展目录
define('EXTEND_PATH', CMF_ROOT . 'simplewind/extend/');
define('VENDOR_PATH', CMF_ROOT . 'simplewind/vendor/');

// 定义应用的运行时目录
define('RUNTIME_PATH', CMF_ROOT . '/data/runtime/api/');

// 加载框架基础文件
require CMF_ROOT . 'simplewind/thinkphp/base.php';

// 执行应用
\think\App::run()->send();
