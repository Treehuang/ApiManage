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
 * Class HttpService
 * @package common\library
 *
 * @method EasyResponse request($request)
 */
class HttpService extends ServiceBase
{
    protected $name;
    protected $multi;
    protected $host;
    protected $ip;
    protected $port;

    /**
     * @param string $name
     * @param bool $multi
     * @param string|null $host
     * @return HttpService
     */
    public static function newService($name, $multi = false, $host = null)
    {
        return new self($name, $multi, $host);
    }

    /**
     * HttpService constructor.
     * @param string $name
     * @param bool $multi
     * @param string|null $host
     */
    public function __construct($name, $multi = false, $host = null)
    {
        $this->name = $name;
        $this->multi = $multi;
        $this->host = $host;
    }

    /** @return string 服务名 */
    protected function _getServiceName()
    {
        return $this->name;
    }

    /** @return bool true多实例服务, false单实例服务 */
    protected function _isMultiService()
    {
        return $this->multi;
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
        if (!method_exists(static::class, $methodName)) return false;

        $this->ip = $this->serviceList[0]['ip'];
        $this->port = $this->serviceList[0]['port'];
        // $this->host = isset($config['host']) ? $config['host'] : "";
        $this->code = 0;
        return call_user_func_array([$this, $methodName], $arguments);
    }

    /**
     * @param EasyRequest $request
     * @return EasyResponse|null
     */
    protected function _request($request)
    {
        $url = $request->getUrl();
        $request->setUrl($this->parse_url($url));
        !empty($this->host) && $request->setHeader('host', $this->host);
        $response  = $request->send();
        return $response;
    }

    private function parse_url($url) {
        $parsed_url = parse_url($url);
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = $this->ip;
        $port     = ':'.$this->port;
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
