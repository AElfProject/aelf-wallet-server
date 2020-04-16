<?php
/**
 * 添加和删除自选
 * User: David
 * Date: 2019/07/12
 * Time: 17:17
 */

require_once __DIR__.'/../user/base.php';

class app_market_custom extends app_user_base {

    public function doRequest(){

        $op = (int)(post('op'));    // -1 删除自选  1 添加自选
        $coin = trim(post('coin'));    //币种

        if (empty($op) || empty($coin)) $this->error(__('参数错误'));

        $mdl_user_address = $this->db('index', 'user_address', 'master');

        $custom_coin = json_decode($this->user['custom_coin'], true);      //自选币种

        if (empty($custom_coin)) {
            $custom_coin = [];
        }

        if ($op == 1) {
            if (!in_array($coin, $custom_coin)) {
                array_push($custom_coin, $coin);
            }
        } elseif($op == -1) {
            if (in_array($coin, $custom_coin)) {
                $key = array_search($coin, $custom_coin);
                unset($custom_coin[$key]);
                $custom_coin = array_values($custom_coin);
            }
        }

        $updateData['custom_coin'] = json_encode($custom_coin);
        $res = $mdl_user_address->update($updateData, $this->user['id']);

        if ($res) {
            $this->returnSuccess(__( '成功' ), $custom_coin);
        } else {
            $this->returnError(__( '失败' ));
        }

    }

}