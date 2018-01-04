<?php

namespace controller;

use common\library\Log;
use common\library\Parameter;
use common\library\Result;
use common\library\ReturnCode;
use logic\MessageLogic;
use Predis\Client;

/**
 * Class MessageController.
 *
 * @author Kinming
 */
class MessageController
{
    /*
     * @doc
     * @name    新增消息
     * @url     POST /addMessage/@project_name
     *
     * @param   string  project_name           项目名称
     * @param   struct  message           消息对象
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *                "code": 0,
     *                "error": "成功",
     *                "message": "Success",
     *                "data": {
     *                    "message_name": "message1",
     *                    "fields": [
     *                        {
     *                            "name": "Name0",
     *                            "type": "number",
     *                            "default": 0,
     *                            "required": "true",
     *                            "check": {
     *                                "format": "int32",
     *                                "min": 0,
     *                                "max": 9999
     *                            }
     *                        }
     *                    ]
     *                }
     *           }
     *
     * @keyword add message
     */
    public static function addMessage($project_name)
    {
        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = $request->data->getData();

        //将新增消息信息写入yaml文件
        $code = (new MessageLogic($project_name))->writeMessageFile($project_name, $data, $info);

        if (0 != $code) {
            Result::Error(ReturnCode::ERROR, $info, $info);
        }

        Result::success($data);
    }

