<?php
 /*
 * Model基类
 */

namespace common\core;
use common\library\Log;
use common\library\NotifyService;
use common\library\ReturnCode;

/**
 * Class MongoModel
 * @deprecated 不要再使用, 将要废弃, 改用更简单的MongoServiceClient
 * @package common\core
 */
abstract class MongoModel
{
    /** @var int|string|null $id 文档ID */
    public $id = null;

    /**
     * 当前生效的数据库配置
     */
    protected $dbConfig = 'db';
    
    /**
     * 当前生效的数据库操作对象
     */
    private $db;
    private $mongo;
    private $mongodb;
    private $coll;

    /**
     * 对象数据
     */
    public $info;
    public $database;
    protected $table_name;
    protected $org;
    protected $user;
    protected $_org;
    protected $_user;
    protected $_session_id;

    /**
     * 数据日志
     * @var array
     */
    private $__log = array();

    /**
     * 数据日志用,记录当前数据操作类型
     * todo: 这种做法不好,需要修改
     * @var string
     */
    private $__opt;

    /**
     * MongoModel constructor.
     * @param string|null $id 文档ID
     */
	public function __construct($id = null)
	{
	    // 设置文档ID
        $this->id = $id;

        // TODO: 不需要初始化函数
		$this->init();
	}
    
    public function init() {
        //如果table_name和database已经设置,即初始化数据库
        if (!empty($this->table_name) && !empty($this->database)) {
            $this->initDB($this->table_name,$this->database);
        }
        return;
    }

    /**
     * 初始化数据库操作对象
     * @param string $table_name
     * @param string|bool $database
     * @param string $config_name
     * @return bool|int|null
     */
	public function initDB($table_name, $database  = false, $config_name = 'mongodb')
	{

        // 获取数据库配置
        $config =  Configure::get($config_name);;

        // 定义mongo连接选项
        $mongo_options = array(
            "connect"=>true,
            //"replicaSet" => "rs0",
        );
        if ( !empty($config['username'] && !empty($config['password'])) ) {
            $mongo_options['username'] = $config['username'];
            $mongo_options['password'] = $config['password'];
        }
        

        // 如果已经配置名字服务, 则根据名字获取mongo的ip和port
        if(isset($config['name']))
        {
            $host = "mongodb://"; // 设置host前缀
            $this->_session_id = null; // 初始化_session_id为空

            // 获取mongo服务所有ip和port
            $serviceList = ens_get_all_service_by_name(APP_NAME, $config['name']);
            // 判断是否是集群模式, 如果replicaSet存在, 则配置集群名
            count($serviceList) > 1 && !empty($config['replica_set']) && $mongo_options['replicaSet'] = $config['replica_set'];
            foreach ($serviceList as $service) {
                !isset($this->_session_id) && $this->_session_id = $service->session_id;
                $host .= $service->ip.":".$service->port.",";
            }
            $host = rtrim($host, ","); // 去掉最后一个逗号

            // 如果session_id为空, 则表示获取名字服务失败, 记录日志并报错
            if (empty($this->_session_id)) {
                Log::emergency('ens', "{$config['name']} ");
                \Flight::halt(400, 'The config data of database connect is not correct!');
                return false;
            }

            // 设置配置文件host部分
            $config['host'] = $host;
        }
        $this->__timer();
        try{
            $this->mongo = new \MongoClient($config['host'], $mongo_options);
        } catch (\MongoConnectionException $exception) {
            ens_report_stat($this->_session_id, 'init', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage());
            return ReturnCode::DATABASE_ERROR;
        }
        ens_report_stat($this->_session_id, 'init', 0, $this->__timer(), "", "");

        //todo: 数据库初始化逻辑需要优化
        $database !== false && $this->database = $database;
        $this->table_name = $table_name;

        if (empty($this->database))
        {
            if($database === false)
            {
                $this->database = 'easyops_cmdb';
            }
            else
            {
                $this->database = $database;
            }
        }
        $this->mongodb = $this->mongo->selectDB($this->database);
        $this->coll = $this->mongodb->selectCollection($table_name);
        $this->db = $this->coll;
		return true;
	}

