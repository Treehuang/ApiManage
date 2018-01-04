<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

class ElasticSearchServiceEngine
{
    use ServiceEngineTrait;

    protected $_address;
    protected $_host;

    /**
     * 创建文档(重复不创建)
     *
     * @param string $index _index
     * @param string $type _type
     * @param string|null $id _id
     * @param array $document 需索引的文档
     * @return array 返回结果
     */
    public function createDocument($index, $type, $id = null, $document)
    {

        // 请求uri: /$index/$type/$id/_create
        $uri = 'http://' . $this->_address . '/' . $index . '/' . $type;

        // 判断请求是否制定document id
        if (!empty($id)) {
            $uri .= '/' . $id;
            unset($document['_id']);
        }


        // 制定请求为创建模式
        $uri .= '/_create';

        // 执行请求
        $response = EasyCurl::post($uri, null, json_encode($document));

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);
        return $response;
    }

    /**
     * 索引文档
     *
     * @param string $index _index
     * @param string $type _type
     * @param string|null $id _id
     * @param array $document 需索引的文档
     * @return array 返回结果
     */
    public function indexDocument($index, $type, $id = null, $document)
    {

        // 请求uri: /$index/$type/$id
        $uri = 'http://' . $this->_address . '/' . $index . '/' . $type;

        // 判断请求是否制定document id
        if (!empty($id)) {
            $uri .= '/' . $id;
            unset($document['_id']);
        }

        if (empty($id)) {
            $response = EasyCurl::post($uri, null, json_encode($document));
        } else {
            $response = EasyCurl::put($uri, null, json_encode($document));
        }

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);
        return $response;
    }


    /**
     * 检索文档
     *
     * @param string $index _index
     * @param string $type _type
     * @param string|null $id _id
     * @return array 返回结果
     */
    public function retrieveDocument($index, $type, $id)
    {

        // 请求uri: /$index/$type/$id?pretty
        $uri = 'http://' . $this->_address . '/' . $index . '/' . $type . '/' . $id . '?pretty';

        $response = EasyCurl::get($uri, null);

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);
        return $response;
    }

    /**
     * 检索文档
     *
     * @param string $index _index
     * @param string $type _type
     * @param string|null $id _id
     * @return array 返回结果
     */
    public function deleteDocument($index, $type, $id)
    {

        // 请求uri: /_index/_type/_id/_create
        $uri = 'http://' . $this->_address . '/' . $index . '/' . $type . '/' . $id;

        $response = EasyCurl::delete($uri);

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);
        return $response;
    }

    /**
     * 删除index
     *
     * @param string $index _index
     * @return array 返回结果
     */
    public function deleteIndex($index)
    {

        // 请求uri: /$index
        $uri = 'http://' . $this->_address . '/' . $index;

        $response = EasyCurl::delete($uri);

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);
        return $response;
    }

    /**
     * 统计符合查询条件的文档数
     *
     * @param string|array|null $index _index
     * @param string|array|null $type _type
     * @param array|null $query 查询条件
     * @return int 结果数量
     */
    public function count($index = null, $type = null, $query = null)
    {

        $uri = 'http://' . $this->_address;

        // 处理$index
        if (empty($index)) {
            $uri .= '/_all';
        } elseif (is_string($index)) {
            $uri .= '/' . $index;
        } elseif (is_array($index)) {
            $uri .= '/' . implode(',', $index);
        } else {
            trigger_error("Argument index should be string|array|null", E_USER_ERROR);
        }

        // 处理$type
        if (empty($type)) {
        } elseif (is_string($type)) {
            $uri .= '/' . $type;
        } elseif (is_array($type)) {
            $uri .= '/' . implode(',', $type);
        } else {
            trigger_error('Argument type should be string|array|null', E_USER_ERROR);
        }

        $uri .= '/_count';

        // 处理查询条件
        $data = [];
        !empty($query) && is_array($query) && $data['query'] = $query;

        // 执行请求
        $response = EasyCurl::post($uri, null, json_encode($data));

        // 解析结果
        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);

        // 获取结果
        $count = isset($response['body']['count']) ? $response['body']['count'] : 0;

        return $count;
    }

    /**
     * 搜索文档
     *
     * @param string|null $index _index
     * @param string|null $type _type
     * @param array|null $query 查询条件
     * @param int $from 起始位置
     * @param int $size 文档数
     * @param array|null $aggregations 聚合
     * @return array|null 返回结果
     */
    public function search($index = null, $type = null, $query = null, $from = 0, $size = 30, $aggregations = null)
    {

        $uri = 'http://' . $this->_address;

        // 处理$index
        if (empty($index)) {
            $uri .= '/_all';
        } elseif (is_string($index)) {
            $uri .= '/' . $index;
        } elseif (is_array($index)) {
            $uri .= '/' . implode(',', $index);
        } else {
            trigger_error('Argument index should be string|array|null', E_USER_ERROR);
            return NULL;
        }

        // 处理$type
        if (empty($type)) {
        } elseif (is_string($type)) {
            $uri .= '/' . $type;
        } elseif (is_array($type)) {
            $uri .= '/' . implode(',', $type);
        } else {
            trigger_error('Argument type should be string|array|null', E_USER_ERROR);
            return NULL;
        }
        $uri .= '/_search';

        $data = [];
        !is_null($query) && $data['query'] = $query;
        !is_null($aggregations) && $data['aggregations'] = $aggregations;
        $data['from'] = $from;
        $data['size'] = $size;

        $response = EasyCurl::post($uri, null, json_encode($data));

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);

        return $response;
    }

    public function searchCountType($index = null, $query = [])
    {
        $uri = 'http://' . $this->_address;

        // 处理$index
        if (empty($index)) {
            $uri .= '/_all';
        } elseif (is_string($index)) {
            $uri .= '/' . $index;
        } elseif (is_array($index)) {
            $uri .= '/' . implode(',', $index);
        } else {
            trigger_error('Argument index should be string|array|null', E_USER_ERROR);
            return NULL;
        }

        $uri .= '/_search';
        $data = ['size' => 0];
        !empty($query) && $data['query'] = $query;
        $data['aggregations'] = [
            'types' => [
                'terms' => [
                    'field' => '_type'
                ]
            ]
        ];

        $response = EasyCurl::post($uri, null, json_encode($data));
        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);

        return $response;
    }

    /**
     * 批量索引文档
     *
     * @param string $index _index
     * @param string $type _type
     * @param array $ids 文档id数组
     * @param array $docs 文档数组
     * @return array|null 返回结果
     */
    public function bulkIndexDocs($index, $type, $ids, $docs)
    {

        $uri = 'http://' . $this->_address . '/_bulk';

        if (count($ids) !== count($docs)) {
            trigger_error("Count of ids should be equal to count of docs", E_USER_ERROR);
            return NULL;
        }

        $data = '';
        foreach ($ids as $i => $id) {

            $meta = [];
            $meta['_index'] = empty($id['index']) ? $index : $id['index'];
            $meta['_type'] = empty($id['type']) ? $type : $id['type'];
            $doc = $docs[$i];

            // 检查_index和_type是否为空, 忽略没有明确_index和_type的文档
            if (empty($meta['_index'] || empty($meta['_type']))) continue;

            if (!empty($id['id'])) {
                $meta['_id'] = $id['id'];
                unset($doc['_id']);
            }

            $data .= json_encode(['index' => $meta]) . "\n";
            $data .= json_encode($doc) . "\n";
        }

        $response = EasyCurl::post($uri, null, $data);

        $this->_lastCode = $response['code'];
        isset($response['body']) && !is_null(json_decode($response['body'])) && $response['body'] = json_decode($response['body'], true);

        return $response;
    }

    /**
     * 初始化方法
     *
     * @access protected
     * @return bool
     */
    protected function _init()
    {
        $this->_address = $this->_serviceList[0]['ip'] . ':' . $this->_serviceList[0]['port'];
        $config = Configure::get('elastic_search');
        $this->_host = isset($config['host']) ? $config['host'] : '';
        $this->_lastCode = 0;
    }
}