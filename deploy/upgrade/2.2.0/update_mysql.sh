#!/bin/bash
#读取配置文件
source ./update.config
#构建mongodb连接字符串
mysql="${MYSQL_HOME}/bin/mysql -h${MYSQL_IP} -P${MYSQL_PORT} -D${MYSQL_DB}"
if [ "${MYSQL_USER}" -a "${MYSQL_PASSWORD}" ]; then
    mysql="${mysql} -u${MYSQL_USER} -p${MYSQL_PASSWORD}"
fi

${mysql} <./t_trial.sql
