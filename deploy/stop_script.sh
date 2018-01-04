#!/bin/bash
# Name    : stop_script.py
# Date    : 2016.03.28
# Func    : 停止脚本
# Note    : 注意：当前路径为应用部署文件夹

#############################################################
# 注册的服务名，多个用空格隔开
ens_names="logic.apimanage apimanage.easyops-only.com"
ens_port=8888

#############################################################
# 注销名字服务，暂不注销名字
# for name in $(echo $ens_names)
# do
#     /usr/local/easyops/ens_client/tools/unregister_service.py $name $ens_port
#     if [[ $? -ne 0 ]];then
#         echo "unregister name error, exit"
#         exit 255
#     fi
# done

