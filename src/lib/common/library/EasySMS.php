<?php
/**
 * Created by IntelliJ IDEA.
 * User: lights
 * Date: 2016/12/1
 * Time: 下午4:36
 */

namespace common\library;

/**
 * Interface EasySMS
 * @package common\library
 * @memo 短信发送接口，提供基本的短信发送方式，为客户提供个性化短信平台对接的二次开发的可能
 */
interface EasySMS
{
    /**
     * @memo 传递配置数据
     * @param $configInfo
     */
    function setConfigInfo($configInfo);

    /**
     * 设置电话号码
     * @param string $tel
     */
    function setTel($tel);

    /**
     * 设置短信类型，往往会与短信模板有关
     * @param string $type
     */
    function setType($type);

    /**
     * 设置短信参数
     * @param array $params [
     *                          "event" => "",
     *                          "metric" => "",
     *                          "target" => "",
     *                          "value" => "",
     *                          "threshold" => "",
     *                      ]
     */
    function setParams($params);

    /**
     * @param $retMessage
     * @return int 0 成功，-1 失败
     */
    function sendSMS(&$retMessage);
}