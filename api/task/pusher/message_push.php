<?php
/**
 * 消息推送.
 * User: Jett
 * Date: 2019-07-02
 * Time: 13:58
 */
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));

require_once __DIR__ . '/../data.config.php';
require_once __DIR__ . '/../base.php';
require_once __DIR__ . '/../redis.php';
require_once 'pushMessage.php';

define("PUSHENV", false); //true正式环境false测试环境

//android key、seckey
define("UMENGKEY", '');
define("UMENGSECKEY", "");
//ios key、seckey
define("IOSUMENGKEY", '');
define("IOSUMENGSECKEY", "");

define("IOSUMENGKEY2", '');
define("IOSUMENGSECKEY2", "");
class message_push extends base
{
    private $queueName = 'transaction_push_queue';

    function action($flag)
    {
        if($flag==1){
            $this->queueName = "sys_push_queue";
        }

        echo date('Y-m-d H:i:s') . ' start ' . PHP_EOL;
        $queueName = $this->queueName;
        while (true) {
            $queueSize = $this->redis()->Llen($queueName);
            if ($queueSize > 0) {
                echo date('Y-m-d H:i:s').':'.$queueSize . PHP_EOL;
            }
            $queueData = $this->redis()->lPop($queueName);
            if ($queueData !== false) {
                $this->push($queueData);
            } else {
                echo date('Y-m-d H:i:s') . ' waiting…… ' . PHP_EOL;
                sleep(5);
            }
        }
        echo date('Y-m-d H:i:s') . ' end ' . PHP_EOL;
    }

    /**
     * 消息推送
     *
     * @param $queueData
     * @return bool|int|mixed|void
     */
    public function push($queueData)
    {
        $queueData = json_decode($queueData, true);

        //打印日志
        echo date('Y-m-d H:i:s').PHP_EOL;
        print_r($queueData);

        $address = $queueData['address'];
        $messageDetail = $queueData['message'];

        // $lang = $this->redis()->get( 'lang/' . $userId );

        $title = !empty($messageDetail['title']) ? $messageDetail['title'] : 'ELF Wallet';
        $content = isset($messageDetail['content']) ? $messageDetail['content'] : '';
        $extras = isset($messageDetail['extras']) ? $messageDetail['extras'] : [];

        $mdl_member = $this->db('index', 'com_addr');
        $sql = "SELECT * FROM `#@_com_addr` WHERE `address`=:address ORDER BY `lasttime` DESC LIMIT 1";
        $tran = $mdl_member->query($sql, [':address'=>$address]);
        if (empty($tran)) {
            return;
        }
        $tran = $tran[0];

        $device = $tran['android_notice_token'] ? 'Android' : 'iOS';
        $token = $tran['android_notice_token'] ? $tran['android_notice_token'] : $tran['ios_notice_token'];

        echo date('Y-m-d H:i:s').':'.'token => '.$token . PHP_EOL;

        $this->pusher($device, $token, $title, $content, $extras);
    }

    private function pusher($device, $token, $title, $content, $extra){

        if($device == "Android"){
            $pm = new PushMessage(UMENGKEY, UMENGSECKEY);
            // Set your appkey and master secret here
            $pm->device_tokens = $token;    //设备token
            $pm->ticker = $title; //脉搏、心跳
            $pm->title = $title;
            $pm->text = $content;
            //$pm->after_open = "go_app"; //点击通知消息跳转到app首页

            //------通知跳转至详情页
            $pm->after_open = "go_activity"; //点击通知消息跳转到app首页
            $pm->activity = "io.aelf.MainActivity";//跳转路径
            //------end

            //扩展数据
            /*
            $pm->extra = [
                'txid'=>'ssss',
                'address'=>'XXXXX',
                'currency'=>'RMB',
            ];
            */
            $pm->extra = $extra;
            $pm->sendAndroidUnicast();  //个体推送

        }elseif($device == "iOS"){
            $pm = new PushMessage(IOSUMENGKEY, IOSUMENGSECKEY);
            $pm->device_tokens = $token;
            $pm->alert = [
                'title' => $title,
                'body' =>$content,
            ];
            //扩展数据
            /*
            $pm->extra = [
                'txid'=>'ssss',
                'address'=>'XXXXX',
                'currency'=>'RMB',
            ];
            */
            $pm->extra = $extra;
            $pm->sendIOSUnicast();
            
            // add IOS TestFlight App
            $pm2 = new PushMessage(IOSUMENGKEY2, IOSUMENGSECKEY2);
            $pm2->device_tokens = $token;
            $pm2->alert = [
                'title' => $title,
                'body' =>$content,
            ];
            $pm2->extra = $extra;
            $pm2->sendIOSUnicast();
        }
    }
}
$param = getopt('', array('flag::'));

$flag = $param['flag'];

set_time_limit(0);
$markets = new message_push;
$markets->action($flag);