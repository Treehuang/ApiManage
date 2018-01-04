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

namespace common\core;


use common\library\Log;
use common\library\MongoServiceCollection;
use common\library\ReturnCode;
use MongoDB\InsertOneResult;
use MongoDB\Model\IndexInfo;
use MongoDB\UpdateResult;

/**
 * Class MongoDAO
 * @package common\core
 */
abstract class MongoDAO
{
    /** @var string $dbName database name */
    protected $dbName = 'test';

    /** @var string $tbName table name */
    protected $tbName = 'test';

    /** @var array $indexes */
    protected $indexes = [];

    /** @var MongoServiceCollection $mongo */
    protected $mongo;

    /**
     * MongoDAO constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * MongoDAO init.
     */
    protected function init()
    {
        $this->mongo = new MongoServiceCollection($this->dbName, $this->tbName);
    }

    /**
     * MongoDAO save.
     * @memo 保存文档, 根据_id寻找文档, 文档存在则替换, 文档不存在则新建
     * @param array &$doc 待保存文档
     * @return int 返回码
     */
    public function save(&$doc)
    {
        // 获取id
        $filter = [];
        isset($doc['_id']) && $filter['_id'] = $doc['_id'];
        unset($doc['_id']);

        // 先尝试更新替换
        /** @var UpdateResult $result */
        $result = $this->mongo->replaceOne($filter, $doc, ['upsert' => true]);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }

        if (!$result->isAcknowledged()) return ReturnCode::DATABASE_UPDATE_FAILED;
        !isset($doc['_id']) && $doc['_id'] = $result->getUpsertedId();

