#!/bin/bash
# Name    : install_postscript.py
# Date    : 2016.03.28
# Func    : 安装后脚本
# Note    : 注意：当前路径为应用部署文件夹

#############################################################
# 用户自定义
app_folder="api_manage"                 # 项目名称
install_base="/usr/local/easyops"       # 安装根目录
data_base="/data/easyops"               # 日志/数据根目录


#############################################################

BASE_PATH=`pwd`

# 执行准备
source /usr/local/easyops/deploy_init/env.config

# LC_ALL 为mongodb依赖
export LC_ALL=C

# 加载默认配置
source ./env.config


# 名字服务
getIpPort()
{
   name=$1
   if [ -f /usr/local/easyops/ens_client/tools/get_service.py ];then
       ret=`cd /usr/local/easyops/ens_client/tools;./get_service.py ${name}`
       session_id=`echo ${ret}|awk '{print $1}'`
       if [[ ${session_id} == *[!0-9]* ]];then
           echo "name service error"
           exit 1
       fi
       ip=`echo ${ret}|awk '{print $2}'`
       port=`echo ${ret}|awk '{print $3}'`
   else
       echo  "name service error"
       exit 2
   fi
   echo "${ip} ${port}"
}

# 创建.patch.log
touch ${BASE_PATH}/.patch.log
echo 'patch02250000.sh' > ${BASE_PATH}/.patch.log

exit 0



