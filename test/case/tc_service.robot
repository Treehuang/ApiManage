*** Settings ***
Documentation   服务模块相关接口 测试用例
Resource  ../resource/host_env.robot

*** Variables ***

${project_name}         auto_test_project
${error_project_name}   1
${name}                 auto_test_service
${protocol}             http
${name_new}             auto_test_service2
${updateService}        {"name":"auto_test_service2", "protocol":"https"}
${url}                  http://kinming:kinming123@git.easyops.local/kinming/test.git


*** Test Cases ***

test add service 01
    [Documentation]     新增服务成功 I03.1
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}

    test add service should ok        ${project_name}   ${name}     ${protocol}

    [Teardown]        test delete project should ok        ${project_name}


test add service 02
    [Documentation]     新增服务失败 I03.a
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}
    test add service should ok        ${project_name}   ${name}     ${protocol}

    ${ret2} =   test add service should fail        ${project_name}   ${name}     ${protocol}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test check service name 01
    [Documentation]     判断服务名是否重复-成功 I03.1
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}

    test check service name should ok        ${project_name}   ${name_new}

    [Teardown]        test delete project should ok        ${project_name}


test check service name 02
    [Documentation]     判断服务名是否重复-失败 I03.a
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}
    test add service should ok        ${project_name}   ${name}     ${protocol}

    ${ret2} =   test check service name should fail        ${project_name}   ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test get serviceList 01
    [Documentation]     获取服务列表成功 I02.1
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}
    test add service should ok        ${project_name}   ${name}     ${protocol}

    test get serviceList should ok      ${project_name}

    [Teardown]        test delete project should ok        ${project_name}

test get serviceList 02
    [Documentation]     获取服务列表失败 I02.f
    [Tags]              Service
    ${ret2} =   test get serviceList should fail    ${error_project_name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

test search 01
    [Documentation]     搜索服务成功    I02.1
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}
    test add service should ok        ${project_name}   ${name}     ${protocol}

    test search service should ok    ${project_name}   ${name}

    [Teardown]        test delete project should ok        ${project_name}


test search 02
    [Documentation]    搜索服务失败    I02.f
    [Tags]             Service
    test add project should ok     ${url}    ${project_name}

    ${ret2} =  test search service should fail   ${project_name}   ${name_new}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}


test update service 01
    [Documentation]     修改服务成功 I04.1
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}
    test add service should ok        ${project_name}   ${name}     ${protocol}

    ${ret} =    evaluate    ${updateService}
    test update service should ok        ${project_name}   ${name}   ${ret}

    [Teardown]        test delete project should ok        ${project_name}


test update service 02
    [Documentation]     修改服务失败 I04.d
    [Tags]              Service
    ${ret} =    evaluate    ${updateService}
    ${ret2} =   test update service should fail    ${error_project_name}  ${name}   ${ret}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test delete service 01
    [Documentation]     删除服务成功 I05.1
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}
    test add service should ok        ${project_name}   ${name}     ${protocol}

    test delete service should ok        ${project_name}   ${name}

    [Teardown]        test delete project should ok        ${project_name}


test delete service 02
    [Documentation]     删除服务失败 I05.a
    [Tags]              Service
    test add project should ok     ${url}    ${project_name}

    ${ret2} =   test delete service should fail        ${project_name}   ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${project_name}

