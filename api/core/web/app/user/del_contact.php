<?php
/**
 * 新增联系人
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_del_contact extends app_user_base {

    public function doRequest(){
        $contact_address = trim(post('contact_address'));
        $name = trim(post('name'));    //联系人名称
        $note = trim(post('note'));    //备注

        if ( empty( $contact_address )  ) return $this->error( __( '参数错误' ) );

        $mdl_address_book = $this->db( 'index', 'address_book');

        $address_book = $mdl_address_book->getByWhere( array( 'userid' => $this->user['id'], 'address' => $contact_address,'status'=>1 ) );

        if (!$address_book) {
            return $this->error( __( '该联系人不存在' ) );
        }

        $insertData = [
            'userid' => $this->user['id'],
            'address' => $contact_address
        ];

        $res = $mdl_address_book->updateByWhere(['status'=>2],$insertData);

        if ($res) {
            $this->returnSuccess(__('成功'));
        } else {
            $this->returnError(__('失败'));
        }


    }

}