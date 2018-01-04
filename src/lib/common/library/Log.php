<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;
use Katzgrau\KLogger\Logger;
use Psr\Log\LogLevel;

/**
 * Class Log
 * @package common\library
 * 
 * @method static void info($message, array $context = array())
 * @method static void emergency($message, array $context = array())
 * @method static void alert($message, array $context = array())
 * @method static void critical($message, array $context = array())
 * @method static void error($message, array $context = array())
 * @method static void warning($message, array $context = array())
 * @method static void notice($message, array $context = array())
 * @method static void debug($message, array $context = array())
 */
abstract class Log
{
    /** @var Logger */
    private static $_logger;

    public static function __callStatic($name, $params) {
        // 获取堆栈信息
        $message = $params[0];
        $stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
        $params[0] = (isset($stack[2]) ? $stack[2]['class'].$stack[2]['type'].$stack[2]['function'].'(): ' : '').$stack[1]['file'].'('.$stack[1]['line'].'): '.$message;
        return call_user_func_array([self::logger(), $name], $params);
    }

    public static function logger() {
        static $initialized = false;

        if (!$initialized) {

            // 获取日志配置
            $logConfig = Configure::get('log');
            
            // 设置日志路径
            $logPath = !empty($logConfig['path']) ? $logConfig['path'] : APP_ROOT.'/log';
            
            // 设置日志级别
            $logLevelName = !empty($logConfig['level']) ? $logConfig['level'] : 'INFO';
            $logLevel = defined(LogLevel::class."::".$logLevelName) ? constant(LogLevel::class."::".$logLevelName) : LogLevel::DEBUG;

            // 实例化日志操作类
            self::$_logger = new Logger($logPath, $logLevel);

            $initialized = true;
        }

        return self::$_logger;
    }
}