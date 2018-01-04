<?php

namespace logic;

use dao\InterfaceDao;
use common\library\Log;
use library\Tools;
use common\library\ReturnCode;

class InterfaceLogic
{
    public function writeInterfaceFile($projectName, $serviceName, $data, &$saveDetail)
    {
        $interfaceName = $data['interfaceName'];
        $endpoint = $data['endpoint'];
        $timeout = $data['timeout'];
        $request = $data['request'];
        $response = $data['response'];
        $errors = $data['errors'];
        $requestMessageName = $data['request']['message'];
        $responseMessageName = $data['response']['message'];

        if (!isset($timeout)) {
            $timeout = 0;
        }
        if (empty($errors)) {
            $errors = array(
                'code' => '',
                'error' => '',
            );
        }

        // 判断请求报文和返回报文是否存在
        (new InterfaceDao())->existsMessage($projectName, $requestMessageName, $responseMessageName);

        // 判断请求报文和返回报文是否相同
        if ($requestMessageName === $responseMessageName) {
            $saveDetail = '请求报文和返回报文重复';
            Log::error($saveDetail);

            return ReturnCode::ERROR;
        }

        // 保存成接口列表
        $saveList = array(
            'version' => 1,
            'interfaceName' => $interfaceName,
            'endpoint' => $endpoint,
            'timeout' => $timeout,
            'requestMessageName' => $requestMessageName,
            'responseMessageName' => $responseMessageName,
        );

        // 保存成接口详情列表
        $saveDetail = array(
            'version' => 1,
            'interfaceName' => $interfaceName,
            'endpoint' => $endpoint,
            'timeout' => $timeout,
            'request' => $request,
            'response' => $response,
            'errors' => $errors,
        );

        // 修改项目的最新时间
        $code = (new ProjectLogic())->updateProjectTime($projectName, $saveDetail);
        if (0 != $code) {
            return $code;
        }

        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return ReturnCode::ERROR;
        }

        // 判断接口名是否存在
        if (file_exists($interfaceName.'.yaml')) {
            $saveDetail = '接口名已存在';
            Log::error($saveDetail);

            return ReturnCode::ERROR;
        }

        // 开始写入yaml文件
        $code = (new InterfaceDao())->startWriteFile($saveList, $saveDetail, $interfaceName);
        if (0 !== $code) {
            Log::error('写入yaml文件失败');

            return ReturnCode::ERROR;
        }

        // 提交git
        $code = Tools::commitGit('.', "新建接口 $interfaceName", '新增本地接口成功，git push失败', $saveDetail);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    public function getInterfaceList($projectName, $serviceName, &$info = array())
    {
        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return ReturnCode::ERROR;
        }

        // 解析
        $code = (new InterfaceDao())->getInterfaceYaml($parsed);
        if (0 !== $code) {
            Log::error('接口列表文件解析失败');

            return ReturnCode::ERROR;
        }

        // 将名称按照字母、数字和中文排序
        $list = array();
        Tools::orderbyName($parsed, $list, 'interfaceName');
        $info = $list;

