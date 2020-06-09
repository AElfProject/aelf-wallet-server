<?php
/**
 * 数据库、相关配置的信息
 *
 */

$DB_LIST = [
    //主索引库
    'index' => [
        'host'   => 'mymysql',
        'port'   => '3306',
        'name'   => 'root',
        'pwd'    => '123456',
        'db'     => 'aelf_bk2020',
        'slaves' => []  //从库
    ],

    //aelf扫链 AELF链数据mysql连接  https://github.com/AElfProject/aelf-scan-mysql
    'AELF' => [
        'host'   => '3.112.73.152',
        'port'   => '3306',
        'name'   => 'normal_aelf',
        'pwd'    => 'password',
        'db'     => 'aelf_test',
        'slaves' => []  //从库
    ],

    //aelf扫链 tDVV链数据mysql连接  https://github.com/AElfProject/aelf-scan-mysql
    'tDVV' => [
        'host'   => '18.179.200.57',
        'port'   => '3306',
        'name'   => 'root',
        'pwd'    => 'root',
        'db'     => 'aelf_side_01',
        'slaves' => []  //从库
    ]
];

$REDIS_LIST = [
    'index' => [
        'host' => 'myredis',
        'port' => '6379',
        'auth' => '123456',
        'dbNumber' => '1',
    ]
];

//rsa公密钥
$RSA_LIST =[
    'privateKey'=><<<EOF
-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALdLYKcGJwJRa1+Z
/7wnqLLWWiqaRegf2hodekqz9O0zYM0SZLIS+/rpE/XJshMlyJ+jyyVCUcLLNaqG
GITPNxOFxBWkbkZrgTdpNwFQ0ZDbTjMJ8xiLOadSPSutZPbCQmTcJM3sKFhDXza+
Q1D7auZmr2mlPI1lk/M9GLj5f3oZAgMBAAECgYBWsVMsjG9k3EeRtw/K2WMj+tg4
sDECQhZxJaIStRzSF+vf8qQnyWze1uC27sfH4KYMQ3cwzzZGkMB4P+ZW4n8kchm4
pPhaSW1lGPXJWd5lLstxtvLxJTmv0IvsPRZkCO4FW/t2uredZldGDwksJI+psq2q
pUFOm+TJhH8E7NMzNQJBAOrnyKzbtsbzi6e6nMbrnUlHS7arDFfTw03xLhizA47r
z3MtBsi4lyt20x57o9x2A0WkzQdU1dFyDQsUMQrLY2MCQQDHwR1VHuTCrRVTcAB6
JHZdTjK712u2kRFUzFOps1HpGUzsBV/Vxo3QgRvYAz73ZfLs6rBIJT8rx0VzZhCP
YwtTAkEAlH+Odug2tbLEuHXaIk5UkjyF+qZLGUJ/lsg+0dJpD3K3JCJ0xXMb7Zgi
goS64+Wez+oMyvOwb8VfxX8wOZi17QJBAL5nOFN7yCg8nXhT6VCD0wNrV3avhy+V
pcSDoze+AtTC1gyfrtLxmRnnByhnJ6zgU6c6qV+LiWRsZKnz3tMeYJsCQBUcFP+p
RzTOHpoC6Nua5dt6fjZKtatXZ1LVa4b2GfLLAUlBUuWfOOxfAZ2vuGiuGqnqkPk4
Dicrq2WniG8jY2g=
-----END PRIVATE KEY-----
EOF,
    'publicKey'=><<<EOF
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3S2CnBicCUWtfmf+8J6iy1loq
mkXoH9oaHXpKs/TtM2DNEmSyEvv66RP1ybITJcifo8slQlHCyzWqhhiEzzcThcQV
pG5Ga4E3aTcBUNGQ204zCfMYizmnUj0rrWT2wkJk3CTN7ChYQ182vkNQ+2rmZq9p
pTyNZZPzPRi4+X96GQIDAQAB
-----END PUBLIC KEY-----
EOF
];
