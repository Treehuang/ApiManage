#!/bin/bash

# 根目录
BASE_PATH=`dirname $0`/../..
PHP_HOME='/usr/local/easyops/php'
export LD_LIBRARY_PATH=${LD_LIBRARY_PATH}:/usr/local/easyops/ens_client/sdk

${PHP_HOME}/bin/php ${BASE_PATH}/src/tools/create_object_permission.php
