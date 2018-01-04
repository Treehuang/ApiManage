*** Settings ***
Library  ../library/SpecialInterfaceLib.py  ${ip}    ${port}    ${host}    ${user}    ${org}
Library         json

*** Variables ***
${ip}    192.168.100.123
#${port}  9999
${port}  8888
${host}  apimanage.easyops-only.com
${user}  easyops
${org}   8888
