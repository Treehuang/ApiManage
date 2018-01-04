#!/bin/bash
#读取配置文件
source ./update.config
#构建mongodb连接字符串
mongo="${MONGODB_HOME}/bin/mongo --quiet --host ${MONGODB_IP} --port ${MONGODB_PORT}"
if [ "${MONGODB_USER}" -a "${MONGODB_PASSWORD}" ]; then
    mongo="${mongo} -u ${MONGODB_USER} -p ${MONGODB_PASSWORD}"
fi

#hotfix_issue56
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"INTERFACE","attrList.id":"serivce"},{$set:{"attrList.$.id":"service"}})'
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
while read database
do
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"INTERFACE","attrList.id":"serivce"},{$set:{"attrList.$.id":"service"}})'
	${mongo} ${database} --eval 'db.t_instance_INTERFACE.update({},{$rename:{"serivce":"service"}},{multi:true})'
done

#hotfix_issue59
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_email"},{$set:{"attrList.$.id":"user_email"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_tel"},{$set:{"attrList.$.id":"user_tel"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_type"},{$set:{"attrList.$.id":"user_type"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_active"},{$set:{"attrList.$.id":"user_active"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_memo"},{$set:{"attrList.$.id":"user_memo"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER"},{$pull:{"attrList":{"id":"tf_user_org"}}})'
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
while read database
do
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_email"},{$set:{"attrList.$.id":"user_email"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_email":"user_email"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_tel"},{$set:{"attrList.$.id":"user_tel"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_tel":"user_tel"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_type"},{$set:{"attrList.$.id":"user_type"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_type":"user_type"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_active"},{$set:{"attrList.$.id":"user_active"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_active":"user_active"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_memo"},{$set:{"attrList.$.id":"user_memo"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_memo":"user_memo"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER"},{$pull:{"attrList":{"id":"tf_user_org"}}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$unset:{"tf_user_org":1}},{multi:true});'
done

#hotfix_issue64
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"CLUSTER","attrList.id":"name"},{$set:{"attrList.$.unique":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_instance_CLUSTER.dropIndex("name_1")'
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
while read database
do
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"CLUSTER","attrList.id":"name"},{$set:{"attrList.$.unique":"false"}})'
	${mongo} ${database} --eval 'db.t_instance_CLUSTER.dropIndex("name_1")'
done

#hotfix_issue65
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"BUSINESS"},{$set:{"delete":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"APP"},{$set:{"delete":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"CLUSTER"},{$set:{"delete":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"HOST"},{$set:{"delete":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"HOST_MAC"},{$set:{"delete":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER"},{$set:{"delete":"false"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"INTERFACE"},{$set:{"delete":"false"}})'
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
while read database
do
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"BUSINESS"},{$set:{"delete":"false"}})'
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"APP"},{$set:{"delete":"false"}})'
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"CLUSTER"},{$set:{"delete":"false"}})'
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$set:{"delete":"false"}})'
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST_MAC"},{$set:{"delete":"false"}})'
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER"},{$set:{"delete":"false"}})'
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"INTERFACE"},{$set:{"delete":"false"}})'
done

#执行更新
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_email"},{$set:{"attrList.$.id":"user_email"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_tel"},{$set:{"attrList.$.id":"user_tel"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_type"},{$set:{"attrList.$.id":"user_type"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_active"},{$set:{"attrList.$.id":"user_active"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_memo"},{$set:{"attrList.$.id":"user_memo"}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"USER"},{$pull:{"attrList":{"id":"tf_user_org"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"APP"},{$pull:{"attrList":{"id":"createtime"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"BUSINESS"},{$pull:{"attrList":{"id":"createtime"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"CLUSTER"},{$pull:{"attrList":{"id":"createtime"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"CLUSTER"},{$pull:{"attrList":{"id":"modtime"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"CLUSTER"},{$pull:{"attrList":{"id":"creator"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"HOST"},{$pull:{"attrList":{"id":"createtime"}}})'
${mongo} cmdb_saas_base --eval 'db.t_object.update({"objectId":"HOST"},{$pull:{"attrList":{"id":"modifytime"}}})'
#获取所有cmdb数据库名
echo 'show databases'|${mongo}|grep -E '^cmdb_org_[0-9]+'|awk '{print $1}'|
#遍历所有数据库
while read database
do
    echo "update database ${database}"
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_email"},{$set:{"attrList.$.id":"user_email"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_email":"user_email"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_tel"},{$set:{"attrList.$.id":"user_tel"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_tel":"user_tel"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_type"},{$set:{"attrList.$.id":"user_type"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_type":"user_type"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_active"},{$set:{"attrList.$.id":"user_active"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_active":"user_active"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER","attrList.id":"tf_user_memo"},{$set:{"attrList.$.id":"user_memo"}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$rename:{"tf_user_memo":"user_memo"}},{multi:true});'
	${mongo} ${database} --eval 'db.t_object.update({"objectId":"USER"},{$pull:{"attrList":{"id":"tf_user_org"}}})'
	${mongo} ${database} --eval 'db.t_instance_USER.update({},{$unset:{"tf_user_org":1}},{multi:true});'
    #删除APP的createtime属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"APP"},{$pull:{"attrList":{"id":"createtime"}}})'
	${mongo} ${database} --eval 'db.t_instance_APP.update({},{$unset:{"createtime":1}},{multi:true});'
    #删除BUSINESS的createtime属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"BUSINESS"},{$pull:{"attrList":{"id":"createtime"}}})'
	${mongo} ${database} --eval 'db.t_instance_BUSINESS.update({},{$unset:{"createtime":1}},{multi:true});'
    #删除CLUSTER的createtime属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"CLUSTER"},{$pull:{"attrList":{"id":"createtime"}}})'
	${mongo} ${database} --eval 'db.t_instance_CLUSTER.update({},{$unset:{"createtime":1}},{multi:true});'
    #删除CLUSTER的modtime属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"CLUSTER"},{$pull:{"attrList":{"id":"modtime"}}})'
	${mongo} ${database} --eval 'db.t_instance_CLUSTER.update({},{$unset:{"modtime":1}},{multi:true});'
    #删除CLUSTER的creator属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"CLUSTER"},{$pull:{"attrList":{"id":"creator"}}})'
	${mongo} ${database} --eval 'db.t_instance_CLUSTER.update({},{$unset:{"creator":1}},{multi:true});'
    #删除HOST的createtime属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$pull:{"attrList":{"id":"createtime"}}})'
	${mongo} ${database} --eval 'db.t_instance_HOST.update({},{$unset:{"createtime":1}},{multi:true});'
    #删除HOST的modifytime属性
    ${mongo} ${database} --eval 'db.t_object.update({"objectId":"HOST"},{$pull:{"attrList":{"id":"modifytime"}}})'
	${mongo} ${database} --eval 'db.t_instance_HOST.update({},{$unset:{"modifytime":1}},{multi:true});'
done
