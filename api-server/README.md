# AELF Wallet management system

The system provides data service, announcement management, user statistics and system management functions for aelf app


### development environment

- Linux Ubuntu 16.04.6 LTS
- PHP 7.3.6
- Mysql 5.6.16
- Nginx 1.10.3

### Deployment steps

1.Nginx Partial configuration
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

2.Modify profile data/config.inc.php
3.AliYun OSS configuration information
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
//#table cc_config_data-->oss_url
// eg.aliyun
https://xxx.oss-accelerate.aliyuncs.com/
```

3.Import sql
```angular2
data/aelf_test.sql
```
4.Modify database profile

In this example, we get two chains named AELF, tDVV.

If you get AELF only, remove the config of tDVV.

aelf Interface file configuration
```javascript
//#table cc_config_data-->api_config
{
    "web_api": {
        "AELF": "http://54.199.254.157:8000",  //aelf chain node（agent）https://xx.aelf.io/8000
        "tDVV": "http://3.112.250.87:8000"   //tdvv chain node（agent）https://xx.aelf.io/8001
    },
    "balance_url": "http://127.0.0.1:8000/elf", //go crontab服务
    "base58_url": "http://127.0.0.1:8000/elf_trans", //go crontab服务
    "address_url": "http://127.0.0.1:8000/elf_address", //go crontab服务
    "tokens_url": "http://127.0.0.1:8000/elf_tokens", //go crontab服务
    "history_api": {
        "AELF": "http://3.112.73.152:7101", //aelf scaner chain api http://127.0.0.1:7101
	"tDVV": "http://18.179.200.57:7101" //tdvv scaner chain api http://127.0.0.1:7102
    },
    "scaner_node": {
        "AELF": "http://54.199.254.157:8000", //aelf chain node  http://127.0.0.1:8000
        "tDVV": "http://3.112.250.87:8000"  //tdvv chain node  http://127.0.0.1:8001
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
CrossChain information
```javascript
//#table cc_config_data-->chains
[
    {
        "type": "main",
        "name": "AELF",
        //contract address
        // mainnet token contract: JRmBduh4nXWi1aXgdUsj5gJrzeZb2LxmrAbf7W99faZSvoAaE
        "contract_address": "",
        //aelf chain node. Public network ip
        "node": "http://54.199.254.157:8000/",
        "symbol": "ELF",
        "logo": "elf_wallet/elf/elf.png",
        "explorer": "https://explorer-test.aelf.io",
        //crosschain contract address
	// mainnet: 2SQ9LeGZYSWmfJcYuQkDQxgd3HzwjamAaaL4Tge2eFSXw2cseq
        "crossChainContractAddress": "",
	// eg."ELF,USDT"
        "transferCoins": "*"
    },
    {
        "type": "side",
        "name": "tDVV",
        //contract address
	// mainnet: 7RzVGiuVWkvL4VfVHdZfQF2Tri3sgLe9U991bohHFfSRZXuGX
        "contract_address": "",
        //tdvv chain node Public network ip
        "node": "http://3.112.250.87:8000/",
        "symbol": "ELF",
        "logo": "elf_wallet/elf/tDVV.png",
        "explorer": "https://explorer-test-side01.aelf.io",
        //crosschain contract address
	// mainnet: 2snHc8AMh9QMbCAa7XXmdZZVM5EBZUUPDdLjemwUJkBnL6k8z9
        "crossChainContractAddress": "",
        "transferCoins": "*"
    }
]

```
other information
```json
//#table cc_config_data-->access_ip
//debug api ip
127.0.0.1

//#table cc_config_data-->url
// web domain
// some task depend on this api, like updateBalance.
// if you set your iner ip, please set the referer ip in access_ip.
// eg. 192.xxx.xxx.88:8081 is the api. 
// 192.xxx.xxx.77 request the 192.xxx.xxx.88.
// Set 192.xxx.xxx.77 in access_ip
http://127.0.0.1:8081
```


### login 
```
 url http://ip:port/index.php?con=admin&ctl=default
 admin  super_admin
 passwd Admin@123
```

