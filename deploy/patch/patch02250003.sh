#!/bin/bash

# 修改apikey_read 权限为 apikey_manage权限

PHP_HOME='/usr/local/easyops/php'
PATCH_PATH='/usr/local/easyops/cmdb/patch'
TOOL_PATH='/usr/local/easyops/cmdb/src/tools'

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/easyops/ens_client/sdk

${PHP_HOME}/bin/php ${TOOL_PATH}/sync_permissions.php all
