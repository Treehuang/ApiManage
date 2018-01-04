<?php

/**
 * 返回码管理类
 */
 
namespace common\library;

class Code {

    protected static $codeArray = null;
    protected static $codeStatus = null;

    public static function init()
    {
        require_once __DIR__ . "/codeExplains.php";
        require_once __DIR__ . "/codeStatus.php";
        self::$codeArray = $codeArray;
        self::$codeStatus = $codeStatus;
    }

    /**
     * 错误码解释
     * 
     * @param int $code 错误码
     * @return string 错误描述
     */
    public static function getCodeExplain($code)
    {
        if ($code == 0) {
            return '成功';
        }
        if (isset(self::$codeArray) && array_key_exists($code, self::$codeArray)) {
            return self::$codeArray[$code];
        } else {
            return '错误未定义';
        }
    }
    
    /**
     * 状态码解释
     * 
     * @param int $code 错误码
     * @return int 状态码
     */
    public static function getCodeStatus($code) {
        if ($code == 0) {
            return 200;
        }
        if (isset(self::$codeStatus)) {
            foreach (array_reverse(self::$codeStatus, true) as $key => $status) {
                if ($code >= $key) {
                    return $status;
                }
            }
        }
        return 500;
    }
    
    /** @var int 保存最后一次set的返回码*/
    private static $_lastCode;

    final static public function set_last_code($code) {
        self::$_lastCode = $code;
    }
    
    final static public function get_last_code() {
        return self::$_lastCode;
    }

    final static public function clear_last_code() {
        self::$_lastCode = [];
    }

}

Code::init();
