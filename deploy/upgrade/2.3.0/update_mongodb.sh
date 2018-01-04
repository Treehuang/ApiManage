#!/bin/bash
#读取配置文件
source ./update.config
#构建mongodb连接字符串
mongo="${MONGODB_HOME}/bin/mongo --quiet --host ${MONGODB_IP} --port ${MONGODB_PORT}"
if [ "${MONGODB_USER}" -a "${MONGODB_PASSWORD}" ]; then
    mongo="${mongo} -u ${MONGODB_USER} -p ${MONGODB_PASSWORD}"
fi

#执行更新
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"user_email"},{$set:{"attrList.$.readonly":"true"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"user_tel"},{$set:{"attrList.$.readonly":"true"}})'
#获取所有cmdb数据库名
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
#遍历所有数据库
while read database
do
    echo "update database ${database}"
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"user_email"},{$set:{"attrList.$.readonly":"true"}})'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"user_tel"},{$set:{"attrList.$.readonly":"true"}})'
done
