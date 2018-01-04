<?php
/**
 * @author index
 *
 * 通用函数
 */

namespace common\library;

abstract class EasyFunc {
    
    /**
     * 解析搜索参数
     *
     * @param $params
     * @param string $mode
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    final public static function parseSearchParams($params, &$mode = null, &$page = null, &$pageSize = null) {

        isset($params['page']) && $page = intval($params['page']);
        isset($params['pageSize']) && $pageSize = intval($params['pageSize']);
        isset($params['mode']) && $mode = $params['mode'];
        empty($page) && $page = 1;
        empty($pageSize) && $pageSize = 30;
        empty($mode) && $mode = 'and';

        $searchParam = [];
        foreach ($params as $keyOperator => $value) {

            //模式匹配
            if (preg_match('/^([\w\:]+)(\$\w+)$/', $keyOperator, $match) !== 1) continue;

            //获取key和operator
            $key = $match[1];
            $operator = $match[2];

            //key包含该":"
            if (strpos($key, ":") !== false) {
                $key = str_replace(':','.',$key);
            }

            //处理value类型
            if (is_array($value)) {
                foreach ($value as &$elem) {
                    $elem = self::parseParamType($elem);
                }
            }
            else {
                $value = self::parseParamType($value);
            }

            $searchParam[] = array(
                'key' => $key,
                'operator' => $operator,
                'value' => $value
            );
        }

        return $searchParam;
    }

    /**
     * 判断参数数据类型
     *
     * @param $value
     * @return float|int
     */
    final public static function parseParamType($value) {

        if (preg_match('/^"(.*)"$/', $value, $match) === 1) {
            return $match[1];
        }
        elseif (preg_match('/^-{0,1}\d+\.\d+$/', $value) === 1) {
            return floatval($value);
        }
        elseif (preg_match('/^-{0,1}\d+$/', $value) === 1) {
            return intval($value);
        }
        return $value;
    }

    /**
     * 用闭包实现的一个计时器函数
     *
     * @author index
     */
    final public static function markTime() {
        /**
         * @var int
         */
        $time = null;

        $mark = function() use (&$time) {
            $t = microtime(true);
            $ret = round($t - $time, 3) * 1000;
            $time = $t;
            return (int)$ret;
        };
        $mark();

        return $mark;
    }

    /**
     * 向uri添加参数
     *
     * @param string $uri
     * @param array $params
     */
    final public static function uriAddParams(&$uri, $params) {
        if (!empty($params)) {
            strpos($uri, "?") === false && $uri .= "?";
            substr($uri, -1) !== "?" && $uri .= "&";
            $uri .= http_build_query($params);
        }
    }

    /**
     * Hello
     *
     * @author index
     * @param $name
     * @return int
     */
    final public static function hello($name) {
        echo "Hello $name!\n";
        return 0;
    }

    /**
     * stdObject转array
     * 
     * @param mixed $array 待转换的对象或数组
     * @return array 转换结果
     */
    final public static function object_array($array)
    {
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key=>$value)
            {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }

    /**
     * @param array $array
     * @param array ...$fields
     * @return array
     */
    final public static function array_distinct($array, $fields) {

        $newArray = [];
        foreach ($array as $elem) {
            $newArray[] = array_filter(
                $elem,
                function ($key) use ($fields) {
                    return in_array($key, $fields);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return array_values(array_map("unserialize", array_unique(array_map('serialize', $newArray))));
    }

    /**
     * 加盐SHA1哈希
     * 
     * @param string $data 待哈希数据
     * @param string &$salt 生成的salt数据, 如果salt为null, 即随机产生salt
     * @return string 生成的哈希数据(16进制不区分大小写字符串)
     */
    public static function salt_hash($data, &$salt = null)
    {
        if (is_null($salt)) {
            $crypto_strong = true;
            $salt = base64_encode(openssl_random_pseudo_bytes(24, $crypto_strong));
        }
        return hash('sha1', $data.$salt);
    }

    /**
     * 验证是否为邮箱
     *
     * @param string $email 待验证邮箱
     * @return int 1:验证通过;
     */
    public static function is_email($email)
    {
        $regex = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
        return preg_match($regex, $email);
    }

    /**
     * 读取文件
     *
     * @param string $filePath 文件路径
     * @param mixed $fileData 文件数据
     * @return int 返回码
     */
    public static function readFile($filePath, &$fileData)
    {
        // 打开文件
        if (!is_file($filePath)) return ReturnCode::DISK_FILE_NOT_EXIST;
        $fileHandle = fopen($filePath, "rb");
        if ( $fileHandle === false ) return ReturnCode::DISK_FILE_OPEN_FAILED;

        // 读取文件
        $fileData = "";
        while (!feof($fileHandle)) $fileData .= fread($fileHandle, 8*1024);
        fclose($fileHandle);

        return 0;
    }

    /**
     * 转换value为string, 如果value为数组, 数组所有值为string
     * @param mixed $value
     * @return array|string
     */
    public static function strval_array($value) {
        return is_array($value) ? array_map('\common\library\EasyFunc::strval_array', $value) : strval($value);
    }

    /**
     * 提取数组值摘要
     * @param array $array
     * @return string
     */
    public static function array_summary($array) {
        $summary = "";
        $queue = [];
        $queue[] = $array;
        while (!empty($queue)) {
            $item = array_shift($queue);
            foreach ($item as $value) {
                if (is_array($value)) {
                    array_push($queue, $value);
                }
                else {
                    $summary .= " ".strval($value);
                }
            }
        }
        return $summary;
    }

    /**
     * @param $str
     * @param int $l
     * @return array
     */
    public static function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }



    /**
     * 对字符串做base64转码和签名
     * @param $str
     * @param $result
     * @return int
     */
    public static function SignatureAndEncode($str)
    {
        $base64Str = base64_encode($str);
        $result = sha1($base64Str . $base64Str[0]) . $base64Str;

        return $result;
    }


    /**
     * 对字符串做签名校验和base64解码
     * @param $str
     * @param $result
     * @return int
     */
    public static function CheckSignatureAndDecode($str, &$result)
    {
        if (strlen($str) <= 40) {
            Log::error("Str is too short: $str.");
            return ReturnCode::LOGICAL_ERROR;
        }
        $hash = substr($str, 0, 40);
        $base64Str = substr($str, 40);
        if ($hash != sha1($base64Str . $base64Str[0])) {
            Log::error("Check signature failed.", [$str]);
            return ReturnCode::LOGICAL_ERROR;
        }

        $result = base64_decode($base64Str);

        return 0;
    }
}
