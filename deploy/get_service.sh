#!/bin/bash

if [ -f /usr/local/easyops/ens_client/tools/get_service.py ];then
    ret=`cd /usr/local/easyops/ens_client/tools;./get_service.py $1`
    session_id=`echo ${ret}|awk '{print $1}'`
    if [[ ${session_id} == *[!0-9]* ]];then
        echo "ERROR: NameService Failed"
        exit 1
    fi
    ip=`echo ${ret}|awk '{print $2}'`
    port=`echo ${ret}|awk '{print $3}'`
else
    echo  "ERROR: NameService Not Available"
    exit 2
fi
echo ${session_id} ${ip} ${port}
