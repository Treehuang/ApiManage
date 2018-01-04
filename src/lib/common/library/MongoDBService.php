<?php
/**
 * @author index
 */

namespace common\library;

use common\core\Configure;
use MongoDB\BulkWriteResult;
use MongoDB\DeleteResult;
use MongoDB\Driver\Cursor;
use MongoDB\InsertManyResult;
use MongoDB\InsertOneResult;
use MongoDB\Model\CollectionInfoIterator;
use MongoDB\Model\DatabaseInfoIterator;
use MongoDB\Model\IndexInfoIterator;
use MongoDB\UpdateResult;
use Traversable;


/**
 * Class MongoDBService
 * @deprecated 将要废弃, 不要使用, 由更简单的MongoServiceClient代替
 * @package common\library
 *
 * Client
 * @method static array|object dropDatabase($databases, array $options = [])
 * @method static DatabaseInfoIterator listDatabases(array $options = [])
 *
 * Database
 * @method static Cursor command($database, $command, array $options = [])
 * @method static array|object createCollection($database, $collection, array $options = [])
 * @method static array|object dropCollection($database, $collection, array $options = [])
 * @method static CollectionInfoIterator listCollections($database, array $options = [])
 *
 * Collection
 * @method static Traversable aggregate(string $database, $collection, array $pipeline, array $options = [])
 * @method static BulkWriteResult bulkWrite($database, $collection, array $operations, array $options = [])
 * @method static integer count($database, $collection, $filter = [], array $options = [])
 * @method static string createIndex($database, $collection, $key, array $options = [])
 * @method static string[] createIndexes($database, $collection, array $indexes)
 * @method static DeleteResult deleteMany($database, $collection, $filter, array $options = [])
 * @method static DeleteResult deleteOne($database, $collection, $filter, array $options = [])
 * @method static mixed[] distinct($database, $collection, $fieldName, $filter = [], array $options = [])
 * @method static array|object dropIndex($database, $collection, $indexName, array $options = [])
 * @method static array|object dropIndexes($database, $collection, array $options = [])
 * @method static Cursor find($database, $collection, $filter = [], array $options = [])
 * @method static array|object|null findOne($database, $collection, $filter = [], array $options = [])
 * @method static object|null findOneAndDelete($database, $collection, $filter, array $options = [])
 * @method static object|null findOneAndReplace($database, $collection, $filter, $replacement, array $options = [])
 * @method static object|null findOneAndUpdate($database, $collection, $filter, $update, array $options = [])
 * @method static InsertManyResult insertMany($database, $collection, array $documents, array $options = [])
 * @method static InsertOneResult insertOne($database, $collection, $document, array $options = [])
 * @method static IndexInfoIterator listIndexes($database, $collection, array $options = [])
 * @method static UpdateResult replaceOne($database, $collection, $filter, $replacement, array $options = [])
 * @method static UpdateResult updateMany($database, $collection, $filter, $update, array $options = [])
 * @method static UpdateResult updateOne($database, $collection, $filter, $update, array $options = [])
 */
class MongoDBService
{
    use ServiceSingletonTrait;

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    public function _isMultiService()
    {
        return Configure::get('mongodb.multi', false);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    public function _getServiceName()
    {
        return Configure::get('mongodb.name', 'data.mongodb');
    }

    /**
     * Get ServiceEngine instance.
     *
     * @param array $serviceList service instance list
     * @return ServiceEngineTrait ServiceEngine instance
     */
    public function _getServiceEngine($serviceList)
    {
        return new MongoDBServiceEngine($serviceList);
    }
}