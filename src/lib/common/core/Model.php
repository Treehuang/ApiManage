<?php
 /*
 * Model基类
 */

namespace common\core;
use common\library\ReturnCode;
use common\library\Log;

abstract class Model {
	/**
	 * 数据库操作对象数组
	 */
	protected $dbs = array();
    
    /**
     * 当前生效的数据库配置
     */
    protected $dbConfig = 'db';
    
    /**
     * 当前生效的数据库操作对象
     */
    protected $db = null;
    
    /**
     * 数据对象主键
     */
    public $id;
    
    /**
     * 对象数据
     */
    public $info;
    
	public function __construct( $id = null )
	{
	    if ($id !== null) {
            $this->id = $id;
        }
       
		$this->init();	
	}
    
    public function init() {

        return;
    }
	
	/**
	 * 初始化数据库操作对象
	 */
	public function initDB($table_name, $config='mysql')
	{
		if (! $table_name ) {
			return false;
		}
		
		if ( !isset($this->dbs[$config]) ) {
			$this->dbs[$config] = new DbModel($config);
		}
		
        $this->dbConfig = $config;
        $this->db = $this->dbs[$this->dbConfig];

		$this->db->setTableName($table_name);

		return true;
	}
    
    /**
     * 新建对象
     */
    public function DBInsert() {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        // 执行数据插入操作
        try {
            $ret = $this->db->insert($this->info);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'insert', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('new_object'=>$this->info));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'insert', 0, $this->__timer(), "", "");

        // 错误处理
        if (!$ret) {
            return ReturnCode::DATABASE_INSERT_FAILED;
        }

        return 0;
    }
    
    /**
     * 更新对象
     */
    public function DBUpdate($info, $where, $values) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        // 执行数据更新操作
        try {
            $ret = $this->db->update($info, $where, $values);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'update', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('info'=>$info, 'where'=>$where, 'values'=>$values));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'update', 0, $this->__timer(), "", "");

        // 错误处理
        if ( !$ret ) {
            return ReturnCode::DATABASE_UPDATE_FAILED;
        }

        return 0;
    }
    
    /**
     * 删除对象
     */
    public function DBDelete($where, $values) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        // 执行数据删除操作
        try {
            $ret = $this->db->delete($where, $values);
        } catch ( \Exception $exception ) {
            ens_report_stat($this->db->session_id, 'delete', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'values'=>$values));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'delete', 0, $this->__timer(), "", "");

        // 错误处理
        if ( !$ret ) {
            return ReturnCode::DATABASE_DELETE_FAILED;
        }

        return 0;
    }
    
    /**
     * 查询分页结果
     */
    public function SearchPage(&$result, $where, $values, $page, $pageSize, $order) {
        $this->initDB($this->table_name);

        //查询总记录数
        $ret = $this->DBCount($count, $where, $values);
        if ( $ret != 0 ) {
            return $ret;
        }
        
        //设置分页、排序
        $this->db->order($order)->limit(($page-1)*$pageSize, $pageSize);

        // 设置计时器
        $this->__timer();

        // 查询包列表
        try {
            $ret = $this->db->getAll($where, $values);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'select', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'values'=>$values));            
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'select', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $ret === false ) {
            //ens_report_stat($this->db->session_id, 0);
            return ReturnCode::DATABASE_QUERY_FAILED;
        }

        $result = array('total'=>(int)$count, 'list'=>$ret);

        return 0;
    }
	
    /**
     * 查询分页结果
     */
    public function SearchAll(&$result, $where, $values, $order=null) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

         //查询列表
        try {
            $order !== null && $this->db->order($order);
            $ret = $this->db->getAll($where, $values);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'select', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'values'=>$values));            
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'select', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $ret === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }

        $result = $ret;

        return 0;
    }
    
    /**
     * 查询记录数
     */
    public function DBCount(&$result, $where, $values) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

         //查询总记录数
        try {
            $count = $this->db->count('*', $where, $values);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'count', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'values'=>$values));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'count', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $count === 0 ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        $result = $count;

        return 0;
    }
    
    /**
     * 查询单行记录
     */
    public function SearchOne($where, $values, &$result=false, $fields=null) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        try {
            $ret = $this->db->getOne($where, $values, $fields);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'select', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('where'=>$where, 'values'=>$values, 'fields'=>$fields));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'select', 0, $this->__timer(), "", "");

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

    public function findAll(&$result = false, $fields = null, $orderDesc = null, $limitStart = null, $listNum = null) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        try {
            $ret = $this->db->findAll($fields, $orderDesc, $limitStart, $listNum);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'select', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('fields'=>$fields, 'orderDesc'=>$orderDesc, 'limitStart'=>$limitStart, 'listNum'=>$listNum));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'select', 0, $this->__timer(), "", "");

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

    public function GetLastInsertId(&$id)
    {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        try {
            $ret = $this->db->lastInsertId();
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'select', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage());
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'select', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $ret === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }
        if ($ret !== false) {
            $id = $ret;
        }

        return 0;
    }
    
    /**
     * 个性化查询
     */
    public function DBQeury(&$result, $sql, $values) {
        $this->initDB($this->table_name);

        // 设置计时器
        $this->__timer();

        try {
            $ret = $this->db->query($sql, $values);
        } catch (\Exception $exception) {
            ens_report_stat($this->db->session_id, 'sql', $exception->getCode(), $this->__timer(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            Log::error($exception->getMessage(), array('sql'=>$sql, 'values'=>$values));
            return ReturnCode::DATABASE_ERROR;
        }

        // 上报成功结果
        ens_report_stat($this->db->session_id, 'sql', 0, $this->__timer(), "", "");

        // 错误处理
        if ( $ret === false ) {
            return ReturnCode::DATABASE_QUERY_FAILED;
        }

        $result = $ret->fetchAll();

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
