<?php
/**
 * Created by IntelliJ IDEA.
 * User: lights
 * Date: 2016/12/22
 * Time: 下午7:14
 */

namespace common\library;

use common\core\Configure;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use easyops\easykin\core\ClientSpan;


/**
 * Class ElasticSearchService2
 * @package common\library
 * @method array delete($params)
 * @method array count($params = [])
 * @method array|boolean exists($params)
 * @method array create($params)
 * @method array bulk($params = [])
 * @method array index($params)
 * @method array search($params = [])
 * @method array updateByQuery($params = [])
 */
class ElasticSearchService2 extends ServiceBase
{
    /** @var Client */
    protected $client = null;

    protected static $instance;

    /** @var ClientSpan $span */
    protected $span;

    final public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    protected function _isMultiService()
    {
        return Configure::get('elastic_search.multi', false);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    protected function _getServiceName()
    {
        return Configure::get('elastic_search.name', 'data.elastic_search');
    }

    /**
     * init Elasticsearch link
     * @return bool
     */
    protected function init()
    {
        // 连接字符串
        $username = Configure::get('elastic_search.username', null);
        $password = Configure::get('elastic_search.password', null);
        $hosts = [];
        foreach ($this->serviceList as $service) {
            $host = [
                'host' => $service['ip'],
                'port' => $service['port']
            ];
            if (isset($username) && isset($password)) {
                $host['user'] = $username;
                $host['password'] = $password;
            }
            $hosts[] = $host;
        }

        // 获取重试次数
        $retry = Configure::get('elastic_search.retry', 0);

        // 链接数据库
        try {
            $this->client = ClientBuilder::create()->setHosts($hosts)->setRetries($retry)->build();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), [$hosts, $retry, $this->serviceList]);
            $this->code = ReturnCode::DATABASE_CONNECT_FAILED;
            return false;
        }

        return true;
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
        // 建立连接
        if (!$this->init()) {
            Log::error("Service init failed.", $this->serviceList);
            $this->code = ReturnCode::DATABASE_CONNECT_FAILED;
            return null;
        }

        // 执行数据库请求
        try {
            $this->traceSpan($name, $arguments);
            $result = call_user_func_array([$this->client, $name], $arguments);
            $this->traceSpanEnd();
            return $result;
        }
        catch (\Exception $exception) {
            Log::error($exception->getMessage(), [$name, $arguments]);
            $this->code = ReturnCode::DATABASE_ERROR;
            $this->traceSpanEnd();
            return null;
        }
    }

    /**
     * trace span
     * @param $name
     * @param $arguments
     * @param $databaseName
     * @param $collectionName
     */
    protected function traceSpan($name, $arguments)
    {
        if(!is_null(\EasyKin::getTrace())){
            $ip = $this->serviceList[0]['ip'];
            $port = $this->serviceList[0]['port'];
            $this->span = \EasyKin::newSpan($name, $this->_getServiceName(), $ip, $port);
            $this->span->tag("arguments", json_encode($arguments));
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
            Log::error("elastic_search trace span end failed ", [$exception->getCode(), $exception->getMessage()]);
        }
    }
}