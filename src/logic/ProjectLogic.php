<?php

namespace logic;

use library\ExecCommand;
use library\FileConst;
use common\library\Log;
use common\library\Result;
use common\library\ReturnCode;
use library\Tools;
use dao\InterfaceDao;
use dao\ProjectDao;

/**
 * Class ProjectLogic.
 *
 * @author Kinming
 */
class ProjectLogic
{
    /**
     * 写入文件.
     *
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeFile($data, &$info)
    {
        $url = $data['url'];
        $name = $data['name'];

        chdir(FileConst::RETURN_PATH);
        $date = date('Y-m-d H:i:s', time());

        $array = array(
            'version' => 1,
            'name' => $name,
            'url' => $url,
            'latestTime' => $date,
        );

        $info = $array;

        $code = (new ProjectDao())->startWriteFile($array);
        if (0 !== $code) {
            Log::error("写入项目列表的yaml文件失败:$array");

            return $code;
        }

        return 0;
    }

    /**
     * 更新项目列表文件.
     *
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function updateProjectYaml($data, &$info)
    {
        $date = date('Y-m-d H:i:s', time());
        $data['latestTime'] = $date;

        $name = $data['name'];
        $url = $data['url'];

        (new ProjectDao())->parseYamlToArray($total);

        $i = 0;
        $flag = 0;
        if (isset($total)) {
            $count = count($total);
            for ($i = 0; $i < $count; ++$i) {
                if ($total[$i]['url'] == $url) {
                    //有对应项目记录
                    $flag = 1;
                    break;
                }
            }
        }

        //在yaml匹配到相同id
        if (1 == $flag) {
            $code = (new ProjectDao())->updateProjectYaml($i, $total, $data);
            if (0 !== $code) {
                $info = '更新project_info.yaml文件失败';
                Log::error($info, [$total, $data]);

                return $code;
            }

            //修改项目文件夹名称
            chdir(FileConst::USERNAME);
            $oldName = $total[$i]['name'];
            ExecCommand::execMv($oldName, $name);
        } else {
            $info = '该项目不存在';
            Log::error($info, $data);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 解析文件内容为数组.
     *
     * @param $detail
     * @param $project_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function parseFileToYamlArray(&$detail, $project_name, &$info)
    {
        //将yaml文件解析成数组
        $total = yaml_parse_file(FileConst::PROJECT_INFO_YAML, -1);
        if (!$total) {
            $info = 'yaml解析文件失败';
            Log::error($info.":$total");

            return ReturnCode::ERROR;
        }

        $count = count($total);

        for ($i = 0; $i < $count; ++$i) {
            if ($total[$i]['name'] == $project_name) {
                $detail = $total[$i];
                break;
            }
        }

        if ($i == $count) {
            $info = '该项目不存在';
            Log::error($info.":$project_name");

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 获取项目列表.
     *
     * @param array $info
     *
     * @return int
     *
     * @author Tree
     */
    public function GetProList(&$info = array())
    {
        // 进入project_info.yaml所在路径
        chdir(FileConst::BASE_FILE_PATH);

        // 如果没有项目，返回null
        if (!file_exists(FileConst::PROJECT_INFO_YAML)) {
            Result::Success(null);
            exit;
        }

        // 将yaml文件解析成数组
        $parsed = @yaml_parse_file(FileConst::PROJECT_INFO_YAML, -1);
        if (!$parsed) {
            Log::error('yaml解析文件失败'.": $parsed");

            return ReturnCode::ERROR;
        }

        // 提取项目的latestTime,转为时间戳
        $latestTimeList = array();
        foreach ($parsed as $k => $value) {
            $latestTimeList[] = strtotime($parsed[$k]['latestTime']);
        }

        // 排序
        rsort($latestTimeList);

        // 转化为日期格式
        $sortLatestTimeList = array();
        foreach ($latestTimeList as $value) {
            $sortLatestTimeList[] = date('Y-m-d H:i:s', $value);
        }

        // 获取项目列表
        $projectCount = count($parsed);
        $timeCount = count($sortLatestTimeList);
        for ($i = 0; $i < $timeCount; ++$i) {
            for ($k = 0; $k < $projectCount; ++$k) {
                if ($parsed[$k]['latestTime'] === $sortLatestTimeList[$i]) {
                    $info[] = $parsed[$k];
                }
            }
        }

        return 0;
    }

