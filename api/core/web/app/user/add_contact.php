<?php
/**
 * 新增联系人
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_add_contact extends app_user_base {

    public function doRequest(){
        $contact_address = trim(post('contact_address'));
        $name = trim(post('name'));    //联系人名称
        $note = trim(post('note'));    //备注

        if ( empty( $name )  ) return $this->error( __( '请输入联系人名称' ) );
        if ( empty( $contact_address )  ) return $this->error( __( '参数错误' ) );


        if(!preg_match("/^[0-9a-zA-Z]+_[0-9a-zA-Z]+_[0-9a-zA-Z]+$/i", $contact_address)){
            $this->error( __( '地址格式不正确' ) );
        }

        $mdl_address_book = $this->db( 'index', 'address_book');

        //$address_book = $mdl_address_book->getByWhere( array( 'userid' => $this->user['id'], 'address' => $contact_address, 'status' => 1 ) );
        $address_book = $mdl_address_book->getByWhere( array( 'userid' => $this->user['id'], "(address='{$contact_address}' or `name`='{$name}')", 'status' => 1 ) );

        $fc = $this->getFirstChar($name);
        if ($address_book) {
            //更新
            $data = [
                'name'=>$name?$name:$address_book['name'],
                'address'=>$contact_address?$contact_address:$address_book['address'],
                'note'=>$note?$note:$address_book['note']
            ];
            $res = $mdl_address_book->update($data, $address_book['id']);
        }else{
            //新增
            $insertData = [
                'userid' => $this->user['id'],
                'name' => $name,
                'fc' => $fc,
                'address' => $contact_address,
                'note' => $note,
                'create_time' => time(),
            ];
            $res = $mdl_address_book->insert($insertData);
        }
        if ($res) {
            $this->returnSuccess(__('成功'));
        } else {
            $this->returnError(__('失败'));
        }


    }

}