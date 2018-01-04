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

use common\core\Configure;
use easyops\easykin\core\ClientSpan;
use http\Env\Request;


/**
 * Class PermissionService
 * @package common\library
 *
 * @method bool validate($action, $user, $org, array $params = [], $default = true) 验证权限
 * @method array validate2($action, $user, $org, array $params = [], $default = true) 验证权限
 * @method int addAction($action, $user, $org, $remark, $system, array $roles = [], array $resource = []) 增加权限控制点
 * @method int deleteAction($action, $user, $org) 删除权限控制点
 * @method int|array getAction($user, $org, $action) 获取权限控制点信息
 * @method int|array getActionPage($user, $org, $page=1, $pageSize=300, $params=null) 分页查询权限控制点
 * @method int modifyAction($action, $user, $org, $param) 修改权限控制点信息(不支持修改roles)
 * @method int saveAction($action, $user, $org, $remark, $system, array $roles = [], array $resource = []) 保存权限控制点信息(如果权限不存在，新增权限。如果权限存在，更新权限。如果是更新，原有角色拥有的权限只新增不删除。)
 * @method bool isSuperAdmin($org, $user) 判断用户是超级管理员
 * @method bool registerUser($org, $user, $isAdmin) 注册用户
 * @method bool deleteUser($org, $user) 注册用户
 */
class PermissionService extends ServiceBase
{
    protected static $instance;
    protected $host;
    protected $address;
    /** @var ClientSpan $span */
    private $span;

    final public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

    /** @return string 服务名 */
    protected function _getServiceName()
    {
        return Configure::get('permission.name', 'logic.permission.api');
    }

    /** @return bool true多实例服务, false单实例服务 */
    protected function _isMultiService()
    {
        return Configure::get('permission.multi', false);
    }

    /**
     * ServiceBase call.
     * @memo 用户自定义请求方式
     * @param string $name 请求方法名
     * @param array $arguments 请求参数数组
     * @return mixed
     */
    protected function call($name, $arguments)
    {
        // 判断方法是否存在
        $methodName = '_'.$name;
        if (!method_exists(self::class, $methodName)) return false;

        $config = Configure::get('permission');
        $this->address = $this->serviceList[0]['ip'] . ":" . $this->serviceList[0]['port'];
        $this->host = isset($config['host']) ? $config['host'] : "";
        $this->code = 0;
        return call_user_func_array([$this, $methodName], $arguments);
    }

    /**
     * Trace span
     * @param $url
     * @param EasyRequest $request
     */
    private function traceSpan($url, $request)
    {
        if(!is_null(\EasyKin::getTrace()))
        {
            $ip = $this->serviceList[0]['ip'];
            $port = $this->serviceList[0]['port'];
            $this->span = \EasyKin::newSpan($request->getMethod().":" .$url, $this->_getServiceName(), $ip, $port);
            $request->setHeader('X-B3-TraceId', $this->span->traceId)
                    ->setHeader('X-B3-SpanId', $this->span->id)
                    ->setHeader('X-B3-ParentSpanId', empty($this->span->parentId)? null : $this->span->parentId)
                    ->setHeader('X-B3-Sampled', \EasyKin::isSampled());
            $this->span->tag('http.url', $request->getUrl());
        }
    }

    /**
     * Set span tag
     * @param $key
     * @param $value
     */
    private function setTraceSpanTag($key, $value)
    {
        if(!is_null($this->span)){
            $this->span->tag($key, $value);
        }
    }

    /**
     * span receive
     * @param $status_code
     */
    private function traceSpanEnd($status_code)
    {
        if(!is_null($this->span)){
            $this->span->tag("http.status_code", $status_code);
            $this->span->tag("code", $this->code);
            $this->span->receive();
        }

    }

    protected function _validate($action, $user, $org, array $params = [], $default = true)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission/validate');
        $request->setMethod('GET')
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setParams($params)->setParam('action', $action)->setParam('user', $user);

        $this->traceSpan('/api/v1/permission/validate', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        // 处理权限系统访问错误
        if ($response->getStatusCode() !== 200) {
            Log::error("Access permission service failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return $default;
        }
        $this->code = 0;
        // 处理权限鉴权失败
        $data = json_decode($response->getBody(), true);
        return $data['data']['accepted'];
    }

    protected function _validate2($action, $user, $org, array $params = [], $default = true)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission/validate');
        $request->setMethod('GET')
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setParams($params)->setParam('action', $action)->setParam('user', $user);

        $this->traceSpan('/api/v1/permission/validate', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        // 处理权限系统访问错误
        if ($response->getStatusCode() !== 200) {
            Log::error("Access permission service failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ['accepted' => $default];
        }
        $this->code = 0;
        // 处理权限鉴权失败
        $data = json_decode($response->getBody(), true);
        return $data['data'];
    }

    protected function _addAction($action, $user, $org, $remark, $system, array $roles = [], array $resource = [])
    {
        $data = [
            'action' => $action,
            'system' => $system,
            'resource' => $resource,
            'roles' => $roles,
            'remark' => $remark,
        ];
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission');
        $request->setMethod('POST')
            ->setEncoding(EasyRequest::ENCODING_JSON)
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setData($data);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Add action $action failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ReturnCode::LOGICAL_ERROR;
        }
        $this->code = 0;

        return 0;
    }

    protected function _deleteAction($action, $user, $org)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission');
        $request->setMethod('DELETE')
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setParam('action', $action);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());
        if ($response->getStatusCode() !== 200) {
            Log::error("Delete action $action failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ReturnCode::LOGICAL_ERROR;
        }
        $this->code = 0;

        return 0;
    }


