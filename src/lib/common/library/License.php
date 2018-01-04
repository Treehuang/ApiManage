<?php

namespace common\library;


/**
 * Class License
 * @package common\library
 */
class License
{
    /** license 的版本 (社区版, 企业版等) */
    const COMMUNITY = 0;
    const ENTERPRISE = 1;

    /** @var bool|array $info */
    private static $info = null;

    /**
     * License inti.
     */
    public static function init()
    {
        self::$info = [];
        $data = zend_loader_file_licensed();
        if (empty($data)) return;
        foreach ($data as $key => $value) {
            self::$info[strtolower($key)] = $value;
        }
    }

    /**
     * License get.
     * @memo 获取license中的信息, 不存在则返回null
     * @param string $key
     * @return null|string
     */
    public static function get($key)
    {
        $key = strtolower($key);
        if (self::$info && isset(self::$info[$key])) {
            return self::$info[$key];
        }
        return null;
    }

    /**
     * License checkOrg.
     * @memo 获取license中的org列表, 无限制则返回null
     * @param int $org 组织ID
     * @return int 返回码: 130603超出许可范围
     */
    public static function checkOrg($org)
    {
        return self::checkLimit('org', $org, function($info, $org) {
            $list = explode(',', $info);
            foreach ($list as $value) {
                if (intval($value) === $org) return true;
            }
            return false;
        });
    }

    /**
     * License checkLimit.
     * @memo 检查限制逻辑,
     * @param string $key 条件key
     * @param mixed $value 条件值
     * @param callable $func bool callback(string $info, mixed $value) true通过检查
     * @return int 返回码: 130603超出许可范围
     */
    public static function checkLimit($key, $value, $func)
    {
        $info = self::get($key);
        if (!is_null($info) && !$func($info, $value)) return ReturnCode::PERMISSION_LICENSE_LIMIT;
        return 0;
    }

    /**
     * License checkMaximumLimit.
     * @memo 检查最大限制
     * @param string $key 条件key
     * @param mixed $value 条件值
     * @return int 返回码: 130603超出许可范围
     */
    public static function checkMaximumLimit($key, $value)
    {
        return self::checkLimit($key, $value, function($info, $value) {
            if (is_int($value)) {
                return intval($info) >= $value;
            }
            elseif (is_float($value)) {
                return floatval($info) >= $value;
            }
            else {
                return $info >= strval($value);
            }
        });
    }

    /**
     * @memo 根据license获取系统的版本
     * @return int
     */
    public static function getEdition()
    {
        $edition = self::get('Product-Edition');
        switch ($edition)
        {
            case 'Community' :
                return self::COMMUNITY;

            case 'Enterprise' :
                return self::ENTERPRISE;

            default :
                return self::ENTERPRISE;
        }
    }
}

