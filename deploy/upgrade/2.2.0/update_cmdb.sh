#!/bin/bash
#读取配置文件
source ./update.config
#备份配置文件
cp ${CMDB_CONFIG_PATH}/config.php ${CMDB_CONFIG_PATH}/config"`date +%Y%m%d`".php
#追加配置
echo '$config["trial"]["mailTo"] = "'${CMDB_TRIAL_MAILTO}'";' >> ${CMDB_CONFIG_PATH}/config.php
echo '$config["agent"]["secret_key"] = "'${CMDB_AGENT_SECRETKEY}'";' >> ${CMDB_CONFIG_PATH}/config.php
echo '$config["mongodb"]["replicaSet"] = "'${CMDB_MONGODB_REPLICASET}'";' >> ${CMDB_CONFIG_PATH}/config.php
#Agent公私钥加密
cd ${TOOLS_PATH}/agent
${PHP_HOME}/bin/php ./encrAgent.php all