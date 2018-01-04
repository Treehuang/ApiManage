#!/bin/bash
#当前执行绝对路径
CUR_PATH=`pwd`
#当前脚本所在目录绝对路径
ABS_PATH=`pwd`/`dirname $0`

#读取配置文件
source ${ABS_PATH}/update.config

#通过名字服务获取IP:PORT
SERVICE=(`${ABS_PATH}/../../get_service.sh ${MONGODB_SERVICE_NAME}`)
if [[ $? -ne 0 ]];then
    echo "ERROR: NameService Failed"
    exit 3
fi
#echo ${SERVICE[0]}
MONGODB_IP=${SERVICE[1]}
MONGODB_PORT=${SERVICE[2]}

#构建mongodb连接字符串
mongo="${MONGODB_HOME}/bin/mongo --quiet --host ${MONGODB_IP} --port ${MONGODB_PORT}"
if [ "${MONGODB_USER}" -a "${MONGODB_PASSWORD}" ]; then
    mongo="${mongo} -u ${MONGODB_USER} -p ${MONGODB_PASSWORD}"
fi

#执行更新
#获取所有cmdb数据库名
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
#遍历所有数据库
while read database
do
    echo "update database ${database}"
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST","attrList.id":"ip"},{$set:{"attrList.$.unique":"false"}})'
	${mongo} ${database} --eval 'db.t_instance_HOST.dropIndex("ip_1")'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$pull:{"attrList":{"id":"_mac"}}})'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$push:{"attrList":{"id":"_mac","name":"物理地址","required":"false","readonly":"false","unique":"false","value":{"type":"str"},"tag":["默认属性"],"custom":"false"}}})'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$pull:{"attrList":{"id":"_uuid"}}})'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$push:{"attrList":{"id":"_uuid","name":"uuid","required":"false","readonly":"false","unique":"false","value":{"type":"str"},"tag":["默认属性"],"custom":"false"}}})'
done