        return 0;
    }

    /**
     * MongoDAO replace.
     * @memo 替换文档, 文档不存在则报错
     * @param array $doc 文档
     * @return int 返回码
     */
    public function replace($doc)
    {
        // 获取文档id
        if (!isset($doc['_id'])) return ReturnCode::DATABASE_NO_AFFECTED;
        $filter = ['_id' => $doc['_id']];
        unset($doc['_id']);

        // 更新文档
        $result = $this->mongo->replaceOne($filter, $doc);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }

        // 处理异常
        if (!$result->isAcknowledged()) return ReturnCode::DATABASE_UPDATE_FAILED;
        if ($result->getMatchedCount() === 0) return ReturnCode::DATABASE_NO_RESULT;

        return 0;
    }

    /**
     * MongoDAO insert.
     * @memo 新建文档
     * @param array &$doc 待插入文档
     * @return int 返回码
     */
    public function insert(&$doc)
    {
        /** @var InsertOneResult $result */
        $result = $this->mongo->insertOne($doc);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (!$result->isAcknowledged()) return ReturnCode::DATABASE_INSERT_FAILED;
        !isset($doc['_id']) && $doc['_id'] = $result->getInsertedId();

        return 0;
    }

    /**
     * MongoDAO update.
     * @memo 更新文档
     * @param array $filter 过滤器
     * @param array $update 更新操作
     * @return int 返回码
     */
    public function update(array $filter = [], array $update)
    {
        $result = $this->mongo->updateOne($filter, $update);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (!$result->isAcknowledged()) return ReturnCode::DATABASE_UPDATE_FAILED;
        if ($result->getMatchedCount() === 0) return ReturnCode::DATABASE_NO_RESULT;

        return 0;
    }

    /**
     * @param array $filter 过滤器
     * @param array $update 更新操作
     * @return int 返回码
     */
    public function updateMany(array $filter = [], array $update)
    {
        $result = $this->mongo->updateMany($filter, $update);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (!$result->isAcknowledged()) return ReturnCode::DATABASE_UPDATE_FAILED;
        if ($result->getMatchedCount() === 0) return ReturnCode::DATABASE_NO_RESULT;

        return 0;
    }

    /**
     * MongoDAO findBeforeUpdate.
     * @memo 更新数据, 返回更新前文档
     * @param array $filter 过滤器
     * @param array $update 更新操作
     * @param array &$doc 更新前文档
     * @return int
     */
    public function findBeforeUpdate(array $filter = [], array $update, &$doc)
    {
        $options['returnDocument'] = 1;
        $doc = $this->mongo->findOneAndUpdate($filter, $update, $options);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (is_null($doc)) {
            return ReturnCode::DATABASE_NO_RESULT;
        }

        return 0;
    }

    /**
     * MongoDAO findAfterUpdate.
     * @memo 更新数据, 返回更新后文档
     * @param array $filter 过滤器
     * @param array $update 更新操作
     * @param array &$doc 更新后文档
     * @return int
     */
    public function findAfterUpdate(array $filter = [], array $update, &$doc)
    {
        $options['returnDocument'] = 2;
        $doc = $this->mongo->findOneAndUpdate($filter, $update, $options);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (is_null($doc)) {
            return ReturnCode::DATABASE_NO_RESULT;
        }

        return 0;
    }

    /**
     * MongoDAO get.
     * @memo 根据_id查询文档
     * @param mixed $id 文档id
     * @param array|null &$doc 文档
     * @return int 返回码
     */
    public function get($id, &$doc)
    {
        return $this->findOne(['_id' => $id], $doc);
    }

    /**
     * MongoDAO delete.
     * @memo 根据_id删除文档
     * @param mixed $id 文档id
     * @return int 返回码
     */
    public function delete($id)
    {
        return $this->deleteOne(['_id' => $id]);
    }

    /**
     * MongoDAO deleteOne.
     * @memo 根据条件删除文档
     * @param array $filter 过滤器
     * @return int 返回码
     */
    public function deleteOne(array $filter)
    {
        $result = $this->mongo->deleteOne($filter);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (!$result->isAcknowledged()) return ReturnCode::DATABASE_DELETE_FAILED;
        if ($result->getDeletedCount() === 0) return ReturnCode::DATABASE_NO_RESULT;

        return 0;
    }

    /**
     * MongoDAO count.
     * @memo 根据filter条件统计
     * @param array $filter 过滤器
     * @param int &$count 总数
     * @return int 返回码 返回码
     */
    public function count(array $filter = [], &$count)
    {
        $count = $this->mongo->count($filter);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }

        return 0;
    }

    /**
     * MongoDAO findOne.
     *
     * @param array $filter 过滤器
     * @param array|null $doc 文档
     * @return int 返回码
     */
    public function findOne(array $filter = [], &$doc)
    {
        // 查询文档
        $doc= $this->mongo->findOne($filter);

        //Log::info("kinming: doc: ", $doc);

        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }
        if (is_null($doc)) {
            Log::info("kinming: doc为空");
            return ReturnCode::DATABASE_NO_RESULT;
        }

        return 0;
    }

    /**
     * MongoDAO findPage.
     *
     * @param array $filter 过滤器
     * @param int $page 页码, 默认1
     * @param int $pageSize 页大小, 默认20
     * @param array &$docs 文档列表
     * @return int 返回码
     */
    public function findPage(array $filter = [], $page = 1, $pageSize = 20, &$docs)
    {
        // 最小页码为1
        $page <= 0 && $page = 1;

        // 查询文档列表
        $cursor = $this->mongo->find($filter, ['skip' => ($page-1)*$pageSize, 'limit' => $pageSize]);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            return $code;
        }

        // 读取结果
        $docs = iterator_to_array($cursor);

        return 0;
    }

    /**
     * MongoDAO rebuildIndexes.
     * @param array &$result
     * @return int
     */
    public function rebuildIndexes(&$result)
    {
        $this->mongo->dropIndexes();
        $result = $this->mongo->createIndexes($this->indexes);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            Log::error("create indexes failed: $code.", $this->indexes);
            return $code;
        }
        Log::info("create indexes: ", $result);
        return 0;
    }

    /**
     * MongoDAO syncIndexes.
     * @param array &$result
     * @return int
     */
    public function syncIndexes(&$result)
    {
        $cursor = $this->mongo->listIndexes();
        $code = $this->mongo->getCode();
        if ($code !== 0) return $code;
        $dbIndexes = [];
        /** @var IndexInfo $index */
        foreach ($cursor as $index) {
            $dbIndexes[serialize($index->getKey())] = [
                'key' => $index->getKey(),
                'name' => $index->getName(),
                'unique' => $index->isUnique(),
                'sparse' => $index->isSparse(),
            ];
        }
        unset($dbIndexes[serialize(['_id' => 1])]);
        $indexes = [];
        foreach ($this->indexes as $index) {
            $indexes[serialize($index['key'])] = [
                'key' => $index['key'],
                'unique' => isset($index['unique']) ? $index['unique'] : false,
                'sparse' => isset($index['sparse']) ? $index['sparse'] : false,
            ];
        }

        // 删除多余索引
        $error = 0;
        foreach (array_diff(array_keys($dbIndexes), array_keys($indexes)) as $key) {
            $result = $this->mongo->dropIndex($dbIndexes[$key]['name']);
            $code = $this->mongo->getCode();
            if ($code !== 0) $error = $code;
        }

        // 比较已存在索引
        foreach (array_intersect(array_keys($indexes), array_keys($dbIndexes)) as $key) {
            if ($indexes[$key]['unique'] !== $dbIndexes[$key]['unique'] || $indexes[$key]['sparse'] !== $dbIndexes[$key]['sparse']) {
                $result = $this->mongo->dropIndex($dbIndexes[$key]['name']);
                $code = $this->mongo->getCode();
                if ($code !== 0) $error = $code;
                unset($dbIndexes[$key]);
            }
        }

        // 增加缺失索引
        foreach (array_diff(array_keys($indexes), array_keys($dbIndexes)) as $key) {
            $index = $indexes[$key];
            $key = $index['key'];
            $options = $index;
            unset($options['key']);
            $result = $this->mongo->createIndex($key, $options);
            $code = $this->mongo->getCode();
            if ($code !== 0) $error = $code;
        }

        // 处理异常
        if ($error !== 0) {
            Log::error("Sync indexes failed: $error");
            return $error;
        }

        return 0;
    }

    /**
     * MongoDAO search.
     * @param array $filter
     * @param int $page
     * @param int $pageSize
     * @param array $projection
     * @param array $sort
     * @param array &$list
     * @return int
     */
    public function search(array $filter = [], $page = 1, $pageSize = 30, array $projection = [], array $sort = [], &$list)
    {
        // 构建查询条件
        $options = [
            'limit' => $pageSize,
            'skip' => ($page-1)*$pageSize,
            'sort' => $sort,
            'projection' => $projection,
        ];

        // 查询数据
        $cursor = $this->mongo->find($filter, $options);
        $code = $this->mongo->getCode();
        if ($code !== 0) {
            Log::error("search data failed: $code.", [$filter, $options]);
            return $code;
        }

        $list = iterator_to_array($cursor);

        return 0;
    }


    /**
     * MongoDAO aggregate
     * @param array $pipeline
     * @param array $options
     * @param array $list
     * @return int
     */
    public function aggregate(array $pipeline , array $options = [],&$list)
    {
        // 查询数据
        $cursor = $this->mongo->aggregate($pipeline);

        $code = $this->mongo->getCode();
        if ($code !== 0) {
            Log::error("search data failed: $code.", [$pipeline, $options]);
            return $code;
        }

        $list = iterator_to_array($cursor);

        return 0;

    }
}

