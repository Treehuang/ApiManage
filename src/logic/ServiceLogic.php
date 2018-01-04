<?php

namespace logic;

use library\ExecCommand;
use library\FileConst;
use common\library\Log;
use common\library\ReturnCode;
use library\Tools;
use dao\InterfaceDao;
use dao\ServiceDao;

/**
 * Class ServiceLogic.
 *
 * @author Kinming
 */
class ServiceLogic
{
    /**
     * 新增服务
     *
     * @param $project_name
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function addService($project_name, &$data, &$info)
    {
        //修改项目的最新时间
        $code = (new ProjectLogic())->updateProjectTime($project_name, $info);
        if (0 != $code) {
            return $code;
        }

        //进入service文件夹所在位置
        $code = (new ServiceDao())->enterServiceListDir($project_name);
        if (0 != $code) {
            $info = '该项目不存在';
            Log::error($info, [$project_name]);

            return $code;
        }

        //创建服务名文件夹
        @mkdir(FileConst::SERVICE);
        chdir(FileConst::SERVICE);
        $service_name = $data['name'];

        $bool = @mkdir("$service_name");
        if (!$bool) {
            $info = '该服务已存在';
            Log::error($info, [$service_name]);

            return ReturnCode::ERROR;
        }
        chdir($service_name);
        ExecCommand::execTouch('.gitkeep');
        chdir(FileConst::RETURN_PATH);

        chdir(FileConst::RETURN_PATH);
        //开始写入yaml文件
        $code = (new ServiceDao())->writeServiceList($data);
        if (0 !== $code) {
            $info = '写入yaml文件失败';
            Log::error($info, [$data]);

            return ReturnCode::ERROR;
        }

        $info = $data;
        $info['interfaceNumber'] = 0;

        //提交git
        $code = Tools::commitGit('.', "新增服务 $service_name", '新增本地服务成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    /**
     * 检查服务名称是否重复.
     *
     * @param $project_name
     * @param $service_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function checkServiceName($project_name, $service_name, &$info)
    {
        //进入service文件夹所在位置
        (new ServiceDao())->enterServiceListDir($project_name);

        @chdir(FileConst::SERVICE);

        ExecCommand::execLs($rs, $code);

        if (in_array("$service_name", $rs)) {
            $info = '该服务名已存在';
            Log::error($info, [$service_name]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 删除服务
     *
     * @param $project_name
     * @param $service_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteService($project_name, $service_name, &$info)
    {
        //修改项目的最新时间
        $code = (new ProjectLogic())->updateProjectTime($project_name, $info);
        if (0 != $code) {
            return $code;
        }

        //进入service文件夹所在位置
        (new ServiceDao())->enterServiceListDir($project_name);

        //将该服务从服务列表文件中删除
        $code = (new ServiceDao())->deleteService($service_name, $info);
        if (0 !== $code) {
            return $code;
        }

        //删除该服务下的所有接口
        @chdir(FileConst::SERVICE);

        if (!is_dir("$service_name")) {
            $info = '该服务不存在';
            Log::error($info, [$service_name]);

            return ReturnCode::ERROR;
        }
        ExecCommand::execRm($service_name);

        //提交git
        $code = Tools::commitGit('-A ..', "删除服务 $service_name", '删除本地服务成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    /**
     * @param  $projectName
     * @param  $list
     *
     * @return int
     *
     * @author Tree
     */
    public function getServiceList($projectName, &$list)
    {
        $code = (new ServiceDao())->getServiceList($projectName, $list);
        if (0 !== $code) {
            Log::error('获取服务列表失败', [$projectName, $list]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * @param   $projectName
     * @param   $serviceName
     * @param   $data
     * @param   $info
     *
     * @return int
     *
     * @author  Tree
     */
    public function updateService($projectName, $serviceName, $data, &$info)
    {
        //修改项目的最新时间
        (new ProjectLogic())->updateProjectTime($projectName, $error);

        $code = (new ServiceDao())->enterServiceListDir($projectName);
        if (0 !== $code) {
            $info = '进入服务列表路径失败';
            Log::error($info, [getcwd(), $projectName]);

            return ReturnCode::ERROR;
        }

        $code = (new ServiceDao())->updateService($serviceName, $data);

        if (0 !== $code) {
            $info = '修改服务失败';
            Log::error($info, [$serviceName, $data]);

            return ReturnCode::ERROR;
        }

        // 提交git
        $code = Tools::commitGit('-A ..', "修改服务 $serviceName", '修改本地服务成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    /**
     * 搜索服务
     *
     * @param $projectName
     * @param $data
     * @param $list
     * @param $info
     *
     * @return int
     *
     * @author Tree
     */
    public function searchService($projectName, $data, &$list, &$info)
    {
        // 搜索的服务名称关键字
        $field = 'name';
        $serviceName = isset($data['name']) && is_string($data['name']) ? $data['name'] : '';
        // 判断服务关键字是否存在
        if ('' === $serviceName) {
            $code = (new self())->getServiceList($projectName, $list);
            if (0 !== $code) {
                $info = '获取服务列表失败';
                Log::error($info, [$projectName, $list]);

                return ReturnCode::ERROR;
            }

            return 0;
        }
        $code = (new ServiceDao())->enterServiceListDir($projectName);
        if (0 !== $code) {
            $info = '进入服务列表路径失败';
            Log::error($info, [$projectName, getcwd()]);

            return ReturnCode::ERROR;
        }

        // 将yaml文件解析成数组
        $parsed = @yaml_parse_file(FileConst::SERVICE_INFO_YAML, -1);

        // 判断解析结果
        if (!$parsed) {
            $info = 'yaml解析文件失败';
            Log::error($info, [$parsed]);

            return ReturnCode::ERROR;
        }

        // 取出服务列表的所有服务名
        $serviceNameList = array();
        foreach ($parsed as $k => $value) {
            $serviceNameList[] = $parsed[$k]['name'];
        }

        // 匹配
        $code = (new InterfaceDao())->matchAndSearchName($serviceNameList, $serviceName, $totalMachName);
        if (0 !== $code) {
            $info = '匹配失败';
            Log::error($info, [$serviceNameList, $serviceName, $totalMachName]);

            return ReturnCode::ERROR;
        }

        // 搜索
        $code = (new InterfaceDao())->searchName($totalMachName, $list, $parsed, $field);
        if (0 !== $code) {
            $info = '搜索失败';
            Log::error($info, [$totalMachName, $list, $parsed, $field]);

            return ReturnCode::ERROR;
        }

        return 0;
    }
}
