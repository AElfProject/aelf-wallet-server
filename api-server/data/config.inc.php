<?php

$DB_LIST = array(
    //主索引库
    'index' => array(
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'root',
        'pwd' => 'root',
        'db' => 'aelf_bk2020',
        'slaves' => array(  //从库

        ),
    ),

    //以下是子库，需要与主索引库中database表对应
    'sub1' => array(

    ),
);

$REDIS_LIST = array(
    'index' => array(
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => 'today',
        'dbNumber' => '0',
    )
);

?>