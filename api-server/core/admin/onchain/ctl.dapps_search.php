<?php
/**
 * 搜索热词管理
 * User: Jett
 * Date: 2019/5/21
 * Time: 10:16 AM
 * @ctl_name = onchain_dapps_search dapps popular search
 */
class ctl_dapps_search extends adminPage{

    public function index_action(){ #act_name = 列表#

        $mdl_search = $this->db( 'index', 'dapps_search', 'master' );

        $list = $mdl_search->getList( null, null, '', 100 );
        $ids = array_map(function ($item){ return $item['id']; }, $list);
        if ( is_post() ) {
            $gid = post('gid');
            $rank = post('rank');
            $name = post('name');
            $id = post('id');

            $mdl_search->begin();
            foreach($gid as $k=>$item){
                if($item) {
                    $tmp = [
                        'gid' => $item,
                        'rank' => $rank[$k],
                        'name' => $name[$k]
                    ];
                    $_id = $id[$k];
                    if ($_id) {
                        $mdl_search->update($tmp, $_id);
                    } else {
                        $mdl_search->insert($tmp);
                    }
                }
            }
            //删除操作
            $del_ids = array_diff($ids, $id);
            foreach ($del_ids as $item){
                $mdl_search->delete($item);
            }

            if($mdl_search->isError()){
                $mdl_search->rollback();

                $this->formReturn['success'] = false;
                $this->formReturn['msg'] = '操作失败';
            }else {
                $mdl_search->commit();

                $this->formReturn['success'] = true;
                $this->formReturn['msg'] = '操作成功';
                $this->session('form-success-msg', '创建成功');
                $this->sheader($this->parseUrl()->set('act')->set('id')->toString());
            }
        }

        $info['list'] = $list;
        $this->formData = array_merge( $info, $this->formData );
        $this->setData( $this->formData, 'formData' );
        $this->setData( $this->formError, 'formError' );
        $this->setData( $this->formReturn, 'formReturn' );


        $this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
        $this->display();
    }
}