    public function setCollection($table_name) {
        $this->table_name = $table_name;
        $this->coll = $this->mongodb->selectCollection($this->table_name);
        $this->db = $this->coll;
        return 0;
    }

    public function setDatabase($database, $table_name = false) {
        $this->database = $database;
        $this->mongodb = $this->mongo->selectDB($this->database);
        $table_name === false && $table_name = $this->table_name;
        return $this->setCollection($table_name);
    }

    /**
     * 新建对象
     * @param array|null $data
     * @return int|null
     */
    private function __DBInsert(&$data = null) {
        $this->initDB($this->table_name,$this->database);
        if(empty($data))
        {
            $data = $this->info;
        }

        // 赋值给临时变量, 为了获取_id
        $temp = $data;
        $temp['ctime'] = date('Y-m-d H:i:s', time());
        $temp['mtime'] = $temp['ctime'];
        $temp['creator'] = $this->user;
        $temp['modifier'] = $this->user;
        $temp['org'] = $this->org;

        // 设置计时器
        $this->__timer();

        // 执行插入操作
        try {
            $ret = $this->db->insert($temp);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'insert', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('new_object'=>$temp));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'insert', 0, $this->__timer(), "", "");

        $data = $temp;
        if (!$ret) {
            return ReturnCode::DATABASE_INSERT_FAILED;
        }

