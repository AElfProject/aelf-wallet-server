<?php

$DB_LIST = array(
    //主索引库
    'index' => array(
        'host' => 'mymysql',
        'port' => '3306',
        'name' => 'root',
        'pwd' => '123456',
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
        'host' => 'myredis',
        'port' => '6379',
        'auth' => '123456',
        'dbNumber' => '1',
    )
);

?>
