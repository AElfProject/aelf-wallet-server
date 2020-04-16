<?php
/**
 * 消息推送
 */
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));

require_once __DIR__ . '/../data.config.php';
require_once __DIR__ . '/../base.php';
require_once __DIR__ . '/../redis.php';
require_once 'pusher_ios.php';
require_once 'pusher_android.php';

class message_push extends base
{
    private $queueName = 'transaction_push_queue';

    function action()
    {
        echo date('Y-m-d H:i:s') . ' start ' . PHP_EOL;
        $queueName = $this->queueName;
        while (true) {
            $queueSize = $this->redis()->Llen($queueName);
            if ($queueSize > 0) {
                echo $queueSize . PHP_EOL;
            }
            $queueData = $this->redis()->lPop($queueName);
            if ($queueData !== false) {
                $result = $this->push($queueData);
            } else {
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

        //$isStore = $loginLog['is_store'];
        $isStoreList = [0, 1];
        foreach ($isStoreList as $isStore) {
            if ($device == 'iOS') {
                $pusherHandler = new pusher_ios($isStore);
                $result = $pusherHandler->pusher($token, $title, $content, $extras);
            } elseif ($device == 'Android') {
                $pusherHandler = new pusher_android($isStore);
                $result = $pusherHandler->pusher(['registration_id' => [$token]], $title, $content, $extras);
            }
        }
    }
}

set_time_limit(0);
$markets = new message_push;
$markets->action();