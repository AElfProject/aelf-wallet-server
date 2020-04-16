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

        //aelf链上数据mysql连接方式
        'AELF' => [
            'host'   => '',
            'port'   => '',
            'name'   => '',
            'pwd'    => '',
            'db'     => '',
            'slaves' => []  //从库
        ],
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