        return 0;
    }

    public function updateInterfaceYaml($projectName, $serviceName, $interfaceName, $data, &$info)
    {
        $requestMessageName = $data['request']['message'];
        $responseMessageName = $data['response']['message'];

        // 修改项目的最新时间
        $info = '';
        $code = (new ProjectLogic())->updateProjectTime($projectName, $info);
        if (0 != $code) {
            return $code;
        }

        // 判断请求报文和返回报文是否存在
        (new InterfaceDao())->existsMessage($projectName, $requestMessageName, $responseMessageName);

        // 判断请求报文和返回报文是否重复
        if ($requestMessageName === $responseMessageName) {
            $info = '请求报文和返回报文重复';
            Log::error($info);

            return ReturnCode::ERROR;
        }

        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return $code;
        }

        // 判断接口是否存在
        if (!file_exists($interfaceName.'.yaml')) {
            $info = '该接口已被删除';
            Log::error($info);

            return ReturnCode::ERROR;
        }

        // 将修改的接口覆盖写入接口详情文件
        $code = (new InterfaceDao())->writeInterfaceDetailYaml($data, $interfaceName);
        if (0 !== $code) {
            Log::error('追加写入接口详情文件失败');

            return ReturnCode::ERROR;
        }

        // 将修改的接口替换写入接口列表文件interface_info.yaml
        $code = (new InterfaceDao())->writeInterfaceList($data, $interfaceName);
        if (0 !== $code) {
            Log::error('覆盖写入消息列表文件interface_info.yaml失败');

            return ReturnCode::ERROR;
        }

        // 提交git
        $code = Tools::commitGit('*', "修改接口 $interfaceName", '修改本地接口成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    public function getInterfaceDetail($projectName, $serviceName, $interfaceName, &$detail)
    {
        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return ReturnCode::ERROR;
        }

        // 判断接口是否存在
        if (!file_exists($interfaceName.'.yaml')) {
            Log::error('该接口已被删除');

            return ReturnCode::ERROR;
        }

        //获取详情
        $code = (new InterfaceDao())->getInterfaceDetail($interfaceName, $detail);

        if (0 !== $code) {
            return ReturnCode::ERROR;
        }

        return 0;
    }

    public function deleteInterface($projectName, $serviceName, $interfaceName, &$info)
    {
        // 修改项目的最新时间
        $info = '';
        $code = (new ProjectLogic())->updateProjectTime($projectName, $info);
        if (0 != $code) {
            return $code;
        }
        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return ReturnCode::ERROR;
        }

        // 判断接口是否存在
        if (!file_exists($interfaceName.'.yaml')) {
            $info = '无该接口';
            Log::error($info);

            return ReturnCode::ERROR;
        }

        // 删除该接口详情文件
        exec("rm -f $interfaceName.yaml");

        // 将该接口从接口列表文件中删除
        $code = (new InterfaceDao())->deleteInterface($interfaceName);
        if (0 !== $code) {
            Log::error('从接口列表文件中删除该接口失败');

            return ReturnCode::ERROR;
        }

        // 提交git
        $code = Tools::commitGit('-A .', "删除接口 $interfaceName", '删除本地接口成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    public function checkInterfaceName($projectName, $serviceName, $interfaceName)
    {
        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return ReturnCode::ERROR;
        }

        // 判断接口名是否存在
        if (file_exists($interfaceName.'.yaml')) {
            return ReturnCode::ERROR;
        }

        return 0;
    }

    public function searchInterface($projectName, $serviceName, $data, &$list)
    {
        // 搜索的接口名称关键字
        $field = 'interfaceName';
        $interfaceName = isset($data['interfaceName']) && is_string($data['interfaceName']) ? $data['interfaceName'] : '';
        // 判断关键字是否为空，为空返回列表
        if ('' === $interfaceName) {
            // 获取接口列表
            $code = (new self())->getInterfaceList($projectName, $serviceName, $list);
            // 判断获取结果
            if (0 !== $code) {
                Log::error('接口列表为空');

                return ReturnCode::ERROR;
            }

            return 0;
        }

        // 进入接口路径
        $code = (new InterfaceDao())->enterInterfaceDir($projectName, $serviceName);
        if (0 !== $code) {
            Log::error('进入接口路径失败');

            return ReturnCode::ERROR;
        }

        // 解析
        $code = (new InterfaceDao())->getInterfaceYaml($parsed);
        if (0 !== $code) {
            Log::error('yaml解析文件失败');

            return ReturnCode::ERROR;
        }

        // 取出接口列表的所有接口名
        $interfaceNameList = array();
        $count = count($parsed);
        for ($i = 0; $i < $count; ++$i) {
            $interfaceNameList[] = $parsed[$i]['interfaceName'];
        }

        // 匹配
        $code = (new InterfaceDao())->matchAndSearchName($interfaceNameList, $interfaceName, $totalMachName);
        if (0 !== $code) {
            Log::error('匹配失败');

            return ReturnCode::ERROR;
        }

        // 搜索
        $code = (new InterfaceDao())->searchName($totalMachName, $list, $parsed, $field);
        if (0 !== $code) {
            Log::error('搜索失败');

            return ReturnCode::ERROR;
        }

        return 0;
    }
}
