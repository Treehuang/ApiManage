<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

class MongoServiceEngine
{
    use ServiceEngineTrait;

    /** @var \MongoClient $_client */
    protected $_client;

    public function __call($name, $arguments)
    {

        /** @var \MongoClient|\MongoDB|\MongoCollection|NULL $instance 执行实例 */
        $instance = NULL;

        // 属于MongoClient的方法
        if (method_exists(\MongoClient::class, $name)) {
            $instance = $this->_client;
        } // 属于MongoDB的方法
        elseif (method_exists(\MongoDB::class, $name)) {
            $instance = $this->_client->selectDB($arguments[0]);
            $arguments = array_slice($arguments, 1);
        } // 属于MongoCollection的方法
        elseif (method_exists(\MongoCollection::class, $name)) {
            $instance = $this->_client->selectCollection($arguments[0], $arguments[1]);
            $arguments = array_slice($arguments, 2);
        } else {
            Log::warning('MongoServiceEngine: called method ' . $name . ' not exists');
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
            return NULL;
        }

        return $this->_callMongoMethod($instance, $name, $arguments);

    }

    /**
     * Call mongodb method.
     *
     * @param \MongoClient|\MongoDB|\MongoCollection $instance mongodb instance
     * @param string $name mongodb method name
     * @param array $arguments method parameters
     * @return mixed|null result
     */
    protected function _callMongoMethod($instance, $name, $arguments)
    {

        try {
            $this->_lastCode = 0;
            return call_user_func_array([$instance, $name], $arguments);
        } catch (\MongoResultException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_QUERY_FAILED;
        } catch (\MongoCursorTimeoutException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_REQUEST_TIMEOUT;
        } catch (\MongoDuplicateKeyException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_DUPLICATE_KEY;
        } catch (\MongoWriteConcernException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_INSERT_FAILED;
        } catch (\MongoCursorException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (\MongoConnectionException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
        } catch (\MongoGridFSException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        } catch (\MongoProtocolException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
        } catch (\MongoExecutionTimeoutException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_REQUEST_TIMEOUT;
        } catch (\MongoException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
        }
        return NULL;

    }

    /**
     * 初始化方法
     *
     * @access protected
     * @return bool
     */
    protected function _init()
    {
        $options = ['connect' => true];

        // 配置文件读取数据库用户名和密码
        $username = Configure::get('mongodb.username', false);
        $password = Configure::get('mongodb.password', false);
        if ($username && $password) {
            $options['username'] = $username;
            $options['password'] = $password;
        }
        $ip = $this->_serviceList[0]['ip'];
        $port = $this->_serviceList[0]['port'];

        // 首次连接, 获取信息
        try {
            $this->_client = new \MongoClient('mongodb://' . $ip . ':' . $port, $options);
        } catch (\MongoException $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
            return;
        }
        $db = $this->_client->selectDB('admin');
        $info = $db->command(['isMaster' => 1]);

        // 判断是否集群
        if (array_key_exists('setName', $info)) {
            // 集群模式
            $replicaSet = $info['setName'];
            $hosts = $info['hosts'];
            $address = 'mongodb://';
            foreach ($hosts as $host) {
                $address .= $host . ',';
            }
            $address = rtrim($address, ',');
            $options['replicaSet'] = $replicaSet;

            // 以集群模式重连数据库
            try {
                $this->_client = new \MongoClient($address, $options);
            } catch (\MongoException $exception) {
                Log::error($exception->getMessage());
                $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
                return;
            }
        }

        $this->_lastCode = 0;
    }
}