<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

/**
 * Class MySQLService
 * @package common\library
 *
 * @method static array select($table, $columns, $where)
 * @method static int insert($table, $data)
 * @method static int update($table, $data, $where)
 * @method static int delete($table, $where)
 * @method static array|string get($table, $columns, $where)
 * @method static bool has($table, $where)
 * @method static int count($table, $where)
 * @method static int max($table, $column, $where)
 * @method static int min($table, $column, $where)
 * @method static int avg($table, $column, $where)
 * @method static int sum($table, $column, $where)
 * @method static \PDOStatement query($query)
 * @method static array error()
 */
class MySQLService
{
    use ServiceSingletonTrait;

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    protected function _isMultiService()
    {
        return Configure::get('mysql.multi', false);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    protected function _getServiceName()
    {
        return Configure::get('mysql.name', 'data.mysql');
    }

    /**
     * Get ServiceEngine instance.
     *
     * @param array $serviceList service instance list
     * @return ServiceEngineTrait ServiceEngine instance
     */
    protected function _getServiceEngine($serviceList)
    {
        return new MySQLServiceEngine($serviceList);
    }
}