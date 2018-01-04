#!/bin/bash

BASE_PATH=`pwd`
PATCH_PATH=${BASE_PATH}/deploy/patch
PATCH_LOG=${BASE_PATH}/.patch.log

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/easyops/ens_client/sdk

if [[ ! -f ${PATCH_LOG} ]]; then
    echo "ERROR: .patch.log not exist!"
    exit 1;
fi

last=`tail -n 1 ${PATCH_LOG}`

if [ ! -d "${PATCH_PATH}" ]; then
  mkdir -p ${PATCH_PATH}
fi

for file in `ls -l ${BASE_PATH}/deploy/patch/patch*.sh`
do
    if [[ -f ${file} && `basename ${file}` > ${last} ]]; then
        bash ${file}
        if [[ $? -eq 0 ]]; then
            echo `basename ${file}` succeed
            echo `basename ${file}` >> ${PATCH_LOG}
        else
            echo `basename ${file}` failed
            exit 2;
        fi
    fi
done