<?php

use common\core\Configure;

// 定义应用根目录
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(__FILE__)));
}

// autoload
if (file_exists(APP_ROOT.'/lib/vendor/autoload.php')) {
    $loader = require_once APP_ROOT.'/lib/vendor/autoload.php';
} else {
    throw new Exception('Can\'t find autoload.php.');
}

// 加载配置文件
$files = [];
file_exists(APP_ROOT.'/config/config-default.ini') && $files[] = APP_ROOT.'/config/config-default.ini';
file_exists(APP_ROOT.'/config/config.ini') && $files[] = APP_ROOT.'/config/config.ini';
/* begin: 临时增加 */
file_exists(APP_ROOT.'/config/pipeline-default.yaml') && $files[] = APP_ROOT.'/config/pipeline-default.yaml';
file_exists(APP_ROOT.'/config/pipeline.yaml') && $files[] = APP_ROOT.'/config/pipeline.yaml';
file_exists(APP_ROOT.'/config/action-default.yaml') && $files[] = APP_ROOT.'/config/action-default.yaml';
file_exists(APP_ROOT.'/config/action.yaml') && $files[] = APP_ROOT.'/config/action.yaml';
/* end: 临时增加 */
Configure::load($files);

// 定义主调服务名
define('APP_NAME', Configure::get('app.name', 'logic.cmdb'));

// 设置时区
date_default_timezone_set(Configure::get('app.timezone', 'Asia/ShangHai'));

//设置环境目录路径
define('CACHE_PATH', APP_ROOT.'/cache');

//定义路径分隔符
define('DS', DIRECTORY_SEPARATOR);
