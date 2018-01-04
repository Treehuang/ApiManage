<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

/**
 * Class ElasticSearchService
 * @package common\library
 *
 * @method static mixed createDocument(string $index, string $type, string $id, array $document) 创建一个文档(重复的不创建)
 * @method static mixed indexDocument(string $index, string $type, string $id, array $document) 索引一个文档
 * @method static mixed retrieveDocument(string $index, string $type, string $id) 检索一个文档
 * @method static mixed deleteDocument(string $index, string $type, string $id) 删除一个文档
 * @method static mixed deleteIndex(string $index) 删除一个index
 * @method static int count(string|array|null $index = null, string|array|null $type = null, array $query = null) 删除一个index
 * @method static mixed search($index = null, $type = null, array|null $query = null, $from = 0, $size = 30, array|null $aggregations = null) 搜索文档
 * @method static mixed searchCountType($index = null, array $query = [])
 * @method static mixed bulkIndexDocs(string $index, string $type, array $ids, array $docs) 批量索引文档
 */
class ElasticSearchService
{
    use ServiceSingletonTrait;

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    public function _isMultiService()
    {
        return Configure::get('elastic_search.multi', false);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    public function _getServiceName()
    {
        return Configure::get('elastic_search.name', 'data.elastic_search');
    }

    /**
     * Get ServiceEngine instance.
     *
     * @param array $serviceList service instance list
     * @return ServiceEngineTrait ServiceEngine instance
     */
    public function _getServiceEngine($serviceList)
    {
        return new ElasticSearchServiceEngine($serviceList);
    }
}