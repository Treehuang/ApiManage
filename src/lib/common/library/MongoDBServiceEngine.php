<?php
/**
 * @author index
 */

namespace common\library;

use common\core\Configure;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Driver\Exception;
use MongoDB\Exception\BadMethodCallException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\RuntimeException;
use MongoDB\Exception\UnexpectedValueException;

class MongoDBServiceEngine
{
    use ServiceEngineTrait;

    /** @var Client $_client */
    private $_client;

    /** @var array $_methodBlockList */
    private static $_methodBlockList = ['drop', 'selectCollection', 'selectDatabase'];

    /**
     * @param string $name 方法名
     * @param array $arguments 参数数组
     * @return mixed|null 结果
     */
    public function __call($name, $arguments)
    {
        /** @var Collection|Database|NULL $instance 执行实例 */
        $instance = NULL;

        // 判断方法是否被禁用
        if (in_array($name, self::$_methodBlockList)) {
            Log::warning('MongoDBServiceEngine: called method ' . $name . ' was blocked');
            throw new \BadFunctionCallException('MongoDBServiceEngine: called method ' . $name . ' was blocked');
        }

        // 属于MongoDB\Client的方法
        if (method_exists(Client::class, $name)) {
            $instance = $this->_client;
        } // 属于MongoDB\Database的方法
        elseif (method_exists(Database::class, $name)) {
            $instance = $this->_client->selectDatabase($arguments[0]);
            $arguments = array_slice($arguments, 1);
        } // 属于MongoDB\Collection的方法
        elseif (method_exists(Collection::class, $name)) {
            $instance = $this->_client->selectCollection($arguments[0], $arguments[1]);
            $arguments = array_slice($arguments, 2);
        } else {
            Log::warning('MongoServiceEngine: called method ' . $name . ' not exists');
            throw new \BadFunctionCallException('MongoServiceEngine: called method ' . $name . ' not exists');
        }

        return $this->_callMongoMethod($instance, $name, $arguments);
    }

    protected function _callMongoMethod($instance, $name, $arguments)
    {
        try {
            $this->_lastCode = 0;
            return call_user_func_array([$instance, $name], $arguments);
        } catch (BadMethodCallException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (InvalidArgumentException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (RuntimeException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (UnexpectedValueException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (Exception\InvalidArgumentException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (Exception\RuntimeException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (Exception\UnexpectedValueException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        }
        return null;
    }

    /**
     * 初始化方法
     *
     * @access protected
     * @return void
     */
    protected function _init()
    {
        $this->_lastCode = 0;

        // 设置结果自动转字符串
        $driverOptions = ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']];

        // 设置连接参数
        $uriOptions = [];

        // 连接字符串前缀
        $connectionStringPrefix = 'mongodb://';

        // 获取数据库用户名和密码
        $username = Configure::get('mongodb.username', false);
        $password = Configure::get('mongodb.password', false);

        // 如果数据库用户名密码已设置, 更新连接字符串前缀
        $username && $password && $connectionStringPrefix .= $username . ':' . $password . '@';

        // 设置uri
        $uri = $connectionStringPrefix . $this->_serviceList[0]['ip'] . ':' . $this->_serviceList[0]['port'];

        // 以单机模式连接
        try {
            $this->_client = new Client($uri, $uriOptions, $driverOptions);
        } catch (Exception\ConnectionException $exception) {
            Log::error($exception->getMessage(), [$uri, $uriOptions, $driverOptions]);
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
            return;
        }

        // 获取数据库连接信息
        try {
            $info = $this->_client->selectDatabase('admin')->command(['isMaster' => 1])->toArray()[0];
        } catch (Exception\ConnectionException $exception) {
            Log::error($exception->getMessage(), [$uri, $uriOptions, $driverOptions]);
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
            return;
        }

        // 如果是单机模式, 则返回
        if (!array_key_exists('setName', $info)) {
            return;
        }

        // 以集群模式连接
        $uri = $connectionStringPrefix;
        foreach ($info['hosts'] as $host) {
            $uri .= $host . ',';
        }
        $uri = rtrim($uri, ',');
        $uriOptions['replicaSet'] = $info['setName'];
        try {
            $this->_client = new Client($uri, $uriOptions, $driverOptions);
        } catch (Exception\ConnectionException $exception) {
            Log::error($exception->getMessage(), [$uri, $uriOptions, $driverOptions]);
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
            return;
        }
    }
}