<?php
/**
 * 配置文件管理类
 */
 
namespace common\core;

use Noodlehaus\AbstractConfig;
use Noodlehaus\Config;

abstract class Configure
{
    /** @var AbstractConfig $_config 配置实例*/
    private static $_config = null;

    /**
     * 加载配置文件
     *
     * @param string $path
     */
    public static function load($path) {

        is_string($path) && $path = [$path];
        array_unshift($path, __DIR__.'/defaultConfigure.php');
        self::$_config = Config::load($path);
    }

    /**
     * 获取所有配置信息
     *
     * @return array|null
     */
    public static function all()
    {
        return self::$_config->all();
    }

    /**
     * 根据Key获取配置值
     *
     * @param string $key 查询键
     * @param mixed|null $default 默认值
     * @return mixed|null
     */
    public static function get($key, $default = null) {

        return self::$_config->get($key, $default);
    }
}