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


use MongoDB\Driver\Cursor;
use MongoDB\Model\CollectionInfoIterator;

/**
 * Class MongoServiceDatabase
 * @package common\library
 * @method Cursor command($command, array $options = [])
 * @method array|object createCollection($collection, array $options = [])
 * @method array|object dropCollection($collection, array $options = [])
 * @method CollectionInfoIterator listCollections(array $options = [])
 */
class MongoServiceDatabase extends MongoServiceBase
{
    /** @var string $databaseName */
    protected $databaseName;

    /** @var array $options */
    protected $options;

    /**
     * MongoServiceDatabase constructor.
     * @param string $databaseName 数据库名
     * @param array $options
     */
    public function __construct($databaseName, array $options = [])
    {
        $this->databaseName = $databaseName;
        $this->options = $options;
    }

    /**
     * @param string $collectionName 数据表名
     * @param array $options
     * @return MongoServiceCollection
     */
    public function selectCollection($collectionName, array $options = [])
    {
        return new MongoServiceCollection($this->databaseName, $collectionName, $options);
    }

    /**
     * ServiceBase call.
     * @memo 用户自定义请求方式
     * @param string $name 请求方法名
     * @param array $arguments 请求参数数组
     * @return mixed
     */
    final protected function __callMongo($name, $arguments)
    {
        $this->traceSpan($name, $arguments, $this->databaseName, '');
        // 执行数据库请求
        try {
            $database = $this->client->selectDatabase($this->databaseName, $this->options);
            return call_user_func_array([$database, $name], $arguments);
        }
        catch (\Exception $exception) {
            $this->code = ReturnCode::DATABASE_ERROR;
            return null;
        }
    }
}