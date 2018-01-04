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
use Doctrine\Instantiator\Exception\InvalidArgumentException;


/**
 * Class EasyResponse
 * @package common\library
 */
class EasyResponse
{
    /**
     * The response headers.
     *
     * @var array
     */
    public $headers = array();

    /**
     * The response body.
     *
     * @var string
     */
    public $body;

    /**
     * The results of curl_getinfo on the response request.
     *
     * @var array|false
     */
    public $info;

    /**
     * The response code including text, e.g. '200 OK'.
     *
     * @var string
     */
    public $statusText;

    /**
     * The response code.
     *
     * @var int
     */
    public $statusCode;

    /**
     * @param string $body
     * @param string $headerString
     * @param mixed  $info
     */
    public function __construct($body, $headerString, $info = array())
    {
        $this->body = $body;
        $this->info = $info;
        $this->statusCode = intval($this->info['http_code']);
        $this->parseHeaderString($headerString);
    }

    /**
     * @param string $headerString
     */
    private function parseHeaderString($headerString)
    {
        $headers = explode("\r\n", trim($headerString));
        $this->parseHeaders($headers);
    }

    /**
     * @param array $headers "key: value"
     * @return void
     * @throws InvalidArgumentException
     */
    private function parseHeaders(array $headers)
    {
        $this->headers = array();

        // 解析状态码及状态信息
        if (!preg_match('/^HTTP\/\d\.\d (([0-9]{3}).*)$/', array_shift($headers), $match)) {
            throw new \InvalidArgumentException('Invalid response header');
        }
        $this->statusText = $match[1];
        // $this->statusCode = intval($match[2]); 不可靠

        foreach ($headers as $header) {
            // 跳过空行
            if (!$header) continue;

            $delimiter = strpos($header, ':');
            if (!$delimiter) {
                continue;
            }

            $key = trim(strtolower(substr($header, 0, $delimiter)));
            $val = ltrim(substr($header, $delimiter + 1));

            if (isset($this->headers[$key])) {
                if (is_array($this->headers[$key])) {
                    $this->headers[$key][] = $val;
                } else {
                    $this->headers[$key] = array($this->headers[$key], $val);
                }
            } else {
                $this->headers[$key] = $val;
            }
        }
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function getHeader($key)
    {
        $key = strtolower($key);

        return array_key_exists($key, $this->headers) ?
            $this->headers[$key] : null;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'headers' => $this->headers,
            'body' => $this->body,
            'info' => $this->info
        );
    }

    /**
     * @return string json string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}