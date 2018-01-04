<?php

use common\library\Parameter;

require_once 'bootstrap.php';

//路由定义
$routes = array(
    'GET /downFile' => ['TestController', 'downFile'],
    'GET /testRegex' => ['TestController', 'testRegex'],

    // Tree
    // 搜索消息
    'POST /searchMessage/@projectName' => ['MessageController', 'searchMessage'],
    // 项目模块
    'GET /projectList' => ['ProjectController', 'GetProList'],
    'POST /searchProject' => ['ProjectController', 'searchProject'],
    // 分支模块
    'GET /branchList/@projectName' => ['BranchController', 'getBranchList'],
    'GET /getOriginBranch/@projectName' => ['BranchController', 'getOriginBranch'],
    'POST /checkoutBranch/@projectName' => ['BranchController', 'checkoutBranch'],
    // 服务模块
    'POST /searchService/@projectName' => ['ServiceController', 'searchService'],
    'GET /serviceList/@projectName' => ['ServiceController', 'getServiceList'],
    'PUT /updateService/@projectName/@serviceName' => ['ServiceController', 'updateService'],
    // 接口模块
    'POST /searchInterface/@projectName/@serviceName' => ['InterfaceController', 'searchInterface'],
    'POST /addInterface/@projectName/@serviceName' => ['InterfaceController', 'addInterface'],
    'GET /interfaceList/@projectName/@serviceName' => ['InterfaceController', 'getInterfaceList'],
    'GET /checkInterfaceName/@projectName/@serviceName/@interfaceName' => ['InterfaceController', 'checkInterfaceName'],
    'GET /interfaceDetail/@projectName/@serviceName/@interfaceName' => ['InterfaceController', 'getInterfaceDetail'],
    'PUT /updateInterface/@projectName/@serviceName/@interfaceName' => ['InterfaceController', 'updateInterface'],
    'DELETE /deleteInterface/@projectName/@serviceName/@interfaceName' => ['InterfaceController', 'deleteInterface'],

    //Kinming
    //项目模块
    'POST /addProject' => ['ProjectController', 'addProject'],
    'PUT /updateProject' => ['ProjectController', 'updateProject'],
    'GET /getProjectDetail/@project_name' => ['ProjectController', 'getProjectDetail'],
    'DELETE /deleteProject/@project_name' => ['ProjectController', 'deleteProject'],
    'GET /checkProjectName/@project_name' => ['ProjectController', 'checkProjectName'],
    'POST /checkProjectUrl' => ['ProjectController', 'checkProjectUrl'],
    'GET /getServiceAndInterfaceAmount/@project_name' => ['ProjectController', 'getServiceAndInterfaceAmount'],
    //服务模块
    'POST /addService/@project_name' => ['ServiceController', 'addService'],
    'GET /checkServiceName/@project_name/@service_name' => ['ServiceController', 'checkServiceName'],
    'DELETE /deleteService/@project_name/@service_name' => ['ServiceController', 'deleteService'],
    //消息模块
    'GET /getMessageTypeTemplate/@type' => ['MessageController', 'getMessageTypeTemplate'],
    'POST /addMessage/@project_name' => ['MessageController', 'addMessage'],
    'GET /getMessageTypeList' => ['MessageController', 'getMessageTypeList'],
    'GET /getMessageList/@project_name' => ['MessageController', 'getMessageList'],
    'GET /checkMessageName/@project_name/@message_name' => ['MessageController', 'checkMessageName'],
    'GET /getMessageDetail/@project_name/@message_name' => ['MessageController', 'getMessageDetail'],
    'PUT /updateMessage/@project_name/@message_name' => ['MessageController', 'updateMessage'],
    'DELETE /deleteMessage/@project_name/@message_name' => ['MessageController', 'deleteMessage'],
    'GET /createRequestAndResponseMessage/@project_name/@service_name/@interface_name' => ['MessageController', 'createRequestAndResponseMessage'],
    'GET /createCorrectMessage/@project_name/@message_name/@amount/@id' => ['MessageController', 'createCorrectMessage'],
    'GET /createErrorMessage/@project_name/@message_name' => ['MessageController', 'createErrorMessage'],
    'POST /exportMessageDataToFile' => ['MessageController', 'exportMessageDataToFile'],
    'GET /getCurrentCreateMessageAmount/@id' => ['MessageController', 'getCurrentCreateMessageAmount'],
    'POST /checkRegex' => ['MessageController', 'checkRegex'],
);

//加载路由
foreach ($routes as $pattern => $target) {
    //统一添加命名空间前缀
    $target[0] = 'controller\\'.$target[0];
    Flight::route($pattern, $target);
}

//获取通用参数
$valid = array(
    'HTTP_TS' => '*int',
    'HTTP_ACCESSID' => '*int',
    'HTTP_SIGN' => '*str/^[a-zA-Z0-9]{1,}$/',
    'HTTP_NONCE' => '*int',
    'HTTP_USER' => '*str/^\S{1,43}$/',
    'HTTP_ORG' => '*int',
);

$ret = Parameter::Load($_SERVER, $valid);

isset($ret['HTTP_TS']) && Flight::set('ts', $ret['HTTP_TS']);
isset($ret['HTTP_ACCESSID']) && Flight::set('accessId', $ret['HTTP_ACCESSID']);
isset($ret['HTTP_SIGN']) && Flight::set('sign', $ret['HTTP_SIGN']);
isset($ret['HTTP_NONCE']) && Flight::set('nonce', $ret['HTTP_NONCE']);
isset($ret['HTTP_USER']) && Flight::set('user', $ret['HTTP_USER']);
isset($ret['HTTP_ORG']) && Flight::set('org', $ret['HTTP_ORG']);

//var_dump(__FILE__.__LINE__);

\Flight::start();
