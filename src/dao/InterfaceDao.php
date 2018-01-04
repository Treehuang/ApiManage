<?php

namespace dao;

use library\FileConst;
use common\library\Log;
use common\library\Result;
use common\library\ReturnCode;

class InterfaceDao
{
    public function startWriteFile($saveList, $saveDetail, $interfaceName)
    {
        // 列表和详情数据
        $yamlList = yaml_emit($saveList);
        $yamlDetail = yaml_emit($saveDetail);

        // 写入列表文件
        $rt = file_put_contents(FileConst::INTERFACE_INFO_YAML, $yamlList, FILE_APPEND);
        if (false === $rt) {
            Log::error('写入接口列表文件失败');

            return ReturnCode::ERROR;
        }

        // 写入详情文件
        $rt = file_put_contents("$interfaceName.yaml", $yamlDetail, FILE_APPEND);
        if (false === $rt) {
            Log::error('写入接口详情文件失败');

            return ReturnCode::ERROR;
        }

        return 0;
    }

    public function getInterfaceYaml(&$parsed = array())
    {
        // 一开始没有创建接口时做的处理
        if (!file_exists(FileConst::INTERFACE_INFO_YAML)) {
            Log::error('没有接口列表文件');

            return ReturnCode::ERROR;
        }

        // 将yaml文件解析成数组
        $parsed = @yaml_parse_file(FileConst::INTERFACE_INFO_YAML, -1);

        // 判断解析结果
        if (!$parsed) {
            Log::error('接口列表文件无内容');

            return ReturnCode::ERROR;
        }

        return 0;
    }

    public function getLatestVersion($interfaceName, &$latestVersion)
    {
        // 将yaml文件解析成数组
        $list = yaml_parse_file($interfaceName.'.yaml', -1);
        if (!$list) {
            Log::error('yaml解析文件失败');

            return ReturnCode::ERROR;
        }

        $len = count($list);
        $latestVersion = $list[$len - 1]['version'];

        return 0;
    }

    public function writeInterfaceList($data, $interfaceName)
    {
        // 取出要存进接口列表的数据
        $newData['interfaceName'] = $data['interfaceName'];
        $newData['timeout'] = $data['timeout'];
        $newData['method'] = $data['method'];
        $newData['url'] = $data['url'];
        $newData['requestMessageName'] = $data['request']['message'];
        $newData['responseMessageName'] = $data['response']['message'];

        // 将yaml文件解析成数组
        $list = yaml_parse_file(FileConst::INTERFACE_INFO_YAML, -1);
        if (!$list) {
            Log::error('yaml解析文件失败');

            return ReturnCode::ERROR;
        }

        $oldInterface = null;
        foreach ($list as $item) {
            if ($item['interfaceName'] === $interfaceName) {
                $oldInterface = $item;
            }
        }

        if (isset($oldInterface)) {
            // 写入接口列表文件
            $oldYaml = yaml_emit($oldInterface);
            $newYaml = yaml_emit($newData);
            $origin_str = file_get_contents('interface_info.yaml');
            $update_str = str_replace($oldYaml, $newYaml, $origin_str);
            $put = file_put_contents('interface_info.yaml', $update_str);
            if (false === $put) {
                Log::error('文件写入失败');

                return ReturnCode::ERROR;
            }
        }

        return 0;
    }

    public function writeInterfaceDetailYaml($data, $interfaceName)
    {
        // 判断错误定义是否为空
        if (empty($data['errors'])) {
            $data['errors'] = null;
        }
        $yaml = yaml_emit($data);

        // 写入接口详情的yaml文件
        $rt = file_put_contents($interfaceName.'.yaml', $yaml);
        if (false === $rt) {
            Log::error('接口详情写入yaml文件失败');

            return ReturnCode::ERROR;
        }

        return 0;
    }

    public function getInterfaceDetail($interfaceName, &$detail)
    {
        // 将yaml文件解析成数组
        $list = yaml_parse_file($interfaceName.'.yaml', -1);
        if (!$list) {
            Log::error('yaml解析文件失败');

            return ReturnCode::ERROR;
        }

        // 提取被删除的消息
        $request = $response = array();
        foreach ($list as $item) {
            $request = $item['request'];
            $response = $item['response'];
        }

        chdir(FileConst::RETURN_PATH.'/'.FileConst::RETURN_PATH);
        @mkdir(FileConst::MESSAGE);
        chdir(FileConst::MESSAGE);
        exec('ls', $msg, $code);
        if (0 !== $code) {
            Log::error('获取消息失败');

            return ReturnCode::ERROR;
        }

        $deleteMessage = array();
        if (!empty($request)) {
            if (!in_array($request['message'].'yaml', $msg)) {
                $deleteMessage[] = $request['message'];

                // 删除接口详情文件的request
            }
        }
        if (!empty($response)) {
            if (!in_array($response['message'].'yaml', $msg)) {
                $deleteMessage[] = $response['message'];

                // 删除接口详情文件的response
            }
        }
        if (empty($deleteMessage)) {
            $deleteMessage = null;
        }

        $detail['deleteMessage'] = $deleteMessage;
        $detail['interfaceDetail'] = $list[0];

        return 0;
    }

