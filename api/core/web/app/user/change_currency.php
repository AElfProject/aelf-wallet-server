<?php
/**
 * 切换币种
 * User: David
 * Date: 2019/07/03
 * Time: 7:48 PM
 */
require_once __DIR__.'/base.php';

class app_user_change_currency extends app_user_base
{
    public function doRequest(){
        $currency = trim(post('currency'));
        $mdl_user_address = $this->db('index', 'user_address', 'master');

        if ($mdl_user_address->update(['currency' => $currency], $this->user['id'])) {
            $this->returnSuccess(__('成功'));
        } else {
            $this->returnError(__('失败'));
        }
    }
}
