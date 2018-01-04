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


use MongoDB\BulkWriteResult;
use MongoDB\DeleteResult;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\DuplicateKeyException;
use MongoDB\Driver\Exception\ExecutionTimeoutException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Exception\WriteException;
use MongoDB\InsertManyResult;
use MongoDB\InsertOneResult;
use MongoDB\Model\IndexInfoIterator;
use MongoDB\UpdateResult;
use Traversable;

/**
 * Class MongoServiceCollection
 * @package common\library
 * @method Traversable aggregate(array $pipeline, array $options = [])
 * @method BulkWriteResult bulkWrite(array $operations, array $options = [])
 * @method integer count($filter = [], array $options = [])
 * @method string createIndex($key, array $options = [])
 * @method string[] createIndexes(array $indexes)
 * @method DeleteResult deleteMany($filter, array $options = [])
 * @method DeleteResult deleteOne($filter, array $options = [])
 * @method mixed[] distinct($fieldName, $filter = [], array $options = [])
 * @method array|object drop()
 * @method array|object dropIndex($indexName, array $options = [])
 * @method array|object dropIndexes(array $options = [])
 * @method Cursor find($filter = [], array $options = [])
 * @method array|object|null findOne($filter = [], array $options = [])
 * @method object|null findOneAndDelete($filter, array $options = [])
 * @method object|null findOneAndReplace($filter, $replacement, array $options = [])
 * @method object|null findOneAndUpdate($filter, $update, array $options = [])
 * @method InsertManyResult insertMany(array $documents, array $options = [])
 * @method InsertOneResult insertOne($document, array $options = [])
 * @method IndexInfoIterator listIndexes(array $options = [])
 * @method UpdateResult replaceOne($filter, $replacement, array $options = [])
 * @method UpdateResult updateMany($filter, $update, array $options = [])
 * @method UpdateResult updateOne($filter, $update, array $options = [])
 */
class MongoServiceCollection extends MongoServiceBase
{
    /** @var string $databaseName */
    protected $databaseName;

    /** @var string $collectionName */
    protected $collectionName;

    /** @var array $options */
    protected $options;

    /**
     * MongoServiceCollection constructor.
     * @param string $databaseName 数据库名
     * @param string $collectionName 数据表名
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->options = $options;
    }

    /**
     * @memo 请求mongo并捕获exception
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    final protected function __callMongo($name, $arguments)
    {
        $this->traceSpan($name, $arguments, $this->databaseName, $this->collectionName);
        // 执行数据库请求
        try {
            $collection = $this->client->selectCollection($this->databaseName, $this->collectionName, $this->options);
            return call_user_func_array([$collection, $name], $arguments);
        }

        // 捕获数据库错误
        catch (AuthenticationException $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_LOGON_FAILED;
            return null;
        }
        catch (ConnectionException $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_CONNECT_FAILED;
            return null;
        }
        catch (ExecutionTimeoutException $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_REQUEST_TIMEOUT;
            return null;
        }
        catch (DuplicateKeyException $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_DUPLICATE_KEY;
            return null;
        }

        catch (WriteException $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_ERROR;

            $writeResult = $exception->getWriteResult();
            $writeErrs = $writeResult->getWriteErrors();
            if (count($writeErrs) > 0) {
                $writeErr = $writeErrs[0];
                $code = $writeErr->getCode();
                Log::error("$code");
                switch ($code){
                    case 11000: $this->code = ReturnCode::DATABASE_DUPLICATE_KEY;break;
                    case 17280: $this->code = ReturnCode::DATABASE_KEY_TOO_LARGE_TO_INDEX;break;
                    default :   $this->code = ReturnCode::DATABASE_ERROR;break;
                }
            }
            return null;
        }
        catch (RuntimeException $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_ERROR;

            $code = $exception->getCode();
            switch ($code){
                case 11000: $this->code = ReturnCode::DATABASE_DUPLICATE_KEY;break;
                case 17280: $this->code = ReturnCode::DATABASE_KEY_TOO_LARGE_TO_INDEX;break;
                default :   $this->code = ReturnCode::DATABASE_ERROR;break;
            }
            return null;
        }

        catch (\Exception $exception) {
            Log::error($exception->getMessage());
            $this->code = ReturnCode::DATABASE_ERROR;
            return null;
        }
    }
}