    /*
     * @doc
     * @name    获取某一个消息类型模板
     * @url     GET /getMessageTypeTemplate/@type
     *
     * @param   string  type           消息类型
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response{
     *              "code": 0,
     *              "error": "成功",
     *              "message": "Success",
     *              "data": {
     *                  "name": null,
     *                  "type": "number",
     *                  "required": null,
     *                  "default": null,
     *                  "check": {
     *                      "min": null,
     *                      "max": null
     *                  }
     *              }
     *          }
     *
     * @keyword get message type template
     */
    public static function getMessageTypeTemplate($type)
    {
        $template = array();
        //获取消息模板
        $code = (new MessageLogic())->getMessageTypeTemplate($type, $template, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($template);
    }

    /*
     * @doc
     * @name    获取消息的列表
     * @url     GET /getMessageList/@project_name
     *
     * @param   string  project_name           项目名称
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": [
     *                   {
     *                       "message_name": "message",
     *                       "fields": [
     *                           {
     *                               "name": "string",
     *                               "type": "string",
     *                               "show_type": "string",
     *                               "comment": "属性说明",
     *                               "default": "",
     *                               "required": "true",
     *                               "check": {
     *                                   "minLength": 1,
     *                                   "maxLength": 20,
     *                                   "pattern": "[A-Za-z0-9]"
     *                               },
     *                               "show_detail": false
     *                           }
     *                       ]
     *                   }
     *               ]
     *           }
     *
     * @keyword get message list
     */
    public static function getMessageList($project_name)
    {
        //获取消息列表
        $code = (new MessageLogic())->getMessageList($project_name, $list, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($list);
    }

    /*
     * @doc
     * @name    检查消息名称是否重复
     * @url     GET /checkMessageName/@project_name/@message_name
     *
     * @param   string  project_name           项目名称
     * @param   string  message_name           消息名称
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 400,
     *               "error": "失败",
     *               "message": "消息名已存在",
     *               "data": null
     *           }
     *
     * @keyword check message name
     */
    public static function checkMessageName($project_name, $message_name)
    {
        $code = (new MessageLogic())->checkMessageName($project_name, $message_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('该消息名可用');
    }

    /*
     * @doc
     * @name    获取消息详情
     * @url     GET /getMessageDetail/@project_name/@message_name
     *
     * @param   string  project_name           项目名称
     * @param   string  message_name           消息名称
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *                "code": 0,
     *                "error": "成功",
     *                "message": "Success",
     *                "data": {
     *                    "message_name": "message1",
     *                    "fields": [
     *                        {
     *                            "name": "Name0",
     *                            "type": "number",
     *                            "default": 0,
     *                            "required": "true",
     *                            "check": {
     *                                "format": "int32",
     *                                "min": 0,
     *                                "max": 9999
     *                            }
     *                        }
     *                    ]
     *                }
     *           }
     *
     * @keyword get message detail
     */
    public static function getMessageDetail($project_name, $message_name)
    {
        $code = (new MessageLogic())->getMessageDetail($project_name, $message_name, $detail, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($detail);
    }

    /*
     * @doc
     * @name    修改消息
     * @url     PUT /updateMessage/@project_name/@message_name
     *
     * @param   string  project_name           项目名称
     * @param   string  message_name           消息名称
     * @param   struct  message                消息对象
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "修改消息成功"
     *           }
     *
     * @keyword update message
     */
    public static function updateMessage($project_name, $message_name)
    {
        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = $request->data->getData();

        $code = (new MessageLogic($project_name))->updateMessage($project_name, $message_name, $data, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($data);
    }

    /*
     * @doc
     * @name    删除消息
     * @url     DELETE /deleteMessage/@project_name/@message_name
     *
     * @param   string  project_name           项目名称
     * @param   string  message_name           消息名称
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "删除消息成功"
     *           }
     *
     * @keyword delete message
     */
    public static function deleteMessage($project_name, $message_name)
    {
        $code = (new MessageLogic())->deleteMessage($project_name, $message_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success('删除消息成功');
    }

    /*
     * @doc
     * @name    构建请求和响应消息
     * @url     GET /createRequestAndResponseMessage/@project_name/@service_name/@interface_name
     *
     * @param   string  project_name           项目名称
     * @param   string  service_name           服务名称
     * @param   string  interface_name           接口名称
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @keyword create request and response message
     */
    public static function createRequestAndResponseMessage($project_name, $service_name, $interface_name)
    {
        $code = (new MessageLogic($project_name))->createRequestAndResponseMessage($service_name, $interface_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($info);
    }

    /*
     * @doc
     * @name    构建正确的消息数据
     * @url     GET /createCorrectMessage/@project_name/@message_name/@amount/@id
     *
     * @param   string  project_name           项目名称
     * @param   string  message_name           消息名称
     * @param   int     amount                 构造条数
     * @param   string  id                     随机生成的id
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": [
     *                   {
     *                       "string1": "G13Exn07AFU",
     *                       "number1": 2799
     *                   },
     *                   {
     *                       "string1": "h3d14",
     *                       "number1": 4755
     *                   }
     *               ]
     *           }
     *
     * @keyword create correct message
     */
    public static function createCorrectMessage($project_name, $message_name, $amount, $id)
    {
        $code = (new MessageLogic($project_name))->createCorrectMessage($message_name, $info, $amount, $id);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($info);
    }

    /*
     * @doc
     * @name    构建异常的消息数据
     * @url     GET /createErrorMessage/@project_name/@message_name
     *
     * @param   string  project_name           项目名称
     * @param   string  message_name           消息名称
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": [
     *                   {
     *                       "string1": "",
     *                       "number1": 1996
     *                   },
     *                   {
     *                       "string1": "20CspfQcDC472XIZ0376c",
     *                       "number1": 1996
     *                   },
     *                   {
     *                       "string1": "中文",
     *                       "number1": 1996
     *                   },
     *                   {
     *                       "string1": " ",
     *                       "number1": 1996
     *                   },
     *                   {
     *                       "string1": 123,
     *                       "number1": 1996
     *                   },
     *                   {
     *                  	 "number1": 1996
     *                   },
     *                   {
     *                       "string1": "5P55Ri0Z",
     *                       "number1": "123"
     *                   },
     *                   {
     *                       "string1": "5P55Ri0Z",
     *                    	 "number1": -1
     *                   },
     *                   {
     *                       "string1": "5P55Ri0Z",
     *                       "number1": 10000
     *                   },
     *                   {
     *                       "string1": "5P55Ri0Z",
     *               		 "number1": 0.01
     *                   },
     *                   {
     *                       "string1": "5P55Ri0Z"
     *                   }
     *               ]
     *           }
     *
     * @keyword create error message
     */
    public static function createErrorMessage($project_name, $message_name)
    {
        $code = (new MessageLogic($project_name))->createErrorMessage($message_name, $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::Success($info);
    }

    /*
     * @doc
     * @name    搜索消息
     * @url     POST /searchMessage/@projectName
     *
     * @param   string    projectName   项目名称
     * @param   string    message_name  消息关键字
     *
     * @return  json    null
     * @code    0       返回成功
     * @code    400     返回失败
     *
     * @keyword  search message
     */

    public static function searchMessage($projectName)
    {
        // 获取请求对象
        $request = \Flight::request();
        // 参数规则
        $messageName = array('message_name' => '@str');
        // 参数校验与提取
        $data = Parameter::Load($request->data->GetData(), $messageName);
        // 搜索
        $code = (new MessageLogic())->searchMessage($projectName, $data, $list);
        if (0 !== $code) {
            Result::Error(ReturnCode::ERROR, '搜索失败, 无对应消息', []);
        }

        Result::Success($list);
    }

    /*
     * @doc
     * @name    导出消息数据到文件
     * @url     POST /exportMessageDataToFile
     *
     * @param   struct  message           要导出的消息数据
     *
     * @return  json   null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response file
     *
     * @keyword  export message data to file
     */
    public static function exportMessageDataToFile()
    {
        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = $request->data->getData();

        $code = (new MessageLogic())->exportMessageDataToFile($data);

        if (0 !== $code) {
            Result::Error($code, '导出消息数据失败', '导出消息数据失败');
        }
    }

    /*
     * @doc
     * @name    给前端轮询时调用，获取当前构造数据的进度
     * @url     GET /getCurrentCreateMessageAmount/@id
     *
     * @param   string  id           随机生成的id
     *
     * @return  json   null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "2"
     *           }
     *
     * @keyword get current create message amount
     */
    public static function getCurrentCreateMessageAmount($id)
    {
        //redis认证
        $client = new Client(array(
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '6edc5a13',
        ));

        try {
            $value = $client->get($id);
            Result::Success(intval($value));
        } catch (\Exception $exception) {
            $info = 'redis连接失败';
            Log::error($info, [$exception->getMessage(), $exception->getTraceAsString()]);
            Result::Error(ReturnCode::ERROR, $info, $info);
        }
    }

    /*
     * @doc
     * @name    判断正则表达式是否正确
     * @url     POST /checkRegex
     *
     * @param   string  regex           正则表达式
     *
     * @return  json    null
     * @code    0    返回成功
     * @code    400  返回失败
     *
     * @response {
     *               "code": 0,
     *               "error": "成功",
     *               "message": "Success",
     *               "data": "正则表达式正确"
     *           }
     *
     * @keyword check regex
     */
    public static function checkRegex()
    {
        // 参数规则
        $schema = array(
            'regex' => '@str',
        );

        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = Parameter::Load($request->data->getData(), $schema);

        //判断正则表达式是否正确
        $code = (new MessageLogic())->checkRegex($data['regex'], $info);

        if (0 !== $code) {
            Result::Error($code, $info, $info);
        }

        Result::success('正则表达式正确');
    }
}
