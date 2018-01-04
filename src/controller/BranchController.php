<?php

namespace controller;

use logic\BranchLogic;
use common\library\Result;
use library\FileConst;
use common\library\ReturnCode;

class BranchController
{
    /*
     * @name    拉取远端分支
     * @url     GET /getOriginBranch/@projectName
     *
     * @param   string   projectName    项目名称
     * @doc
     * @keyword get originBranch
     *
     * @return json     成功返回0，失败返回错误码
     */
    public static function getOriginBranch($projectName)
    {
        chdir(FileConst::USERNAME_PATH);
        // 判断项目是否存在
        if (!is_dir($projectName)) {
            Result::Error('项目不存在');
            exit;
        }

        chdir($projectName);

        $code = (new BranchLogic())->getOriginBranch();
        if (0 !== $code) {
            Result::Error('拉取远端分支失败');
            exit;
        }

        Result::Success('拉取远端分支成功');
    }

    /*
     * @name    分支列表
     * @url     GET /branchList/@projectName
     *
     * @param   string   projectName    项目名称
     * @doc
     * @keyword get branchList
     *
     * @author Tree
     *
     * @response{
     *      "code": 0,
     *      "error": "成功",
     *      "message": "Success",
     *      "data":[
     *          "*master"
     *          "one"
     *          "three"
     *      ]
     * }
     * @return json     成功返回0，失败返回错误码
     */

    public static function getBranchList($projectName)
    {
        chdir(FileConst::USERNAME_PATH);

        // 获取项目分支名列表
        $code = (new BranchLogic())->getBranchList($projectName, $info);
        if (0 !== $code) {
            Result::Error(ReturnCode::ERROR, '获取分支列表失败');
            exit;
        }

        Result::Success($info);
    }

    /*
     * @name    切换分支
     * @url     POST /checkoutBranch/@projectName
     *
     * @param   string      projectName     项目名称
     * @param   string      branchName      分支名称
     *
     * @doc
     * @keyword checkout branch
     *
     * @author  Tree
     *
     * @request{
     *      "branchName": "feature/F042"
     * }
     *
     * @response{
     *      "code": 200,
     *      "error": "成功"，
     *      "message": "Success",
     *      "data": "切换到feature/F042分支"
     * }
     * @return json     成功返回0，失败返回错误码
     */

    public static function checkoutBranch($projectName)
    {
        // 获取请求对象
        $request = \Flight::request();
        $data = $request->data->getData();
        $branchName = $data['branchName'];

        // 进入项目
        chdir(FileConst::USERNAME_PATH);
        if (!is_dir($projectName)) {
            Result::Error('项目不存在');
            exit;
        }

        chdir($projectName);

        exec("git checkout $branchName", $rs, $code);
        if (0 !== $code) {
            Result::Error(ReturnCode::ERROR, '切换分支失败');
            exit;
        }

        // 成功返回
        Result::Success($branchName);
    }
}
