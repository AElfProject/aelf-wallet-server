# docker 部署

>centos7 x86_64

### 一、容器化部署elf项目
1、安装docker运行环境
```bash
yum install yum-utils device-mapper-persistent-data lvm2 
yum install docker –y 
sudo curl -L "https://github.com/docker/compose/releases/download/1.25.5/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose 
sudo chmod +x /usr/local/bin/docker-compose 
```

2、git拉取php代码：
```bash
mkdir -p /data/www/
cd /data/www/
git clone https://github.com/AElfProject/aelf-wallet-server.git

cd /data/www/aelf-wallet-server/api-server/data
chmod 0777 tpl_cache tpl_compile

```
修改项目配置信息
```text
1.后台
/data/www/aelf-wallet-server/api-server/data/config.inc.php
2.api
/data/www/aelf-wallet-server/api/data/config.inc.php
3.task
/data/www/aelf-wallet-server/api/task/data.config.php
/data/www/aelf-wallet-server/api/task/cli/conf.ini

```
详情参考

[https://github.com/AElfProject/aelf-wallet-server/tree/master/api-server](https://github.com/AElfProject/aelf-wallet-server/tree/master/api-server)

[https://github.com/AElfProject/aelf-wallet-server/tree/master/api](https://github.com/AElfProject/aelf-wallet-server/tree/master/api)


3、部署编排容器代码
```bash
cd ~
tar -zxvf aelf-wallet-docker.tar.gz 
#到相关目录下，启动编排好的容器：
cd aelf-wallet-docker/
docker-compose up -d
```

4、导入mysql相关数据
```bash
容器内或者第三方软件导入
cp /data/www/aelf-wallet-server/api-server/data/aelf_test.sql /data/mysql/
docker exec -it mymysql /bin/bash
mysql -h127.0.0.1 -uroot -p123456 < /var/lib/mysql/aelf_test.sql
```

5、启动计划任务
```bash
cd aelf-wallet-docker/
bash start_plan.sh
```

6、启动后台
```bash
 http://ip:8082/index.php?con=admin&ctl=default
 admin  super_admin
 passwd Admin@123
```

###二、相关目录及文件介绍
```text
cd aelf-wallet-docker/
conf/mysql  //mysql的配置文件
conf/nginx  //nginx的配置文件
conf/php   //php的配置文件
conf/supervisor  //php-fpm和各个计划任务都是以supervisor来启动的，此目录为各个任务的配置文件

log/mysql  //mysql的日志目录
log/nginx  // nginx 的日志目录
log/php-fpm  // php-fpm的日志目录
log/supervisor  // supervisor各个任务的日志目录

/data/www/aelf-wallet-server  //网站的根目录
/data/mysql    //mysql数据文件目录
/data/redis     //redis数据文件的目录
```