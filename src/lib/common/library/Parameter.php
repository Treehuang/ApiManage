<?php
/*
 * 参数提取类
 */

namespace common\library;

use models\ObjectModel;
use business\ObjectBO;

class Parameter {
    
    public static function Load($input, $validation) {
        $data = array();
        $error = array();
        
        $param_not_found = false;
        $param_not_match = false;

        // 兼容page_size 和pageSize
        if (!isset($input["page_size"]) && isset($input["pageSize"])) {
            @$input["page_size"] = $input["pageSize"];
        }
        if (!isset($input["pageSize"])  && isset($input["page_size"])) {
            @$input["pageSize"]  = $input["page_size"];
        }

        foreach ($validation as $name => $pattern) {
            /*
             * 数据定义模式：{need}{type}/{regex}/
             * need: @表示必选、其它字符表示可选
             * type：int|str|flt
             * regex: 正则表达式
             */
            $need = substr($pattern, 0, 1)=='@'?true:false;
            $type = substr($pattern, 1, 3);
            $regex = substr($pattern, 4);
            
            $match = strlen($regex)==0 ? false : true;
            
            $val = isset($input[$name]) ? $input[$name] : null;
            
            
            //必选数据不存在
            if ( $need && $val === null ) {
                $error[$name] = 'data not found';
                $param_not_found = true;
                continue;
            }
            
            //可选数据不存在，直接跳过
            if ( !$need && $val === null ) {
                continue;
            }
            //一维数组类型
            if ($type == 'arr')
            {
                if(!is_array($val))
                {
                    $error[$name] = 'pattern not match';
                    $param_not_match = true;
                    continue;
                }
                if ( $match ) {
                    //校验正则
                    foreach($val as $v) {
                        if (preg_match($regex, $v) === 1) {
                        } else {
                            $error[$name] = 'pattern not match';
                            $param_not_match = true;
                        }
                    }
                    $data[$name] = $val;
                } else {
                    //无需校验
                    $data[$name] = $val;
                }
                continue;
            }

            //字符串类型
            if ( $type == 'str' ) {
                if ( $match ) {
                    //校验正则
                    if ( preg_match($regex, $val) === 1 ) {
                        $data[$name] = $val;
                    } else {
                        $error[$name] = 'pattern not match';
                        $param_not_match = true;
                    }
                } else {
                    //无需校验
                    $data[$name] = $val;
                }
                
                continue;
            }

            //数组类型
            if ( $type == 'arr' ) {
                if ( is_array($val)) {
                    $data[$name] = $val;
                } else {
                    $error[$name] = 'pattern not match';
                    $param_not_match = true;     
                }
                continue;
            }
            
            //GUID
            if ( $type == 'gid' ) {
                //校验正则
                if ( preg_match('/^[0-9a-zA-Z]{32}$/', $val) === 1 ) {
                    $data[$name] = $val;
                } else {
                    $error[$name] = 'guid not match';
                    $param_not_match = true;
                }
                continue;
            }
            
            //整形数值，强制转换
            if ( $type == 'int' ) {
                $data[$name] = (int)$val;
                //转换值为0时再次检验
                if ( $data[$name] == 0 && preg_match('/^[0-9]+/', $val) != 1 ) {
                    $error[$name] = 'not int number';
                    $param_not_match = true;
                }
                continue;
            }
            
            //浮点型，强制转换
            if ( $type == 'flt' ) {
                $data[$name] = (float)$val;
                //转换值为0时再次检验
                if ( $data[$name] == 0 && preg_match('/^[0-9]+(\.[0-9]+)?/', $val) != 1 ) {
                    $error[$name] = 'not float number';
                    $param_not_match = true;
                }
                continue;
            }

            //布尔型
            if ($type == 'boo') {
                if (is_bool($val)) {
                    $data[$name] = $val;
                }
                elseif (strcasecmp($val,'true') === 0) {
                    $data[$name] = 'true';
                }
                elseif (strcasecmp($val, 'false') === 0) {
                    $data[$name] = 'false';
                }
                else {
                    $error[$name] = 'not boolean';
                    $param_not_match = true;
                }
                continue;
            }

            //未知类型，报错
            $error[$name] = 'unknown data type';
        }

        if ( $param_not_found && $param_not_match ) {
            Result::Error(ReturnCode::ERROR, "未知参数错误", "未知参数错误");exit;
        } else if ($param_not_found ) {
            Result::Error(ReturnCode::ERROR, "必填参数不存在", "必填参数不存在");exit;
        } else if ($param_not_match ) {
            Result::Error(ReturnCode::ERROR, "参数格式不对", "参数格式不对");exit;
        }

        // 检查分页参数
        $wrong_page_param = false;
        isset($data["page"]) && $data["page"] < 1 && $wrong_page_param = true;
        isset($data["pageSize"]) && ($data["pageSize"] < 1 || $data["pageSize"] > 3000) && $wrong_page_param = true;
        isset($data["page_size"]) && ($data["page_size"] < 1 || $data["page_size"] > 3000) && $wrong_page_param = true;
        $wrong_page_param && Result::ParamError('Invalid page or pageSize', ReturnCode::PARAMETER_ERROR);;

        return $data;
    }

