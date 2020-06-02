<?php

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

];

$REDIS_LIST = [
    'index' => [
        'host' => '',
        'port' => '',
        'auth' => '',
        'dbNumber' => '0',
    ]
];

//api域名
$API_URL = "";  //li: https://hp-pre-wallet.aelf.io

?>
