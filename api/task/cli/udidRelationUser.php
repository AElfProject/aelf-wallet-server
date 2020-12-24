<?php
/**
 * 设备号关联最后一个登录该设备的用户
 */
require_once '../task.php';

class udidRelationUser extends task
{

    public function doRequest()
    {
        while (true){
            $this->interval();
            echo 'sleep......';
            sleep(5 * 60);
        }
    }

    private function interval()
    {
        $mdl_com_addr = $this->db('index', 'com_addr');
        $mdl_user_address = $this->db('index', 'user_address');

        $i = 0;
        $pageNum = 1000;

        while (true) {
            $sql = "SELECT * FROM `#@_com_addr` GROUP BY `udid` LIMIT " . ($i * $pageNum) . "," . $pageNum . " ";
            $comAddrList = $mdl_com_addr->query($sql);

            if ($comAddrList) {
                foreach ($comAddrList as $k => $comAddr) {
                    $udid = $comAddr['udid'];
                    $recentData = $mdl_com_addr->getList(null, ['udid' => $udid], 'lasttime desc', 1);
                    $recentData = $recentData[0];
                    $recentAddress = $recentData['address'];
                    $user = $mdl_user_address->getByWhere(['address' => $recentAddress]);

                    if ($user) {
                        $this->redis()->set('user_relation_udid_'.$udid, $user, 30 * 60);
                        echo $udid." => ". json_encode($user). PHP_EOL;
                    }
                }

                $i++;
                usleep(50000);
                // sleep(1);
            } else {
                break;
            }
        }

    }
}

$blockSync = new udidRelationUser();
$blockSync->doRequest();