<?php
/**
 * 同步数据库com_addr表中address入redis.
 * User: Jett
 * Date: 2019-12-02
 * Time: 14:12
 */
require_once '../task.php';

class addressSync extends task{

    private $pagesize = 100;
    private $addressKey = "aelf:address:";
    private $cureentTime;

    public function doRequest(){

        $this->cureentTime = date('Ymd');
        while (true){

            //1天同步一次
            if(date('Ymd') != $this->cureentTime){
                $this->interval();
                $this->cureentTime = date('Ymd');
            }
            $this->logScreen("resting……");
            sleep(600);
        }
    }

    private function interval(){
        //获取所有的address
        $mdl_com_address = $this->db('index','com_addr');

        $page = 1;
        while (true){
            $start = ($page-1)*$this->pagesize;
            //循环获取
            $sql = "select `address` from `#@_com_addr` where `address`<>'' group by `address` limit $start, {$this->pagesize} ";
            //echo  $sql.PHP_EOL ;
            $list = $mdl_com_address->query($sql);
            //var_dump($list);
            if(empty($list)){
                break;
            }
            foreach ($list as $item) {
                $this->redis()->set($this->addressKey . $item['address'], 1);
            }
            $this->logScreen("完成address同步:".count($list));
            $page ++;
            usleep(50000);
            // sleep(1);
        }
    }

}

$adressSync = new addressSync();
$adressSync->doRequest();