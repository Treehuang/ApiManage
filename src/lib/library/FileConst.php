<?php
/**
 * Created by IntelliJ IDEA.
 * User: Kinming
 * Date: 2017/11/16
 * Time: 下午4:50.
 */

namespace library;

/**
 * 用于定义文件路径常量
 * Class FileConst.
 *
 * @author Kinming
 */
class FileConst
{
    //文件路径
    const BASE_FILE_PATH = '../../config';
    const USERNAME_PATH = self::BASE_FILE_PATH.'/username';
    const USERNAME = 'username';
    const SERVICE = 'service';
    const CONFIG = 'config';
    const RETURN_PATH = '..';
    const SLASH = '/';
    const MESSAGE = 'message';
    const WWW = '/usr/local/easyops/api_manage/src/www';
    //const WWW = "/usr/local/easyops/kinming/apimanage/src/www";

    //文件名
    const PROJECT_INFO_YAML = 'project_info.yaml';
    const SERVICE_INFO_YAML = 'service_info.yaml';
    const MESSAGE_INFO_YAML = 'message_info.yaml';
    const INTERFACE_INFO_YAML = 'interface_info.yaml';
    const MESSAGETYPE_TEMPLATE_YAML = 'messageType_template.yaml';
}
