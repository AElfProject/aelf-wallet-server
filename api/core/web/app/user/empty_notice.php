<?php
/**
 * 清空所有提醒
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_empty_notice extends app_user_base {

    public function doRequest(){

        $address = $this->user['address'];

        $mdl_user_transaction = $this->db( 'index', 'user_transaction', 'master');
        $mdl_user_transaction->begin();

        $where['address_from'] = $address;
        $updateData['status_from'] = 2;
        $mdl_user_transaction->updateByWhere($updateData, $where);    //删除转账的交易通知

        unset($where);
        unset($updateData);

        $where['address_to'] = $address;
        $updateData['status_to'] = 2;
        $mdl_user_transaction->updateByWhere($updateData, $where);    //删除收款的交易通知

        if ( !$mdl_user_transaction->isError() ) {
            $mdl_user_transaction->commit();
            $this->returnSuccess(__('成功'), (object)[]);
        } else {
            $mdl_user_transaction->rollback();
            $this->returnError(__('失败'), (object)[]);
        }

    }


}