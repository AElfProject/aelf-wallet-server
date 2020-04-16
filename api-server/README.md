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
3.导入sql
```angular2
data/aelf_test.sql
```
4.修改数据库配置文件

aelf接口文件配置
```json
//#table cc_config_data-->api_config
{
    "web_api": {
        "AELF": "",  //aelf链节点（代理）
        "tDVV": ""   //tdvv链节点（代理）
    },
    "balance_url": "http://127.0.0.1:8000/elf",
    "base58_url": "http://127.0.0.1:8000/elf_trans",
    "address_url": "http://127.0.0.1:8000/elf_address",
    "tokens_url": "http://127.0.0.1:8001/elf_tokens",
    "history_api": {
        "AELF": "", //aelf扫链api
	    "tDVV": "" //tdvv扫链api
    },
    "scaner_node": {
        "AELF": "", //aelf链节点
        "tDVV": ""  //tdvv链节点
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
        "node": "",
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
        "node": "",
        "symbol": "ELF",
        "logo": "elf_wallet/elf/tDVV.png",
        "explorer": "https://explorer-test-side01.aelf.io",
        //链跨链合约地址
        "crossChainContractAddress": "RSr6bPc7Hv6dMJiWdPgBBFMacUJcrgQoeHkVBMjqJ5HURtKK3",
        "transferCoins": "ELF"
    }
]

```