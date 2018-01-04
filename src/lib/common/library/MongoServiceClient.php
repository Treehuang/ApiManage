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


use MongoDB\Model\DatabaseInfoIterator;

/**
 * Class MongoServiceClient
 * @package common\library
 * @method array|object dropDatabase($databases, array $options = [])
 * @method DatabaseInfoIterator listDatabases(array $options = [])
 */
class MongoServiceClient extends MongoServiceBase
{
    /**
     * @param string $databaseName 数据库名
     * @param array $options
     * @return MongoServiceDatabase
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        return new MongoServiceDatabase($databaseName, $options);
    }

    /**
     * @param string $databaseName 数据库名
     * @param string $collectionName 数据表名
     * @param array $options
     * @return MongoServiceCollection
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        return new MongoServiceCollection($databaseName, $collectionName, $options);
    }

    /**
     * @memo 请求mongo方法
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    final protected function __callMongo($name, $arguments)
    {
        try {
            return call_user_func_array([$this->client, $name], $arguments);
        }
        catch (\Exception $exception) {
            $this->code = ReturnCode::DATABASE_ERROR;
            return null;
        }
    }
}