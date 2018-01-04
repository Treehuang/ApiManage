#!/bin/bash

# 由于升级CMDB内容安全性控制，所欲需要割接权限控制点
# 因为添加了新的核心对象"用户组", 所以需要升级核心对象

# 根目录
BASE_PATH=`dirname $0`/../..
PHP_HOME='/usr/local/easyops/php'
TOOL_PATH='/usr/local/easyops/cmdb/src/tools'

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/easyops/ens_client/sdk

echo "upgrading permissions ..."

# 升级核心对象-因为新增加了核心对象"用户组"
${PHP_HOME}/bin/php ${TOOL_PATH}/upgrade_objects.php all

# 升级权限控制点
${PHP_HOME}/bin/php ${TOOL_PATH}/upgrade_permissions.php -org all

