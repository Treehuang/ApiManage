<?php

return [
    'app' => [
        // 'name'     => '',
        'timezone' => 'Asia/ShangHai',
    ],
    'log' => [
        'level' => 'INFO',
        'path'  => APP_ROOT.'/log',
    ],
    'ldap' => [
        'name'  => 'data.ldap',
        'multi' => false,
    ],
    'permission' => [
        'name'  => 'logic.permission.api',
        'multi' => false,
    ],
    'notify' => [
        'name'  => 'logic.notify',
        'host'  => 'event.easyops.local',
        'multi' => false,
    ],
    'elastic_search' => [
        'name'  => 'data.elastic_search',
        'host'  => 'es.easyops.local',
        'multi' => false,
    ],
    'redis' => [
        'name'     => 'data.redis',
        'multi'    => false,
    ],
    'mongodb' => [
        'name'     => 'data.mongodb',
        'username' => '',
        'password' => '',
        'replica_set' => 'easyops',
        'multi'    => true,
    ],
    'mysql' => [
        'name'     => 'data.mysql',
        'username' => 'easyops',
        'password' => 'easyops',
        'database' => 'anyclouds_cmdb',
        'charset'  => 'utf8',
        'prefix'   => '',
        'multi'    => false,
    ],
    'email' => [
        'smtp_server'     => 'smtp.exmail.qq.com',
        'smtp_port'       => 25,
        'smtp_encryption' => null, // ssl tls
        'username'        => 'service@easyops.cn',
        'password'        => 'password',
        'from'            => 'service@easyops.cn',
        'from_name'       => '优维科技',
    ],
    'sms_verification' => [
        'sid'         => '',
        'token'       => '',
        'app_id'      => '',
        'template_id' => '',
        'timeout'     => 300,
        'interval'    => 120,
    ],
    'login' => [
        'mode' => 'local',
    ],
    'agent' => [
        'secret_key' => 'password',
        'agent_install_script' => '/data/easyops/fileDownload/agent_install.sh',
        'agent_install_script_windows' => '/data/easyops/fileDownload/agent_install_windows.vbs',
        'proxy_install_script' => '/data/easyops/fileDownload/proxy_install.sh',
    ],
];
