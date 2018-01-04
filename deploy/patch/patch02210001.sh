#!/bin/bash

#用户登录数据割接

PHP_HOME='/usr/local/easyops/php'
PATCH_PATH='/usr/local/easyops/cmdb/patch'

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/easyops/ens_client/sdk

${PHP_HOME}/bin/php ${PATCH_PATH}/patch02210001.php
${PHP_HOME}/bin/php ${PATCH_PATH}/patch02210002.php

