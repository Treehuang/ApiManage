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
 * Class EasyRequest
 * @memo Http请求类
 * @package common\library
 */
class EasyRequest
{
    /**
     * ENCODING_* constants, used for specifying encoding options
     */
    const ENCODING_QUERY = 0;
    const ENCODING_JSON = 1;
    const ENCODING_RAW = 2;

    /** @var string $method */
    private $method = 'get';

    /** @var string $url */
    private $url = '';

    /** @var array $params */
    private $params = [];

    /** @var array $headers */
    private $headers = [];

    /** @var mixed $data */
    private $data = [];

    /** @var int $encoding */
    private $encoding = self::ENCODING_QUERY;

    /**
     * EasyRequest constructor.
     * @param string $url
     */
    public function __construct($url = '')
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method 请求方法: get, post, put, delete
     * @return $this
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        // TODO: 根据EasyCurl判断允许的方法
        $this->method = $method;
        return $this;
    }

    /**
     * @return string URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url URL
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getParam($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = [];
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getHeader($key)
    {
        $key = strtolower($key);

        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[strtolower($key)] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array();

        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param int $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $encoding = intval($encoding);

        if (
            $encoding !== static::ENCODING_QUERY &&
            $encoding !== static::ENCODING_JSON &&
            $encoding !== static::ENCODING_RAW
        ) {
            throw new \InvalidArgumentException("Encoding [$encoding] not a known Request::ENCODING_* constant");
        }

        if ($encoding === static::ENCODING_JSON && !$this->getHeader('Content-Type')) {
            $this->setHeader('Content-Type', 'application/json');
        }

        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @return string
     */
    public function buildUrl()
    {
        if (empty($this->params)) return $this->url;

        $parts = parse_url($this->url);

        $queryString = '';
        if (isset($parts['query']) && $parts['query']) {
            $queryString .= $parts['query'].'&'.http_build_query($this->params);
        } else {
            $queryString .= http_build_query($this->params);
        }

        $retUrl = $parts['scheme'].'://'.$parts['host'];
        if (isset($parts['port'])) {
            $retUrl .= ':'.$parts['port'];
        }

        if (isset($parts['path'])) {
            $retUrl .= $parts['path'];
        }

        if ($queryString) {
            $retUrl .= '?' . $queryString;
        }

        return $retUrl;
    }

    /**
     * @return array
     */
    public function formatHeaders()
    {
        $headers = array();

        foreach ($this->headers as $key => $val) {
            if (is_string($key)) {
                $headers[] = $key . ': ' . $val;
            } else {
                $headers[] = $val;
            }
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function encodeData()
    {
        switch ($this->encoding) {
            case static::ENCODING_JSON:
                return json_encode($this->data);
            case static::ENCODING_QUERY:
                return http_build_query($this->data);
            case static::ENCODING_RAW:
                return $this->data;
            default:
                throw new \UnexpectedValueException("Encoding [$this->encoding] not a known Request::ENCODING_* constant");
        }
    }

    /**
     * EasyRequest send.
     * @return EasyResponse|null 返回对象
     */
    public function send()
    {
        return EasyCurl::sendRequest($this);
    }
}