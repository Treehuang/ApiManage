<?php

namespace common\library;

use common\core\Configure;
use easyops\easykin\core\ClientSpan;

/**
 * Class NotifyService
 * @package common\library
 * 
 * @method int notice($system, $topic, $org = 0, $user = "defaultUser", array $data = []) 发送通知
 */
class NotifyService extends ServiceBase
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
        return Configure::get('notify.name', 'logic.notify');
    }

    /** @return bool true多实例服务, false单实例服务 */
    protected function _isMultiService()
    {
        return Configure::get('notify.multi', false);
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

        $config = Configure::get('notify');
        $this->address = $this->serviceList[0]['ip'] . ":" . $this->serviceList[0]['port'];
        $this->host = isset($config['host']) ? $config['host'] : "";
        $this->code = 0;
        return call_user_func_array([$this, $methodName], $arguments);
    }

    /**
     * @param string $system
     * @param string $topic
     * @param int $org
     * @param string $user
     * @param array $data
     * @return int 返回码
     */
    protected function _notice($system, $topic, $org = 0, $user = 'defaultUser', array $data = [])
    {
        $body = [
            'system' => $system,
            'topic' => $topic,
            'data' => $data,
        ];
        $request = new EasyRequest('http://'.$this->address.'/message');

        $request->setMethod('POST')
            ->setEncoding(EasyRequest::ENCODING_JSON)
            ->setHeader('user', $user)
            ->setHeader('org', $org);

        !empty($this->host) && $request->setHeader('host', $this->host);
        $request->setData($body);
        $this->traceSpan('/message', $request);
        $user = is_null($user) ? "defaultUser" : $user;
        $this->setTraceSpanTag('user', $user);
        $this->setTraceSpanTag('org', $org);

        $response = $request->send();

        $this->traceSpanEnd($response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            Log::error("Notice failed.", $response->toArray());
            return ReturnCode::LOGICAL_ERROR;
        }
        return 0;
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
            $this->span->receive();
        }
    }

}