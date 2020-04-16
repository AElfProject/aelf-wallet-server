<?php
/**
 * 地址簿
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_address_book extends app_user_base {

    public function doRequest(){
        $keyword = trim(post('keyword'));

        $mdl_address_book = $this->db( 'index', 'address_book');
        $where[] = "userid = ".$this->user['id'];
        $where[] = "status = 1";

        if ($keyword) {
            $where[] = "name like '%".$keyword."%' ";
        }

        $list = $mdl_address_book->getList(['id', 'name', 'fc', 'address', 'note', 'create_time'], $where, 'FIELD(fc,"#"),fc asc,id desc');

        $this->returnSuccess('', ['list' => $this->format_elements_to_string($list)]);
    }

}