<?php

namespace controller;

use common\library\Parameter;
use common\library\Result;
use common\library\ReturnCode;
use logic\ProjectLogic;

/**
 * Class ProjectController.
 *
 * @author Kinming
 */
class ProjectController
{
    /*
     * @doc
     * @name    新增项目
     * @url     POST /addProject
     *
     * @param   string  url       项目url
     * @param   string  name           项目名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response    {
     *                  "code": 200,
     *                  "error": "成功",
     *                  "message": "Success",
     *                  "data": "新增项目成功"
     *              }
     *
     * @keyword add project
     */
    public static function addProject()
    {
        // 参数规则
        $schema = array(
            'name' => '@str/^[\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{20000}-\x{2ebef}a-zA-Z_][\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{20000}-\x{2ebef}\w]{3,39}$/u',
            'url' => '@str/^[\w\-\.,@?^=%&:\/~\+#]+$/',
        );

        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = Parameter::Load($request->data->getData(), $schema);
        $url = $data['url'];

        //判断url是否以.git结尾，是否含有http://或https://或git@
        if (!empty(strstr($url, '.git')) && (!empty(strstr($url, 'http://'))
                || !empty(strstr($url, 'https://')) || !empty(strstr($url, 'git@'))) && empty(strstr($url, '///'))) {
            //使用url从git上克隆项目
            $code = (new ProjectLogic())->cloneProject($url, $data, $info);
            if (0 != $code) {
                Result::error(ReturnCode::ERROR, $info, $info);
            }

            Result::success($info);
        } else {
            $info = '项目路径错误';
            Result::error(ReturnCode::ERROR, $info, $info);
        }
    }

    /*
     * @doc
     * @name    修改项目
     * @url     PUT /updateProject
     *
     * @param   string  url       项目url
     * @param   string  name           项目名称
     * @param   string  latestTime           最新更新时间
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response    {
     *                  "code": 200,
     *                  "error": "成功",
     *                  "message": "Success",
     *                  "data": "修改项目成功"
     *              }
     *
     * @keyword update project
     */
    public static function updateProject()
    {
        // 参数规则
        $schema = array(
            'name' => '@str/^[\x{4e00}-\x{9fa5}\w]+$/u',
            'url' => '@str/^[\w\-\.,@?^=%&:\/~\+#]+$/',
            'latestTime' => '@str',
        );

        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = Parameter::Load($request->data->getData(), $schema);

        $info = null;
        $code = (new ProjectLogic())->updateProjectYaml($data, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('修改项目成功');
    }

    /*
     * @name     显示项目列表
     * @url      GET /ProjectList
     *
     * @doc
     * @keyword  get Project List
     *
     * @author   Tree
     *
     * @response{
     *      "code": "200",
     *      "error": "成功"，
     *      "message": "Success",
     *      "data":[
     *          {
     *              "name": "git-php",
     *              "url": "https://github.com/czproject/git-php.git"
     *              "latestTime": "2017-11-08 20:08:30",
     *          },
     *          {
     *              "name": "MyProject",
     *              "url": "https://github.com/zjm138238/MyProject.git"
     *              "latestTime": "2017-11-08 14:37:01",
     *          }
     *      ]
     *  }
     * @return json     成功返回0，失败返回错误码
     */

    public static function GetProList()
    {
        // 项目列表
        $code = (new ProjectLogic())->GetProList($info);
        if (0 !== $code) {
            Result::Error($code, '没有项目');
        }

        // 成功返回
        Result::Success($info);
    }

    /*
     * @doc
     * @name    获取项目详情
     * @url     GET /branchList/@project_name
     *
     * @param   string  project_name           项目名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": {
     *                   "defaultBranch": "master",
     *                   "branchNameList": [
     *                       "master",
     *                       "test1"
     *                   ]
     *               }
     *           }
     *
     * @keyword get project detail
     */

    public static function getProjectDetail($project_name)
    {
        chdir('../../config');

        $detail = array();
        //将yaml文件解析成数组
        $code = (new ProjectLogic())->parseFileToYamlArray($detail, $project_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($detail);
    }

    /*
     * @doc
     * @name    删除项目
     * @url     DELETE /deleteProject/@project_name
     *
     * @param   string  project_name           项目名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "删除项目成功"
     *           }
     *
     * @keyword delete project
     */
    public static function deleteProject($project_name)
    {
        $info = '';
        $code = (new ProjectLogic())->deleteProject($project_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('删除项目成功');
    }

    /*
     * @doc
     * @name    判断项目名称是否重复
     * @url     GET /checkProjectName/@project_name
     *
     * @param   string  project_name           项目名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "项目名称可用"
     *           }
     *
     * @keyword check project name
     */
    public static function checkProjectName($project_name)
    {
        $code = (new ProjectLogic())->checkProjectName($project_name);

        if (0 !== $code) {
            Result::Error($code, '项目名称已存在', '项目名称已存在');
        }

        Result::Success('项目名称可用');
    }

    /*
     * @doc
     * @name    判断项目url是否重复
     * @url     POST /checkProjectUrl
     *
     * @param   struct  project           项目对象
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response {
     *              "code": 0,
     *              "error": "成功",
     *              "message": "Success",
     *              "data": {
     *                  "name": "test123"
     *              }
     *           }
     *
     * @keyword check project url
     */
    public static function checkProjectUrl()
    {
        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = $request->data->getData();

        $code = (new ProjectLogic())->checkProjectUrl($data['url'], $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($info);
    }

    /*
     * @doc
     * @name    获取项目下的服务和接口数量
     * @url     GET /getServiceAndInterfaceAmount/@project_name
     *
     * @param   string  project_name          项目名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @keyword get service and interface amount
     */
    public static function getServiceAndInterfaceAmount($project_name)
    {
        $code = (new ProjectLogic())->getServiceAndInterfaceAmount($project_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($info);
    }

    /*
     * @doc
     * @name    搜索项目
     * @url     POST /searchProject
     *
     * @param   string  $name    项目名称关键字
     *
     * @return  json    null
     * @code    0       成功返回
     * @code    400     返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": [
     *                   {
     *                       "name": "auto_test_project",
     *                       "url": "http://kinming:kinming123@git.easyops.local/kinming/test.git",
     *                       "latestTime": "2017-12-07 10:22:25"
     *                   }
     *               ]
     *           }
     *
     * @keyword search Project
     */
    public static function searchProject()
    {
        // 获取请求对象
        $request = \Flight::request();
        // 参数规则
        $projectName = array('name' => '@str');
        // 参数校验与提取
        $data = Parameter::Load($request->data->GetData(), $projectName);
        // 搜索项目
        $code = (new ProjectLogic())->searchProject($data, $list);
        if (0 !== $code) {
            Result::Error($code, '搜索项目失败, 无对应项目', []);
        }

        Result::Success($list);
    }
}
