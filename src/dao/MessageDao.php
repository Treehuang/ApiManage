<?php

namespace dao;

use library\FileConst;
use common\library\Log;
use common\library\ReturnCode;
use library\Tools;

/**
 * Class MessageDao.
 *
 * @author Kinming
 */
class MessageDao
{
    /**
     * 获取消息类型模板
     *
     * @param $type
     * @param $template
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageTypeTemplate($type, &$template)
    {
        chdir(FileConst::BASE_FILE_PATH);
        //将yaml文件解析成数组
        $total = @yaml_parse_file(FileConst::MESSAGETYPE_TEMPLATE_YAML, -1);
        if (!$total) {
            $total = [];
        }

        $count = count($total);

        for ($i = 0; $i < $count; ++$i) {
            if ($total[$i]['type'] == $type) {
                $template = $total[$i];
                break;
            }
        }

        if ($i == $count) {
            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 写入消息列表文件.
     *
     * @param $data
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeMessageInfoList(&$data)
    {
        $data['version'] = 1;
        $listYaml = yaml_emit($data);

        //追加写入消息列表的yaml文件
        $rt = file_put_contents(FileConst::MESSAGE_INFO_YAML, $listYaml, FILE_APPEND);
        if (false === $rt) {
            Log::error('消息列表写入yaml文件失败', [$listYaml]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 写入消息详情文件.
     *
     * @param $data
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeMessageDetail($data)
    {
        $detailYaml = yaml_emit($data);

        @mkdir(FileConst::MESSAGE);
        chdir(FileConst::MESSAGE);

        //写入消息详情的yaml文件
        $rt = file_put_contents($data['name'].'.yaml', $detailYaml);
        if (false === $rt) {
            Log::error('消息详情写入yaml文件失败', [$detailYaml]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 获取消息类型.
     *
     * @param $total
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageTypeYaml(&$total)
    {
        chdir(FileConst::BASE_FILE_PATH);

        //将yaml文件解析成数组
        $total = @yaml_parse_file(FileConst::MESSAGETYPE_TEMPLATE_YAML, -1);
        if (!$total) {
            $total = [];
        }

        return 0;
    }

    /**
     * 获取消息列表.
     *
     * @param $project_name
     * @param $list
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageList($project_name, &$list)
    {
        @chdir(FileConst::WWW);
        @chdir(FileConst::USERNAME_PATH);

        //项目不存在
        if (!is_dir("$project_name")) {
            return ReturnCode::ERROR;
        }

        @chdir($project_name);
        @chdir(FileConst::CONFIG);

        //将yaml文件解析成数组
        $list = array();
        $list_tmp = @yaml_parse_file(FileConst::MESSAGE_INFO_YAML, -1);
        if (!$list_tmp) {
            $list = [];
        } else {
            //将名称按照字母、数字和中文排序
            Tools::orderbyName($list_tmp, $list, 'name');
        }

        return 0;
    }

    /**
     * 获取消息详情 （通过消息详情文件）.
     *
     * @param $message_name
     * @param $detail
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageDetail($message_name, &$detail, &$info)
    {
        if (!file_exists($message_name.'.yaml')) {
            $info = '该消息不存在';
            Log::error($info, [getcwd(), $message_name]);

            return ReturnCode::ERROR;
        }

        //将yaml文件解析成数组
        $detail = @yaml_parse_file($message_name.'.yaml', -1);
        if (!$detail) {
            $detail = [];
        }

        return 0;
    }

    /**
     * 获取消息详情（通过消息列表文件）.
     *
     * @param $detail
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageDetailByList(&$detail, &$info)
    {
        if (!file_exists(FileConst::MESSAGE_INFO_YAML)) {
            $info = '该消息列表文件不存在';
            Log::error($info, [getcwd()]);

            return ReturnCode::ERROR;
        }

        //将yaml文件解析成数组
        $detail = @yaml_parse_file(FileConst::MESSAGE_INFO_YAML, -1);
        if (!$detail) {
            $detail = [];
        }

        return 0;
    }

    /**
     * 写入消息列表.
     *
     * @param $data
     * @param $message_name
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeMessageList($data, $message_name)
    {
        //将yaml文件解析成数组
        $list = yaml_parse_file(FileConst::MESSAGE_INFO_YAML, -1);
        if (!$list) {
            Log::error('yaml解析文件失败', [$list]);

            return ReturnCode::ERROR;
        }

        $oldMessage = null;
        foreach ($list as $item) {
            if ($item['name'] == $message_name) {
                $oldMessage = $item;
            }
        }

        if (isset($oldMessage)) {
            //写入消息列表文件
            $oldYaml = yaml_emit($oldMessage);
            $newYaml = yaml_emit($data);
            $origin_str = file_get_contents('message_info.yaml');
            $update_str = str_replace($oldYaml, $newYaml, $origin_str);
            $put = file_put_contents('message_info.yaml', $update_str);
            if (false === $put) {
                Log::error('文件写入失败', [$oldMessage, $data]);

                return ReturnCode::ERROR;
            }
        }

        return 0;
    }

    /**
     * 写入消息详情.
     *
     * @param $data
     * @param $message_name
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeMessageDetailYaml($data, $message_name)
    {
        $yaml = yaml_emit($data);

        //写入消息详情的yaml文件
        $rt = file_put_contents($message_name.'.yaml', $yaml);
        if (false === $rt) {
            Log::error('消息详情写入yaml文件失败', [$yaml]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 删除消息.
     *
     * @param $message_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteMessage($message_name, &$info)
    {
        //将yaml文件解析成数组
        $list = @yaml_parse_file(FileConst::MESSAGE_INFO_YAML, -1);
        if (!$list) {
            $list = [];
        }

        $count = count($list);
        for ($i = 0; $i < $count; ++$i) {
            if ($list[$i]['name'] == $message_name) {
                $oldyaml = yaml_emit($list[$i]);
                $origin_str = file_get_contents('message_info.yaml');
                if (false === $origin_str) {
                    Log::error('文件获取失败', [$origin_str]);

                    return ReturnCode::ERROR;
                }

                $update_str = str_replace($oldyaml, '', $origin_str);
                file_put_contents('message_info.yaml', $update_str);

                break;
            }
        }

        if ($i == $count) {
            $info = '该消息不存在';
            Log::error($info, [getcwd(), $message_name]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 解析yaml文件为数组.
     *
     * @param $name
     * @param $detail
     *
     * @return int
     *
     * @author Kinming
     */
    public function parseYamlToArray($name, &$detail)
    {
        if (!file_exists("$name.yaml")) {
            return ReturnCode::ERROR;
        }

        //将yaml文件解析成数组
        $detail = @yaml_parse_file($name.'.yaml', -1);
        if (!$detail) {
            $detail = [];
        }

        return 0;
    }
}