    public static function Match(&$value, $pattern) {
        $type = substr($pattern, 0, 3);
        $regex = substr($pattern, 3);
        
        if ( $value == null ) {
            Result::ParamError( ReturnCode::PARAMETER_MISSING_PARAMETERS );
        }
        
        //字符串类型
        if ( $type == 'str' ) {
            //校验正则
            if ( preg_match($regex, $value) !== 1 ) {
                Result::ParamError( "Invalid string", ReturnCode::PARAMETER_FORMAT_ERROR );
            }
            
            return;
            
        }
        
        //GUID
        if ( $type == 'gid' ) {
            //校验正则
            if ( preg_match('/^[0-9a-zA-Z]{32}$/', $value) !== 1 ) {
                Result::ParamError( "Invalid guid", ReturnCode::PARAMETER_FORMAT_ERROR );
            }
            
            return;
        }
            
        //整形数值，强制转换
        if ( $type == 'int' ) {
            $tmp = (int)$value;
            //转换值为0时再次检验
            if ( $tmp == 0 && preg_match('/^[0-9]+/', $value) !== 1 ) {
                Result::ParamError( "Invalid integer", ReturnCode::PARAMETER_FORMAT_ERROR );
            }
        
            $value = $tmp;
            
            return;
        }
        
        //浮点型，强制转换
        if ( $type == 'flt' ) {
            $tmp = (float)$value;
            //转换值为0时再次检验
            if ( $tmp == 0 && preg_match('/^[0-9]+(\.[0-9]+)?/', $value) != 1 ) {
                Result::ParamError( "Invalid float", ReturnCode::PARAMETER_FORMAT_ERROR );
            }
            
            $value = $tmp;

            return;
        }

        //未知类型，报错
        Result::ParamError( "Unknown data type", ReturnCode::PARAMETER_FORMAT_ERROR );
    }
    public static function ObjLoad($input, $object,$check_required =true) {
        $data = array();
        $error = array();

        $param_not_found = false;
        $param_not_match = false;

        $orgId = \Flight::get('org');
        $code = (new ObjectBO($orgId))->getObject($object, $result);
        if ($code != 0) {
            Result::ParamError($error, ReturnCode::PARAMETER_ERROR);
        }
        $attrList = $result['attrList'];

        foreach ($attrList as $index => $attr) {
            $name = $attr['id'];
            //$need = $attr['required'];
            $need = 'false'; // 后台去除required控制

            // protect属性的required要求则必填
            if (isset($attr['protected']) && $attr['protected'] == true) {
                if (isset($attr['required']) && $attr['required'] == "true") {
                    $need = "true";
                }
            }

            // [临时方案]有唯一要求的属性，则必填
            if (isset($attr['unique']) && $attr['unique'] == "true") {
                $need = "true";
            }

            $type = $attr['value']['type'];

            if(isset($attr['value']['regex'])) {
                $regex = $attr['value']['regex'];
            }
            elseif (isset($attr['value']['rule'])) {
                $regex = $attr['value']['rule'];
            }
            else {
                $regex = "";
            }
            if(isset($attr['value']['default'])) {
                $default = $attr['value']['default'];
            }
            else {
                $default = null;
            }

            if(is_array($regex))
            {
                $match = count($regex)==0 ? false : true;
            }
            else
            {
                $match = strlen($regex)==0 ? false : true;
            }

            $val = isset($input[$name]) ? $input[$name] : null;

            $exist = array_key_exists($name, $input) ? true : false;

            // 如果该属性值不存在, 并且不进行必要性检查, 即跳过
            if ($check_required !== true && !$exist) continue;

            // 创建默认值
            if ($val === null) {
                $data[$name] = null;
                if($default == 'guid()') {
                    $data[$name] = Guid::get();
                }
                else if($default !== null) {
                    $data[$name] = $default;
                }

                $val = $data[$name];

                // 不必填的数据，在生成默认数据的情况下跳过后续的校验处理
                if ($need === "false") {
                    continue;
                }
            }

            // 如果是非空属性, 并且值为空, 则记录错误并跳过
            if ($need === "true" && $val === null) {
                $error[$name] = 'data not found';
                $param_not_found = true;
                continue;
            }

            //枚举类型
            if ($type == 'enum')
            {
                if ( $match ) {
                    //校验正则
                    if (in_array($val,$regex) ) {
                        $data[$name] = $val;
                    } else {
                        $error[$name] = 'pattern not match';
                        $param_not_match = true;
                    }
                } else {
                    //无需校验
                    $data[$name] = $val;
                }
                continue;
            }
            //一维数组类型
            if ($type == 'arr')
            {
                if(!is_array($val))
                {
                    $error[$name] = 'pattern not match';
                    $param_not_match = true;
                    continue;
                }
                if ( $match ) {
                    //校验正则
                    foreach($val as $v) {
                        if (preg_match('/'.$regex.'/', $v) === 1) {
                        } else {
                            $error[$name] = 'pattern not match';
                            $param_not_match = true;
                        }
                    }
                    $data[$name] = $val;
                } else {
                    //无需校验
                    $data[$name] = $val;
                }
                continue;
            }

            //字符串类型
            if ( $type == 'str' ) {
                if ( $match ) {
                    //校验正则
                    if ( preg_match('/'.$regex.'/', $val) === 1 ) {
                        $data[$name] = $val;
                    } else {
                        $error[$name] = 'pattern not match';
                        $param_not_match = true;
                    }
                } else {
                    //无需校验
                    $data[$name] = $val;
                }

                continue;
            }

            //数组类型
            if ( $type == 'arr' ) {
                if ( is_array($val)) {
                    $data[$name] = $val;
                } else {
                    $error[$name] = 'pattern not match';
                    $param_not_match = true;
                }
                continue;
            }

            //GUID
            if ( $type == 'gid' ) {
                //校验正则
                if ( preg_match('/^[0-9a-zA-Z]{32}$/', $val) === 1 ) {
                    $data[$name] = $val;
                } else {
                    $error[$name] = 'guid not match';
                    $param_not_match = true;
                }
                continue;
            }

            //整形数值，强制转换
            if ( $type == 'int' ) {
                $data[$name] = (int)$val;
                //转换值为0时再次检验
                if ( $data[$name] == 0 && preg_match('/^[0-9]+/', $val) != 1 ) {
                    $error[$name] = 'not int number';
                    $param_not_match = true;
                }
                continue;
            }

            //浮点型，强制转换
            if ( $type == 'flt' ) {
                $data[$name] = (float)$val;
                //转换值为0时再次检验
                if ( $data[$name] == 0 && preg_match('/^[0-9]+(\.[0-9]+)?/', $val) != 1 ) {
                    $error[$name] = 'not float number';
                    $param_not_match = true;
                }
                continue;
            }

            //布尔型
            if ($type == 'bool') {
                if (strcasecmp($val, 'true') === 0) {
                    $data[$name] = 'true';
                }
                elseif (strcasecmp($val, 'false') === 0) {
                    $data[$name] = 'false';
                }
                else {
                    $error[$name] = 'not boolean';
                    $param_not_match = true;
                }
                continue;
            }

            //外键类型,不作处理
            if ( $type == 'FK' or $type == 'FKs' ) {
                $data[$name] = $val;
                continue;
            }

            // Date类型, 转字符串
            if ( $type == 'date' ) {
                $timestamp = strtotime($val);
                if ($timestamp) {
                    $data[$name] = date('Y-m-d', $timestamp);
                }
                else {
                    $error[$name] = 'not date';
                    $param_not_match = true;
                }
                continue;
            }

            //Datetime类型, 转字符串
            if ( $type == 'datetime') {
                $timestamp = strtotime($val);
                if ($timestamp) {
                    $data[$name] = date('Y-m-d H:i:s', $timestamp);
                }
                else {
                    $error[$name] = 'not datetime';
                    $param_not_match = true;
                }
                continue;
            }

            //未知类型，报错
            $error[$name] = 'unknown data type';
        }

        if ( $param_not_found && $param_not_match ) {
            Result::ParamError($error, ReturnCode::PARAMETER_ERROR);
        } else if ($param_not_found ) {
            Result::ParamError($error, ReturnCode::PARAMETER_MISSING_PARAMETERS);
        } else if ($param_not_match ) {
            Result::ParamError($error, ReturnCode::PARAMETER_FORMAT_ERROR);
        }

        //检查分页参数
        if ( isset($data['page']) && isset($data['pageSize']) ) {
            //分页不能超过200
            $page = $data['page'];
            $pageSize = $data['pageSize'];
            if ($page<1||$pageSize<1||$pageSize>1000) {
                Result::ParamError('Invalid page or pageSize', ReturnCode::PARAMETER_ERROR);
            }
        }

        return $data;
    }

}

