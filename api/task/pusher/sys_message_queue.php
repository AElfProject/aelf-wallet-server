<?php
/**
 * 系统消息推送.
 * User: Jett
 * Date: 2019-07-02
 * Time: 13:58
 */
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));

require_once __DIR__ . '/../data.config.php';
require_once __DIR__ . '/../base.php';
require_once __DIR__ . '/../redis.php';


class sys_message_queue extends base
{
    private $queueName = 'sys_message_transaction_push_queue';
    private static $uniqueUserArr = [];    //用户唯一数组，防止一个用户推送多次消息

    function action()
    {
        echo date('Y-m-d H:i:s') . ' start ' . PHP_EOL;
        $queueName = $this->queueName;
        while (true) {
            $queueSize = $this->redis()->Llen($queueName);
            if ($queueSize > 0) {
                echo $queueSize . PHP_EOL;
            }
            $messageId = $this->redis()->lPop($queueName);

            if ($messageId !== false) {
                $mdl_message = $this->db('index', 'message');
                $mdl_com_addr = $this->db('index', 'com_addr');
                $messageData = $mdl_message->get($messageId);
                $pageNum = 1000;
                $i = 0;

                self::$uniqueUserArr = [];    //推送消息前，清空用户唯一数组

                while (true) {
                    $sql = "SELECT * FROM `#@_com_addr` GROUP BY `udid` LIMIT ".($i * $pageNum).",".$pageNum." ";
                    $comAddrList = $mdl_com_addr->query($sql);

                    if ($comAddrList) {
                        foreach ($comAddrList as $k => $comAddr) {
                            $this->pushMessage($comAddr['udid'], $messageData);
                        }
                        $i++;
                        sleep(1);
                    } else {
                        break;
                    }
                }
            } else {
                sleep(5);
            }

        }
        echo date('Y-m-d H:i:s') . ' end ' . PHP_EOL;

    }

    /**
     * 加入推送消息队列
     * @param $user
     * @param $messageData
     */
    public function pushMessage($udid, $messageData) {

            $user = $this->getRecentUserByUdid($udid);

            /*如果用户已推送，则直接跳出*/
            if (in_array($user['address'], self::$uniqueUserArr)) {
                return;
            }

            array_push(self::$uniqueUserArr, $user['address']);

            $lang = $this->redis()->get('lang/'.$user['id']);
            $messageData = $this->formatLangJsonValue($messageData, ['title', 'desc'], $lang);

            $currency = $user['currency'] ? $user['currency'] : 'RMB';

            $queueData = [
                'address' => $user['address'],
                'message' => [
                    'title' => $messageData['title'],
                    'content' => $messageData['desc'],
                    'extras'=>[
                        'address'=> $user['address'],
                        'currency'=> $currency,
                        'type'=>1
                    ]
                ],
            ];

            echo date('Y-m-d H:i:s').'_'.$user['address'].'推送消息id:'.$messageData['id']. 'lang:' .$lang .PHP_EOL;
            $this->redis()->rPush('sys_push_queue', json_encode($queueData));
    }


    /**
     * 格式化多语言json数据
     * @param $data         数据
     * @param $keyArr       需要格式化的key值
     * @param string $lang  对应语言
     * @return mixed
     */
   public function formatLangJsonValue($data, $keyArr , $lang = 'zh-cn'){
        if (!empty($keyArr)) {
            foreach ($keyArr as $key) {
                if (isset($data[$key])) {
                    $defaultValue = json_decode($data[$key], true)['en'];
                    $formatValue = json_decode($data[$key], true)[$lang];
                    if ($formatValue){  //如果当前语言值为空，那么默认显示英文
                        $data[$key] = $formatValue;
                    }else{
                        $data[$key] = $defaultValue ? $defaultValue : '';
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 根据udid获取该设备最后一个登录的用户信息
     * @param $udid
     * @return mixed
     */
    public function getRecentUserByUdid($udid) {
       $user = $this->redis()->get("user_relation_udid_".$udid);

       if (empty($user)) {
           $mdl_com_addr = $this->db('index', 'com_addr');
           $mdl_user_address = $this->db('index', 'user_address');
           $recentData = $mdl_com_addr->getList(null, ['udid' => $udid], 'lasttime desc', 1);
           $recentData = $recentData[0];
           $recentAddress = $recentData['address'];
           $user = $mdl_user_address->getByWhere(['address' => $recentAddress]);
       }

       return $user;
    }


}

set_time_limit(0);
$markets = new sys_message_queue();
$markets->action();



