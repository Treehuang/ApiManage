<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;
use Medoo\Medoo;

class MySQLServiceEngine
{
    use ServiceEngineTrait;

    /** @var medoo $mysql */
    private $mysql;

    public function __call($name, $arguments)
    {
        $this->_lastCode = 0;
        try {
            return call_user_func_array([$this->mysql, $name], $arguments);
        } catch (\Exception $exception) {
            $this->_lastCode = ReturnCode::DATABASE_ERROR;
            Log::error($exception->getMessage());
        }
        return null;
    }

    /**
     * @return array 上次执行错误信息
     */
    public function error()
    {
        return $this->mysql->error();
    }

    /**
     * @return mixed
     */
    protected function _init()
    {
        $dbName = Configure::get('mysql.database', 'anyclouds_cmdb');
        $username = Configure::get('mysql.username');
        $password = Configure::get('mysql.password');
        $charset = Configure::get('mysql.charset', 'utf8');
        $prefix = Configure::get('mysql.prefix');
        
        $ip = $this->_serviceList[0]['ip'];
        $port = $this->_serviceList[0]['port'];
        
        $options = [
            'database_type' => 'mysql',
            'database_name' => $dbName,
            'server'        => $ip,
            'port'          => $port,
            'username'      => $username,
            'password'      => $password,
            'charset'       => $charset,
            'prefix'        => $prefix,
        ];
        
        try {
            $this->mysql = new medoo($options);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            $this->_lastCode = ReturnCode::DATABASE_CONNECT_FAILED;
            return;
        }
        
        $this->_lastCode = 0;
    }
}