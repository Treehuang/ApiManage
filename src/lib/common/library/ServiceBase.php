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


/**
 * Class ServiceBase
 * @package common\library
 */
abstract class ServiceBase
{
    /** @var int $code */
    protected $code;

    /** @var string $method */
    protected $method;

    /** @var int $time */
    protected $time;

    /** @var int $sessionId */
    protected $sessionId;

    /** @var array $serviceList */
    protected $serviceList = [];

    /** @return string 服务名 */
    abstract protected function _getServiceName();

    /** @return bool true多实例服务, false单实例服务 */
    abstract protected function _isMultiService();

    /**
     * ServiceBase call.
     * @memo 用户自定义请求方式
     * @param string $name 请求方法名
     * @param array $arguments 请求参数数组
     * @return mixed
     */
    abstract protected function call($name, $arguments);

    /**
     * ServiceBase _getService.
     * @memo 获取服务信息
     * @access private
     */
    private function _getService()
    {
        $serviceName = $this->_getServiceName();
        // If the service was multiple instances
        if ($this->_isMultiService()) {
            $ensList = ens_get_all_service_by_name(APP_NAME, $serviceName);
        }
        else {
            $ensList = [ens_get_service_by_name(APP_NAME, $serviceName)];
        }
        // If the service's instance/instances return successfully
        $this->sessionId = $ensList[0]->session_id;
        if (empty($this->sessionId)) {
            // Get service' instance/instances failed, log the error and give the default serviceList
            Log::error('NameService get '.$serviceName.' service failed');
            // TODO: 改为由配置文件中获取
            $this->serviceList = [['ip' => '127.0.0.1', 'port' => 80]];
        }
        else {
            $this->serviceList = [];
            foreach ($ensList as $service) {
                $this->serviceList[] = [
                    'ip' => $service->ip,
                    'port' => $service->port,
                ];
            }
        }
    }

    /**
     * ServiceBase _reportService.
     * @memo 上报服务请求结果
     * @access private
     */
    private function _reportService()
    {
        // Report the service performance and state.
        if (!empty($this->sessionId)) {
            ens_report_stat($this->sessionId, $this->method, $this->code, $this->time, __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        }
    }

    /**
     * ServiceBase __call.
     * @memo 魔术方法, 所有服务函数应该先通过该方法
     * @access public
     * @param string $name 请求方法名
     * @param array $arguments 请求参数数组
     * @return mixed
     */
    final public function __call($name, $arguments)
    {
        $this->code = 0; // 默认返回码
        $this->method = $name; // 记录请求方法
        $this->_getService(); // 获取服务
        $time = microtime(true);
        $result = $this->call($name, $arguments);
        $this->time = round(microtime(true)-$time, 3)*1000;
        $this->_reportService(); // 上报服务状态
        return $result;
    }

    /**
     * ServiceBase getCode.
     * @return int 返回码
     */
    public function getCode()
    {
        return intval($this->code);
    }
}