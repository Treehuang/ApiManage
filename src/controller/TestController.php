<?php

namespace controller;

use library\FileConst;
use common\library\Parameter;
use common\library\Result;
use Hoa\Compiler\Llk\Llk;
use Hoa\Exception\Exception;
use Hoa\File\Read;
use Hoa\Math\Sampler\Random;
use Hoa\Regex\Visitor\Isotropic;

//require_once '../lib/vendor/hoa/regex/Grammar.pp';

class TestController
{
    public static function test1()
    {
        Result::Success();
//        //exec("mkdir ../../config");
//
//        //exec("mkdir ../../config/username");
//
//        chdir(FileConst::USERNAME_PATH);
//
//        //chdir("../../test2");
//
//        //throw new RuntimeException('Division by zero.');
//
//        //$repo = GitRepository::cloneRepository('https://github.com/zjm138238/MyProject.git');
//
////exec("mkdir test", $rs, $status);
//
////echo $rs.",  ".$status."\n";
//
//        $info = array(
//            "data" => "test1成功接收"
//        );
//
//        try {
//            //$repo = GitRepository::cloneRepository('https://github.com/zjm138238/MyProject.git');
//            //throw new Exception("Some error message");
//            exec("git clone https://github.com/zjm138238/MyProject.git", $rs, $code);
//            $info["rs"] = $rs;
//            $info["code"] = $code;
//
//            if($code != 0) {
//                if(count($rs) > 0) {
//                    $info["error"] = "项目路径错误";
//                } else {
//                    $info["error"] = "项目已存在";
//                }
//            }
//        } catch(Exception $e) {
//            $info["error2"] = $e->getMessage();
//        }
//
//        //Log::info("branchlist: ", $repo->getBranches());
//
//        Result::Success($info);
        //return "123";
    }

    public static function test2()
    {
        $info = array(
            'data' => '成功接收',
        );

        Result::Success($info);
        //return "123";
    }

    public static function test_branch()
    {
        $info = array(
            'data' => '成功接收',
        );

        chdir('../../test/MyProject');
        exec('git branch', $rs, $code);
        $info['rs'] = $rs;
        $info['code'] = $code;

        foreach ($info['rs'] as &$branch) {
            $branch = str_replace('* ', '', $branch);
            $branch = str_replace(' ', '', $branch);
        }
        //$info["branch"] = ;

        Result::Success($info);
        //return "123";
    }

    public static function test_taglist()
    {
        $info = array(
            'data' => '成功接收',
        );

        chdir('../../config/username/MyProject');
        exec('git tag', $rs, $code);
        $info['rs'] = $rs;
        $info['code'] = $code;

        Result::Success($info);
    }

    public static function test3()
    {
        // 参数规则
        $schema = array(
            'array1' => '@str',
            'array2' => '@arr',
        );

        // 获取请求对象
        $request = \Flight::request();

        // 参数校验与提取
        $data = Parameter::Load($request->data->getData(), $schema);

        $data['array3'] = null;

        print_r($data);
        print_r($request->data->getData());

        //Result::Success($data);
    }

    public static function test4()
    {
        $data = array(
            'invoice' => 34843,
            'date' => '2001-01-23',
            'product' => array(
                array(
                    'sku' => 'BL394D',
                    'quantity' => 4,
                    'description' => 'Basketball',
                    'price' => 450,
                ),
                array(
                    'sku' => 'BL4438H',
                    'quantity' => 1,
                    'description' => 'Super Hoop',
                    'price' => 2392,
                ),
            ),
            'tax' => 251.42,
            'total' => 4443.52,
            'comments' => 'Late afternoon is best. Backup contact is Nancy Billsmer @ 338-4338.',
        );

        chdir(FileConst::USERNAME_PATH);

        yaml_emit_file('a1.yaml', $data);

        $data2 = yaml_parse_file('a1.yaml');

        print_r($data2);
    }

    public static function testRegex()
    {
        //$regex = "[A-Za-z0-9]"."{".$minLength.",".$maxLength."}";
        //$regex = "^13[123569]{1}\d{8}|15[1235689]\d{8}|188\d{8}$";
        $regex = '\\d{4}\\-(0\\d|1[0-2])\\-([0-2]\\d|3[01])(([01]\\d|2[0-3])\\:[0-5]\\d\\:[0-5]\\d)?';
        $grammar = new Read('hoa://../lib/vendor/hoa/regex/Grammar.pp');
        $compiler = Llk::load($grammar);
        try {
            $ast = $compiler->parse($regex);
            $generator = new Isotropic(new Random());
            print_r($generator->visit($ast));
        } catch (Exception $e) {
            print_r('123');
            exit;
        }
    }

    public static function downFile()
    {
        $filepath = 'bootstrap.php';

        header('Content-Description: File Transfer');

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename('test.txt'));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.filesize($filepath));
        //readfile($filepath);

        \Flight::json(123);
    }
}
