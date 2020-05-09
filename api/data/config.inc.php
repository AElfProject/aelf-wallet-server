<?php
/**
 * 数据库、相关配置的信息
 *
 */

$DB_LIST = [
        //主索引库
        'index' => [
            'host'   => '',
            'port'   => '',
            'name'   => '',
            'pwd'    => '',
            'db'     => '',
            'slaves' => []  //从库
        ],

        //aelf扫链 AELF链数据mysql连接  https://github.com/AElfProject/aelf-scan-mysql
        'AELF' => [
            'host'   => '',
            'port'   => '',
            'name'   => '',
            'pwd'    => '',
            'db'     => '',
            'slaves' => []  //从库
        ],

        //aelf扫链 tDVV链数据mysql连接  https://github.com/AElfProject/aelf-scan-mysql
        'tDVV' => [
            'host'   => '',
            'port'   => '',
            'name'   => '',
            'pwd'    => '',
            'db'     => '',
            'slaves' => []  //从库
        ]
    ];

$REDIS_LIST = [
        'index' => [
            'host' => '',
            'port' => '',
            'auth' => '',
            'dbNumber' => '0',
        ]
    ];

    //rsa公密钥
$RSA_LIST =[
        'privateKey'=><<<EOF
    -----BEGIN PRIVATE KEY-----
    -----END PRIVATE KEY-----
EOF,
        'publicKey'=><<<EOF
            -----BEGIN PUBLIC KEY-----
    -----END PUBLIC KEY-----
EOF
    ];

