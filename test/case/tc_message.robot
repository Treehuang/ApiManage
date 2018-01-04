*** Settings ***
Documentation   消息模块相关接口 测试用例
Resource  ../resource/host_env.robot

*** Variables ***

${project_name}             auto_test_project
${project_name_error}       auto_test_project_error
${message}                  {"version":1,"name":"auto_test_message","type":"object","fields":[{"name":"string_new","type":"string","enum":false,"show_type":"string","show_detail":false,"comment":"属性说明","default":"test","required":true,"check":{"minLength":1,"maxLength":20,"pattern":"[A-Za-z0-9]"}}],"unique_keys":[{"name":"key1","fields":["message10"]}]}
${message1}                 {"version":1,"name":"auto_test_message1","type":"object","fields":[{"name":"string_new","type":"string","enum":false,"show_type":"string","show_detail":false,"comment":"属性说明","default":"test","required":true,"check":{"minLength":1,"maxLength":20,"pattern":"[A-Za-z0-9]"}}],"unique_keys":[{"name":"key1","fields":["message10"]}]}
${message_update}           {"version":1,"name":"auto_test_message","type":"object","fields":[{"name":"string_update","type":"string","enum":false,"show_type":"string","show_detail":false,"comment":"属性说明","default":"test","required":true,"check":{"minLength":1,"maxLength":20,"pattern":"[A-Za-z0-9]"}}],"unique_keys":[{"name":"key1","fields":["message10"]}]}
${type}                     string
${type_error}               test
${project_name_error}       auto_test_project2
${message_name}             auto_test_message
${message_name_error}       auto_test_message_error
${message_name_update}      auto_test_message_update
${message_name_new}         auto_test_message2
${service_name}             auto_test_service
${interface_name}           auto_test_interface
${interface_name_error}     auto_test_interface_error
${url}                      git@git.easyops.local:kinming/test.git
${protocol}                 http
${request}                  {"message":"auto_test_message", "stream":"false"}
${response}                 {"message":"auto_test_message1", "stream":"true"}
${errors}                   []
${interface_url}            /createData
${timeout}                  3
${method}                   Get
${amount}                   2
${id}                       12345678910
${regex}                    \\d{4}\\-(0\\d|1[0-2])\\-([0-2]\\d|3[01])(([01]\\d|2[0-3])\\:[0-5]\\d\\:[0-5]\\d)?
${regex_error}              \\d{4}\\-(0\\d|1[0-2])-([0-2]\\d|3[01])(([01]\\d|2[0-3])\\:[0-5]\\d\\:[0-5]\\d)?
${export_message}           {"code":0,"error":"错误信息","message":"fail","data":{"message_name":"auto_test_message_update","fields":[{"name":"name1","type":"string","required":"true","comment":"属性说明","check":{"minLength":1,"maxLength":20,"pattern":"[A-Za-z0-9]"}},{"name":"name2","type":"string","required":"true","comment":"属性说明","check":{"minLength":1,"maxLength":20,"pattern":"[A-Za-z0-9]"}}]}}


*** Test Cases ***

test add message 01
    [Documentation]     新增消息成功 I03.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    [Teardown]        test delete project should ok        ${project_name}


test add message 02
    [Documentation]     新增消息失败（数据库记录已存在） I03.a
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    ${ret2} =   test add message should fail        ${project_name}   ${ret}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test search message 01
    [Documentation]    搜索消息成功  I02.1
    [Tags]             Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    test search message should ok  ${project_name}   ${message_name}

    [Teardown]        test delete project should ok        ${project_name}


test search message 02
    [Documentation]   搜索消息失败   I02.f
    [Tags]            Message

    test search message should fail  ${project_name}  ${message_name_error}


test get message type template 01
    [Documentation]     获取消息类型模板成功 I01.1
    [Tags]              Message
    test get message type template should ok        ${type}


test get message type template 02
    [Documentation]     获取消息类型模板失败 I01.c
    [Tags]              Message
    ${ret2} =   test get message type template should fail        ${type_error}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test get message list 01
    [Documentation]     获取消息列表成功 I02.5
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    test get message list should ok         ${project_name}

    [Teardown]        test delete project should ok        ${project_name}


test get message list 02
    [Documentation]     获取消息列表失败 I02.f
    [Tags]              Message
    ${ret2} =   test get message list should fail         ${project_name_error}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test check message name 01
    [Documentation]     判断消息名是否存在-不存在可用 I01.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    test check message name should ok         ${project_name}   ${message_name_new}

    [Teardown]        test delete project should ok        ${project_name}


