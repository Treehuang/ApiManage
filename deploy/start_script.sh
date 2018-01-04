#!/bin/bash
# Name    : start_script.py
# Date    : 2016.03.28
# Func    : 启动脚本
# Note    : 注意：当前路径为应用部署文件夹

#############################################################
# 用户自定义
app_folder="api_manage"                 # 项目根目录
process_name=""                   # 进程名

install_base="/usr/local/easyops" # 安装根目录
data_base="/data/easyops"         # 日志/数据根目录

# 启动命令
start_cmd=""
# 基于easy_framework的启动方式
# start_cmd="/usr/local/easyops/easy_framework/easy_service.py conf/client.yaml start"

# 注册的服务名，多个用空格隔开
ens_names="logic.apimanage apimanage.easyops-only.com"
ens_port=8888

#############################################################
# 执行准备
source /usr/local/easyops/deploy_init/env.config

install_path="${install_base}/${app_folder}/"
if [[ ! -d ${install_path} ]]; then
    echo "${install_path} is not exist"
    exit 1
fi

# 日志目录
log_path="${data_base}/${app_folder}/log"
mkdir -p ${log_path}
cd ${install_path} && ln -snf ${log_path} src/log

# 数据目录
# data_path="${data_base}/${app_folder}/data"
# mkdir -p ${data_path}
# cd ${install_path} && ln -snf ${data_path} src/data

# 注册名字服务，注册失败直接退出，不启动程序。确保进程启动时候名字是有注册的
for name in $(echo $ens_names)
do
    /usr/local/easyops/ens_client/tools/register_service.py $name $ens_port
    if [[ $? -ne 0 ]];then
        echo "register name error, exit"
        exit 255
    fi
done

# # 启动程序
# echo "start by cmd: ${start_cmd}"
# cd ${install_path} && eval $start_cmd
# if [[ $? -ne 0 ]];then
#     echo "start error, exit"
#     exit 1
# fi

