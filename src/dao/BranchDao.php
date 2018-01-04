<?php

namespace dao;

use common\library\Log;
use common\library\Result;
use common\library\ReturnCode;

class BranchDao
{
    /**
     * 获取分支名.
     *
     * @param $projectName
     * @param array $info
     *
     * @return int
     *
     * @author Tree
     */
    public function getBranchNames($projectName, &$info = array())
    {
//        chdir($projectName);
//        exec('git branch', $bra, $code);
//        if($code !== 0){
//            Log::error("获取分支列表失败");
//            return ReturnCode::ERROR;
//        }
//
//        foreach($bra as $braName){
//            # 去掉空字符
//            $braName = str_replace(" ", "", $braName);
//            $info[] = $braName;
//        }
//
//        return 0;
        chdir($projectName);
        exec('git branch -r', $bra, $code);

        // 判断远端仓库是否有分支
        if (0 === count($bra)) {
            Result::Success('无分支');
            exit;
        }

        // 获取项目分支名
        $defaultBranch = '';
        foreach ($bra as $braName) {
            // 去掉远端显示的默认分支
            if ('  origin/HEAD -> origin/' === substr($braName, 0, 24)) {
                // 获取远端默认分支名称
                $defaultBranch = str_replace('  origin/HEAD -> origin/', '', $braName);
                $info['defaultBranch'] = $defaultBranch;
                continue;
            }
            $braName = str_replace('  origin/', '', $braName);
            $info['branchNameList'][] = $braName;
        }

        // 如果远端没有默认分支,指定下次切换的默认分支
        if ('' === $defaultBranch) {
            $info['defaultBranch'] = $info['branchNameList'][0];
            $defaultBranch = $info['branchNameList'][0];
        }

        // 切换到远端默认分支
        exec("git checkout $defaultBranch");

        return 0;
    }

    public function getOriginBranch($originBra, &$info, &$defaultBranch)
    {
        $info = array();
        $defaultBranch = '';
        // 获取项目分支名
        foreach ($originBra as $braName) {
            // 去掉远端显示的默认分支
            if ('  origin/HEAD -> origin/' === substr($braName, 0, 24)) {
                $defaultBranch = str_replace('  origin/HEAD -> origin/', '', $braName);
                continue;
            }
            $braName = str_replace('  origin/', '', $braName);
            $info[] = $braName;
        }

        if ('' === $defaultBranch) {
            $defaultBranch = $info[0];
        }

        return 0;
    }

    public function setBranch($result)
    {
        // 创建本地分支
        $count = count($result);
        for ($i = 0; $i < $count; ++$i) {
            exec("git checkout $result[$i]", $rs, $code);
            if (0 !== $code) {
                Result::Error(ReturnCode::ERROR, '切换分支失败');
                exit;
            }
        }

        return 0;
    }
}
