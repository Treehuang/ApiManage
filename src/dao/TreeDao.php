<?php
/**
 * Created by IntelliJ IDEA.
 * User: treehuang
 * Date: 2017/11/11
 * Time: 下午3:27.
 */

namespace dao;

use common\library\Log;

class TreeDao
{
//    public static function updateService($data){
//        # 解析yaml文件为数组
//        $total = yaml_parse_file('service_info.yaml', -1);
//        if(!$total){
//            Log::error("yaml解析文件失败");
//            return ReturnCode::ERROR;
//        }
//
//       foreach ($total as $item){
//            if($item['name'] === $data['name']){
//                $oldData = $item;
//            }
//       }
//
//        if(isset($oldData)) {
//            # 写入服务列表文件
//            $oldYaml = yaml_emit($oldData);
//            $newYaml = yaml_emit($data);
//            $origin_str = file_get_contents('service_info.yaml');
//            $update_str = str_replace($oldYaml, $newYaml, $origin_str);
//            $put = file_put_contents('service_info.yaml', $update_str);
//            if(!$put) {
//                Log::error("文件写入失败");
//                return ReturnCode::ERROR;
//            }
//        }
//
//        return 0;
//    }
}
