<?php

namespace controller;

use common\library\Parameter;
use common\library\Result;
use common\library\ReturnCode;
use logic\ServiceLogic;

/**
 * Class ServiceController.
 *
 * @author Kinming
 */
class ServiceController
{
    /*
     * @doc
     * @name    新增服务
     * @url     POST /addService/@project_name
     *
     * @param   string  project_name           项目名称
     * @param   string  name           服务名称
     * @param   string  protocol*           服务协议
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
     *                   "name": "auto_test_service",
     *                   "protocol": "http",
     *                   "interfaceNumber": 0
     *               }
     *           }
     *
     * @keyword add service
     */
    public static function addService($project_name)
    {
        // 参数规则
        $schema = array(
            'name' => '@str/^[\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{20000}-\x{2ebef}a-zA-Z_][\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{20000}-\x{2ebef}\w]{3,39}$/u',
            'protocol' => '*str/^\w+$/',
        );

        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = Parameter::Load($request->data->getData(), $schema);

        //将新建服务信息写入yaml文件
        $code = (new ServiceLogic())->addService($project_name, $data, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::success($info);
    }

    /*
     * @name    服务列表
     * @url     GET /serviceList/@projectName
     *
     * @param   string    projectName   项目名称
     *
     * @doc
     * @keyword get serviceList
     *
     * @author  Tree
     *
     * response{
     *      "code": 200,
     *      "error": "成功"，
     *      "message": "Success",
     *      "data":[
     *          {
     *              "name": "CMDB",
     *              "protocol": "http",
     *              "interfaceNumber": 2
     *          },
     *          {
     *              "name": "Tools-P",
     *              "protocol": "Https",
     *              "interfaceNumber": 1
     *          }
     *      ]
     * }
     *
     * @return json     成功返回0，失败返回错误码
     */
    public static function getServiceList($projectName)
    {
        $code = (new ServiceLogic())->getServiceList($projectName, $list);

        if (0 !== $code) {
            Result::Error($code, '获取服务列表失败');
        }

        Result::Success($list);
    }

    /*
     * @doc
     * @name    修改服务
     * @url     PUT /updateService/@projectName/@serviceName
     *
     * @param   string  projectName       项目名称
     * @param   string  serviceName       服务名称
     * @param   struct  updateService     服务对象
     *
     * @return  json    null
     * @code    0       返回成功
     * @code    400     返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "修改服务成功"
     *           }
     *
     * @keyword update service
     */
    public static function updateService($projectName, $serviceName)
    {
        $request = \Flight::request();

        $data = $request->data->getData();

        $code = (new ServiceLogic())->updateService($projectName, $serviceName, $data, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('修改服务成功');
    }

    /*
     * @doc
     * @name    检查服务名称是否重复
     * @url     GET /checkServiceName/@project_name/@service_name
     *
     * @param   string  project_name           项目名称
     * @param   string  service_name           服务名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "该服务名不存在，可以使用"
     *           }
     *
     * @keyword check service name
     */
    public static function checkServiceName($project_name, $service_name)
    {
        $code = (new ServiceLogic())->checkServiceName($project_name, $service_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('该服务名不存在，可以使用');
    }

    /*
     * @doc
     * @name    删除服务
     * @url     DELETE /deleteService/@project_name/@service_name
     *
     * @param   string  project_name           项目名称
     * @param   string  service_name           服务名称
     *
     * @return  json    null
     * @code    200  返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "删除服务成功"
     *           }
     *
     * @keyword delete service
     */
    public static function deleteService($project_name, $service_name)
    {
        $code = (new ServiceLogic())->deleteService($project_name, $service_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('删除服务成功');
    }

    /*
     * @doc
     * @name    搜索服务
     * @url     POST /searchService/@projectName
     *
     * @param   string      projectName     项目名称
     * @param   string      name            服务名称关键字
     *
     * @return  json        null
     * @code    0           返回成功
     * @code    400         返回失败
     *
     * @keyword search service
     */
    public static function searchService($projectName)
    {
        // 获取请求对象
        $request = \Flight::request();
        // 参数规则
        $serviceName = array('name' => '@str');
        // 参数校验与提取
        $data = Parameter::Load($request->data->GetData(), $serviceName);
        // 搜索服务
        $code = (new ServiceLogic())->searchService($projectName, $data, $list, $info);
        if (0 !== $code) {
            Result::Error(ReturnCode::ERROR, '搜索失败,无对应服务', []);
        }

        Result::Success($list);
    }
}