        return 0;
    }

    /**
     * 更新对象
     * @param array $data
     * @param array $where
     * @param array $info
     * @return int|null
     */
    private function __DBUpdate($data, $where, &$info = null) {
        $this->initDB($this->table_name,$this->database);

        // 设置修改时间和修改人
        $data['mtime'] = date('Y-m-d H:i:s', time());
        $data['modifier'] = $this->user;

        // 设置计时器
        $this->__timer();

        // 执行更新操作
        try {
            $info = $this->db->update($where, array('$set'=>$data), array("multiple" => true));
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'update', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'new_object'=>array('$set'=>$data)));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'update', 0, $this->__timer(), "", "");
        if ( !$info ) {
            return ReturnCode::DATABASE_UPDATE_FAILED;
        }

        return 0;
    }

    /**
     * 追加对象
     * @param $data
     * @param $where
     * @param null $info
     * @return int|null
     */
    private function __DBPush($data, $where, &$info = null) {
        $this->initDB($this->table_name,$this->database);

        // 设置修改时间和修改人
        $updateInfo['mtime'] = date('Y-m-d H:i:s', time());
        $updateInfo['modifier'] = $this->user;

        // 设置计时器
        $this->__timer();

        // 执行更新操作
        try {
            $info = $this->db->update($where, array('$push'=>$data, '$set'=>$updateInfo), array("multiple" => true));
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'update', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'new_object'=>array('$push'=>$data, '$set'=>$updateInfo)));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'update', 0, $this->__timer(), "", "");

        // 错误处理
        if ( !$info ) {
            return ReturnCode::DATABASE_UPDATE_FAILED;
        }

        return 0;
    }

    /**
     * 追加对象
     * @param $data
     * @param $where
     * @param null $info
     * @return int|null
     */
    private function __DBAddtoset($data, $where, &$info = null) {
        $this->initDB($this->table_name,$this->database);

        // 设置修改时间和修改人
        $updateInfo['mtime'] = date('Y-m-d H:i:s', time());
        $updateInfo['modifier'] = $this->user;

        // 设置计时器
        $this->__timer();

        // 执行更新操作
        try {
            $info = $this->db->update($where, array('$addToSet'=>$data, '$set'=>$updateInfo), array("multiple" => true));
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'update', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'new_object'=>array('$addToSet'=>$data, '$set'=>$updateInfo)));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'update', 0, $this->__timer(), "", "");

        // 错误处理
        if ( !$info ) {
            return ReturnCode::DATABASE_UPDATE_FAILED;
        }

        return 0;
    }
    /**
     * 删除对象
     */
    private function __DBPull($data, $where, &$info = null) {
        $this->initDB($this->table_name,$this->database);

        // 设置修改时间和修改人
        $updateInfo['mtime'] = date('Y-m-d H:i:s', time());
        $updateInfo['modifier'] = $this->user;

        // 设置计时器
        $this->__timer();

        // 执行更新操作
        try {
            $info = $this->db->update($where, array('$pull'=>$data, '$set'=>$updateInfo), array("multiple" => true));
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'update', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'new_object'=>array('$pull'=>$data, '$set'=>$updateInfo)));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'update', 0, $this->__timer(), "", "");

        // 错误处理
        if ( !$info ) {
            return ReturnCode::DATABASE_UPDATE_FAILED;
        }

        return 0;
    }

    /**
     * 删除对象
     * @param $where
     * @return int|null
     */
    private function __DBDelete($where) {
        $this->initDB($this->table_name,$this->database);

        // 设置计时器
        $this->__timer();

        // 执行删除操作
        try {
            $ret = $this->db->remove($where);
        } catch ( \Exception $exception ) {
            ens_report_stat($this->_session_id, 'delete', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'delete', 0, $this->__timer(), "", "");

        // 错误处理
        if ($ret === false) {
            return ReturnCode::DATABASE_DELETE_FAILED;
        }

        return 0;
    }

    /**
     * MongoModel _collectionUpdate 集合更新方法,单操作
     * @author index
     * @param array $where 查询条件
     * @param string $opt 更新操作符($set, $pull, $unset, $push)
     * @param array $data 更新数据
     * @param bool $multi 是否多文档更新,默认true
     * @param array|null $info 更新后状态信息
     * @return int 返回码
     */
    protected function _collectionUpdate($where, $opt, $data, $multi = true, &$info = null) {

        // 设置更新信息
        $updateInfo = array($opt => $data);
        !isset($updateInfo['$set']) && $updateInfo['$set'] = array();
        // 设置更新时间和修改者
        $updateInfo['$set']['mtime'] = date('Y-m-d H:i:s', time());
        $updateInfo['$set']['modifier'] = $this->user;

        // 设置计时器
        $this->__timer();

        // 执行更新操作
        try {
            $info = $this->db->update($where, $updateInfo, array("multiple" => $multi));
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'update', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'new_object'=>$updateInfo));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'update', 0, $this->__timer(), "", "");
        if ( !$info ) return ReturnCode::DATABASE_UPDATE_FAILED;

        return 0;
    }

    /**
     * MongoModel _collectionUnset 集合unset方法
     * @param array $where 查询条件
     * @param array|string $keys 需要删除的键列表key或者[key1, key2, key3]
     * @param bool $multi 是否更新多个文档,默认true
     * @param array|null $info 更新后状态信息
     * @return int 返回码
     */
    protected function _collectionUnset($where, $keys, $multi = true, &$info = null) {

        // 单个key转换为keys列表
        is_string($keys) && $keys = array($keys);

        // 转换格式
        $data = array();
        foreach ($keys as $key) {
            $data[$key] = 1;
        }

        return $this->_collectionUpdate($where, '$unset', $data, $multi, $info);
    }

    /**
     * MongoModel _collectionSet 集合set方法
     * @param array $where 查询条件
     * @param array $data 需要插入数据的列表[key1:value1, key2:value2]
     * @param bool $multi 是否更新多个文档,默认true
     * @param array|null $info 更新后状态信息
     * @return int 返回码
     */
    protected function _collectionSet($where, $data, $multi = true, &$info = null) {

        return $this->_collectionUpdate($where, '$set', $data, $multi, $info);
    }

    /**
     * 查询分页结果
     */
    public function SearchPage(&$result, $where, $page, $pageSize, $order,$fields = array()) {
        $this->initDB($this->table_name,$this->database);

        //查询总记录数
        $ret = $this->DBCount($count, $where);
        if ( $ret != 0 ) {
            return $ret;
        }
        
        //设置分页、排序
        //$this->db->order($order)->limit(($page-1)*$pageSize, $pageSize);
        $fields['_id'] = 0;

        // 设置计时器
        $this->__timer();
        
         //查询包列表
        try {
            $cur = $this->db->find($where,$fields);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'find', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'fields'=>$fields));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'find', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $cur === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }

        // 分页处理
        if($page > 1) {
            $cur->skip(($page-1)*$pageSize)->limit($pageSize);
        }
        $ret = Array();
        foreach($cur as $doc) {
            $ret[] = $doc;
        }

        $result = array('total'=>(int)$count, 'list'=>$ret);

        return 0;
    }

    /**
     * 查询分页结果
     *
     * @param array &$result 查询结果
     * @param array $where 查询条件
     * @param array $resultOpt 字段选择信息及分页信息
     * @param array &$resultInfo 返回分页数据等
     * @param array $sort 排序参数
     * @return int $ret 返回码
     */
    public function SearchAll(&$result, $where,$resultOpt = array(),&$resultInfo = array(),$sort=array()) {
        $this->initDB($this->table_name,$this->database);
        //查询列表
        //查询总记录数
        $count = 0;
        $ret = $this->DBCount($count, $where);
        if ( $ret != 0 ) {
            return $ret;
        }
        $result_fields = [];
        //$result_fields['_id'] = 0;
        if(isset($resultOpt['fields']))
        {
            foreach($resultOpt['fields'] as $field)
            {
                $result_fields[$field]=1;
            }
        }
        if(isset($resultOpt['ignore'])) {
            foreach ($resultOpt['ignore'] as $field) {
                $result_fields[$field] = 0;
            }
        }
        if(isset($resultOpt['projection'])){
            $result_fields = array_merge($result_fields,$resultOpt['projection']);
        }

        // 设置计时器
        $this->__timer();

        // 执行查询
        try {
            $cur = $this->db->find($where,$result_fields);
            !empty($sort) && $cur = $cur->sort($sort);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'find', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'result_fields'=>$result_fields));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'find', 0, $this->__timer(), "", "");

        // 处理错误
        if ( $cur === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        if(isset($resultOpt['page']) && $resultOpt['page'] >= 1) {
            $page = $resultOpt['page'];
            if(isset($resultOpt['pageSize'])) {
                $pageSize = $resultOpt['pageSize'];
            }
            else {
                $pageSize = 30;
            }
        }
        else {
            $page = 0;
            $pageSize = 30;
        }

        if($page >= 1) {
            $cur->skip(($page-1)*$pageSize)->limit($pageSize);
        }
        $ret = Array();
        foreach($cur as $doc) {
            $ret[] = $doc;
        }
        $result = $ret;

        // 附加信息
        $resultInfo =array(
            'total' => $count,
            'page' => $page,
            'pageSize' => $pageSize,
        );
        return 0;
    }
    
    /**
     * 查询记录数
     */
    public function DBCount(&$result, $where) {
        $this->initDB($this->table_name,$this->database);

        // 设置计时器
        $this->__timer();

        // 查询总记录数
        try {
            $count = $this->db->count($where);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'count', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'count', 0, $this->__timer(), "", "");

        $result = $count;

        return 0;
    }


    /**
     * 查询记录总数
     */
    public function count($where,&$result=false ) {
        $this->initDB($this->table_name,$this->database);
        $fields['_id'] = 0;

        // 设置计时器
        $this->__timer();

        // 执行查询记录数
        try {
            $num = $this->db->count($where);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'count', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'count', 0, $this->__timer(), "", "");

        $result = $num;

        return 0;
    }

    /**
     * 查询单行记录
     * @param $where
     * @param array|bool|null $result
     * @param array $fields
     * @return int|null
     */
    public function SearchOne($where, &$result=false, $fields=array()) {
        $this->initDB($this->table_name,$this->database);
        //$fields['_id'] = 0;

        // 设置计时器
        $this->__timer();

        // 执行查询
        try {
            $doc = $this->db->findOne($where, $fields);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'find', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'fields'=>$fields));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'find', 0, $this->__timer(), "", "");

        if ( $doc === null ) {
            $result = null;
            return 0;
        }
        if ($result !== false) {
            $result = $doc;
        } else {
            $this->info = $doc;
            $result = $doc;
        }

        return 0;
    }

    public function findAll(&$result = false, $fields = null, $orderDesc = null, $limitStart = null, $listNum = null) {

        $this->initDB($this->table_name,$this->database);

        // 设置计时器
        $this->__timer();

        // 执行查询
        try {
            $ret = $this->db->findAll($fields, $orderDesc, $limitStart, $listNum);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'find', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('fields'=>$fields, 'orderDesc'=>$orderDesc, 'limitStart'=>$limitStart, 'listNum'=>$listNum));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'find', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $ret === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        if ($result !== false) {
            $result = $ret;
        } else {
            $this->info = $ret;
        }

        return 0;
    }

    public function GetLastInsertId(&$id) {

        $this->initDB($this->table_name,$this->database);

        // 设置计时器
        $this->__timer();

        // 执行查询
        try {
            $ret = $this->db->lastInsertId();
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'find', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage());
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'find', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $ret === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        if ($ret !== false) {
            $id = $ret;
        }

    }
    
    /**
     * 个性化查询
     */
    public function DBQeury(&$result, $sql, $values) {
        $this->initDB($this->table_name,$this->database);
        try {
            $ret = $this->db->query($sql, $values);
            if ( $ret === false ) {
                return ReturnCode::DATABASE_QUERY_FAILED;
            }
            
            $result = $ret->fetchAll();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), array('sql'=>$sql, 'values'=>$values));
            return ReturnCode::DATABASE_ERROR;
        }
        
        return 0;
    }
    /**
     * 生成自增id
     */
    public function autoId($name = null){
        if (!$name)
        {
            $name = $this->table_name;
        }
        $this->initDB($this->table_name,$this->database);
        $update = array('$inc'=>array("id"=>1));
        $query = array('name'=>$name);
        $command = array(
            'findandmodify'=>'ids',
            'update'=>$update,
            'query'=>$query,
            'new'=>true,
            'upsert'=>true
        );
        $id = $this->mongodb->command($command);
        return $id['value']['id'];
    }

    /**
     * MongoModel _aggregateCount
     * 用于计算聚合的记录数
     *
     * @author index
     * @param $pipeline
     * @return int
     */
    protected function _aggregateCount($pipeline) {

        $pipeline[] = array(
            '$group' => array('_id' => null, 'count' => array('$sum' => 1))
        );

        $this->_aggregate($result, $pipeline);

        $ret = 0;
        if (isset($result[0]['count'])) {
            $ret = $result[0]['count'];
        }
        return $ret;
    }

    /**
     * @author index
     * @param $result
     * @param $pipeline
     * @return int
     */
    protected function _aggregate(&$result, $pipeline) {

        /*
        $db->col->aggregate(array(
            array('$match'=>$where)
            array('$group'=>array('_id'=>'$key', 'count'=>array('$sum'=>1))),
            array('$sort'=>array('_id'=>-1)),
            array('$skip'=>4),
            array('$limit'=>20)
        ));
        */

        $options = array(
            "maxTimeMS" => 1
        );

        // 设置计时器
        $this->__timer();

        // 执行聚合
        try {
            $ret = $this->db->aggregate($pipeline, $options);
        } catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'aggregate', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('pipeline'=>$pipeline, '$options'=>$options));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'aggregate', 0, $this->__timer(), "", "");

        if (isset($ret['result'])) {
            $result = $ret['result'];
        }
        else {
            $result = null;
        }
        return ($ret['ok']+1)%2;
    }

    /**
     * DBInsertAndLog
     * 带数据日志的数据插入方法, 兼容
     *
     * @param array|null $data
     * @return int|null
     */
    public function DBInsertAndLog($data = null) {
        return $this->DBInsert($data, array('log'=>true));
    }

    /**
     * DBInsert
     *
     * @param array|null $data
     * @param array $options
     * @return int|null
     */
    public function DBInsert(&$data = null, $options = array()) {

        $this->initDB($this->table_name,$this->database);

        $ret = $this->__DBInsert($data);
        if ($ret !== 0) {
            return $ret;
        }

        if (isset($options['log']) && $options['log']) {
            $this->__initLogData("insert");
            $this->__appendLogData($data['_id']);
            $this->__log[(string)$data['_id']]['currentVersion'] = $data;
            $this->__writeLogData();
        }

        return 0;
    }

    /**
     * DBUpdateAndLog
     * 带数据日志记录的数据更新方法
     *
     * @param array $data
     * @param array $where
     * @return int|null
     */
    public function DBUpdateAndLog($data, $where) {
        return $this->DBUpdate($data, $where, array('log'=>true));
    }

    /**
     * DBUpdate
     *
     * @param array $data
     * @param array $where
     * @param array $options
     * @return int|null
     */
    public function DBUpdate($data, $where, $options = array()) {
        $this->initDB($this->table_name,$this->database);
        $log = isset($options['log']) && $options['log'];
        $safe = isset($options['_version']);

        // 初始化日志
        $log && $this->__initLogData("update");
        // 获取更新前数据版本
        $log && $this->__getPreviousVersion($where);

        // 安全更新机制
        $safe && $where['_version'] = $options['_version'];

        // 更新数据
        $ret = $this->__DBUpdate($data, $where, $info);
        if ($ret !== 0) {
            return $ret;
        }

        // 如果没有数据变更, 则不记录日志
        if (!$info['nModified'] && $info['n']) {
            return 0;
        }
        elseif (!$info['nModified']) {
            unset($where['_version']);
            $this->count($where, $num);
            $safe && $num && $ret = ReturnCode::LOGICAL_DIRTY_DATA;
            return $ret;
        }

        // 获取更新后数据版本
        $log && $this->__getCurrentVersion();
        // 写日志
        $log && $this->__writeLogData();

        return $ret;
    }

    /**
     * DBDeleteAndLog
     * 带数据日志记录的数据删除方法
     *
     * @param $where
     * @return int
     */
    public function DBDeleteAndLog($where) {
        return $this->DBDelete($where, array('log'=>true));
    }

    /**
     * DBDelete
     *
     * @param $where
     * @param array $options
     * @return int
     */
    public function DBDelete($where, $options = array()) {

        $this->initDB($this->table_name,$this->database);

        $log = isset($options['log']) && $options['log'];
        $log && $this->__initLogData("delete");
        $log && $this->__getPreviousVersion($where);

        $ret = $this->__DBDelete($where);
        if ($ret !== 0) {
            return $ret;
        }

        $log && $this->__writeLogData();

        return 0;
    }

    public function DBPushAndLog($data, $where) {
        return $this->DBPush($data, $where, array('log'=>true));
    }

    public function DBPush($data, $where, $options = array()) {
        $this->initDB($this->table_name,$this->database);

        $log = isset($options['log']) && $options['log'];
        $log && $this->__initLogData("update");
        $log && $this->__getPreviousVersion($where);

        // 更新数据
        $ret = $this->__DBPush($data, $where, $info);
        if ($ret !== 0) {
            return $ret;
        }
        // 如果没有数据变更, 则不记录日志
        if (!$info['nModified']) {
            return 0;
        }

        $log && $this->__getCurrentVersion();
        $log && $this->__writeLogData();

        return 0;
    }

    public function DBAddtosetAndLog($data, $where) {
        return $this->DBAddtoset($data, $where, array('log'=>true));
    }

    public function DBAddtoset($data, $where, $options = array()) {
        $this->initDB($this->table_name,$this->database);

        $log = isset($options['log']) && $options['log'];
        $log && $this->__initLogData("update");
        $log && $this->__getPreviousVersion($where);

        // 更新数据
        $ret = $this->__DBAddtoset($data, $where, $info);
        if ($ret !== 0) {
            return $ret;
        }
        // 如果没有数据变更, 则不记录日志
        if (!$info['nModified']) {
            return 0;
        }

        $log && $this->__getCurrentVersion();
        $log && $this->__writeLogData();

        return 0;
    }

    public function DBPullAndLog($data, $where) {
        return $this->DBPull($data, $where, array('log'=>true));
    }

    public function DBPull($data, $where, $options = array()) {
        $this->initDB($this->table_name,$this->database);

        $log = isset($options['log']) && $options['log'];
        $log && $this->__initLogData("update");
        $log && $this->__getPreviousVersion($where);

        // 更新数据
        $ret = $this->__DBPull($data, $where, $info);
        if ($ret !== 0) {
            return $ret;
        }
        // 如果没有数据变更, 则不记录日志
        if (!$info['nModified']) {
            return 0;
        }

        $log && $this->__getCurrentVersion();
        $log && $this->__writeLogData();

        return 0;
    }

    /**
     * _iniLogData
     * 内部方法,初始化日志数据
     *
     * @param $opt
     * @return int
     */
    private function __initLogData($opt) {
        $this->__log = array();
        $this->__opt = $opt;
        return 0;
    }

    /**
     * 根据数据ID添加数据变更日志
     *
     * @param object $id MongoId
     * @return int
     */
    private function __appendLogData($id) {
        $this->__log[(string)$id] = array();
        $this->__log[(string)$id]["database"] = $this->database;
        $this->__log[(string)$id]["collection"] = $this->table_name;
        $this->__log[(string)$id]["objectId"] = $id;
        $this->__log[(string)$id]["org"] = $this->org;
        $this->__log[(string)$id]["user"] = $this->user;
        $this->__log[(string)$id]["databaseOperations"] = $this->__opt;
        $this->__log[(string)$id]["changeDateTime"] = date('Y-m-d H:i:s', time());
        $this->__log[(string)$id]["previousVersion"] = null;
        $this->__log[(string)$id]["currentVersion"] = null;
        return 0;
    }

    /**
     * 获取更新前数据版本
     *
     * @param $where
     * @return int|null
     */
    private function __getPreviousVersion($where) {
        $cur = $this->db->find($where);
        if ( $cur === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        foreach ($cur as $doc) {
            !isset($this->__log[(string)$doc['_id']]) && $this->__appendLogData($doc['_id']);
            $this->__log[(string)$doc['_id']]["previousVersion"] = $doc;
        }
        return 0;
    }

    /**
     * 获取数据当前版本
     *
     * @param null $where
     * @return int|null
     */
    private function __getCurrentVersion($where = null) {
        if (!isset($where)) {
            $idList = array();
            foreach ($this->__log as $log) {
                $idList[] = $log['objectId'];
            }
            $where = array();
            $where['_id'] = array('$in' => $idList);
        }
        $cur = $this->db->find($where);
        if ( $cur === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        foreach ($cur as $doc) {
            !isset($this->__log[(string)$doc['_id']]) && $this->__appendLogData($doc['_id']);
            $this->__log[(string)$doc['_id']]["currentVersion"] = $doc;
        }
        return 0;
    }

    /**
     * __writeLogData
     * 内部方法,写入日志
     *
     * @return int|null
     */
    private function __writeLogData() {

        $table_name_bak = $this->table_name;    //备份table
        $this->table_name = "t_data_log_".date('Ym', time());
        $this->initDB($this->table_name,$this->database);

        foreach ($this->__log as $log) {
            try {
                if (!$this->db->insert($log)) {
                    return ReturnCode::DATABASE_INSERT_FAILED;
                }
                else {
                    //发送消息
                    NotifyService::getInstance()->notice("cmdb", "datalog", intval($log['org']), $log['user'], $log);
                }
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                return ReturnCode::DATABASE_ERROR;
            }
        }

        $this->table_name = $table_name_bak;    //还原table
        $this->initDB($this->table_name,$this->database);
        return 0;
    }

    /**
     * @param string $key
     * @param array $options
     * @return int
     */
    public function createUniqueIndex($key, $options = array()) {
        $keys = array($key=>1);
        $options['unique'] = true;
        !isset($options['sparse']) && $options['sparse'] = false;
        $ret = $this->createIndex($keys, $options);
        return $ret;
    }

    /**
     * @param array $keys
     * @param array $options
     * @return int
     */
    public function createIndex($keys, $options = array()) {

        // 默认采取后台更新模式
        !isset($options['background']) && $options['background'] = true;

        // 设置计时器
        $this->__timer();

        try {
            $ret = $this->db->createIndex($keys, $options);
        }
        catch (\MongoDuplicateKeyException $exception) {
            ens_report_stat($this->_session_id, 'createIndex', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('keys'=>$keys, 'options'=>$options));
            return ReturnCode::LOGICAL_ERROR;
        }
        catch (\MongoException $exception) {
            ens_report_stat($this->_session_id, 'createIndex', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('keys'=>$keys, 'options'=>$options));
            return ReturnCode::DATABASE_CREATE_INDEX_FAILED;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'createIndex', 0, $this->__timer(), "", "");

        // 处理错误情况
        if (isset($ret['note'])) {
            return ReturnCode::DATABASE_INDEX_ALREADY_EXISTS;
        }

        // 默认返回0
        return 0;
    }


    /**
     * @param string|array $keys
     * @return int
     */
    public function deleteIndex($keys) {

        // 设置计时器
        $this->__timer();

        try {
            $ret = $this->db->deleteIndex($keys);
        }
        catch (\MongoException $exception) {
            ens_report_stat($this->_session_id, 'deleteIndex', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('keys'=>$keys));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'deleteIndex', 0, $this->__timer(), "", "");

        // 默认返回0
        return 0;
    }

    /**
     * 复制数据库到目标数据库
     * @param string $src
     * @param string|bool $des
     * @return int
     */
    public function copyDatabase($src, $des = false) {

        // 设置目标数据库
        $des === false && $des = $this->database;

        // 设置复制数据库命令
        $admin = $this->mongo->selectDB('admin');
        $command = array(
            'copydb' => 1,
            'fromdb' => $src,
            'todb' => $des
        );

        // 设置计时器
        $this->__timer();

        // 执行命令
        try {
            $ret = $admin->command($command);
        }
        catch (\Exception $exception) {
            ens_report_stat($this->_session_id, 'command', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('command'=>$command));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->_session_id, 'command', 0, $this->__timer(), "", "");

        // 错误处理
        if ((int)$ret['ok'] !== 1) return ReturnCode::DATABASE_ERROR;

        return 0;
    }

    /**
     * @return int
     */
    public function dropDatabase() {
        $ret = $this->mongodb->drop();
        // 错误处理
        if ((int)$ret !== 1) return ReturnCode::DATABASE_ERROR;
        return 0;
    }

    private $__timer;

    /**
     * @return int millisecond
     */
    private function __timer() {
        $t = microtime(true);
        $ret = round($t - $this->__timer, 3) * 1000;
        $this->__timer = $t;
        return (int)$ret;
    }
}

