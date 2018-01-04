<?php
/**
 * Created by IntelliJ IDEA.
 * User: Kinming
 * Date: 2017/11/22
 * Time: 上午9:56.
 */

namespace library;

use common\library\Log;
use common\library\ReturnCode;

class Tools
{
    /**
     * 提交到git上.
     *
     * @param $file
     * @param $comment
     * @param $error
     * @param $push
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public static function commitGit($file, $comment, $error, &$info, $push = '2>&1')
    {
        ExecCommand::execGitAddCommitPush($file, $comment, $push, $rs, $code);

        $count = count($rs);
        //判断该分支是否被保护
        if ($count > 0 && !empty(strstr($rs[0], 'protected branches'))) {
            //回滚之前操作
            ExecCommand::execGitResetHead();

            $info = '该分支被保护，无法执行该操作';
            Log::error($info, [$rs]);

            return ReturnCode::ERROR;
        }

        //如果push失败，则先pull后push
        if (0 !== $code) {
            ExecCommand::execGitForcePull();
            ExecCommand::execGitAddCommitPush($file, $comment, $push, $rs2, $code);
            if (0 !== $code) {
                //回滚之前操作
                ExecCommand::execGitResetHead();

                $info = $error;
                Log::error($info, [$rs2]);

                return ReturnCode::ERROR;
            }
        }

        return 0;
    }

    /**
     * 获取分支名.
     *
     * @param array $info
     *
     * @return int
     *
     * @author Kinming
     */
    public static function getBranchNames(&$info)
    {
        exec('git branch -r', $bra, $code);

        if (0 == count($bra)) {
            return ReturnCode::ERROR;
        }

        //获取项目分支名
        foreach ($bra as $braName) {
            //去掉远端显示的默认分支
            if ('  origin/HEAD -> origin/' === substr($braName, 0, 24)) {
                continue;
            }
            $braName = str_replace('  origin/', '', $braName);
            $info[] = $braName;
        }

        return 0;
    }

    /**
     * 检查项目名和消息名是否存在.
     *
     * @param $project_name
     * @param $message_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public static function checkProjectAndMessage($project_name, $message_name, &$info)
    {
        @chdir(FileConst::USERNAME_PATH);
        $project_name_bool = @chdir($project_name);
        if (empty($project_name_bool)) {
            $info = '该项目不存在';
            Log::error($info, [getcwd(), $project_name]);

            return ReturnCode::ERROR;
        }
        @mkdir(FileConst::CONFIG);
        @chdir(FileConst::CONFIG);
        @mkdir(FileConst::MESSAGE);
        @chdir(FileConst::MESSAGE);
        exec('ls', $rs, $code);

        if (!in_array($message_name.'.yaml', $rs)) {
            $info = '该消息不存在';
            Log::error($info, [getcwd(), $message_name, $rs]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 将数组按字典序排序.
     *
     * @return mixed
     *
     * @author Kinming
     */
    public static function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    /**
     * 将名称按照字母、数字和中文排序.
     *
     * @param $list_tmp
     * @param $list
     * @param $name
     *
     * @author Kinming
     */
    public static function orderbyName($list_tmp, &$list, $name)
    {
        //按字符串和中文排序，不区分大小写
        $count = count($list_tmp);
        for ($i = 0; $i < $count; ++$i) {
            $list_tmp[$i][$name] = iconv('UTF-8', 'GBK//IGNORE', $list_tmp[$i][$name]);
        }

        //标志10代表不区分大小写
        $list_tmp = self::array_orderby($list_tmp, $name, 10);
        for ($i = 0; $i < $count; ++$i) {
            $list_tmp[$i][$name] = iconv('GBK', 'UTF-8//IGNORE', $list_tmp[$i][$name]);
        }

        $numberList = array();
        //将数字排序转为字符串排序
        for ($i = 0; $i < $count; ++$i) {
            while (is_numeric($list_tmp[$i][$name])) {
                $numberList[$i] = $list_tmp[$i];
                ++$i;
            }
            array_push($list, $list_tmp[$i]);
        }

        //SORT_STRING按照字符串排序
        $numberList = self::array_orderby($numberList, $name, SORT_STRING);

        $list = array_merge($list, $numberList);
    }
}
