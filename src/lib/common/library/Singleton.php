<?php
/**
 * 单实例基础类
 * 
 * @author index
 */

namespace common\library;

/**
 * Class Singleton
 * @package common\library
 */
class Singleton
{
    /** @var array 实例数组 */
    private static $_instances = array();

    /**
     * Singleton constructor.
     */
    protected function __construct()
    {
    }

    /** 禁止克隆 */
    final public function __clone()
    {
        trigger_error("clone method is not allowed.", E_USER_ERROR);
    }

    /** 获取实例 */
    final public static function getInstance()
    {
        $c = get_called_class();

        if(!isset(self::$_instances[$c])) {
            self::$_instances[$c] = new $c;
        }

        return self::$_instances[$c];
    }
}