<?php

namespace controller;

use common\library\ReturnCode;
use logic\InterfaceLogic;
use common\library\Result;
use common\library\Parameter;

class InterfaceController
{
    /*
    * @name        新增接口
    * @url         POST /addInterface/@projectName/@serviceName
    *
    * @param       string      projectName        项目名称
    * @param       string      serviceName        服务名称
    * @param       string      interfaceName      接口名称
    * @param       array       endpoint           访问点
    * @param       string      timeout*           超时
    * @param       array       request            请求报文
    * @param       array       response           返回报文
    * @param       array       errors*            错误定义
    *
    * @doc
    * @keyword     add interface
    *
    * @author      Tree
    *
    * @request{
    *	    "InterfaceName":"One",
    *		"method":"post",
    *       "endpoint":{
    *      		"url":"/cluster",
    *	        "timeout":"3",
    *       },
    *	    "request":{
    *           "message":"cluster",
    *		    "stream":"false"
    *	    },
    *
    *	    "response":{
    *           "message":"gui",
    *		    "stream":"false"
    *	    },
    *
    *	    "errors":[
    *           {
    *		        "code":"309",
    *		        "error":"Create failed",
    *	        },
    *           {
    *               "code":"310",
    *               "error":"Update failed",
    *           }
    *       ]
    * }
    *
    * @response{
    *      "code": 200,
    *      "error": "成功",
    *      "message": "Success",
    *      "data": "新增接口成功"
    * }
    *
    * @return json     成功返回0，失败返回错误码
    */
    public static function addInterface($projectName, $serviceName)
    {
        // 参数规则
        $param = array(
            'interfaceName' => '@str/^[\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{20000}-\x{2ebef}a-zA-Z_][\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{20000}-\x{2ebef}\w]{3,39}$/u',
            'endpoint' => '@arr',
            'timeout' => '*str/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/',
            'request' => '*arr',
            'response' => '@arr',
            'errors' => '*arr',
        );

        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = Parameter::Load($request->data->getData(), $param);

        // 将新建接口信息写入yaml文件
        $code = (new InterfaceLogic())->writeInterfaceFile($projectName, $serviceName, $data, $saveDetail);

        if (0 !== $code) {
            Result::Error($code, $saveDetail);
        }

        Result::success($saveDetail);
    }

    /*
     * @name    获取接口列表
     * @url     GET /interfaceList/@projectName/@serviceName
     *
     * @param   string    projectName    项目名称
     * @param   string    serviceName    服务名称
     *
     * @doc
     * @keyword get interfaceList
     *
     * @author  Tree
     *
     * @response{
     *      "code": 0,
     *      "error": "成功"，
     *      "message": "Success"，
     *      "data": [
     *          {
     *              "interfaceName": "One",
     *              "method": "post",
     *              "url": "you.com",
     *              "timeout": "2",
     *              "requestMessageName": "OK",
     *              "responseMessageName": "gui"
     *          },
     *          {
     *              "interfaceName": "Two",
     *              "method": "get",
     *              "url": "my.com"
     *              "timeout": "10",
     *              "requestMessageName": "No",
     *              "responseMessageName": "gui"
     *          }
     *      ]
     * }
     * @return json     成功返回0，失败返回错误码
     */

    public static function getInterfaceList($projectName, $serviceName)
    {
        // 获取接口列表
        $code = (new InterfaceLogic())->getInterfaceList($projectName, $serviceName, $info);

        // 判断获取结果
        if (0 !== $code) {
            Result::Error(ReturnCode::ERROR, '接口列表为空');
        }

        // 成功返回
        Result::success($info);
    }

    /*
     * @doc
     * @name    修改接口
     * @url     PUT /updateInterface/@projectName/@serviceName/@interfaceName
     *
     * @param   string  projectName           项目名称
     * @param   string  serviceName           服务名称
     * @param   string  interfaceName         接口名称
     * @param   struct  updateInterface       接口对像
     *
     * @return  json    null
     * @code    0       返回成功
     * @code    400     返回失败
     *
     * @keyword update interface
     */
    public static function updateInterface($projectName, $serviceName, $interfaceName)
    {
        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = $request->data->getData();

        $code = (new InterfaceLogic())->updateInterfaceYaml($projectName, $serviceName, $interfaceName, $data, $info);

        if (0 !== $code) {
            Result::Error($code, $info);
        }

        Result::Success('修改接口成功');
    }

    /*
     * @doc
     * @name    获取接口详情
     * @url     GET /interfaceDetail/@projectName/@serviceName/@interfaceName
     *
     * @param   string  projectName           项目名称
     * @param   string  serviceName           服务名称
     * @param   string  interfaceName         接口名称
     *
     * @return  json    null
     * @code    0  返回成功
     * @code    400  返回失败
     *
     * @keyword get interface detail
     */

    public static function getInterfaceDetail($projectName, $serviceName, $interfaceName)
    {
        $code = (new InterfaceLogic())->getInterfaceDetail($projectName, $serviceName, $interfaceName, $detail);

        if (0 !== $code) {
            Result::Error($code, '获取接口详情失败');
        }

        Result::Success($detail);
    }

    /*
    * @doc
    * @name    删除接口
    * @url     DELETE /deleteInterface/@projectName/@serviceName/@interfaceName
    *
    * @param   string  projectName           项目名称
    * @param   string  serviceName           服务名称
    * @param   string  interfaceName         接口名称
    *
    * @return  json    null
    * @code    0       返回成功
    * @code    400     返回失败
    *
    * @keyword delete interface
    */

    public static function deleteInterface($projectName, $serviceName, $interfaceName)
    {
        $code = (new InterfaceLogic())->deleteInterface($projectName, $serviceName, $interfaceName, $info);

        if (0 !== $code) {
            Result::Error($code, $info);
        }

        Result::Success('删除接口成功');
    }

    /*
     * @doc
     * @name    检查接口名称是否重复
     * @url     GET /checkInterfaceName/@projectName/@serviceName/@interfaceName
     *
     * @param   string  projectName           项目名称
     * @param   string  serviceName           服务名称
     * @param   string  interfaceName         接口名称
     *
     * @return  json    null
     * @code    0       返回成功
     * @code    400     返回失败
     *
     * @keyword check interface name
     */
    public static function checkInterfaceName($projectName, $serviceName, $interfaceName)
    {
        $code = (new InterfaceLogic())->checkInterfaceName($projectName, $serviceName, $interfaceName);
        if (0 != $code) {
            Result::Error($code, '接口名已存在');
        }

        Result::Success(null);
    }

    /*
     * @doc
     * @name   搜索接口
     * @url    POST /searchInterface/@projectName/@serviceName
     *
     * @param  string  projectName     项目名称
     * @param  string  serviceName     服务名称
     * @param  string  interfaceName   接口名称关键字
     *
     * @return json    null
     * @code   0       返回成功
     * @code   400     返回失败
     *
     * @keyword search interfaceName
     */
    public static function searchInterface($projectName, $serviceName)
    {
        // 获取请求对象
        $request = \Flight::request();
        // 参数规则
        $param = array('interfaceName' => '*str');
        // 参数校验与提取
        $data = Parameter::Load($request->data->GetData(), $param);
        // 搜索接口
        $code = (new InterfaceLogic())->searchInterface($projectName, $serviceName, $data, $list);
        if (0 !== $code) {
            Result::Error($code, '搜索失败,无对应接口', []);
        }
        // 成功返回
        Result::Success($list);
    }
}
