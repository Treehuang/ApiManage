*** Settings ***
Documentation   项目模块相关接口 测试用例
Resource  ../resource/host_env.robot

*** Variables ***

${url}              http://kinming:kinming123@git.easyops.local/kinming/test.git
${url_error}        url_error
${name}             auto_test_project
${name_update}      auto_test_project_update
${name_error}       auto_test_project_error
${latestTime}       2017-11-11
${project}          {"url":"http://kinming:kinming123@git.easyops.local/kinming/test123.git"}
${project_exist}    {"url":"http://kinming:kinming123@git.easyops.local/kinming/test.git"}
${project_error}    {"url":"url_error"}
${url_auth}         http://git.easyops.local/anyclouds/easyadmin.git

*** Test Cases ***

test add project 01
    [Documentation]     新增项目成功 I03.1
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    [Teardown]        test delete project should ok        ${name}


test add project 02
    [Documentation]     新增项目失败（项目路径错误）I03.c
    [Tags]              Project
    ${ret2} =   test add project should fail        ${url_error}    ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test add project 03
    [Documentation]     新增项目失败（项目已存在）I03.a
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    ${ret2} =   test add project should fail        ${url}    ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${name}


test add project 04
    [Documentation]     新增项目失败（没有权限克隆该项目)
    [Tags]              Project
    ${ret2} =   test add project should fail        ${url_auth}    ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test check project name 01
    [Documentation]     判断项目名称是否存在-不存在可用 I03.1
    [Tags]              Project
    test check project name should ok        ${name_update}


test check project name 02
    [Documentation]     判断项目名称是否存在-存在重复 I03.a
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    ${ret2} =   test check project name should fail        ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${name}


test check project url 01
    [Documentation]     判断项目url是否存在-不存在可用 I03.1
    [Tags]              Project
    ${ret} =    evaluate    ${project}
    test check project url should ok        ${ret}


test check project url 02
    [Documentation]     判断项目url是否存在-存在 I03.a
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    ${ret} =    evaluate    ${project_exist}
    ${ret2} =   test check project url should fail        ${ret}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]        test delete project should ok        ${name}


test check project url 03
    [Documentation]     判断项目url是否存在-项目路径错误 I03.c
    [Tags]              Project
    ${ret} =    evaluate    ${project_error}
    ${ret2} =   test check project url should fail        ${ret}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test get service and interface amount 01
    [Documentation]     获取项目下的服务和接口数量成功 I01.1
    [Tags]              Project
    get service and interface amount        ${name}


test get service and interface amount 02
    [Documentation]     获取项目下的服务和接口数量失败 I01.c
    [Tags]              Project
    ${ret2} =   test get service and interface amount should fail        ${name_update}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

test search Project 01
    [Documentation]     搜索项目成功
    [Tags]              Project     I02.5
    test add project should ok        ${url}    ${name}

    test search Project should ok  ${name}

    [Teardown]        test delete project should ok        ${name}

test search Project 02
    [Documentation]     搜索项目失败  I02.f
    [Tags]              Project
    ${ret2} =  test search Project should fail  ${name_error}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

test update project 01
    [Documentation]     修改项目成功 I04.1
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    test update project should ok        ${url}     ${name_update}     ${latestTime}

    [Teardown]        test delete project should ok        ${name_update}

test update project 02
    [Documentation]     修改项目失败 I04.a
    [Tags]              Project
    ${ret2} =   test update project should fail        ${url_error}   ${name_update}     ${latestTime}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test get project detail 01
    [Documentation]     获取项目详情成功 I01.1
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    test get project detail should ok        ${name}

    [Teardown]        test delete project should ok        ${name}


test get project detail 02
    [Documentation]     获取项目详情失败 I01.c
    [Tags]              Project
    ${ret2} =   test get project detail should fail        ${name}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'


test delete project 01
    [Documentation]     删除项目成功 I05.1
    [Tags]              Project
    test add project should ok        ${url}    ${name}

    test delete project should ok        ${name}


test delete project 02
    [Documentation]     删除项目失败 I05.a
    [Tags]              Project

    ${ret2} =   test delete project should fail        ${name_update}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'
