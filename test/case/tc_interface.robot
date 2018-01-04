*** Settings ***
Documentation  接口特性模块相关接口  测试用例
Resource  ../resource/host_env.robot
Force Tags          MyTag


*** Variables ***
${projectName}          auto_test_project
${project_url}          http://treehuang:treehuang@git.easyops.local/treehuang/Tree3.git
${errorProjectName}     1
${serviceName}          auto_test_service
${protocol}             http
${errorserviceName}     T
${interfaceName}        One1
${newInterfaceName}     Three1
${errorInterfaceName}   Two1
${endpoint}             {"method":"Get","uri":"baidu"}
${timeout}              3
${request}              {"message":"auto_test_message", "stream":"false"}
${error_response}       {"message":"auto_test_message", "stream":"false"}
${response}             {"message":"auto_test_message_update", "stream":"true"}
${errors}               []
${updateInterface}      {"interfaceName":"One","endpoint":{"method":"Post","uri":"baidu"},"timeout":1,"request":{"message":"auto_test_message", "stream":"false"},"response":{"message":"auto_test_message_update", "stream":"true"},"errors":[]}
${message}              {"version":1,"name":"auto_test_message","type":"object","fields":[{"name":"string1","type":"string","enum":false,"show_type":"string","show_detail":false,"comment":"属性说明","default":"test","required":"true","check":{"minLength":1,"maxLength":20,"pattern": "[A-Za-z0-9]"}}]}
${message_update}       {"version":1,"name":"auto_test_message_update","type":"object","fields":[{"name":"number1","type":"number","enum":false,"show_type":"number","show_detail":false,"default":0,"required":true,"comment":"属性说明","check":{"format":"int32","min":0,"max":999}}]}

*** Test Cases ***

test add interface 01
    [Documentation]         新增接口成功  I03.1
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}

    [Teardown]  test delete project should ok  ${projectName}

test add interface 02
    [Documentation]          新增接口失败  (数据库记录已存在)  I03.a
    [Tags]                   Interface
    ${ret} =     evaluate    ${request}
    ${ret2} =    evaluate    ${response}
    ${ret3} =    evaluate    ${errors}
    ${ret4} =    evaluate    ${message}
    ${ret5} =    evaluate    ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test add interface should fail  ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint} ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test add interface 03
    [Documentation]         新增接口失败
    [Tags]                  Interface
    ${ret} =     evaluate    ${request}
    ${ret2} =    evaluate    ${error_response}
    ${ret3} =    evaluate    ${errors}
    ${ret4} =    evaluate    ${message}
    ${ret5} =    evaluate    ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    ${ret6} =  test add interface should fail  ${projectName}  ${serviceName}  ${newInterfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test check interface name 01
    [Documentation]         检查接口名是否存在-成功
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint} ${ret}  ${ret2}  ${timeout}  ${ret3}
    test check interface name should ok  ${projectName}  ${serviceName}  ${errorInterfaceName}

    [Teardown]  test delete project should ok  ${projectName}

test check interface name 02
    [Documentation]         检查接口名是否存在-失败
    [Tags]                   Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test check interface name should fail  ${projectName}  ${serviceName}  ${interfaceName}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test get interfacelist 01
    [Documentation]         查询接口列表成功    I02.1
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    test get interfaceList should ok      ${projectName}  ${serviceName}

    [Teardown]  test delete project should ok  ${projectName}

test get interfacelist 02
    [Documentation]         查询接口列表失败    (数据库记录不存在)  I02.f
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test get interfaceList should fail    ${errorProjectName}  ${serviceName}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test get interfacelist 03
    [Documentation]         查询接口列表失败    (数据库记录不存在)    I02.f
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test get interfaceList should fail   ${projectName}    ${errorserviceName}
    ${code} =  evaluate     ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test get interface detail 01
    [Documentation]         查询接口详情成功    I01.1
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    test get interface detail should ok  ${projectName}  ${serviceName}  ${interfaceName}

    [Teardown]  test delete project should ok  ${projectName}

test get interface detail 02
    [Documentation]         查询接口详情失败    (数据库记录不存在)  I01.c
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test get interface detail should fail   ${errorProjectName}  ${serviceName}  ${interfaceName}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test search interfaceName 01
    [Documentation]         搜索接口成功      I02.1
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    test search interfaceName should ok  ${projectName}  ${serviceName}  ${interfaceName}

    [Teardown]  test delete project should ok  ${projectName}

test search interfaceName 02
    [Documentation]         搜索接口失败      (数据库记录不存在)  I02.f
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret2} =  test search interfaceName should fail   ${projectName}  ${serviceName}  ${errorInterfaceName}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test update interface 01
    [Documentation]         修改接口成功      I04.1
    [Tags]                  Interface
    ${ret} =    evaluate    ${updateInterface}
    ${ret1} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret1}  ${ret2}  ${timeout}  ${ret3}
    test update interface should ok      ${projectName}  ${serviceName}  ${interfaceName}  ${ret}

    [Teardown]  test delete project should ok  ${projectName}

test update interface 02
    [Documentation]         修改接口失败      (数据库记录不存在)  I04.a
    [Tags]                  Interface
    ${ret} =    evaluate    ${updateInterface}
    ${ret1} =    evaluate   ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret1}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test update interface should fail       ${errorProjectName}  ${serviceName}  ${interfaceName}  ${ret}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test delete interface 01
    [Documentation]         删除接口成功      I05.1
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${interfaceName}  ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    test delete interface should ok       ${projectName}  ${serviceName}  ${interfaceName}

    [Teardown]  test delete project should ok  ${projectName}

test delete interface 02
    [Documentation]         删除接口失败      (数据库记录不存在)  I05.a
    [Tags]                  Interface
    ${ret} =    evaluate    ${request}
    ${ret2} =   evaluate    ${response}
    ${ret3} =   evaluate    ${errors}
    ${ret4} =    evaluate   ${message}
    ${ret5} =    evaluate   ${message_update}
    test add project should ok     ${project_url}  ${projectName}
    test add service should ok     ${projectName}  ${serviceName}  ${protocol}
    test add message should ok     ${projectName}  ${ret4}
    test add message should ok     ${projectName}  ${ret5}
    test add interface should ok   ${projectName}  ${serviceName}  ${newInterfaceName}   ${endpoint}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    ${ret6} =  test delete interface should fail     ${projectName}  ${serviceName}  ${interfaceName}
    ${code} =   evaluate    ${ret6}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}