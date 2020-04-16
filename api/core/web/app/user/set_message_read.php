<?php

/**
 * 设置已读
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_set_message_read extends app_user_base {

    function doRequest() {
        $mid = (int) post( 'mid' );
        $type = (int) post( 'type' );

        if ( empty( $mid )  ) return $this->error( __( '参数错误' ) );
        if ( empty( $type )  ) return $this->error( __( '参数错误' ) );

        $this->setMessageRead($mid, $type);

        $this->returnSuccess(__('成功'), (object)[]);
    }
}
