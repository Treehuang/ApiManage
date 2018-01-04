<?php

namespace logic;

use library\DataConst;
use library\FileConst;
use common\library\Log;
use common\library\ReturnCode;
use library\Tools;
use dao\InterfaceDao;
use dao\MessageDao;
use Hoa\Compiler\Llk\Llk;
use Hoa\File\Read;
use Hoa\Math\Sampler\Random;
use Hoa\Regex\Visitor\Isotropic;
use library\ExecCommand;
use Predis\Client;

/**
 * Class MessageLogic.
 *
 * @author Kinming
 */
class MessageLogic
{
    //用于正则表达式生成测试数据
    public static $grammar;
    public static $compiler;

    //项目名称
    protected $project_name;
    //保存生成的正确数据
    protected $total_create_data = array();
    //保存要返回的response信息
    protected $total_return_message = array();
    //保存message列表
    protected $message_list = array();
    //保存第一层的message
    protected $message_tmp = array();

    //无参构造函数
    public function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();

        if (method_exists($this, $f = '__construct'.$i)) {
            //若存在xx方法，使用call_user_func_array(arr1,arr2)函数调用他,该函数的参数为两个数组，前面的数组为调用谁($this)的什么($f)方法，后一个数组为参数
            call_user_func_array(array($this, $f), $a);
        }
    }

    //有一个参数的构造函数
    public function __construct1($project_name)
    {
        $this->project_name = $project_name;
    }

    /**
     * 获取消息类型模板
     *
     * @param $type
     * @param $template
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageTypeTemplate($type, &$template, &$info)
    {
        $code = (new MessageDao())->getMessageTypeTemplate($type, $template);
        if (0 != $code) {
            $info = '该消息模板不存在';
            Log::error($info, [$type, $template]);

            return $code;
        }

        return 0;
    }

    /**
     * 写入消息文件.
     *
     * @param $project_name
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function writeMessageFile($project_name, &$data, &$info)
    {
        //修改项目的最新时间
        $code = (new ProjectLogic())->updateProjectTime($project_name, $info);
        if (0 != $code) {
            return $code;
        }

        @chdir(FileConst::USERNAME_PATH);
        if (!is_dir($project_name)) {
            $info = '项目不存在';
            Log::error($info, [$project_name, getcwd()]);

            return ReturnCode::ERROR;
        }
        @chdir($project_name);
        @mkdir(FileConst::CONFIG);
        @chdir(FileConst::CONFIG);

        //判断消息是否存在
        @mkdir(FileConst::MESSAGE);
        @chdir(FileConst::MESSAGE);
        ExecCommand::execLs($rs, $code);

        $name = $data['name'];
        if (in_array($name.'.yaml', $rs)) {
            $info = '消息名已存在';
            log::error($info, [getcwd(), $name]);

            return ReturnCode::ERROR;
        }

        @chdir(FileConst::RETURN_PATH);
        //写入消息列表的yaml文件
        $code = (new MessageDao())->writeMessageInfoList($data);
        if (0 !== $code) {
            $info = '消息列表写入yaml文件失败';
            Log::error($info, $data);

            return ReturnCode::ERROR;
        }

        //写入消息详情的yaml文件
        $code = (new MessageDao())->writeMessageDetail($data);
        if (0 !== $code) {
            $info = '消息详情写入yaml文件失败';
            Log::error($info, $data);

            return ReturnCode::ERROR;
        }

        //提交git
        $code = Tools::commitGit('..', "新增消息 $name", '新增本地消息成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    /**
     * 判断子消息内是否嵌套当前消息.
     *
     * @param array $data     消息详情
     * @param array $array    存放消息名称的数组
     * @param array $data_new 要修改的新消息
     * @param array $info     存放错误信息
     *
     * @return int
     *
     * @author Kinming
     */
    public function filterDeadLoop($data, $array, $data_new, &$info)
    {
        if (isset($data['message_name'])) {
            $message_name = $data['message_name'];
        } else {
            return 0;
        }

        if (isset($data['fields'])) {
            $fields = $data['fields'];
        } else {
            //判断要获取消息详情的消息名与新消息名是否相同
            if ($data_new['message_name'] != $message_name) {
                //获取消息详情
                @chdir(FileConst::WWW);
                $code = $this->getMessageDetailByList($message_name, $detail, $info);
                if (0 !== $code) {
                    return $code;
                }
            } else {
                $detail = $data_new;
            }

            $code = $this->filterDeadLoop($detail, $array, $data_new, $info);
            if (0 !== $code) {
                return $code;
            }

            return 0;
        }

        $count = count($fields);
        for ($i = 0; $i < $count; ++$i) {
            if ($fields[$i]['type'] == 'object' || $fields[$i]['type'] == 'array_object') {
                if (in_array($message_name, $array)) {
                    $info = '子消息不能嵌套当前消息';
                    Log::error($info);

                    return ReturnCode::ERROR;
                }
                array_push($array, $message_name);
                $count2 = count($fields[$i]['fields']);
                for ($j = 0; $j < $count2; ++$j) {
                    //强势递归
                    $code = $this->filterDeadLoop($fields[$i]['fields'][$j], $array, $data_new, $info);
                    if (0 !== $code) {
                        return $code;
                    }
                    unset($array);
                    $array = [];
                }
            }
        }

        return 0;
    }

    /**
     * 获取消息类型列表.
     *
     * @param $list
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageTypeList(&$list)
    {
        $code = (new MessageDao())->getMessageTypeYaml($total);
        if (0 !== $code) {
            Log::error('yaml解析文件失败', [$total]);

            return $code;
        }

        $count = count($total);

        for ($i = 0; $i < $count; ++$i) {
            array_push($list, $total[$i]['type']);
        }

        return 0;
    }

    /**
     * 获取消息列表.
     *
     * @param $project_name
     * @param $list
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageList($project_name, &$list, &$info)
    {
        $code = (new MessageDao())->getMessageList($project_name, $list);
        if (0 != $code) {
            $info = '该项目不存在';
            Log::error($info, [$project_name, getcwd()]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 检查消息名称是否重复.
     *
     * @param $project_name
     * @param $message_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function checkMessageName($project_name, $message_name, &$info)
    {
        @chdir(FileConst::USERNAME_PATH);
        $project_name_bool = @chdir($project_name);
        if (empty($project_name_bool)) {
            $info = '该项目不存在';
            Log::error($info, [getcwd(), $project_name]);

            return ReturnCode::ERROR;
        }
        @chdir(FileConst::CONFIG);
        @chdir(FileConst::MESSAGE);

        ExecCommand::execLs($rs, $code);

        if (in_array($message_name.'.yaml', $rs)) {
            $info = '该消息名已存在';
            Log::error($info, [getcwd(), $message_name]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 获取消息详情（通过详情文件）.
     *
     * @param $project_name
     * @param $message_name
     * @param $detail
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageDetail($project_name, $message_name, &$detail, &$info)
    {
        //检查项目和消息是否存在
        $code = Tools::checkProjectAndMessage($project_name, $message_name, $info);
        if (0 != $code) {
            return $code;
        }

        $code = (new MessageDao())->getMessageDetail($message_name, $detail, $info);

        if (0 !== $code) {
            return $code;
        }

        return 0;
    }

    /**
     * 获取消息详情（通过消息列表文件）.
     *
     * @param $message_name
     * @param $detail
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function getMessageDetailByList($message_name, &$detail, &$info)
    {
        if (empty($this->message_list)) {
            $code = $this->getMessageList($this->project_name, $this->message_list, $info);
            if (0 != $code) {
                return $code;
            }
        }

        foreach ($this->message_list as $item) {
            if ($item['name'] == $message_name) {
                $detail = $item;

                return 0;
            }
        }

        $info = '该消息不存在';
        Log::error($info, [getcwd(), $message_name]);

        return ReturnCode::ERROR;
    }

    /**
     * 修改消息定义.
     *
     * @param $project_name
     * @param $message_name
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function updateMessage($project_name, $message_name, &$data, &$info)
    {
        //检查项目和消息是否存在
        $code = Tools::checkProjectAndMessage($project_name, $message_name, $info);
        if (0 != $code) {
            return $code;
        }

        //修改项目的最新时间
        $code = (new ProjectLogic())->updateProjectTime($project_name, $info);
        if (0 != $code) {
            return ReturnCode::ERROR;
        }

        @chdir(FileConst::USERNAME_PATH.FileConst::SLASH.$project_name.FileConst::SLASH.FileConst::CONFIG.FileConst::SLASH.FileConst::MESSAGE);

        //将修改的消息覆盖写入消息详情文件
        $code = (new MessageDao())->writeMessageDetailYaml($data, $message_name);
        if (0 !== $code) {
            Log::error('写入消息详情文件失败', [$data, $message_name]);

            return $code;
        }

        //修改消息文件名
        $message_name_new = $data['name'];

        ExecCommand::execMv("$message_name.yaml", "$message_name_new.yaml");

        @chdir(FileConst::RETURN_PATH);
        //将修改的消息覆盖写入消息列表文件message_info.yaml
        $code = (new MessageDao())->writeMessageList($data, $message_name);
        if (0 !== $code) {
            Log::error('覆盖写入消息列表文件message_info.yaml失败', [$data, $message_name]);

            return $code;
        }

        //提交git
        $code = Tools::commitGit('-A .', "修改消息 $message_name", '修改本地消息成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    /**
     * 添加正则表达式的^$前后缀
     *
     * @param $pattern
     *
     * @author Kinming
     */
    public function addPrefixAndSuffix(&$pattern)
    {
        if ('^' != $pattern[0]) {
            //在开头加^
            $pattern = '^'.$pattern;
        }
        if ('$' != substr($pattern, -1)) {
            //在结尾加$
            $pattern = $pattern.'$';
        }
    }

    /**
     * 删除消息.
     *
     * @param $project_name
     * @param $message_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteMessage($project_name, $message_name, &$info)
    {
        //修改项目的最新时间
        $code = (new ProjectLogic())->updateProjectTime($project_name, $info);
        if (0 != $code) {
            return $code;
        }

        //检查项目和消息是否存在
        $code = Tools::checkProjectAndMessage($project_name, $message_name, $info);
        if (0 != $code) {
            return $code;
        }

        //删除该消息
        ExecCommand::execRm("$message_name.yaml");

        @chdir(FileConst::RETURN_PATH);
        //将该消息从消息列表文件中删除
        $code = (new MessageDao())->deleteMessage($message_name, $info);
        if (0 !== $code) {
            $info = '从消息列表文件中删除该消息失败';
            Log::error($info, [$message_name]);

            return $code;
        }

        //提交git
        $code = Tools::commitGit('-A .', "删除消息 $message_name", '删除本地消息成功，git push失败', $info);
        if (0 != $code) {
            return $code;
        }

        return 0;
    }

    /**
     * 构建请求和响应消息.
     *
     * @param $service_name
     * @param $interface_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createRequestAndResponseMessage($service_name, $interface_name, &$info)
    {
        //根据正则表达式生成测试数据
        self::$grammar = new Read('hoa://../lib/vendor/hoa/regex/Grammar.pp');
        self::$compiler = Llk::load(self::$grammar);

        @chdir(FileConst::USERNAME_PATH);
        @chdir($this->project_name);
        @chdir(FileConst::CONFIG);
        @chdir(FileConst::SERVICE);
        @chdir("$service_name");

        $code = (new MessageDao())->parseYamlToArray($interface_name, $detail);
        if (0 !== $code) {
            $info = '该接口不存在';
            Log::error($info, [getcwd(), $interface_name, $detail]);

            return $code;
        }

        //构建request和response的数据
        $code = $this->createRequestAndResponseData($detail, $request_message, $response_message, $info);
        if (0 !== $code) {
            return $code;
        }

        $info['request'] = $request_message;
        $info['response'] = $response_message;

        return 0;
    }

    /**
     * 构建正确的消息数据.
     *
     * @param $detail
     * @param $message
     * @param $info
     * @param bool $required true代表构造消息的每个属性必须要有，false代表不一定要有所有属性
     *
     * @return int
     *
     * @author Kinming
     */
    public function createCorrectMessageData($detail, &$message, &$info, $required)
    {
        $name = $detail['name'];
        $type = $detail['type'];

        //没有fields属性
        if ('' == isset($detail['fields'])) {
            //false代表构造的消息不一定要有所有属性
            if (false == $required && false == $detail['required'] && 0 == mt_rand(0, 1)) {
                unset($message[$name]);
                if (is_array($message) && 0 == count($message)) {
                    $message = null;
                }

                return 0;
            }
        }

        switch ($type) {
            case 'string':
                if (false == $detail['enum']) {
                    //构建string类型的数据
                    $code = $this->createStringData($detail, 'check', $message, $info);
                    if (0 !== $code) {
                        return $code;
                    }
                } else {
                    //构建enum类型的数据
                    $code = $this->createEnumData($detail, $enum);
                    if (0 !== $code) {
                        return $code;
                    }
                    $message[$name] = $enum;
                }
                break;
            case 'number':
                if (false == $detail['enum']) {
                    //构建number类型的数据
                    $code = $this->createNumberData($detail, $message, $name, 'check');
                    if (0 !== $code) {
                        return $code;
                    }
                } else {
                    //构建enum类型的数据
                    $code = $this->createEnumData($detail, $enum);
                    if (0 !== $code) {
                        return $code;
                    }
                    $message[$name] = $enum;
                }
                break;
            case 'bool':
                //构建bool类型的数据
                $bool = [false, true];
                $random = mt_rand(0, 1);
                $message[$name] = $bool[$random];
                break;
            case 'datetime':
                //构建datetime类型的数据
                $pattern = $detail['check'];
                $code = $this->createRegexData($pattern, $date, $info);
                if (0 !== $code) {
                    return $code;
                }
                $message[$name] = $date;
                break;
            case '[]' == substr($type, -2):
                //构建数组的数据
                $code = $this->createArrayData($detail, $array, $info, $required);
                if (0 !== $code) {
                    return $code;
                }

                if ('object[]' == $type) {
                    $message = $array;
                } else {
                    $message[$name] = $array;
                }
                break;
            case 'object':
            default:
                //构造object和消息类型的数据
                $code = $this->createObjectData($detail, $message, $info, $required);
                if (0 !== $code) {
                    return $code;
                }
                break;
        }

        return 0;
    }

    /**
     * 构建enum类型的数据.
     *
     * @param $detail
     * @param $enum
     *
     * @return int
     *
     * @author Kinming
     */
    public function createEnumData($detail, &$enum)
    {
        $array = $detail['item'];
        $count = count($array);
        $random = mt_rand(0, $count - 1);
        $i = 0;
        foreach ($array as $item) {
            if ($random == $i) {
                $enum = $item['value'];
                break;
            }
            ++$i;
        }

        return 0;
    }

    /**
     * 随机生成符合正则表达式的测试数据.
     *
     * @param $pattern
     * @param $data
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createRegexData($pattern, &$data, &$info)
    {
        try {
            $ast = self::$compiler->parse($pattern);
            $generator = new Isotropic(new Random());
            $data = $generator->visit($ast);
        } catch (\Exception $exception) {
            $info = '正则表达式格式有误';
            Log::error($info, [$pattern, $exception->getMessage(), $exception->getTraceAsString()]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 构建array类型的数据.
     *
     * @param $detail
     * @param $array
     * @param $info
     * @param bool $required true代表构造消息的每个属性必须要有，false代表不一定要有所有属性
     *
     * @return int
     *
     * @author Kinming
     */
    public function createArrayData($detail, &$array, &$info, $required)
    {
        $array_type = $detail['type'];
        $minItems = $detail['array_check']['minItem'];
        $maxItems = $detail['array_check']['maxItem'];
        $count = mt_rand($minItems, $maxItems);

        if ('string[]' == $array_type) {
            //构造string的array类型数据
            for ($i = 0; $i < $count; ++$i) {
                $code = $this->createStringData($detail, 'check', $data, $info);
                if (0 !== $code) {
                    return $code;
                }
                $array[$i] = $data;
            }
        } elseif ('number[]' == $array_type) {
            //构造number的数组类型数据
            for ($i = 0; $i < $count; ++$i) {
                $code = $this->createNumberData($detail, $data, null, 'check');
                if (0 !== $code) {
                    return $code;
                }
                $array[$i] = $data;
            }
        } else {
            //构造消息的数组类型数据
            for ($i = 0; $i < $count; ++$i) {
                $code = $this->createObjectData($detail, $data, $info, $required);
                if (0 !== $code) {
                    return $code;
                }
                $array[$i] = $data;
            }
        }

        return 0;
    }

    /**
     * 构造string类型数据.
     *
     * @param $detail
     * @param $check
     * @param $message
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createStringData($detail, $check, &$message, &$info)
    {
        $minLength = $detail[$check]['minLength'];
        $maxLength = $detail[$check]['maxLength'];
        //判断正则表达式是否为空
        if (isset($detail['check']['pattern'])) {
            //随机生成符合正则表达式的测试数据
            $pattern = $detail['check']['pattern'];
            $code = $this->createRegexData($pattern, $data, $info);
            if (0 !== $code) {
                return $code;
            }
            //判断生成的字符串是否超过最大长度
            $strlen = strlen($data);
            if ($strlen > $maxLength) {
                $data = substr($data, 0, $maxLength);
            }
        } else {
            $pattern = '[A-Za-z0-9]'.'{'.$minLength.','.$maxLength.'}';
            //随机生成符合正则表达式的测试数据
            $code = $this->createRegexData($pattern, $data, $info);
            if (0 !== $code) {
                return $code;
            }
        }

        if (isset($detail['array_check'])) {
            //是string数组类型
            $message = $data;
        } else {
            //string类型
            $name = $detail['name'];
            $message[$name] = $data;
        }

        return 0;
    }

    /**
     * 构建file类型的数据.
     *
     * @param $detail
     * @param $data
     *
     * @author Kinming
     */
    public function createFileData($detail, &$data)
    {
        $file_type = $detail['check']['fileType'];
        $count = count($file_type);
        $random = mt_rand(0, $count - 1);
        $data = 'test.'.$file_type[$random];
    }

    /**
     * 构建object类型的数据.
     *
     * @param $detail
     * @param $message
     * @param $info
     * @param bool $required true代表构造消息的每个属性必须要有，false代表不一定要有所有属性
     *
     * @return int
     *
     * @author Kinming
     */
    public function createObjectData($detail, &$message, &$info, $required)
    {
        if (isset($detail['fields'])) {
            //存在消息属性
            $fields = $detail['fields'];
            $count = count($fields);
            for ($i = 0; $i < $count; ++$i) {
                //强势递归
                $code = $this->createCorrectMessageData($fields[$i], $message, $info, $required);
                if (0 !== $code) {
                    return $code;
                }
            }
        } else {
            //消息和消息数组类型
            $type = $detail['type'];

            if ('[]' == substr($type, -2)) {
                //如果是消息数组类型
                $message_name = substr($type, 0, -2);
            } else {
                //如果是消息类型
                $message_name = $type;
            }

            //获取消息详情
            @chdir(FileConst::WWW);
            $this->getMessageDetailByList($message_name, $message_detail, $error);

            if ('[]' == substr($type, -2)) {
                //如果是消息数组类型
                $code = $this->createCorrectMessageData($message_detail, $message, $info, $required);
            } else {
                //如果是消息类型
                $code = $this->createCorrectMessageData($message_detail, $message[$detail['name']], $info, $required);
            }

            if (0 !== $code) {
                return $code;
            }
        }

        return 0;
    }

    /**
     * 构建number类型的数据.
     *
     * @param $detail
     * @param $message
     * @param string $name 值为null时代表为number数组类型，其他值代表number类型
     * @param $check
     *
     * @return int
     *
     * @author Kinming
     */
    public function createNumberData($detail, &$message, $name, $check)
    {
        $min = $detail[$check]['min'];
        $max = $detail[$check]['max'];
        $format = $detail[$check]['format'];
        if (!empty(strstr($format, 'int'))) {
            //整数
            $random = mt_rand($min, $max);
        } else {
            //浮点数
            $random = $this->randomFloat($min, $max, '%.2f');
        }

        if (is_null($name)) {
            //是number数组类型
            $message = $random;
        } else {
            $message[$name] = $random;
        }

        return 0;
    }

    /**
     * 随机生成浮点数.
     *
     * @param int $min
     * @param int $max
     * @param $format
     *
     * @return string
     *
     * @author Kinming
     */
    public function randomFloat($min = 0, $max = 1, $format)
    {
        $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        $random = sprintf($format, $num);

        return $random;
    }

    /**
     * 构建request和response的数据.
     *
     * @param $detail
     * @param $request_message
     * @param $response_message
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createRequestAndResponseData($detail, &$request_message, &$response_message, &$info)
    {
        $request_message_name = $detail[0]['request']['message'];
        $response_message_name = $detail[0]['response']['message'];

        chdir(FileConst::RETURN_PATH.'/'.FileConst::RETURN_PATH);
        chdir(FileConst::MESSAGE);

        $code = (new MessageDao())->parseYamlToArray($request_message_name, $request_detail);
        if (0 !== $code) {
            $info = '解析yaml文件失败';
            Log::error($info, [$request_message_name, $request_detail]);

            return ReturnCode::ERROR;
        }

        $code = (new MessageDao())->parseYamlToArray($response_message_name, $response_detail);
        if (0 !== $code) {
            $info = '解析yaml文件失败';
            Log::error($info, [$response_message_name, $response_detail]);

            return ReturnCode::ERROR;
        }

        //构建requst消息数据
        $code = $this->createCorrectMessageData($request_detail[0], $request_message, $info, true);
        if (0 !== $code) {
            return $code;
        }

        //构造response消息数据
        $response_message = [];
        $code = $this->createCorrectMessageData($response_detail[0], $response_data, $info, true);
        if (0 !== $code) {
            return $code;
        }
        $this->total_create_data = $response_data;
        //第一组正常数据
        array_push($this->total_return_message, $response_data);

        //构造各种异常消息数据
        $code = $this->createErrorMessageData($response_detail[0], $response_data, null, $info);
        if (0 !== $code) {
            return $code;
        }

        $response_message = $this->total_return_message;

        return 0;
    }

    /**
     * 构造string和number类型的各种异常数据.
     *
     * @param $detail
     * @param $message
     * @param $type
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorAllStringAndNumber($detail, &$message, $type)
    {
        $count = 0;
        if ('string' == $type) {
            $count = DataConst::STRING_ERROR_COUNT;
        } elseif ('number' == $type) {
            $count = DataConst::NUMBER_ERROR_COUNT;
        }

        $j = 1;
        while ($j <= $count) {
            $error_bool = false;
            if ('string' == $type) {
                $error_bool = $this->createErrorStringData($detail, $message, $j);
            } elseif ('number' == $type) {
                $error_bool = $this->createErrorNumberData($detail, $message, $j);
            }

            if ($error_bool) {
                $tmp = serialize($this->message_tmp);
                $tmp = unserialize($tmp);

                array_push($this->total_return_message, $tmp);
            }
            ++$j;
        }

        return 0;
    }

    /**
     * 构造各种异常消息数据.
     *
     * @param $detail
     * @param $message
     * @param string $type 值为null时，表示正在执行json数据的第一层
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorMessageData($detail, &$message, $type, &$info)
    {
        $fields = $detail['fields'];
        $count = count($fields);

        //深拷贝message
        $old_message = serialize($message);
        $old_message = unserialize($old_message);

        for ($i = 0; $i < $count; ++$i) {
            if (is_null($type)) {
                //只有message的json数据第一层才要执行这一句，保留最新值
                $this->message_tmp = &$message;
            }
            $message_type = $fields[$i]['type'];
            switch ($message_type) {
                case 'string':
                    if ($fields[$i]['enum'] == false) {
                        //构造string类型的各种异常数据
                        $code = $this->createErrorAllStringAndNumber($fields[$i], $message, 'string');
                        if (0 !== $code) {
                            return $code;
                        }
                    } else {
                        //构造enum类型的各种异常数据
                        $code = $this->createErrorEnumData($fields[$i], $message);
                        if (0 !== $code) {
                            return $code;
                        }
                    }
                    break;
                case 'number':
                    if ($fields[$i]['enum'] == false) {
                        //构造number类型的各种异常数据
                        $code = $this->createErrorAllStringAndNumber($fields[$i], $message, 'number');
                        if (0 !== $code) {
                            return $code;
                        }
                    } else {
                        //构造enum类型的各种异常数据
                        $code = $this->createErrorEnumData($fields[$i], $message);
                        if (0 !== $code) {
                            return $code;
                        }
                    }
                    break;
                case 'bool':
                    //构造bool类型的各种异常数据
                    $code = $this->createErrorBoolAndDateTimeData($fields[$i], $message, 'bool');
                    if (0 !== $code) {
                        return $code;
                    }
                    break;
                case 'datetime':
                    //构造datetime类型的各种异常数据
                    $code = $this->createErrorBoolAndDateTimeData($fields[$i], $message, 'datetime');
                    if (0 !== $code) {
                        return $code;
                    }
                    break;
                case '[]' == substr($message_type, -2):
                    //构造array类型的各种异常数据
                    $code = $this->createErrorArrayData($fields[$i], $message, $old_message, $info);
                    if (0 !== $code) {
                        return $code;
                    }
                    break;
                default:
                    //构造消息类型的各种异常数据
                    $code = $this->createErrorObjectData($fields[$i], $message, $old_message, $info);
                    if (0 !== $code) {
                        return $code;
                    }
                    break;
            }

            //恢复原来的值
            unset($message);
            $message = $this->total_create_data;
        }

        return 0;
    }

    /**
     * 构造string类型的数据.
     *
     * @param $detail
     * @param $message
     * @param $count
     * @param string $type
     *
     * @return bool
     *
     * @author Kinming
     */
    public function createErrorStringData($detail, &$message, $count, $type = 'string')
    {
        if ('string[]' == $type) {
            //指向string数组的第一个元素
            $message_tmp = &$message[$detail['name']][0];
        } else {
            //指向string类型的名称
            $message_tmp = &$message[$detail['name']];
        }

        $pattern = $detail['check']['pattern'];
        switch ($count) {
            case DataConst::STRING_BLANK_CHARACTER_TYPE:
                //该属性值为空字符串的情况
                $bool = @preg_match("/$pattern/", '');
                if (!$bool) {
                    $message_tmp = '';

                    return true;
                }
                break;
            case DataConst::STRING_LT_MIN_TYPE:
                //该属性值小于最小长度的情况
                $minLength = $detail['check']['minLength'];
                if (($minLength - 1) > 0) {
                    $pattern = '[A-Za-z0-9]'.'{'.($minLength - 1).'}';
                    //随机生成符合正则表达式的测试数据
                    $this->createRegexData($pattern, $data, $info);
                    $message_tmp = $data;

                    return true;
                }
                break;
            case DataConst::STRING_GT_MAX_TYPE:
                //该属性值大于最大长度的情况
                $maxLength = $detail['check']['maxLength'];
                $pattern = '[A-Za-z0-9]'.'{'.($maxLength + 1).'}';
                //随机生成符合正则表达式的测试数据
                $this->createRegexData($pattern, $data, $info);
                $message_tmp = $data;

                return true;
                break;
            case DataConst::STRING_CHINESE_TYPE:
                //该属性值为中文的情况
                $pattern = substr($pattern, 1, strlen($pattern) - 2);
                $bool = @preg_match("$pattern", '中文');
                if (!$bool) {
                    $message_tmp = '中文';

                    return true;
                }
                break;
            case DataConst::STRING_CONTAIN_BLANK_TYPE:
                //该属性值包含空格的情况
                $bool = @preg_match("/$pattern/", ' ');
                if (!$bool) {
                    $message_tmp = ' ';

                    return true;
                }
                break;
            case DataConst::STRING_NUMBER_TYPE:
                //该属性值为数字格式的情况
                $message_tmp = 123;

                return true;
                break;
            default:
                //该属性不存在的情况
                if (true == $detail['required']) {
                    unset($message[$detail['name']]);
                    if (is_array($message) && 0 == count($message)) {
                        $message = null;
                    }

                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * 构造number类型的数据.
     *
     * @param $detail
     * @param $message
     * @param $count
     * @param string $type
     *
     * @return bool
     *
     * @author Kinming
     */
    public function createErrorNumberData($detail, &$message, $count, $type = 'number')
    {
        if ('number[]' == $type) {
            //指向string数组的第一个元素
            $message_tmp = &$message[$detail['name']][0];
        } else {
            //指向string类型的名称
            $message_tmp = &$message[$detail['name']];
        }

        switch ($count) {
            case DataConst::NUMBER_STRING_TYPE:
                //该属性值为字符串的情况
                $message_tmp = '123';

                return true;
                break;
            case DataConst::NUMBER_LT_MIN_TYPE:
                //该属性值小于最小值的情况
                $min = $detail['check']['min'];
                $message_tmp = $min - 1;

                return true;
                break;
            case DataConst::NUMBER_GT_MAX_TYPE:
                //该属性值大于最大值的情况
                $max = $detail['check']['max'];
                $message_tmp = $max + 1;

                return true;
                break;
            case DataConst::NUMBER_FLOAT_TYPE:
                //该属性值为浮点数的情况
                $format = $detail['check']['format'];
                if (!empty(strstr($format, 'int'))) {
                    $message_tmp = 0.01;

                    return true;
                }
                break;
            default:
                //该属性不存在的情况
                if (true == $detail['required']) {
                    unset($message[$detail['name']]);
                    if (is_array($message) && 0 == count($message)) {
                        $message = null;
                    }

                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * 构造array类型的各种异常数据.
     *
     * @param $detail
     * @param $message
     * @param $old_message
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorArrayData($detail, &$message, $old_message, &$info)
    {
        $array_type = $detail['type'];

        if ('string[]' == $array_type) {
            //构建string数组元素层面的异常数据
            $j = 1;
            while ($j <= DataConst::STRING_ERROR_COUNT) {
                $error_bool = $this->createErrorStringData($detail, $message, $j, 'string[]');
                if ($error_bool) {
                    $tmp = serialize($this->message_tmp);
                    $tmp = unserialize($tmp);

                    array_push($this->total_return_message, $tmp);
                }
                ++$j;
            }
        } elseif ('number[]' == $array_type) {
            //构建number数组元素层面的异常数据
            $j = 1;
            while ($j <= DataConst::NUMBER_ERROR_COUNT) {
                $error_bool = $this->createErrorNumberData($detail, $message, $j, 'number[]');
                if ($error_bool) {
                    $tmp = serialize($this->message_tmp);
                    $tmp = unserialize($tmp);

                    array_push($this->total_return_message, $tmp);
                }
                ++$j;
            }
        } else {
            //构建消息类型的数组属性的错误数据
            //获取消息详情
            @chdir(FileConst::WWW);
            $type = substr($detail['type'], 0, -2);

            $code = $this->getMessageDetailByList($type, $message_detail, $info);
            if (0 !== $code) {
                return $code;
            }

            if ('object[]' == $message_detail['type']) {
                $message_detail['type'] = substr($message_detail['type'], 0, -2);
                $code = $this->createErrorMessageData($message_detail, $message[$detail['name']][0][0], 'object', $info);
                if (0 !== $code) {
                    return $code;
                }
            } else {
                $code = $this->createErrorMessageData($message_detail, $message[$detail['name']][0], 'object', $info);
                if (0 !== $code) {
                    return $code;
                }
            }

            $message = serialize($old_message);
            $message = unserialize($message);

            //构建消息数组层面的错误数据
            if (true == $detail['required']) {
                unset($message[$detail['name']]);
                if (is_array($message) && 0 == count($message)) {
                    $message = null;
                }

                $tmp = serialize($this->message_tmp);
                $tmp = unserialize($tmp);
                array_push($this->total_return_message, $tmp);
            }
        }

        return 0;
    }

    /**
     * 构造bool和datetime类型的各种异常数据.
     *
     * @param $detail
     * @param $message
     * @param $type
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorBoolAndDateTimeData($detail, &$message, $type)
    {
        if ('bool' == $type) {
            //设置非bool类型的值
            $message[$detail['name']] = 'errorbool';
        } elseif ('datetime' == $type) {
            //设置非datetime类型的值
            $message[$detail['name']] = '2000-01.01 00:00:00';
        }

        //设置构造的异常数据
        $tmp = serialize($this->message_tmp);
        $tmp = unserialize($tmp);
        array_push($this->total_return_message, $tmp);

        //设置bool类型的属性不存在
        if (true == $detail['required']) {
            unset($message[$detail['name']]);
            if (is_array($message) && 0 == count($message)) {
                $message = null;
            }
            //设置构造的异常数据
            $tmp = serialize($this->message_tmp);
            $tmp = unserialize($tmp);
            array_push($this->total_return_message, $tmp);
        }

        return 0;
    }

    /**
     * 构造enum类型的各种异常数据.
     *
     * @param $detail
     * @param $message
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorEnumData($detail, &$message)
    {
        $enum_type = $detail['type'];

        if ('string' == $enum_type) {
            $message[$detail['name']] = 'testerrorenumstring';
        } elseif ('number' == $enum_type) {
            $message[$detail['name']] = 9876;
        }
        //设置构造的异常数据
        $tmp = serialize($this->message_tmp);
        $tmp = unserialize($tmp);
        array_push($this->total_return_message, $tmp);

        //设置enum类型的属性不存在
        if (true == $detail['required']) {
            unset($message[$detail['name']]);
            if (is_array($message) && 0 == count($message)) {
                $message = null;
            }
            //设置构造的异常数据
            $tmp = serialize($this->message_tmp);
            $tmp = unserialize($tmp);
            array_push($this->total_return_message, $tmp);
        }

        return 0;
    }

    /**
     * 构造object类型的各种异常数据.
     *
     * @param $detail
     * @param $message
     * @param $old_message
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorObjectData($detail, &$message, $old_message, &$info)
    {
        //获取消息详情
        @chdir(FileConst::WWW);
        $code = $this->getMessageDetailByList($detail['type'], $message_detail, $info);
        if (0 !== $code) {
            return $code;
        }
        $code = $this->createErrorMessageData($message_detail, $message[$detail['name']], 'object', $info);
        if (0 !== $code) {
            return $code;
        }
        $message = serialize($old_message);
        $message = unserialize($message);

        //设置消息类型的字段不存在
        if (true == $detail['required']) {
            unset($message[$detail['name']]);
            if (is_array($message) && 0 == count($message)) {
                $message = null;
            }

            $tmp = serialize($this->message_tmp);
            $tmp = unserialize($tmp);
            array_push($this->total_return_message, $tmp);

            $message = serialize($old_message);
            $message = unserialize($message);
        }

        return 0;
    }

    /**
     * 构建正确的消息数据（供controller调用）.
     *
     * @param $message_name
     * @param $info
     * @param $amount
     * @param $id
     *
     * @return int
     *
     * @author Kinming
     */
    public function createCorrectMessage($message_name, &$info, $amount, $id)
    {
        //根据正则表达式生成测试数据
        self::$grammar = new Read('hoa://../lib/vendor/hoa/regex/Grammar.pp');
        self::$compiler = Llk::load(self::$grammar);

        //判断项目和消息是否存在
        $code = Tools::checkProjectAndMessage($this->project_name, $message_name, $info);
        if (0 != $code) {
            return $code;
        }

        //获取消息详情
        $code = $this->getMessageDetailByList($message_name, $message_detail, $info);
        if (0 != $code) {
            return $code;
        }

        //开始构造正确的消息数据
        $info = array();

        //redis认证
        $client = new Client(array(
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '6edc5a13',
        ));
        for ($i = 1; $i <= $amount; ++$i) {
            $code = $this->createCorrectMessageData($message_detail, $data, $info, false);
            if (0 !== $code) {
                return $code;
            }
            array_push($info, $data);

            //保存当前构造消息数量到redis
            $client->set($id, $i);
        }
        //设置过期时间为10分钟
        $client->expire($id, 600);

        return 0;
    }

    /**
     * 构建异常的消息数据（供controller调用）.
     *
     * @param $message_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function createErrorMessage($message_name, &$info)
    {
        //根据正则表达式生成测试数据
        self::$grammar = new Read('hoa://../lib/vendor/hoa/regex/Grammar.pp');
        self::$compiler = Llk::load(self::$grammar);

        //判断项目和消息是否存在
        $code = Tools::checkProjectAndMessage($this->project_name, $message_name, $info);
        if (0 != $code) {
            return $code;
        }

        //获取消息详情
        $code = $this->getMessageDetailByList($message_name, $message_detail, $info);
        if (0 != $code) {
            return $code;
        }

        //开始构造正确的消息数据
        $code = $this->createCorrectMessageData($message_detail, $correct_data, $info, true);
        if (0 !== $code) {
            return $code;
        }
        $this->total_create_data = $correct_data;
        //开始构造异常的消息数据

        $this->message_tmp = &$correct_data;
        if ('object[]' == $message_detail['type']) {
            $message_detail['type'] = substr($message_detail['type'], 0, -2);
            $code = $this->createErrorMessageData($message_detail, $correct_data[0], 'object', $info);
        } else {
            $code = $this->createErrorMessageData($message_detail, $correct_data, null, $info);
        }

        if (0 !== $code) {
            return $code;
        }
        $info = $this->total_return_message;

        return 0;
    }

    public function searchMessage($projectName, $data, &$list = array())
    {
        // 消息名称字段和关键字
        $field = 'message_name';
        $messageName = isset($data['message_name']) && is_string($data['message_name']) ? $data['message_name'] : '';
        if ('' === $messageName) {
            // 获取消息列表
            $code = (new self())->getMessageList($projectName, $list, $error);

            if (0 !== $code) {
                Log::error('获取消息列表失败', [$projectName, $list, $error]);

                return ReturnCode::ERROR;
            }

            return 0;
        }

        // 进入消息列表所在路径
        chdir(FileConst::USERNAME_PATH);
        if (!is_dir($projectName)) {
            Log::error('没有该项目', [getcwd(), $projectName]);

            return ReturnCode::ERROR;
        }
        chdir($projectName);
        chdir(FileConst::CONFIG);
        if (!file_exists(FileConst::MESSAGE_INFO_YAML)) {
            Log::error('没有消息列表文件', [getcwd()]);

            return ReturnCode::ERROR;
        }

        // 解析
        $parsed = @yaml_parse_file(FileConst::MESSAGE_INFO_YAML, -1);
        if (!$parsed) {
            Log::error('解析消息列表文件失败', [$parsed]);

            return ReturnCode::ERROR;
        }
        // 取出消息列表的消息名称
        $messageNameList = array();
        foreach ($parsed as $k => $value) {
            $messageNameList[] = $parsed[$k]['message_name'];
        }

        // 匹配
        $code = (new InterfaceDao())->matchAndSearchName($messageNameList, $messageName, $totalMachName);
        if (0 !== $code) {
            Log::error('匹配失败', [$messageNameList, $messageName, $totalMachName]);

            return ReturnCode::ERROR;
        }

        // 搜索
        $code = (new InterfaceDao())->searchName($totalMachName, $list, $parsed, $field);
        if (0 !== $code) {
            Log::error('搜索失败', [$totalMachName, $list, $parsed, $field]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 导出消息数据到文件.
     *
     * @param $data
     *
     * @return int
     *
     * @author Kinming
     */
    public function exportMessageDataToFile($data)
    {
        //导出到文件
        $this->downFile('auto_test_data.json', $data);

        return 0;
    }

    /**
     * 导出文件.
     *
     * @param $fileName
     * @param $info
     *
     * @author Kinming
     */
    public function downFile($fileName, $info)
    {
        $filepath = 'bootstrap.php';

        header('Content-Description: File Transfer');

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($fileName));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.filesize($filepath));

        \Flight::json($info);
    }

    /**
     * 判断正则表达式是否正确.
     *
     * @param $regex
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function checkRegex($regex, &$info)
    {
        try {
            //解析正则表达式
            self::$grammar = new Read('hoa://../lib/vendor/hoa/regex/Grammar.pp');
            self::$compiler = Llk::load(self::$grammar);
            self::$compiler->parse($regex);

            return 0;
        } catch (\Exception $exception) {
            $info = '正则表达式错误';
            Log::error($info, [$regex, $exception->getMessage(), $exception->getTraceAsString()]);

            return ReturnCode::ERROR;
        }
    }
}
