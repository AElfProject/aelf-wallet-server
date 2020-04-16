<?php

/**
 * 批量删除消息
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_empty_message extends app_user_base {

    function doRequest() {
        $type = (int)post('type');    //消息类型， 1：系统消息

        if ( empty( $type )  ) return $this->error( __( '参数错误' ) );

        $res = $this->emptyMessage($type);

        if ($res) {
            $this->returnSuccess(__('成功'), (object)[]);
        } else {
            $this->returnError(__('失败'), (object)[]);
        }

    }
}
