<?php

namespace dao;

use library\FileConst;
use common\library\Log;
use common\library\ReturnCode;

/**
 * Class ProjectDao.
 *
 * @author Kinming
 */
class ProjectDao
{
    /**
     * 写入项目列表文件.
     *
     * @param $array
     *
     * @return int
     *
     * @author Kinming
     */
    public function startWriteFile($array)
    {
        $yaml = yaml_emit($array);

        $rt = file_put_contents(FileConst::PROJECT_INFO_YAML, $yaml, FILE_APPEND);
        if (false === $rt) {
            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 解析项目列表文件为数组.
     *
     * @param $total
     *
     * @return int
     *
     * @author Kinming
     */
    public function parseYamlToArray(&$total)
    {
        chdir(FileConst::BASE_FILE_PATH);

        //将yaml文件解析成数组
        $total = @yaml_parse_file(FileConst::PROJECT_INFO_YAML, -1);
        if (!$total) {
            $total = [];
        }

        return 0;
    }

    /**
     * 修改项目列表文件.
     *
     * @param $i
     * @param $total
     * @param $data
     *
     * @return int
     *
     * @author Kinming
     */
    public function updateProjectYaml($i, $total, $data)
    {
        $oldyaml = yaml_emit($total[$i]);

        $origin_str = file_get_contents('project_info.yaml');
        if (false === $origin_str) {
            Log::error('文件获取失败'.": $origin_str");

            return ReturnCode::ERROR;
        }
        $yaml = yaml_emit($data);

        $update_str = str_replace($oldyaml, $yaml, $origin_str);
        $put = file_put_contents('project_info.yaml', $update_str);
        if (!$put) {
            Log::error('文件写入失败', [$update_str]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

    /**
     * 获取项目最新版本.
     *
     * @param $info
     *
     * @return int
     *
     * @author Tree
     */
    public function getProLastVersion(&$info)
    {
        // 进入项目目录
        chdir(FileConst::USERNAME);
        exec('ls', $pro, $code);
        if (0 !== $code) {
            Log::error('没有项目', [$pro]);

            return ReturnCode::ERROR;
        }

        // 获取每个项目的最新版本号
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

            chdir(FileConst::RETURN_PATH);
        }

        return 0;
    }

    /**
     * 删除项目列表文件中对应的项目记录.
     *
     * @param $project_name
     * @param $info
     *
     * @return int
     *
     * @author Kinming
     */
    public function deleteProject($project_name, &$info)
    {
        chdir(FileConst::RETURN_PATH);

        if (!file_exists(FileConst::PROJECT_INFO_YAML)) {
            $info = 'project_info.yaml文件不存在';
            Log::error($info, [getcwd()]);

            return ReturnCode::ERROR;
        }
        //将yaml文件解析成数组
        $list = @yaml_parse_file(FileConst::PROJECT_INFO_YAML, -1);
        if (!$list) {
            $list = [];
        }

        $count = count($list);
        for ($i = 0; $i < $count; ++$i) {
            if ($list[$i]['name'] == $project_name) {
                $oldyaml = yaml_emit($list[$i]);
                $origin_str = file_get_contents(FileConst::PROJECT_INFO_YAML);
                if (false === $origin_str) {
                    $info = '文件获取失败';
                    Log::error($info, [getcwd()]);

                    return ReturnCode::ERROR;
                }

                $update_str = str_replace($oldyaml, '', $origin_str);
                file_put_contents(FileConst::PROJECT_INFO_YAML, $update_str);

                break;
            }
        }

        if ($i == $count) {
            $info = '该项目列表不存在';
            Log::error($info, [$list]);

            return ReturnCode::ERROR;
        }

        return 0;
    }

//
//    /**
//     * 匹配项目
//     * @param   $projectName
//     * @param   $projectNameList
//     * @param   $info
//     * @param   $total
//     * @return  int
//     * @auth    Tree
//     */
//    public function matchProjectName($projectName, $projectNameList, &$info=array(), $total){
//        # 匹配以关键字开头的项目
//        $firstProjectName = preg_grep("/^$projectName/", $projectNameList);
//        # 去除已经匹配到的项目
//        foreach($firstProjectName as $k => $value){
//            unset($projectNameList[$k]);
//        }
//        $firstProjectName = array_values($firstProjectName);
//        $projectNameList = array_values($projectNameList);
//
//        # 匹配包含关键字的项目
//        $secondProjectName = preg_grep("/.+$projectName/", $projectNameList);
//        $secondProjectName = array_values($secondProjectName);
//
//        # 排序
//        $totalProjectName = array_merge($firstProjectName, $secondProjectName);
//        if(count($totalProjectName) === 0){
//            Log::error("无匹配的项目");
//            return ReturnCode::ERROR;
//        }
//
//        # 搜索
//        $sureCount = count($totalProjectName);
//        $totalCount = count($total);
//
//        for($i=0; $i<$sureCount; $i++){
//            for($k=0; $k<$totalCount; $k++){
//                if($totalProjectName[$i] === $total[$k]['name']){
//                    $info[] = $total[$k];
//                }
//            }
//        }
//
//        return 0;
//    }
}
