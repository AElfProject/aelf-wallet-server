<?php
/*
 @ctl_name = 消息中心@
*/

class ctl_system_info extends adminPage
{
    public $types = array(1 => '系统消息' , 2 => '公告消息');

    public function index_action () #act_name = 列表#
    {
        $mdl_message = $this->db( 'index', 'message' );

        $where = array();
        $search = array();
//        $search['cid'] = (int)get2( 'cid' );
//
//        if ( $search['cid'] > 0 ) $where = "classId='".$search['cid']."'";


        $count = $mdl_message->getCount( $where );

        list( $sql, $params ) = $mdl_message->getListSql( null, $where, 'sort desc' );

        $pageSql = $sql;
        $pageSize = 10;
        $pageUrl = $this->parseUrl()->set( 'page' );
        $page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
        $list = $mdl_message->getListBySql( $page['outSql'] );


        foreach ($list as $k => $message){
            $message['title'] = $this->formatShow($message['title']);
            $message['desc'] = $this->formatShow($message['desc']);
            $list[$k] = $message;
        }

        $this->setData( $list, 'list' );
        $this->setData( $this->types, 'types' );
        $this->setData( $page['pageStr'], 'pager' );
        $this->setData( $search, 'search' );
        $this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
        $this->setData( $this->parseUrl(), 'refreshUrl' );

        $this->display();
    }

    public function edit_action () #act_name = 编辑#
    {
        $id = (int)get2( 'id' );

        $mdl_message = $this->db( 'index', 'message', 'master' );

        $message = $mdl_message->get( $id );

        if ( is_post() ) {
            $data = array();
            $data['type'] = trim( post( 'type' ) );
            $data['message'] = trim( post( 'message' ) );
            $data['message_en'] = trim( post( 'message_en' ) );
            $data['message_ko'] = trim( post( 'message_ko' ) );
            $data['sort'] =  (int)post( 'sort' );
            $data['status'] =  (int)post( 'status' );
            $data['title'] = trim( stripslashes(post( 'title' )) ) ? trim( stripslashes(post( 'title' )) ) : '{}';
            $data['desc'] = trim( stripslashes(post( 'desc' )) ) ? trim( stripslashes(post( 'desc' )) ) : '{}';

            $this->formData = $_POST;
            $this->formData['title'] = stripslashes($this->formData['title']);
            $this->formData['desc'] = stripslashes($this->formData['desc']);

            $data['define_time'] = post( 'define_time' )?strtotime(trim(post( 'define_time' ))):time();


            if ( empty( $data['type'] ) ) $this->formError[] = '请选择类型';
            if ( empty( $data['title'] ) ) $this->formError[] = '请填写标题';

            if ( !$this->formError ) {
                if ( $message ) {
                    $mdl_message->begin();
                    $data['update_time'] = time();
                    $mdl_message->update( $data, $message['id'] );

                    if ( !$mdl_message->isError() ) {
                        $mdl_message->commit();

                        $this->formReturn['success'] = true;
                        $this->formReturn['msg'] = '保存成功';
                        $this->session( 'form-success-msg', '保存成功' );
                        $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
                    }
                    else {
                        $mdl_message->rollback();
                        $this->formReturn['success'] = false;
                        $this->formReturn['msg'] = '编辑失败';
                    }
                }
                else {
                    $mdl_message->begin();
                    $data['create_time'] = time();
                    $aid = $mdl_message->insert( $data );

                    if ( !$mdl_message->isError() ) {
                        $mdl_message->commit();

                        $this->formReturn['success'] = true;
                        $this->formReturn['msg'] = '创建成功';
                        $this->session( 'form-success-msg', '创建成功' );
                        $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
                    }
                    else {
                        $mdl_message->rollback();

                        $this->formReturn['success'] = false;
                        $this->formReturn['msg'] = '创建失败';
                    }
                }
            }
        }

        if (empty($message)) {
            $message = [];
        }

        $this->formData = array_merge( $message, $this->formData );

        $this->setData( $this->formData, 'formData' );
        $this->setData( $this->formError, 'formError' );
        $this->setData( $this->formReturn, 'formReturn' );
        $this->setData( unserialize( LANGS ), 'langs' );
        $this->setData( $this->types, 'types' );

        $this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
        $this->display();
    }

    public function delete_action () #act_name = 删除#
    {
        $error = 0;

        if ( is_post() ) {
            $ids = post( 'ids' );
            if ( is_array( $ids) ) {
                foreach ( $ids as $key => $value ) {
                    if ( !self::_delete( trim( $value ) ) ) $error++;
                }
            }
        }
        else {
            if ( !self::_delete( get2( 'id' ) ) ) $error++;
        }

        if ( $error > 0 ) $this->session( 'form-error-msg', '有'.$error.'个删除失败' ); else $this->session( 'form-success-msg', '删除成功' );
        $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' ) );
    }

    private function _delete( $id ) {
        $id = (int)$id;

        $mdl_p2p_info = $this->db( 'index', 'message', 'master' );

        $p2p_info = $mdl_p2p_info->get( $id );

        if ( $p2p_info ) {
            $mdl_p2p_info->begin();
            $mdl_p2p_info->delete( $id );
            if ( !$mdl_p2p_info->isError() ) {
                $mdl_p2p_info->commit();

                return true;
            }
            else {
                $mdl_p2p_info->rollback();
                return false;
            }
        }

        return true;
    }

    public function push_action () #act_name = 推送#
    {
        $id = (int)get2( 'id' ) ;
        $queueName = 'sys_message_transaction_push_queue';

        $this->redis()->rPush($queueName, $id);

        $this->session( 'form-success-msg', '推送成功' );
        $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' ) );
    }

    public function formatShow($data){
        $formatData = '';
        $data = json_decode($data,true);
        if ($data){
            $formatData = $data['zh-cn'];
        }

        return $formatData;
    }

}

?>