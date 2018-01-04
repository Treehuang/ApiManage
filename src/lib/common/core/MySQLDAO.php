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


use common\library\MySQLService;
use common\library\ReturnCode;

abstract class MySQLDAO
{
    /** @var string $tbName table name */
    protected $tbName = 'test';

    /** @var string $idField id field name */
    protected $idField = 'id';

    /** @var array $schema table schema */
    protected $schema = [];

    /**
     * MySQLDAO constructor.
     */
    public function __construct()
    {
    }

    /**
     * MySQLDAO has.
     *
     * @param mixed $id 数据ID
     * @return int 返回码, 0则存在
     */
    public function has($id)
    {
        $exist = MySQLService::has($this->tbName, [$this->idField.'[=]' => $id]);
        $code = MySQLService::getLastCode();
        if ($code !== 0) return $code;
        if (!$exist) {
            return ReturnCode::DATABASE_NO_RESULT;
        }
        return 0;
    }

    /**
     * MySQLDAO get.
     *
     * @param mixed $id 数据ID
     * @param mixed &$data
     * @return int 返回码
     */
    public function get($id, &$data)
    {
        $data = MySQLService::get($this->tbName, $this->schema, [$this->idField.'[=]' => $id]);
        $code = MySQLService::getLastCode();
        if ($code != 0) {
            $vo = null;
            return $code;
        }
        if (!is_array($data)) {
            $vo = null;
            return ReturnCode::DATABASE_NO_RESULT;
        }
        return 0;
    }

    /**
     * MySQLDAO save.
     *
     * @param array $data 值对象
     * @return int 返回码
     */
    public function save($data)
    {
        // 获取值对象数据
        $id = $data[$this->idField];

        // 判断是否存在
        $exist = $this->has($id);
        if ($exist === 0) {
            $code = $this->update($data);
            if ($code !== 0) return $code;
        }
        else {
            $code = $this->insert($data);
            if ($code !== 0) return $code;
        }

        return 0;
    }

    /**
     * MySQLDAO insert.
     *
     * @param array $data
     * @return int 返回码
     */
    public function insert($data)
    {
        $num = MySQLService::insert($this->tbName, $data);
        $code = MySQLService::getLastCode();
        if ($code !== 0) return $code;
        if ($num === false) return ReturnCode::DATABASE_INSERT_FAILED;
        if ($num == 0) return ReturnCode::DATABASE_NO_AFFECTED;

        return 0;
    }

    /**
     * MySQLDAO update.
     *
     * @param array $data
     * @return int 返回码
     */
    public function update($data)
    {
        $id = $data[$this->idField];
        unset($data[$this->idField]);

        $num = MySQLService::update($this->tbName, $data, [$this->idField.'[=]' => $id]);
        $code = MySQLService::getLastCode();
        if ($code !== 0) return $code;
        if ($num === false) return ReturnCode::DATABASE_UPDATE_FAILED;
        if ($num == 0) return ReturnCode::DATABASE_NO_AFFECTED;

        return 0;
    }

    /**
     * MySQLDAO delete.
     *
     * @param mixed $id 数据ID
     * @return int 返回码
     */
    public function delete($id)
    {
        $num = MySQLService::delete($this->tbName, [$this->idField.'[=]' => $id]);
        $code = MySQLService::getLastCode();
        if ($code !== 0) return $code;
        if ($num === false) return ReturnCode::DATABASE_DELETE_FAILED;
        if ($num == 0) return ReturnCode::DATABASE_NO_AFFECTED;

        return 0;
    }

    /**
     * MySQLDAO select.
     *
     * @param array $where
     * @param array $list
     * @return int 返回码
     */
    public function select(array $where, &$list)
    {
        $list = MySQLService::select($this->tbName, $this->schema, $where);
        $code = MySQLService::getLastCode();
        if ($code !== 0) return $code;
        if (empty($list)) return ReturnCode::DATABASE_NO_RESULT;
        return 0;
    }
}