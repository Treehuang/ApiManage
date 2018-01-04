<?php

namespace controller;

use library\FileConst;
use common\library\Parameter;
use common\library\Result;
use common\library\ReturnCode;
use Exception;
use Flight;

class TreeController
{
    public static function test()
    {
        $req = Flight::request();

        $valid = array('url' => '@str');
        $params = Parameter::Load($req->query, $valid);

        $url = $params['url'];

        exec('mkdir ../../MyTest');
        chdir('../../MyTest');

        try {
            exec("git clone $url", $rs, $code);
            $info['rs'] = $rs;
            $info['code'] = $code;
        } catch (Exception $e) {
            $info['error2'] = $e->getMessage();
        }

        Result::Success($info);
    }

    /*
     * @name     显示项目名字列表
     * @url      GET /ProjectList
     *
     * @doc
     * @keyword  get Project
     *
     * @author   TreeHuang
     *
     * @response{
     *      "code": "200",
     *      "error": "成功"，
     *      "message": "Success",
     *      "data":[
     *          {
     *              "number": "#F077",
     *              "name": "git-php",
     *              "leader": "TreeHuang",
     *              "url": "https://github.com/czproject/git-php.git"
     *              "createTime": "2017-11-08 20:08:30",
     *              "version": "v4.0"
     *          },
     *          {
     *              "number" : "#F088",
     *              "name": "MyProject",
     *              "leader": "kinMing",
     *              "url": "https://github.com/zjm138238/MyProject.git"
     *              "createTime": "2017-11-08 14:37:01",
     *              "version": "v1.0"
     *          }
     *      ]
     *  }
     * @return json     成功返回200，失败返回错误码
     */

    public static function GetProList()
    {
        chdir('../../config');

        $parsed = yaml_parse_file(FileConst::PROJECT_INFO_YAML, -1);

        $info = array();
        foreach ($parsed as $k => $item) {
            $info[$k]['number'] = $parsed[$k]['number'];
            $info[$k]['name'] = $parsed[$k]['name'];
            $info[$k]['url'] = $parsed[$k]['url'];
            $info[$k]['leader'] = $parsed[$k]['leader'];
            $info[$k]['createTime'] = $parsed[$k]['createTime'];
        }

        //    $rs = array();

        chdir('username');
        exec('ls', $pro, $code);

        for ($i = 0; $i < count($pro); ++$i) {
            chdir($pro[$i]);
            exec('git tag', $rs, $code);

            $v = 0;
            foreach ($rs as $k) {
                $v = $k;
            }

            for ($x = 0; $x < count($info); ++$x) {
                if ($info[$x]['name'] === $pro[$i]) {
                    $info[$x]['version'] = $v;
                }
            }

            chdir('../');
        }

        Result::Success($info);
    }

    public static function getBranchList($projectName)
    {
        $param = '';

        chdir('../../config/username');
        exec('ls', $pro, $code);

        for ($i = 0; $i < count($pro); ++$i) {
            if ($pro[$i] === $projectName) {
                $param = $pro[$i];
            }
        }

        if ($projectName !== $param) {
            Result::Error(ReturnCode::ERROR, '无此项目');
            exit;
        }

        chdir($param);
        exec('git branch', $bra, $code);

        $info = array();
        foreach ($bra as $k => $braName) {
            $info[$k]['number'] = $k + 1;
            // 去掉头两个字符
            //    $braName = substr($braName, 2);
            $info[$k]['branchName'] = $braName;
        }

        Result::Success($info);
    }

//    public static function updateService($projectName){
//        $request = \Flight::request();
//
//        $data = $request->data->getData();
//
//        $code = TreeLogic::updateService($projectName, $data);
//
//        if($code !== 0){
//            Result::Error($code, "修改服务失败");
//            exit;
//        }
//
//        Result::Success("修改服务成功");
//    }
}
