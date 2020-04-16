<?php
/**
 * 系统消息
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_message extends app_user_base {

    public function doRequest(){

        $type = (int)post('type');    //消息类型， 1：系统消息
        $p = (int)post( 'p' );

        $user_id = $this->user['id'];
        $type = $type ? $type : 1;

        $mdl_message = $this->db( 'index', 'message');

        //b.status 0、未读 1、已读 2、删除
        $sql = "select count(*) as count from #@_message a left join #@_send_message b 
                on b.mid = a.id and b.userid = '".$user_id."'
                where  a.type = '".$type."' and a.status =1  and  (b.status != 2 or b.status is null)";
        $count = $mdl_message->query( $sql );
        $count = $count[0]['count'];

        $page = $p ? $p : 1;
        $pageSize = 10;
        $sql = "select a.*,b.status as is_read from #@_message a left join #@_send_message b 
                on b.mid = a.id and b.userid = '".$user_id."' 
                where a.type = '".$type."' and a.status = 1 and (b.status != 2 or b.status is null) order by a.sort desc limit ".($page - 1) * $pageSize.",".$pageSize." ";

        $list = $mdl_message->query( $sql );

        foreach ($list as $k => $message) {
            $message = $this->formatLangJsonValue($message, ['title', 'desc'], $this->getLang());

            if ($this->getLang() != 'zh-cn') {
                $message['message'] = $message['message_'.$this->getLang()];
            }
            $message['is_read'] = (int)$message['is_read'];
            $message['create_time'] = $message['define_time'];

           $list[$k] = $this->getKeyValue($message, ['id', 'title', 'message', 'type', 'desc', 'create_time', 'sort', 'is_read']);
        }

        $unread_count = $this->UnReadItemCount($type);    //未读条数
        $this->returnSuccess('', ['count' => $count, 'unread_count' => $unread_count, 'list' => $this->format_elements_to_string($list)]);
    }


}