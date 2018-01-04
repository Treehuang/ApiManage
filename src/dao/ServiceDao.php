<?php

namespace dao;

use library\FileConst;
use library\Tools;
use common\library\Log;
use common\library\ReturnCode;

/**
 * Class ServiceDao.
 *
 * @author Kinming
 */
class ServiceDao
{
    /**
     * 写入服务列表文件.
     *
     * @param $data
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeServiceList(&$data)
    {
        $data['version'] = 1;
        $listYaml = yaml_emit($data);

        $rt = file_put_contents(FileConst::SERVICE_INFO_YAML, $listYaml, FILE_APPEND);
        if (false === $rt) {
            Log::error('写入yaml文件失败', [$listYaml]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 获取服务列表.
     *
     * @param $projectName
     * @param array $list
     *
     * @return int
     *
     * @author Tree
     */
    public function getServiceList($projectName, &$list = array())
    {
        $this->enterServiceListDir($projectName);

        // 一开始判断项目里是否创建了服务做的处理
        if (!file_exists('service_info.yaml')) {
            Log::error('无服务列表文件', [$projectName, getcwd()]);

            return ReturnCode::ERROR;
        }

        // 项目里无服务但是该文件为空做的处理
        $parsed = @yaml_parse_file('service_info.yaml', -1);
        if (!$parsed) {
            Log::error('yaml解析文件失败', [$parsed]);

            return ReturnCode::ERROR;
        }
        $newList = array();
        foreach ($parsed as $item) {
            $newList[] = $item;
        }

        chdir(FileConst::SERVICE);
        exec('ls', $service, $code);

        $listCount = count($newList);
        $count = count($service);

        for ($i = 0; $i < $count; ++$i) {
            chdir($service[$i]);
            // 判断服务里是否有接口
            if (file_exists('interface_info.yaml') && @yaml_parse_file('interface_info.yaml', -1)) {
                $interface = yaml_parse_file('interface_info.yaml', -1);
                $interfaceNumber = count($interface);

                for ($k = 0; $k < $listCount; ++$k) {
                    if ($newList[$k]['name'] === $service[$i]) {
                        $newList[$k]['interfaceNumber'] = $interfaceNumber;
                    }
                }

                chdir(FileConst::RETURN_PATH);
            } else {
                for ($k = 0; $k < $listCount; ++$k) {
                    if ($newList[$k]['name'] === $service[$i]) {
                        $newList[$k]['interfaceNumber'] = 0;
                    }
                }

                chdir(FileConst::RETURN_PATH);
            }
        }

        // 将名称按照字母、数字和中文排序
        $list = array();
        Tools::orderbyName($newList, $list, 'name');

        return 0;
    }

    /**
     * 删除服务列表中对应记录.
     *
     * @param $service_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteService($service_name, &$info)
    {
        //将yaml文件解析成数组
        $list = @yaml_parse_file(FileConst::SERVICE_INFO_YAML, -1);
        if (!$list) {
            $list = [];
        }

        $count = count($list);
        for ($i = 0; $i < $count; ++$i) {
            if ($list[$i]['name'] == $service_name) {
                $oldyaml = yaml_emit($list[$i]);
                $origin_str = file_get_contents('service_info.yaml');
                if (false === $origin_str) {
                    $info = '文件获取失败';
                    Log::error($info);

                    return ReturnCode::ERROR;
                }

                $update_str = str_replace($oldyaml, '', $origin_str);

                file_put_contents('service_info.yaml', $update_str);

                break;
            }
        }

        if ($i == $count) {
            $info = '该服务不存在';
            Log::error($info, [$list, $service_name]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 更新服务
     *
     * @param $serviceName
     * @param $data
     *
     * @return int
     *
     * @author Tree
     */
    public function updateService($serviceName, $data)
    {
        // 解析yaml文件为数组
        $total = @yaml_parse_file('service_info.yaml', -1);
        if (!$total) {
            Log::error('yaml解析文件失败', [$total]);

            return ReturnCode::ERROR;
        }

        foreach ($total as $item) {
            if ($item['name'] === $serviceName) {
                $oldData = $item;
            }
        }

        if (isset($oldData)) {
            // 写入服务列表文件
            $oldYaml = yaml_emit($oldData);
            $newYaml = yaml_emit($data);

            $origin_str = file_get_contents('service_info.yaml');
            $update_str = str_replace($oldYaml, $newYaml, $origin_str);
            $put = file_put_contents('service_info.yaml', $update_str);

            if (false === $put) {
                Log::error('文件写入失败', [$oldData, $data]);

                return ReturnCode::ERROR;
            }
        }

        $newName = $data['name'];

        chdir(FileConst::SERVICE);
        exec('ls', $pro, $code);
        foreach ($pro as $item) {
            if ($item === $serviceName) {
                exec("mv $item $newName");
            }
        }

        return 0;
    }

    /**
     * 进入服务列表所在的目录.
     *
     * @param $projectName
     *
     * @return int
     *
     * @author Tree
     */
    public function enterServiceListDir($projectName)
    {
        @chdir(FileConst::USERNAME_PATH);
        if (!is_dir($projectName)) {
            Log::error('该项目不存在', [$projectName]);

            return ReturnCode::ERROR;
        }

        chdir($projectName);
        @mkdir(FileConst::CONFIG);
        chdir(FileConst::CONFIG);

        return 0;
    }
}