    /**
     * 删除项目内容.
     *
     * @param $project_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteProject($project_name, &$info)
    {
        @chdir(FileConst::USERNAME_PATH);
        // 如果没有项目，返回null
        if (!is_dir("$project_name")) {
            $info = '该项目不存在';
            Log::error($info.": $project_name");

            return ReturnCode::ERROR;
        }

        //如果是空项目
        @chdir("$project_name");
        $flag = 0;
        $code = Tools::getBranchNames($branchList);
        if (0 == $code) {
            //删除所有分支下的服务、接口、消息数据
            $code = $this->deleteAllBranchData($branchList);
            if (0 !== $code) {
                $flag = 1;
            }
        }

        //删除整个项目
        @chdir(FileConst::RETURN_PATH);
        ExecCommand::execRm($project_name);

        //将该项目从项目列表文件中删除
        $code = (new ProjectDao())->deleteProject($project_name, $info);
        if (0 !== $code) {
            return $code;
        }

        if (1 == $flag) {
            $info = '删除本地项目分支下的所有服务、接口、消息数据成功，git push失败';
            Log::error($info, $branchList);

            return $code;
        }

        return 0;
    }

    /**
     * 删除所有分支下的服务、接口、消息数据.
     *
     * @param $branchList
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteAllBranchData($branchList)
    {
        foreach ($branchList as $branch) {
            ExecCommand::execGitCheckout($branch);
            @mkdir(FileConst::CONFIG);
            @chdir(FileConst::CONFIG);

            //删除该项目下的服务、接口、消息数据
            ExecCommand::execRm(FileConst::MESSAGE_INFO_YAML);
            ExecCommand::execRm(FileConst::SERVICE_INFO_YAML);
            ExecCommand::execRm(FileConst::MESSAGE);
            ExecCommand::execRm(FileConst::SERVICE);

            //提交git
            ExecCommand::execGitAddCommitPush('-A .', "删除项目 $branch 分支下的所有服务、接口、消息数据", '2>&1', $rs, $code);

            $count = count($rs);
            //判断该分支是否被保护
            if ($count > 0 && !empty(strstr($rs[0], 'protected branches'))) {
                //回滚之前操作
                ExecCommand::execGitResetHead();
                @chdir(FileConst::RETURN_PATH);
                continue;
            }

            //如果push失败，则先pull后push
            if (0 !== $code) {
                ExecCommand::execGitPull();
                ExecCommand::execGitAddCommitPush('-A .', "删除项目 $branch 分支下的所有服务、接口、消息数据", '2>&1', $rs2, $code);
                if (0 !== $code) {
                    return $code;
                }
            }

            @chdir(FileConst::RETURN_PATH);
        }

        return 0;
    }

    /**
     * 克隆项目.
     *
     * @param $url
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function cloneProject($url, &$data, &$info)
    {
        //判断url是否重复
        $code = $this->checkProjectUrl($url, $tmp);
        if (0 !== $code) {
            $info = '项目url已存在';
            Log::error($info.":$url");

            return ReturnCode::ERROR;
        }

        chdir(FileConst::USERNAME);

        ExecCommand::execGitClone($url, $rs, $code);

        //获取.git前面的名称
        $arr = explode('/', $url);
        //获取最后一个/后边的字符
        $last = $arr[count($arr) - 1];
        $oldName = strstr($last, '.', true);

        //判断项目路径不存在，如 http://www.baidu.com/happy.git
        //Initialized empty Git repository in /usr/local/easyops/kinming/happy/.git/
        //warning: remote HEAD refers to nonexistent ref, unable to checkout.
        if (count($rs) >= 2 && !empty(strstr($rs[1], 'nonexistent ref'))) {
            ExecCommand::execRm($oldName);
            $info = '项目仓库不存在';
            Log::error($info.":$url", $rs);

            return ReturnCode::ERROR;
        }

        if (0 != $code) {
            if (count($rs) > 0) {
                $info = '项目仓库不存在或没有权限克隆该项目';
                Log::error($info.":$url", $rs);

                return ReturnCode::ERROR;
            } else {
                $info = '项目已存在';
                Log::error($info.":$url", $rs);

                return ReturnCode::ERROR;
            }
        } else {
            //去除可以clone但不能push的情况
            @chdir($oldName);
            ExecCommand::execGitPush('2>&1', $rs2, $code);
            @chdir(FileConst::RETURN_PATH);
            if (count($rs2) >= 1 && (!empty(strstr($rs2[0], 'not allowed')) || !empty(strstr($rs2[0], '403')) || !empty(strstr($rs2[0], '401')))) {
                //没权限push
                ExecCommand::execRm($oldName);
                $info = '没有权限克隆该项目';
                Log::error($info.":$url", $rs2);

                return ReturnCode::ERROR;
            }
        }

        //开始添加正确数据
        $code = $this->addProjectData($oldName, $data, $info);
        if (0 != $code) {
            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 添加项目数据.
     *
     * @param $oldName
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function addProjectData($oldName, $data, &$info)
    {
        //修改项目名称
        $newName = $data['name'];
        ExecCommand::execMv($oldName, $newName);

        //将项目信息写入文件
        $code = self::writeFile($data, $info);
        if (0 !== $code) {
            $info = '项目信息写入文件失败';
            Log::error($info, $data);

            return ReturnCode::ERROR;
        }

        @chdir(FileConst::USERNAME);
        @chdir($newName);
        @mkdir(FileConst::CONFIG);
        @chdir(FileConst::CONFIG);
        ExecCommand::execTouch('.gitkeep');
        ExecCommand::execGitRemoteBranch($bra, $code);

        //提交git
        if (0 == count($bra)) {
            $code = Tools::commitGit('.', "新增项目 $newName", '新增本地项目成功，git push失败', $info, '--set-upstream origin master');
            if (0 != $code) {
                return $code;
            }
        }

        return 0;
    }

    /**
     * 判断项目名称是否重复.
     *
     * @param $project_name
     *
     * @return int
     *
     * @author Kinming
     */
    public function checkProjectName($project_name)
    {
        chdir(FileConst::USERNAME_PATH);

        ExecCommand::execLs($rs, $code);

        if (in_array("$project_name", $rs)) {
            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 判断项目url是否重复.
     *
     * @param $project_url
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function checkProjectUrl($project_url, &$info)
    {
        //判断url是否以.git结尾，是否含有http://或https://或git@
        if (!empty(strstr($project_url, '.git')) && (!empty(strstr($project_url, 'http://'))
                || !empty(strstr($project_url, 'https://')) || !empty(strstr($project_url, 'git@'))) && empty(strstr($project_url, '///'))) {
            $code = (new ProjectDao())->parseYamlToArray($total);
            if (0 !== $code) {
                Log::error('yaml解析文件失败', $total);

                return $code;
            }

            if (isset($total)) {
                $count = count($total);
                for ($i = 0; $i < $count; ++$i) {
                    if ($total[$i]['url'] == $project_url) {
                        //有对应项目记录
                        return ReturnCode::ERROR;
                    }
                }
            }

            $arr = explode('/', $project_url);
            //获取最后一个/后边的字符
            $last = $arr[count($arr) - 1];
            $name = strstr($last, '.', true);
            $info['name'] = $name;
        } else {
            $info = '项目路径错误';
            Log::error("$info: $project_url");

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 获取项目下的服务和接口数量.
     *
     * @param $project_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getServiceAndInterfaceAmount($project_name, &$info)
    {
        chdir(FileConst::USERNAME_PATH);

        // 如果没有项目，返回null
        if (!is_dir("$project_name")) {
            $info = '该项目不存在';
            Log::error($info.": $project_name");

            return ReturnCode::ERROR;
        }

        chdir("$project_name");
        @mkdir(FileConst::CONFIG);
        $bool1 = @chdir(FileConst::CONFIG);
        @mkdir(FileConst::SERVICE);
        $bool2 = @chdir(FileConst::SERVICE);

        $service_amount = 0;
        $interface_amount = 0;
        if ($bool1 && $bool2) {
            //服务的数量
            ExecCommand::execLs($rs, $code);
            $service_amount = count($rs);

            //接口的数量
            for ($i = 0; $i < $service_amount; ++$i) {
                chdir("$rs[$i]");

                ExecCommand::execLs($rs2, $code2);
                $count = count($rs2);

                0 != $count && $interface_amount += $count - 1;

                chdir(FileConst::RETURN_PATH);
                unset($rs2);
            }
        }

        $info['service_amount'] = $service_amount;
        $info['interface_amount'] = $interface_amount;

        return 0;
    }

    /**
     * 搜索项目.
     *
     * @param   $data
     * @param   $list
     *
     * @return int
     *
     * @author  Tree
     */
    public function searchProject($data, &$list)
    {
        // 项目名称的字段和关键字
        $field = 'name';
        $projectName = isset($data['name']) && is_string($data['name']) ? $data['name'] : '';
        // 判断项目关键字是否为空
        if ('' === $projectName) {
            // 项目列表
            $code = (new self())->GetProList($list);
            if (0 !== $code) {
                Log::error('项目列表为空', $list);

                return  ReturnCode::ERROR;
            }

            return 0;
        }
        // 进入project_info.yaml所在路径
        chdir(FileConst::BASE_FILE_PATH);

        // 将yaml文件解析成数组
        $parsed = @yaml_parse_file(FileConst::PROJECT_INFO_YAML, -1);
        if (!$parsed) {
            Log::error('yaml解析文件失败'.": $parsed");

            return ReturnCode::ERROR;
        }

        // 取出项目名称
        $projectNameList = array();
        foreach ($parsed as $k => $value) {
            $projectNameList[] = $parsed[$k]['name'];
        }

        // 匹配
        $code = (new InterfaceDao())->matchAndSearchName($projectNameList, $projectName, $totalMachName);
        if (0 !== $code) {
            Log::error('匹配失败'.":$projectName:$totalMachName", $projectNameList);

            return ReturnCode::ERROR;
        }

        // 搜索
        $code = (new InterfaceDao())->searchName($totalMachName, $list, $parsed, $field);
        if (0 !== $code) {
            Log::error('搜索失败: $totalMachName', $list);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 更新项目时间.
     *
     * @param $project_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function updateProjectTime($project_name, &$info)
    {
        @chdir(FileConst::WWW);

        (new ProjectDao())->parseYamlToArray($total);

        $i = 0;
        $flag = 0;
        $data = array();
        if (isset($total)) {
            $count = count($total);
            for ($i = 0; $i < $count; ++$i) {
                if ($total[$i]['name'] == $project_name) {
                    //有对应项目记录
                    $latestTime = date('Y-m-d H:i:s', time());
                    $data = $total[$i];
                    $data['latestTime'] = $latestTime;
                    $flag = 1;
                    break;
                }
            }
        }

        //在yaml匹配到相同项目
        if (1 == $flag) {
            $code = (new ProjectDao())->updateProjectYaml($i, $total, $data);
            if (0 !== $code) {
                $info = '更新project_info.yaml文件失败';
                Log::error($info, [$total, $data]);

                return $code;
            }
        } else {
            $info = '该项目不存在';
            Log::error($info.": $project_name");

            return ReturnCode::ERROR;
        }

        @chdir(FileConst::WWW);

        return 0;
    }
}
