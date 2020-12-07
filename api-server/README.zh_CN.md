# AELF钱包后台系统

该系统为AELF APP提供数据服务、通告管理、用户统计以及系统管理功能


### 开发环境

- Linux Ubuntu 16.04.6 LTS
- PHP 7.3.6
- Mysql 5.6.16
- Nginx 1.10.3

### 部署步骤

1.Nginx 部分配置
```nginx
location / {
            #root   html;
            index  index.html index.htm index.php;
            rewrite ^/verifycode.gif$ /index.php?con=admin&ctl=verifycode&$args;
            if (!-e $request_filename){
                 rewrite ^/(.*)$ /index.php/$1 last;
                 #break;
            }
        }
```

2.修改配置文件 data/config.inc.php
3.AliYun OSS配置信息
```php
//path core/v2.1/Aliyun_OSS.php
private static function instance() {
		if ( ! isset( self::$inst ) ) {
			self::$inst = new self();
            self::$inst->keyID = '';
            self::$inst->keySecret = '';
			self::$inst->endPoint = '';
			self::$inst->ossClient = new OssClient( self::$inst->keyID, self::$inst->keySecret, self::$inst->endPoint );
		}
		return self::$inst;
	}
```

```json
// 配置完后，记得在数据库配置对应oss的链接
//#table cc_config_data-->oss_url
// eg.aliyun
https://xxx.oss-accelerate.aliyuncs.com/
```

3.导入sql
```angular2
data/aelf_test.sql
```
4.修改数据库配置文件

在这个案例里，我们配置了AELF和tDVV两条链

如果你只配置了一条AELF链，删掉多余的tDVV链配置即可。配置了更多的链，则新增对应配置。

/api/data/config.inc.php 里对应的内容也记得修改

注意：修改的配置会定时或触发某些规则同步到 redis里，如果需要及时生效，请自己手动delete elf:configs.

aelf接口文件配置
```json
//#table cc_config_data-->api_config
{
    "web_api": {
        "AELF": "http://54.199.254.157:8000",  //aelf链节点（代理）https://xx.aelf.io/8000
        "tDVV": "http://3.112.250.87:8000"   //tdvv链节点（代理）https://xx.aelf.io/8001
    },
    "balance_url": "http://127.0.0.1:8000/elf", //go crontab服务
    "base58_url": "http://127.0.0.1:8000/elf_trans", //go crontab服务
    "address_url": "http://127.0.0.1:8000/elf_address", //go crontab服务
    "tokens_url": "http://127.0.0.1:8000/elf_tokens", //go crontab服务
    "history_api": {
        "AELF": "http://3.112.73.152:7101", //aelf扫链api http://127.0.0.1:7101
	    "tDVV": "http://18.179.200.57:7101" //tdvv扫链api http://127.0.0.1:7102
    },
    "scaner_node": {
        "AELF": "http://54.199.254.157:8000", //aelf链节点 http://127.0.0.1:8000
        "tDVV": "http://3.112.250.87:8000"  //tdvv链节点 http://127.0.0.1:8001
    },
    "chain_color": {
        "AELF": "#5C28A9",
        "tDVV": "#4B60DD"
    },
    "base58_nodes": {
        "AELF": "9992731",
        "tDVV": "1866392"
    }
}
```
跨链信息
```json
//#table cc_config_data-->chains
[
    {
        "type": "main",
        "name": "AELF",
        //链合约地址
        "contract_address": "25CecrU94dmMdbhC3LWMKxtoaL4Wv8PChGvVJM6PxkHAyvXEhB",
        //aelf链节点
        "node": "http://54.199.254.157:8000/",
        "symbol": "ELF",
        "logo": "elf_wallet/elf/elf.png",
        "explorer": "https://explorer-test.aelf.io",
        //链跨链合约地址
        "crossChainContractAddress": "x7G7VYqqeVAH8aeAsb7gYuTQ12YS1zKuxur9YES3cUj72QMxJ",
        "transferCoins": "ELF"
    },
    {
        "type": "side",
        "name": "tDVV",
        //链合约地址
        "contract_address": "EReNnYPBeZ3AfAjPXXdpNK7AV5YCjRPvM7d5M3SLettMZpxre",
        //tDVV链节点
        "node": "http://3.112.250.87:8000/",
        "symbol": "ELF",
        "logo": "elf_wallet/elf/tDVV.png",
        "explorer": "https://explorer-test-side01.aelf.io",
        //链跨链合约地址
        "crossChainContractAddress": "RSr6bPc7Hv6dMJiWdPgBBFMacUJcrgQoeHkVBMjqJ5HURtKK3",
        "transferCoins": "ELF"
    }
]

```
其他信息
```json
//#table cc_config_data-->access_ip
//用来调试的ip
127.0.0.1

//#table cc_config_data-->url
//当前api服务url
// eg. 配置了一个内网链接 192.xxx.xxx.77
// 192.xxx.xxx.88 访问 这个链接，则需要在access_ip中配置 192.xxx.xxx.88.
http://127.0.0.1:8081

```

### 登录
```
 url http://ip:port/index.php?con=admin&ctl=default
 admin  super_admin
 passwd Admin@123
```
