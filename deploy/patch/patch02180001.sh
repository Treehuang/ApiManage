#!/bin/bash

# 修复CLUSTER 数据中缺失 type 字段的BUG

# 根目录
BASE_PATH=`dirname $0`/../..
PHP_HOME='/usr/local/easyops/php'
TOOL_PATH='/usr/local/easyops/cmdb/src/tools'


echo "refreshing table t_instance_APP"
${PHP_HOME}/bin/php ${TOOL_PATH}/refresh_instances.php --object APP --org all