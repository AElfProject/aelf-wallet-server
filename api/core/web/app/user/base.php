<?php
/**
 * 用户基类
 * User: David
 * Date: 2019/5/31
 * Time: 18:19
 */

require_once __DIR__.'/../app.php';

class app_user_base extends app {

    protected $r_nodes;

    function before()
    {

        parent::before();
        $address = trim(post('address'));

        if (empty($address)) {
            $this->error( __( '参数错误' ) );
        }


        $mdl_user_address = $this->db('index', 'user_address', 'master');
        $user = $mdl_user_address->getByWhere( array( 'address' => $address ) );

        if (empty($user)) {
            $insertData['address'] = $address;
            $insertData['create_time'] = time();
            $mdl_user_address->insert($insertData);
            $user = $mdl_user_address->getByWhere( array( 'address' => $address ) );
        }

        $user = $this->db('index', 'user_address')->getByWhere( array( 'address' => $address ) );
        if (empty($user)) {
            $this->error( __( '参数错误' ) );
        }

        $this->user = $user;


        //保存语言
        $this->redis()->set( 'lang/'.$user['id'], $this->getLang() );

        $apiConfig = $this->getConfig('api_config');
        $apiConfig = json_decode($apiConfig, true);
        $this->r_nodes = array_flip($apiConfig["base58_nodes"]);
    }


    /**
     * 获取用户是否有未读的内容项条数
     * @param $type
     * @return int
     */
    protected function UnReadItemCount($type){

        $user_id = $this->user['id'];
        $mdl_message = $this->db( 'index', 'message');

        //b.status 0、未读 1、已读 2、删除
        $sql = "select count(*) as count from #@_message a left join #@_send_message b 
                on b.mid = a.id and b.userid = '".$user_id."' 
                where  a.type = '".$type."' and a.status =1 and (b.status is null or b.status = 0)  " ;
        $count = $mdl_message->query( $sql );
        $count = $count[0]['count'];
        $UnReadCount = $count;

        return $UnReadCount;
    }


    /**
     * 设置内容项已读
     * @param int $mid
     */
    protected function setMessageRead($mid = 0, $type)
    {

        $userId = $this->user['id'];
        $mdl_send_message = $this->db('index', 'send_message', 'master');

        $send_message = $mdl_send_message->getByWhere(['userid' => $userId, 'mid' => $mid]);

        if (empty($send_message)) {
            $data = [
                'userid' => $userId,
                'mid' => $mid,
                'status' => 1,
                'time' => time(),
                'type' => $type
            ];

            $mdl_send_message->insert($data);
        } else {
            if ($send_message['status'] == 0) {
                $updateData['status'] = 1;
                $updateData['type'] = $type;
            }
            $updateData['time'] = time();

            $mdl_send_message->updateByWhere($updateData, ['id' => $send_message['id']]);
        }
    }

    /**
     * 清空信息
     * @param $type
     * @return bool
     */
    protected function emptyMessage($type){
        $userId = $this->user['id'];

        $mdl_message = $this->db('index', 'message', 'master');
        $mdl_send_message = $this->db('index', 'send_message', 'master');
        $send_message_list = $mdl_send_message->getList(null, array('userid' => $userId));

        //发送消息列表id
        $sendIdArr = array_column($send_message_list, 'id');
        //消息列表id
        $mIdArr = array_column($send_message_list, 'mid');

        $mdl_send_message->begin();

        $where = [];
        $where[] = "type = ".$type;
        $where[] = "status = 1";

        $updateWhere = [];
        if ($send_message_list) {
            $where[] = "id not in (". implode(',', $mIdArr) .")";
            $updateWhere[] = "id in (". implode(',', $sendIdArr) .")";
            //把发送消息列表已有的数据改成已删除
            $mdl_send_message->updateByWhere(['status' => 2], $updateWhere);
        }

        $message_list = $mdl_message->getList(null, $where);

        if ($message_list) {
            $sql = "insert into #@_send_message(userid, mid, status, time, type) values";

            foreach ($message_list as $k => $v) {
                $sql .= "('" . $this->user['id'] . "','" . $v['id'] . "',2,'" . time() . "', " . $type . "),";
            }

            $sql = substr($sql, 0, strlen($sql) - 1);

            //插入数据
            $this->db('index', 'send_message', 'master')->sql($sql, array());
        }

        if ( $mdl_send_message->isError() ) {
            $mdl_send_message->rollback();
        } else {
            $mdl_send_message->commit();
            return true;
        }

    }

    //获取用户所有的未读提醒
    public function unReadNoticeCount() {
        $address = $this->user['address'];
        $mdl_user_transaction = $this->db( 'index', 'user_transaction');

        /*
        $fromCount = $mdl_user_transaction->getCount( ['address_from' => $address, 'status_from' => 0] );    //转账的未读提醒
        $sendCount = $mdl_user_transaction->getCount( ['address_to' => $address, 'status_to' => 0] );    //收款的未读提醒

        $count = (int)$fromCount + (int)$sendCount;
        */

        $sql ="
        select sum(bb.aa) as cnt from (
            select count(id) as aa from cc_user_transaction where ((address_from='{$address}' and
            status_from =0) or (address_to='{$address}' and status_to =0)) and
            method='Transfer'
            union all
            select count(id) as aa from cc_user_transaction where address_from='{$address}' and
            method='CrossChainTransfer' and status_from =0
            union all
            select count(id) as aa from cc_user_transaction where address_to='{$address}' and
            method='CrossChainReceiveToken' and status_to =0
        ) as bb;
        ";
        $list = $mdl_user_transaction->query($sql);
        $count = $list[0]['cnt'];

        return $count;
    }

}