*** Settings ***
Force Tags          MyTag
Documentation   分支模块相关接口  测试用例
Resource  ../resource/host_env.robot

*** Variables ***
${projectName}          auto_test_project
${project_url}          http://treehuang:treehuang@git.easyops.local/treehuang/Tree3.git
${errorProjectName}     1
${branchName}           one


*** Test Cases ***
test get originbranch 01
    [Documentation]     拉取远端分支成功
    [Tags]              Branch
    test add project should ok       ${project_url}    ${projectName}
    test get originbranch should ok  ${projectName}

    [Teardown]  test delete project should ok  ${projectName}

test get originBranch 02
    [Documentation]     拉取远端分支失败        (数据库记录不存在)  I02.f
    [Tags]              Branch
    test add project should ok       ${project_url}    ${projectName}
    ${ret2} =  test get originbranch should fail  ${errorProjectName}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test get branchList 01
    [Documentation]     获取分支列表成功
    [Tags]              Branch
    test add project should ok       ${project_url}    ${projectName}
    test get branchList should ok    ${projectName}

    [Teardown]  test delete project should ok  ${projectName}

test get branchList 02
    [Documentation]     获取分支列表失败        (数据库记录不存在)  I02.f
    [Tags]              Branch
    test add project should ok       ${project_url}    ${projectName}
    ${ret2} =  test get branchList should fail  ${errorProjectName}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}

test checkout branch 01
    [Documentation]     切换分支成功
    [Tags]              Branch
    test add project should ok       ${project_url}    ${projectName}
    test checkout branch should ok   ${projectName}   ${branchName}

    [Teardown]  test delete project should ok  ${projectName}

test checkout branch 02
    [Documentation]     切换分支失败
    [Tags]              Branch
    test add project should ok       ${project_url}    ${projectName}
    ${ret2} =  test checkout branch should fail  ${errorProjectName}  ${branchName}
    ${code} =   evaluate    ${ret2}.get('code')
    should not be equal     '${code}'     'None'

    [Teardown]  test delete project should ok  ${projectName}