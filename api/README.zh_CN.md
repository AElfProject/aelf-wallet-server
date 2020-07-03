# AELF钱包接口系统

该系统为AELF APP提供接口支持、数据服务，包含了资产数据、市场数据、 Dapp数据
   个人信息等模块。

### 前提

>1.部署AELF、tDVV节点
>*https://github.com/AElfProject/AElf*

>2.部署AELF、tDVV链的扫链程序以及api服务 *https://github.com/AElfProject/aelf-scan-mysql* *https://github.com/AElfProject/aelf-block-explorer*

>3.部署接口后台系统

### 开发环境

- Linux Ubuntu 16.04.6 LTS
- PHP 7.3.6
- Mysql 5.6.16
- Nginx 1.10.3

### 部署步骤

1. Nginx 部分配置

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

2. 修改配置文件 `data/config.inc.php`

3. AliYun OSS配置信息 `core/v2.1/Aliyun_OSS.php`

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

如果权限受限, 请修改upload文件的权限。以下仅供参考

`chmod 0777 /data/www/aelf-wallet-server/api-server/data/upload`

文档管理>> api.html

# AELF钱包后台任务  

该系统为AELF钱包提供用户数据更新和存储服务、消息推送服务

path: aelf-wallet-server/api/task

### 前提

>1.部署AELF、tDVV节点
>*https://github.com/AElfProject/AElf*

>2.部署AELF、tDVV链的扫链程序以及api服务 *https://github.com/AElfProject/aelf-scan-mysql* *https://github.com/AElfProject/aelf-block-explorer* 

### 开发环境

- Linux Ubuntu 16.04.6 LTS
- PHP 7.3.6
- Mysql 5.6.16

### 部署步骤

- **task为后台任务目录，可进行单独部署**
- 配置文件路径 `data.config.php`

- go服务配置文件路径 `cli/conf.ini`

```ini
ip = 127.0.0.1
port = 3306
user = user
passwd = user
database = aelf
```

- umeng 配置信息 `pusher/message_push.php`

```php
define("PUSHENV", false); //true正式环境false测试环境

//android key、seckey
define("UMENGKEY", '');
define("UMENGSECKEY", "");
//ios key、seckey
define("IOSUMENGKEY", '');
define("IOSUMENGSECKEY", "");
```

- Supervisord管理进程
`进程配置文件路径 ./supervisord.d`

启动命令
`supervisorctal start all`

- 进程任务说明

```$xslt
1.blockSync_aelf AELF链区块扫描
2.blockSync_tDVV tDVV链区块扫描
3.elf-go elf-go-tokens  解析跨链交易数据
4.getAllContracts 缓存合约
5.mail_send  邮箱发送
6.market_chart  汇率
7.message_push 消息推送
8.sys_message_push sys_message_queue 系统消息推送
9.transSync_aelf_0 transSync_tDVV_0 区块交易扫描
10.updateBalanceInit updateBalanceSig 更新钱包余额
11.updateIndex 跨链索引
12.updateTrans 交易更新
```
