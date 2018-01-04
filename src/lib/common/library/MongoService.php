<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;
use MongoCode;
use MongoCommandCursor;
use MongoCursor;

/**
 * Class MongoService
 * @deprecated 将要废弃, 不要使用, 由更简单的MongoServiceClient代替
 * @package common\library
 *
 * MongoClient
 * @method static array listDBs()
 *
 * MongoDB
 * @method static array command($database, array $command, array $options = array())
 * @method static array getCollectionInfo($database, array $options = array())
 * @method static array getCollectionNames($database, array $options = array())
 * @method static array listCollections($database, array $options = array())
 *
 * MongoCollection
 * @method static array aggregate($database, $collection, array $pipeline, array $options)
 * @method static MongoCommandCursor aggregateCursor($database, $collection, array $command, array $options)
 * @method static mixed batchInsert($database, $collection, array $a, array $options = array())
 * @method static int count($database, $collection, array $query = array(), array $options = array())
 * @method static bool createIndex($database, $collection, array $keys, array $options = array())
 * @method static array deleteIndex($database, $collection, string|array $keys)
 * @method static array deleteIndexes($database, $collection)
 * @method static array distinct($database, $collection, string $key, array $query)
 * @method static MongoCursor find($database, $collection, array $query = array(), array $fields = array())
 * @method static array findAndModify($database, $collection, array $query, array $update, array $fields, array $options)
 * @method static array findOne($database, $collection, array $query = array(), array $fields = array(), array $options = array())
 * @method static array getIndexInfo($database, $collection)
 * @method static string getName($database, $collection)
 * @method static array group($database, $collection, mixed $keys, array $initial, MongoCode $reduce, array $options = array())
 * @method static bool|array insert($database, $collection, array|object $document, array $options = array())
 * @method static bool|array remove($database, $collection, array $criteria = array(), array $options = array())
 * @method static mixed save($database, $collection, array|object $document, array $options = array())
 * @method static bool|array update($database, $collection, array $criteria, array $new_object, array $options = array())
 */
class MongoService
{

    use ServiceSingletonTrait;

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    protected function _isMultiService()
    {
        return Configure::get('mongodb.multi', false);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    protected function _getServiceName()
    {
        return Configure::get('mongodb.name', 'data.mongodb');
    }

    /**
     * Get ServiceEngine instance.
     *
     * @param array $serviceList service instance list
     * @return ServiceEngineTrait ServiceEngine instance
     */
    protected function _getServiceEngine($serviceList)
    {
        return new MongoServiceEngine($serviceList);
    }
}