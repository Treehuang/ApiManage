<?php

namespace common\library;

abstract class EasyCurl {

    /**
     * GET 请求
     * @deprecated
     * @param string $uri 请求uri
     * @param array $header 请求头部
     * @return array 返回请求结果"body"(请求结果body), "state"(http状态码)
     */
    public static function get($uri, $header = null) {
        return self::send("GET", $uri, $header);
    }

    /**
     * PUT 请求
     * @deprecated
     * @param string $uri 请求uri
     * @param array $header 请求头部
     * @param string|null $body 请求body
     * @return array 返回请求结果"body"(请求结果body), "state"(http状态码)
     */
    public static function put($uri, $header = null, $body = null) {
        return self::send("PUT", $uri, $header, $body);
    }

    /**
     * POST 请求
     * @deprecated
     * @param string $uri 请求uri
     * @param array $header 请求头部
     * @param string|null $body 请求body
     * @return array 返回请求结果"body"(请求结果body), "state"(http状态码)
     */
    public static function post($uri, $header = null, $body = null) {
        return self::send("POST", $uri, $header, $body);
    }

    /**
     * DELETE 请求
     * @deprecated
     * @param string $uri 请求uri
     * @param array $header 请求头部
     * @return array 返回请求结果"body"(请求结果body), "state"(http状态码)
     */
    public static function delete($uri, $header = null) {
        return self::send("DELETE", $uri, $header);
    }

    /**
     * 通用请求
     * @deprecated
     * @param string $method 请求方法GET, POST, PUT, DELETE
     * @param array $header 请求头部key=>value
     * @param string $uri 请求uri
     * @param string|null $body 请求body
     * @return array 返回请求结果"body"(请求结果body), "code"(http状态码)
     */
    public static function send($method, $uri, $header = null, $body = null) {
        //初始化http请求
        $curlHandle = curl_init();
        //设置请求方法
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        //设置请求header
        if (!empty($header)) { 
            $requestHeader = [];
            foreach ($header as $key => $value) {
                $requestHeader[] = $key.": ".$value;
            }
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $requestHeader);
        }
        //设置请求uri
        curl_setopt($curlHandle, CURLOPT_URL, $uri);
        //设置请求body
        !empty($body) && curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
        
        //其他请求设置
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HEADER, true);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        //执行请求
        $ret = array();
        $response = curl_exec($curlHandle);
        $headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
        $responseHeader = substr($response, 0, $headerSize);
        $ret["header"] = array();
        foreach (explode("\n", $responseHeader) as $line) {
            preg_match('/^(.+):\s*(.+)$/', $line, $match) && $ret["header"][$match[1]] = $match[2];
        }
        $ret["body"] = substr($response, $headerSize);
        $ret["code"] = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        //关闭curl
        curl_close($curlHandle);
        
        return $ret;
    }

    /**
     * EasyCurl parseRequest.
     * @param EasyRequest $request 请求对象
     * @return resource curl句柄
     */
    private static function parseRequest($request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // 设置请求url
        curl_setopt($ch, CURLOPT_URL, $request->buildUrl());

        // 设置请求方法
        $method = $request->getMethod();
        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($method !== 'get') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }

        // 设置请求头部
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request->formatHeaders());

        // 设置请求body
        if (in_array($method, ['post', 'put', 'patch'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->encodeData());
        }

        return $ch;
    }

    /**
     * @param resource $ch
     * @param string $response
     * @return EasyResponse
     */
    private static function parseResponse($ch, $response)
    {
        $info = curl_getinfo($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        return new EasyResponse($body, $headers, $info);
    }

    /**
     * EasyCurl sendRequest.
     * @param EasyRequest $request 请求对象
     * @return EasyResponse|null 返回对象
     */
    public static function sendRequest(EasyRequest $request)
    {
        $ch = self::parseRequest($request);
        $result = curl_exec($ch);

        // 判断请求异常
        if ($result === false) {
            $errno = curl_errno($ch);
            $errmsg = curl_error($ch);
            Log::error("cURL request failed with error [$errno]: $errmsg");
            curl_close($ch);
            return null;
        }
        // 解析curl结果
        $response = self::parseResponse($ch, $result);
        curl_close($ch);
        return $response;
    }
}
