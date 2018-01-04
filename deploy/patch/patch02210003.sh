#!/bin/bash

# CLUSTER资源模型集群类型增加 "预发布" 枚举

PHP_HOME='/usr/local/easyops/php'
PATCH_PATH='/usr/local/easyops/cmdb/patch'
TOOL_PATH='/usr/local/easyops/cmdb/src/tools'

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/easyops/ens_client/sdk

${PHP_HOME}/bin/php ${TOOL_PATH}/upgrade_objects.php all