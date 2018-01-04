<?php
/**
 * @author index
 *   ┏┓   ┏┓+ +
 *  ┏┛┻━━━┛┻┓ + +
 *  ┃       ┃
 *  ┃  ━    ┃ ++ + + +
 * ████━████┃+
 *  ┃       ┃ +
 *  ┃  ┻    ┃
 *  ┃       ┃ + +
 *  ┗━┓   ┏━┛
 *    ┃   ┃
 *    ┃   ┃ + + + +
 *    ┃   ┃     Codes are far away from bugs with the animal protecting
 *    ┃   ┃ +         神兽保佑,代码无bug
 *    ┃   ┃
 *    ┃   ┃   +
 *    ┃   ┗━━━┓ + +
 *    ┃       ┣┓
 *    ┃       ┏┛
 *    ┗┓┓┏━┳┓┏┛ + + + +
 *     ┃┫┫ ┃┫┫
 *     ┗┻┛ ┗┻┛+ + + +
 */

namespace common\library;
use common\core\Configure;


/**
 * Class RedisService
 * @package common\library
 * @method bool set($key, $value)
 * @method bool setex($key, $ttl, $value)
 * @method bool setnx($key, $value)
 * @method string|bool get($key)
 * @method int del($key)
 * @method int incr($key)
 * @method bool expire($key, $ttl)
 * @method bool expireAt($key, $timestamp)
 * @method int ttl($key)
 */
class RedisService extends ServiceBase
{
    /** @var \Redis $redis */
    protected $redis;

    /** @var int $dbindex */
    protected $dbindex;

    /** @var  string $passwd */
    protected $password;

    /**
     * RedisService constructor.
     * @param int $dbindex 数据库index
     */
    public function __construct($dbindex = 0)
    {
        $this->dbindex = $dbindex;
    }

    /** @return string 服务名 */
    protected function _getServiceName()
    {
        return Configure::get('redis.name', 'data.redis');
    }

    /** @return bool true多实例服务, false单实例服务 */
    protected function _isMultiService()
    {
        return Configure::get('redis.multi', false);
    }

    /**
     * ServiceBase call.
     * @memo 用户自定义请求方式
     * @param string $name 请求方法名
     * @param array $arguments 请求参数数组
     * @return mixed
     */
    protected function call($name, $arguments)
    {
        // 初始化redis链接
        if (!$this->init()) {
            Log::error("Redis service init failed.", $this->serviceList);
            $this->code = ReturnCode::DATABASE_CONNECT_FAILED;
            return null;
        }
        // 执行redis方法
        $result = call_user_func_array([$this->redis, $name], $arguments);
        // 断开连接
        $this->redis->close();
        // 返回结果
        return $result;
    }

    /**
     * RedisService init.
     * @memo 初始化redis链接
     */
    protected function init()
    {
        $this->redis = new \Redis();
        $ip = $this->serviceList[0]['ip'];
        $port = $this->serviceList[0]['port'];
        $password = Configure::get('redis.password');
        try {
            if (!$this->redis->connect($ip, $port)) return false;
            if ($password && !$this->redis->auth($password)) return false;
            if (!$this->redis->select($this->dbindex)) return false;
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }

}