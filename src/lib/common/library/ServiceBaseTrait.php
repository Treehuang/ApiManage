<?php
/**
 * @author index
 */

namespace common\library;


trait ServiceBaseTrait
{

    /** @var int $_lastCode */
    protected $_lastCode;

    /**
     * Execute service method.
     *
     * @param string $name Service method name
     * @param array $arguments Service method parameters
     * @return mixed|null Result
     */
    final protected function _callServiceMethod($name, $arguments) {

        $serviceName = $this->_getServiceName();

        // If the service was multiple instances
        if ($this->_isMultiService()) {
            $ensList = ens_get_all_service_by_name(APP_NAME, $serviceName);
        }
        else {
            $ensList = array(ens_get_service_by_name(APP_NAME, $serviceName));
        }

        // If the service's instance/instances return successfully
        $sessionId = $ensList[0]->session_id;
        if (empty($sessionId)) {
            // Get service' instance/instances failed, log the error and give the default serviceList
            Log::error('NameService get '.$serviceName.' service failed');
            $serviceList[] = [
                'ip' => '127.0.0.1',
                'port' => 80,
            ];
        }
        else {
            $serviceList = [];
            foreach ($ensList as $service) {
                $serviceList[] = [
                    'ip' => $service->ip,
                    'port' => $service->port,
                ];
            }
        }

        /** @var ServiceEngineTrait $serviceEngine ServiceEngine instance */
        $serviceEngine = $this->_getServiceEngine($serviceList);

        // If the ServiceEngine initialize successfully.
        $this->_lastCode = $serviceEngine->getLastCode();
        if ( $serviceEngine->getLastCode() !== 0 ) return NULL;

        // Set the ServiceEngine method name.
        $serviceEngine->setLastMethod($name);

        // Start timing.
        $time = EasyFunc::markTime();

        // Invoke ServiceEngine method.
        $result = self::invokeMethod([$serviceEngine, $name], $arguments);

        // Report the service performance and state.
        ens_report_stat($sessionId, $serviceEngine->getLastMethod(), $serviceEngine->getLastCode(), $time(), __FILE__ . ":" . __LINE__, json_encode(debug_backtrace(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

        // Set the last return code.
        $this->_lastCode = $serviceEngine->getLastCode();

        return $result;
    }

    /**
     * Invokes a method.
     *
     * @param mixed $func Class method
     * @param array $params Class method parameters
     * @return mixed Function results
     */
    protected static function invokeMethod($func, array &$params = array()) {
        list($class, $method) = $func;

        $instance = is_object($class);

        switch (count($params)) {
            case 0:
                return ($instance) ?
                    $class->$method() :
                    $class::$method();
            case 1:
                return ($instance) ?
                    $class->$method($params[0]) :
                    $class::$method($params[0]);
            case 2:
                return ($instance) ?
                    $class->$method($params[0], $params[1]) :
                    $class::$method($params[0], $params[1]);
            case 3:
                return ($instance) ?
                    $class->$method($params[0], $params[1], $params[2]) :
                    $class::$method($params[0], $params[1], $params[2]);
            case 4:
                return ($instance) ?
                    $class->$method($params[0], $params[1], $params[2], $params[3]) :
                    $class::$method($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return ($instance) ?
                    $class->$method($params[0], $params[1], $params[2], $params[3], $params[4]) :
                    $class::$method($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                return call_user_func_array($func, $params);
        }
    }

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    abstract protected function _isMultiService();

    /**
     * Get service name.
     *
     * @return string
     */
    abstract protected function _getServiceName();

    /**
     * Get ServiceEngine instance.
     *
     * @param array $serviceList service instance list
     * @return ServiceEngineTrait ServiceEngine instance
     */
    abstract protected function _getServiceEngine($serviceList);
}