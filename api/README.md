# AELF Wallet interface system

The system provides interface support and data service for aelf app, including asset data, market data and DAPP data
Personal information and other modules.

### First

>1.Deploy aelf and tdvv nodes
>*https://github.com/AElfProject/AElf*

>2.Deploy AELF、tDVV scaner and api server *https://github.com/AElfProject/aelf-scan-mysql* *https://github.com/AElfProject/aelf-block-explorer*

>3.Deploy admin System [admin](https://github.com/AElfProject/aelf-wallet-server/tree/master/api-server)

### development environment

- Linux Ubuntu 16.04.6 LTS
- PHP 7.3.6
- Mysql 5.6.16
- Nginx 1.10.3

### Deployment steps

1. Nginx Partial configuration

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

2. Modify profile `data/config.inc.php`

3. AliYun OSS configuration information `core/v2.1/Aliyun_OSS.php`

```php
//path core/v2.1/Aliyun_OSS.php
private static function instance() {
  if ( ! isset( self::$inst ) ) {
    self::$inst = new self();
    self::$inst->keyID = '';
    self::$inst->keySecret = '';
    self::$inst->endPoint = 'oss-accelerate.aliyuncs.com'; // No http, https
    self::$inst->ossClient = new OssClient( self::$inst->keyID, self::$inst->keySecret, self::$inst->endPoint );
  }
  return self::$inst;
  }
```

If permission deny. FYI

`chmod 0777 /data/www/aelf-wallet-server/api-server/data/upload`

Document management >> api.html

# Aelf wallet crontab task

The system provides users' data update, storage and message push services for aelf wallet

### First

>1.Deploy aelf and tdvv nodes
>*https://github.com/AElfProject/AElf*

>2.DeployAELF、tDVV scanner and api server *https://github.com/AElfProject/aelf-scan-mysql* *https://github.com/AElfProject/aelf-block-explorer*

### development environment

- Linux Ubuntu 16.04.6 LTS
- PHP 7.3.6
- Mysql 5.6.16

### Deployment steps

- **Task is the background task directory, which can be deployed separately**
- Profile path `data.config.php`
- go crontab profile `cli/conf.ini`

```ini
ip = 127.0.0.1
port = 3306
user = user
passwd = user
database = aelf
```

- umeng config information `pusher/message_push.php`

```php
define("PUSHENV", false); //=true product, false test

//android key、seckey
define("UMENGKEY", '');
define("UMENGSECKEY", "");
//ios key、seckey
define("IOSUMENGKEY", '');
define("IOSUMENGSECKEY", "");
```

- Supervisord supervisor process
`#path ./supervisord.d`

Start command
`supervisorctal start all`

- some task description

```$xslt
1.blockSync_aelf #AELF scaner
2.blockSync_tDVV #tDVV scaner
3.elf-go elf-go-tokens  #parse crosschain data
4.getAllContracts #cache contract
5.mail_send  #send mail message
6.market_chart  #rate
7.message_push #send message
8.sys_message_push sys_message_queue #system msssage
9.transSync_aelf_0 transSync_tDVV_0 #sancer block transaction 
10.updateBalanceInit updateBalanceSig #update wallet balance
11.updateIndex # crosschain index
12.updateTrans #update transactions

```
