<?php
/**
 * 未读消息
 * User: Jett
 * Date: 2019/6/13
 * Time: 7:48 PM
 */
require_once __DIR__.'/base.php';

class app_user_unread extends app_user_base
{
    public function doRequest(){

        //系统消息未读
        $message = $this->UnReadItemCount(1);

        //提醒消息未读
        $unread_count = $this->unReadNoticeCount();
        $count = $message + $unread_count;


        $this->returnSuccess('', ['unread_count' => (int)$count, 'message_unread_count' => (int)$message, 'notice_unread_count' => (int)$unread_count]);

    }
}
