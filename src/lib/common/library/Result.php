<?php

/*
 * 标准化结果输出类
 */

namespace common\library;

class Result {
    private static $error = null;

    public static function setError($error) {
        self::$error = $error;
    }


    /**
     * 返回标准信息
     *
     * @param int $status http状态码
     * @param int $code 结果代码
     * @param string $message 提示信息
     * @param mixed $data 附加数据
     */
    public static function Json($status, $code, $message = 'Unkown error', $data = null) {
        if ($message !="Unkown error")
        {
            self::setError($message);
        }
        $codeExplain = Code::getCodeExplain($code);
        $result = array(
           'code' => $code,
           'error' => $codeExplain,
           'message' => self::$error,
           'data' => $data,
        );
        Log::debug('Response: ', $result);
        \Flight::json( $result, $status );
    }
    
    /**
     * 返回标准错误信息
     * 
     * @param $code int 错误代码
     * @param $message string 提示信息
     * @param $data mixed 附加数据
     */
    public static function Error($code, $message = 'Unkown error', $data = null) {
        //self::Json(Code::getCodeStatus($code), $code, $message, $data);
        self::Json(200, 400, $message, $data);
    }

    /**
     * 返回参数错误信息
     * 
     * @param array|string $error 参数错误的详细信息
     * @param int $code 
     */
    public static function ParamError($error, $code=0) {
        if ( $code == 0 ) {
            $code = ReturnCode::PARAMETER_ERROR;
        }
        self::Error($code, 'Invalid parameters', $error);
    }
    
    /*
     * 返回成功信息
     * 
     * @param $data mixed 返回的数据
     */
    public static function Success($data=null) {
        self::Json(200, 0, 'Success', $data);
    }
}