    public function deleteInterface($interfaceName)
    {
        // 将yaml文件解析成数组
        $list = yaml_parse_file(FileConst::INTERFACE_INFO_YAML, -1);
        if (!$list) {
            Log::error('接口列表文件解析失败');

            return ReturnCode::ERROR;
        }

        $count = count($list);
        for ($i = 0; $i < $count; ++$i) {
            if ($list[$i]['interfaceName'] == $interfaceName) {
                $oldyaml = yaml_emit($list[$i]);
                $origin_str = file_get_contents('interface_info.yaml');
                if (!$origin_str) {
                    Log::error('文件获取失败');

                    return ReturnCode::ERROR;
                }

                // 删除最后一个时，空字符串写入出错，去掉写入判断
                $update_str = str_replace($oldyaml, '', $origin_str);

                file_put_contents('interface_info.yaml', $update_str);

                break;
            }
        }

        return 0;
    }

    public function matchAndSearchName($nameList, $keywordName, &$totalMachName = array())
    {
        //去掉关键字首尾空格
        $keywordName = trim($keywordName);
        $keywordNameArr[0] = $keywordName;
        //判断是否出现非法字符
        $machKeyWordName = preg_grep('/^[\w{4e00}-\x{9f5a}\w\s]+$/u', $keywordNameArr);
        if (0 === count($machKeyWordName)) {
            Log::error('非法字符');

            return ReturnCode::ERROR;
        }

        // 匹配规则
        $machName = preg_grep("/$keywordName/i", $nameList);
        $machName = array_values($machName);
        if (0 === count($machName)) {
            Log::error('无对应匹配');

            return ReturnCode::ERROR;
        }

        // 匹配以关键字开头的接口
        $firstName = preg_grep("/^$keywordName/i", $machName);
        // 去除已经匹配到的接口
        foreach ($firstName as $key => $value) {
            unset($machName[$key]);
        }
        $firstName = array_values($firstName);
        $machName = array_values($machName);
        // 匹配包含关键字的接口
        $secondName = preg_grep("/.+$keywordName/i", $machName);
        $secondName = array_values($secondName);
        // 排序
        $totalMachName = array_merge($firstName, $secondName);

        if (0 === count($totalMachName)) {
            Log::error('排序失败');

            return ReturnCode::ERROR;
        }

        return 0;
    }

    public function searchName($totalMachName, &$list = array(), $parsed, $field)
    {
        // 搜索
        $totalCount = count($parsed);
        $sureCount = count($totalMachName);
        for ($i = 0; $i < $sureCount; ++$i) {
            for ($k = 0; $k < $totalCount; ++$k) {
                if ($parsed[$k][$field] === $totalMachName[$i]) {
                    $list[] = $parsed[$k];
                }
            }
        }

        return 0;
    }

    public function enterInterfaceDir($projectName, $serviceName)
    {
        // 进入项目所在路径
        chdir(FileConst::USERNAME_PATH);

        // 判断项目是否存在
        if (!is_dir($projectName)) {
            Log::error('项目不存在');

            return ReturnCode::ERROR;
        }

        // 进入项目
        chdir($projectName);

        // 进入项目里的config文件
        chdir(FileConst::CONFIG);

        // 进入服务所在的文件
        chdir(FileConst::SERVICE);
        if (!is_dir($serviceName)) {
            Log::error('服务不存在');

            return ReturnCode::ERROR;
        }

        // 进入服务
        chdir($serviceName);

        return 0;
    }

    public function existsMessage($projectName, $requestMessageName, $responseMessageName)
    {
        chdir(FileConst::USERNAME_PATH);
        if (!is_dir($projectName)) {
            Log::error('项目不存在');

            return ReturnCode::ERROR;
        }
        chdir($projectName);
        chdir(FileConst::CONFIG);
        chdir(FileConst::MESSAGE);

        $noExistsMessage = array();
        if ('' !== $requestMessageName) {
            if (!file_exists($requestMessageName.'.yaml')) {
                $noExistsMessage['noExistsMessage']['requestMessage'] = $requestMessageName;
            }
        }
        if (!file_exists($responseMessageName.'.yaml')) {
            $noExistsMessage['noExistsMessage']['responseMessage'] = $responseMessageName;
        }
        if (0 !== count($noExistsMessage)) {
            Result::Error(ReturnCode::ERROR, '没有消息', $noExistsMessage);
            exit;
        }

        chdir('../../../../../src/www');

        return 0;
    }
}
