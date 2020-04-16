<?php
/**
 * 设置提醒已读
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_set_notice_read extends app_user_base {

    public function doRequest(){
        $id = (int)post('id');

        if ( empty( $id )  ) return $this->error( __( '参数错误' ) );

        $address = $this->user['address'];

        $mdl_user_transaction = $this->db( 'index', 'user_transaction', 'master');
        $data = $mdl_user_transaction->get( $id );

        $updateData = [];
        if(in_array($data['method'], ["CrossChainTransfer", "Transfer"])){
            if ($data['address_from'] == $address) {
                $updateData['status_from'] = 1;
            } elseif($data['address_to'] == $address) {
                $updateData['status_to'] = 1;
            }
        }else{
            if ($data['address_to'] == $address) {
                $updateData['status_to'] = 1;
            } elseif($data['address_from'] == $address) {
                $updateData['status_from'] = 1;
            }
        }

        $mdl_user_transaction->update($updateData, $id);

        $this->returnSuccess(__('成功'), (object)[]);

    }


}