    /**
     * @memo 获取权限控制点的内容
     * @param $user
     * @param $org
     * @param null $action
     * @return int|array 权限控制点列表
     */
    protected function _getAction($user, $org, $action)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission');
        $request->setMethod('GET')
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);

        !is_null($action) && $request->setParam('action', $action);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Delete action $action failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ReturnCode::LOGICAL_ERROR;
        }
        $this->code = 0;

        $result = json_decode($response->getBody(), true);

        return isset($result['data'][0]) ? $result['data'][0] : [];
    }


    /**
     * @memo 分页查询权限控制点
     * @param $user
     * @param $org
     * @param $params
     * @return array|int
     */
    protected function _getActionPage($user, $org, $page=1, $pageSize=300, $params=null)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission');
        $request->setMethod('GET')
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);

        is_null($params) && $params = [];
        $params["page"] = $page;
        $params["page_size"] = $pageSize;
        foreach ($params as $key => $val) $request->setParam($key, $val);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());
        if ($response->getStatusCode() !== 200) {
            Log::error("Get action page failed.", [$response->toArray(), $params]);
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ReturnCode::LOGICAL_ERROR;
        }
        $this->code = 0;

        return json_decode($response->getBody(), true);
    }


    /**
     * @memo 修改权限控制点
     * @param $action
     * @param $user
     * @param $org
     * @param $param [
     *              'system' => $system,
     *              'resource' => $resource,
     *              'remark' => $remark,
     *          ]
     * @return int
     */
    protected function _modifyAction($action, $user, $org, $param)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission/config/'.$action);
        $request->setMethod('PUT')
            ->setEncoding(EasyRequest::ENCODING_JSON)
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setData($param);
        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());
        if ($response->getStatusCode() !== 200) {
            Log::error("Modify action $action failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ReturnCode::LOGICAL_ERROR;
        }
        $this->code = 0;

        return 0;
    }


    /**
     * @memo 保存权限配置。如果权限不存在，新增权限。如果权限存在，更新权限。如果是更新，原有角色拥有的权限只新增不删除。
     * @param $action
     * @param $user
     * @param $org
     * @param $remark
     * @param $system
     * @param array $roles
     * @param array $resource
     * @return int
     */
    protected function _saveAction($action, $user, $org, $remark, $system, array $roles = [], array $resource = [])
    {
        $param = [
            'action' => $action,
        ];

        isset($roles) && $param['roles'] = $roles;
        isset($remark) && $param['remark'] = $remark;
        isset($system) && $param['system'] = $system;
        isset($resource) && $param['resource'] = $resource;

        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission/save');
        $request->setMethod('POST')
            ->setEncoding(EasyRequest::ENCODING_JSON)
            ->setHeader('user', $user)
            ->setHeader('org', $org);
        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setData($param);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Save action $action failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return ReturnCode::LOGICAL_ERROR;
        }
        $this->code = 0;

        return 0;
    }

    /**
     * @param int $org
     * @param string $user
     * @param bool $default
     * @return bool
     */
    protected function _isSuperAdmin($org, $user, $default = false)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission_role/config');
        $request->setMethod('GET')
            ->setHeader('user', $user)
            ->setHeader('org', $org)
            ->setParam('role', '系统管理员');
        !empty($this->host) && $request->setHeader('host', $this->host);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Query user role failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return $default;
        }
        $this->code = 0;

        $body = json_decode($response->getBody(), true);
        return isset($body['data']) && isset($body['data'][0]) && in_array($user, $body['data'][0]['user']) ? true : false;
    }

    /**
     * @param string $username
     * @return bool
     */
    public static function inWhiteList($username)
    {
        $config = Configure::get('permission.white_list', null);
        if (empty($config)) return false;
        return in_array($username, explode(" ", $config));
    }

    /**
     * @param int $org
     * @param string $user
     * @param bool $isAdmin
     * @return EasyResponse
     */
    protected function _registerUser($org, $user, $isAdmin)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v2/permission/register_user');
        $request->setMethod('POST')
            ->setHeader('org', $org)
            ->setEncoding(EasyRequest::ENCODING_JSON)
            ->setData([
                'name' => $user,
                'is_admin' => $isAdmin,
            ]);
        !empty($this->host) && $request->setHeader('host', $this->host);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Register user failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return $response;
        }

        $this->code = 0;
        return $response;
    }

    /**
     * @param int $org
     * @param string $user
     * @return EasyResponse|null
     */
    protected function _deleteUser($org, $user)
    {
        $request = new EasyRequest('http://'.$this->address.'/api/v1/permission/delete_user');
        $request->setMethod('DELETE')
            ->setHeader('org', $org)
            ->setParam('name', $user);
        !empty($this->host) && $request->setHeader('host', $this->host);

        $this->traceSpan('/api/v1/permission', $request);
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Delete user failed.", $response->toArray());
            $this->code = ReturnCode::LOGICAL_ERROR;
            return $response;
        }

        $this->code = 0;
        return $response;
    }
}