<?php

namespace logic;

use common\library\Log;
use common\library\Result;
use common\library\ReturnCode;
use dao\BranchDao;

class BranchLogic
{
    /**
     * 获取分支列表.
     *
     * @param $projectName
     * @param $info
     *
     * @return int
     *
     * @author Tree
     */
    public function getBranchList($projectName, &$info)
    {
        // 判断项目是否存在
        if (!is_dir($projectName)) {
            Log::error('项目不存在');

            return ReturnCode::ERROR;
        }

        // 获取项目分支名
        $code = (new BranchDao())->getBranchNames($projectName, $info);
        if (0 !== $code) {
            Log::error('获取项目分支名列表失败');

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 拉取远端分支.
     */
    public function getOriginBranch()
    {
        exec('git branch', $localBra, $code);
        exec('git branch -r', $originBra, $code);

        // 判断远端仓库是否有分支
        if (0 === count($originBra)) {
            Result::Success('无分支');
            exit;
        }

        // 获取本地分支列表，去掉*
        $localInfo = array();
        if (0 !== count($localBra)) {
            $newBraName = '';
            foreach ($localBra as $braName) {
                // 去掉空字符
                if ('*' === substr($braName, 0, 1)) {
                    $newBraName = str_replace('* ', '', $braName);
                    continue;
                }
                $braName = str_replace(' ', '', $braName);
                $localInfo[] = $braName;
            }

            array_push($localInfo, $newBraName);
        }
        // 获取远端分支列表
        $code = (new BranchDao())->getOriginBranch($originBra, $info, $defaultBranch);
        if (0 !== $code) {
            Log::error('获取远端分支列表失败');

            return ReturnCode::ERROR;
        }
        // 判断本地分支列表和远端分支列表的差异
        $result = array_diff($info, $localInfo);
        $result = array_values($result);
        // 将差异部分拉下来
        if (0 !== count($result)) {
            $code = (new BranchDao())->setBranch($result);
            if (0 !== $code) {
                Log::error('创建分支失败');

                return ReturnCode::ERROR;
            }
        }

        exec("git checkout $defaultBranch");

        return 0;
    }
}