test check message name 02
    [Documentation]     判断消息名是否存在-已存在不可用 I01.c
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    ${ret2} =   test check message name should fail         ${project_name}   ${message_name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test get message detail 01
    [Documentation]     获取消息详情成功 I01.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    test get message detail should ok         ${project_name}   ${message_name}

    [Teardown]        test delete project should ok        ${project_name}


test get message detail 02
    [Documentation]     获取消息详情失败 I01.c
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    ${ret2} =   test get message detail should fail         ${project_name}   ${message_name_new}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test create request and response message 01
    [Documentation]     构造消息数据成功 I01.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret4} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret4}
    ${ret1} =    json.loads    ${message1}
    test add message should ok        ${project_name}   ${ret1}
    test add service should ok         ${project_name}   ${service_name}     ${protocol}
    ${ret} =    json.loads    ${request}
    ${ret2} =   json.loads    ${response}
    ${ret3} =   json.loads    ${errors}
    test add interface should ok      ${project_name}  ${service_name}  ${interface_name}  ${method}  ${interface_url}  ${ret}  ${ret2}  ${timeout}  ${ret3}
    test create request and response message should ok         ${project_name}   ${service_name}     ${interface_name}

    [Teardown]        test delete project should ok        ${project_name}


test create request and response message 02
    [Documentation]     构造消息数据失败 I01.c
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    ${ret2} =   test create request and response message should fail         ${project_name}   ${service_name}     ${interface_name_error}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test create correct message 01
    [Documentation]     构造正常消息数据-成功 I01.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    test create correct message should ok         ${project_name}   ${message_name}     ${amount}   ${id}

    [Teardown]        test delete project should ok        ${project_name}


test create correct message 02
    [Documentation]     构造正常消息数据-失败（消息名不存在） I01.c
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    ${ret2} =   test create correct message should fail         ${project_name}   ${message_name_error}     ${amount}   ${id}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test create correct message 03
    [Documentation]     构造正常消息数据-失败（项目名不存在） I01.c
    [Tags]              Message
    ${ret2} =   test create correct message should fail         ${project_name_error}   ${message_name_error}     ${amount}     ${id}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test create error message 01
    [Documentation]     构造异常消息数据-成功 I01.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    test create error message should ok         ${project_name}   ${message_name}

    [Teardown]        test delete project should ok        ${project_name}


test create error message 02
    [Documentation]     构造异常消息数据-失败（消息名不存在） I01.c
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    ${ret2} =   test create error message should fail         ${project_name}   ${message_name_error}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test create error message 03
    [Documentation]     构造异常消息数据-失败（项目名不存在） I01.c
    [Tags]              Message
    ${ret2} =   test create error message should fail         ${project_name_error}   ${message_name_error}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test check regex 01
    [Documentation]     判断正则表达式是否正确-成功
    [Tags]              Message
    test check regex should ok         ${regex}


test check regex 02
    [Documentation]     判断正则表达式是否正确-失败
    [Tags]              Message
    test check regex should fail         ${regex_error}


test update message 01
    [Documentation]     修改消息成功 I04.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    ${ret} =    json.loads    ${message_update}
    test update message should ok         ${project_name}   ${message_name}   ${ret}

    [Teardown]        test delete project should ok        ${project_name}


test update message 02
    [Documentation]     修改消息失败 I04.a
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    ${ret} =    json.loads    ${message_update}
    ${ret2} =   test update message should fail         ${project_name}   ${message_name_new}   ${ret}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test delete message 01
    [Documentation]     删除消息成功 I05.1
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}
    ${ret} =    json.loads    ${message}
    test add message should ok        ${project_name}   ${ret}

    test delete message should ok         ${project_name}    ${message_name}

    [Teardown]        test delete project should ok        ${project_name}


test delete message 02
    [Documentation]     删除消息失败 I05.a
    [Tags]              Message
    test add project should ok     ${url}    ${project_name}

    ${ret2} =   test delete message should fail         ${project_name}   ${message_name_new}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test get current create message amount 01
    [Documentation]     获取当前构造数据的进度-成功
    [Tags]              Message
    test get current create message amount should ok        ${id}


test get current create message amount 02
    [Documentation]     获取当前构造数据的进度-失败
    [Tags]              Message
    test get current create message amount should fail        ${id}


test export message data to file 01
    [Documentation]     导出消息数据到文件 I09.2
    [Tags]              Message
    ${ret} =    json.loads    ${export_message}
    test export message data to file should ok        ${ret}


test export message data to file 02
    [Documentation]     导出消息数据到文件-失败（返回code不为0）
    [Tags]              Message
    ${ret} =    json.loads    ${message}
    test export message data to file should fail        ${ret}