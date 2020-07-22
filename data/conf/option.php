<?php
/**
 * 配置文件
 */
return [
    'api_domain' => 'http://api.i.iliaozhibo.cn', // api域名
    'admin_domain' => 'http://admin.i.iliaozhibo.cn', // admin域名
    'super_custom_uid' => 100000, // 超级客服uid
    'cjtaqu_db' => [ // 采集数据
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => '127.0.0.1',
        // 数据库名
        'database'    => '',
        // 数据库用户名
        'username'    => '',
        // 数据库密码
        'password'    => '',
        // 数据库连接端口
        'hostport'    => '3306',
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8mb4',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'caiji' => [ // 采集数据 2
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => '127.0.0.1',
        // 数据库名
        'database'    => '',
        // 数据库用户名
        'username'    => '',
        // 数据库密码
        'password'    => '',
        // 数据库连接端口
        'hostport'    => '3306',
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8mb4',
        // 数据库表前缀
        'prefix'      => '',
    ],

    'coin_nickname' => 'i币',
    'coin_rate' => 1,
];
