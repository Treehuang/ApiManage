#!/bin/bash

BASE_PATH=`pwd`
PATCH_PATH=${BASE_PATH}/deploy/patch
PATCH_LOG=${BASE_PATH}/.patch.log

# 判断.patch.log是否存在
if [[ ! -f ${PATCH_LOG} ]]; then
    echo 'patch02100000.sh' > ${BASE_PATH}/.patch.log
fi
