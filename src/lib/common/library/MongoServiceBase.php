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
use MongoDB\Client;
use MongoDB\Driver\Exception\ConnectionException;
use easyops\easykin\core\ClientSpan;

/**
 * Class MongoServiceBase
 * @package common\library
 */
abstract class MongoServiceBase extends ServiceBase
{
    /** @var Client $client */
    protected $client;

    /** @var ClientSpan $span */
    protected $span;

    private $serviceIp;

    private $servicePort;
    /**
     * MongoServiceBase constructor.
     */
    public function __construct()
    {
    }

    /** @return string 服务名 */
    protected function _getServiceName()
    {
        return Configure::get('mongodb.name', 'data.mongodb');
    }

    /** @return bool true多实例服务, false单实例服务 */
    protected function _isMultiService()
    {
        //return Configure::get('mongodb.multi', true);
        return true;
    }


    protected function init()
    {
        // 设置结果自动转字符串
        $driverOptions = ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']];

        // 设置连接参数
        $uriOptions = [];
        // 服务数大于1, 则认为是集群模式
        count($this->serviceList) > 1 && $uriOptions['replicaSet'] = Configure::get('mongodb.replica_set', 'easyops');

        // 是否只连接一次服务
        $uriOptions["serverSelectionTryOnce"] = false;
        // 尝试多次连接的超时时间(毫秒)
        $uriOptions["serverSelectionTimeoutMS"] = 6000;

        // 连接字符串
        $uri = 'mongodb://';
        $username = Configure::get('mongodb.username', false);
        $password = Configure::get('mongodb.password', false);
        $username && $password && $uri .= $username . ':' . $password . '@';
        $serviceList = [];
        foreach ($this->serviceList as $service) $serviceList[] = $service['ip'].':'.$service['port'];
        $uri .= implode(',', $serviceList);

        $this->serviceIp   = $this->serviceList[0]['ip'];
        $this->servicePort = $this->serviceList[0]['port'];
        // 链接数据库
        try {
            $this->client = new Client($uri, $uriOptions, $driverOptions);
        } catch (ConnectionException $exception) {
            Log::error($exception->getMessage(), [$uri, $uriOptions, $driverOptions]);
            $this->code = ReturnCode::DATABASE_CONNECT_FAILED;
            return false;
        }

        return true;
    }

    /**
     * @memo ping一下Mongo尝试连接
     * @return bool
     */
    public function pingMongo()
    {
        try {
            $this->client->selectDatabase("admin")->command(["ping" => 1]);
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }


    /**
     * @memo 访问访问mongo的方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    protected abstract function __callMongo($name, $arguments);


    /**
     * @memo 初始化mongo连接并请求mongo
     * @param string $name
     * @param array $arguments
     * @return mixed|null
     */
    protected function call($name, $arguments)
    {
        // 建立连接
        if (!$this->init()) {
            Log::error("MongoDB service init failed.", $this->serviceList);
            $this->code = ReturnCode::DATABASE_CONNECT_FAILED;
            return null;
        }

        /*
         * 这种做法是因为Mongodb的PHP驱动在mongo server关闭重启时，连接池的连接状态处于CLOSE_WAITE状态
         * 处于CLOSE_WAITE状态的连接不可用，尝试ping多次来确保连接是可用的
         * 尝试次数不应该少于3次，详细参见 https://github.com/mongodb/mongo-php-driver/issues/597
         */
        $try = 3;
        while ($try--) {
            if ($this->pingMongo()) break;
            usleep(1500 * 1000);
        }

        $result = $this->__callMongo($name, $arguments);
        $this->traceSpanEnd();
        return $result;
    }

    /**
     * trace span
     * @param $name
     * @param $arguments
     * @param $databaseName
     * @param $collectionName
     */
    protected function traceSpan($name, $arguments, $databaseName, $collectionName)
    {
        if(!is_null(\EasyKin::getTrace())){
            $this->span = \EasyKin::newSpan($name, $this->_getServiceName(), $this->serviceIp, $this->servicePort);
            $this->span->tag("database", is_null($databaseName)? '' : $databaseName);
            $this->span->tag("tableName",is_null($collectionName)? '' : $collectionName);
            $this->span->tag("arguments", json_encode($arguments));
        }
    }

    /**
     * set span tag
     * @param $key
     * @param $value
     */
    protected function setSpanTag($key, $value)
    {
        try {
            if (!is_null($this->span)) {
                $this->span->tag($key, $value);
            }
        }
        catch (\Exception $exception)
        {
            Log::error("mongodb trace tag failed ", [$exception->getCode(), $exception->getMessage()]);
        }
    }

    /**
     * trace span receive
     */
    protected function traceSpanEnd()
    {
        try {
            if (!is_null($this->span)) {
                $this->span->tag("code", $this->getCode());
                $this->span->receive();
            }
        }
        catch (\Exception $exception)
        {
            Log::error("mongodb trace span end failed ", [$exception->getCode(), $exception->getMessage()]);
        }
    }
}