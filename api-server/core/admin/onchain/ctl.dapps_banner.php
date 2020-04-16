<?php
/**
 * dapps 首页banner图管理
 * User: Jett
 * Date: 2019/5/16
 * Time: 3:22 PM
 * @ctl_name = onchain_dapps_banner dapps首页banner图@
 */

class ctl_dapps_banner extends adminPage{

    private $cat = [1=>"普通链接",2=>"dapp链接"];

    public function index_action(){ #act_name = 列表#

        //获取列表
        $mdl_banner = $this->db("index", 'dapps_banner');
        $mdl_games = $this->db("index", 'dapps_games');
        $games = $mdl_games->getList(null, ['status'=>1], 'id asc', 1000);

        $search = array();
        $search['s'] = trim( get2( 's' ) );
        //if ( !preg_match( '/^[a-z]+$/', $search['s'] ) ) unset( $search['s'] );

        //查询数量
        $where = array();
        if ( $search['s'] )
            $where[] = 'status ="'.$search['s'].'"';
        //$where[] = "(`address` like '%".$search['s']."%')";

        $count = $mdl_banner->getCount( $where );

        list( $sql, $params ) = $mdl_banner->getListSql( null, $where, 'id desc' );

        $pageSql = $sql;
        $pageSize = 10;
        $pageUrl = $this->parseUrl()->set( 'page' );
        $page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
        $list = $mdl_banner->getListBySql( $page['outSql'] );

        $this->setData( $list, 'list' );
        $this->setData( $games, 'games' );
        $this->setData( $page['pageStr'], 'pager' );
        $this->setData( $search, 'search' );
        $this->setData($this->cat, 'cat');

        $this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
        $this->setData( $this->parseUrl()->set( 'act' )->set( 'page' ), 'contractUrl' );
        $this->setData( $this->parseUrl(), 'refreshUrl' );

        $this->display();

    }

    public function edit_action () #act_name = 编辑#
    {
        $id = (int)get2( 'id' );

        $mdl_banner = $this->db( 'index', 'dapps_banner', 'master' );
        $mdl_games = $this->db("index", 'dapps_games');
        $games = $mdl_games->getList(null, ['status'=>1], 'id asc', 1000);
        foreach ($games as $k=>$item){
           $tmp = unserialize($item['name']);
           $games[$k]['name2'] = $tmp['zh-cn'];
        }
        $info = $mdl_banner->get( $id );

        $content = $info_content = unserialize($info['img']); //具体内容

        if ( is_post() ) {
            $status = intval(post('status'));
            $status = $status?$status:2;
            $title = trim(post('title'));
            $url = trim(post('url'));
            $sort = intval(post('sort'));
            $flag = intval(post('flag'));
            $gid = intval(post('gid'));
            $logoDel = post('logoDel');


            //序列化数据
            $content = array();
            $_lang = post('_lang');
            $_img = post('_logo');

            //加载图片上传插件
            require_once 'core/v2.1/AliYun_OSS.php';
            $logoObjs = $_FILES['_logo'];

            foreach ($_lang as $k=>$item){
                $logoObj  = array(
                    'name'=>$logoObjs['name'][$k],
                    'type'=>$logoObjs['type'][$k],
                    'tmp_name'=>$logoObjs['tmp_name'][$k],
                    'error'=>$logoObjs['error'][$k],
                    'size'=>$logoObjs['size'][$k],
                );

                $logo = '';
                if ( $logoObj['size'] > 0 ) {
                    $filepath = date( 'Y-m' );
                    $this->file->createdir( 'data/upload/'.$filepath );
                    $allowExts = array( 'jpg', 'jpeg', 'gif', 'png' );
                    $logo = $this->file->upfile( $allowExts, $logoObj, UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
                    AliYun_OSS::uploadFile( 'aelf', $logo, UPDATE_DIR.$logo );
                    $this->file->deletefile( UPDATE_DIR.$logo );
                }

                //删除图片逻辑
                if (isset($logoDel[$item]) && $logoDel[$item]==1 ) {
                    echo $item;
                    var_dump($info_content[$item]['img']);
                    //die;
                    AliYun_OSS::delFile( 'aelf', $info_content[$item]['img'] );
                }

                $tmp = array(
                    'img' => $logo?$logo:$info_content[$item]['img'],
                );
                $content[$item] = $tmp;
            }

            $sqlArr = array(
                'status'=>$status,
                'gid'=>$gid,
                'flag'=>$flag,
                'title'=>$title,
                'url'=>$url,
                'sort'=>$sort,
                'img'=>serialize($content)
            );
            if(!$id){ $sqlArr['addtime'] = time(); }
            $mdl_banner->begin();
            if($id){
                //编辑
                $mdl_banner->update( $sqlArr, $id );
            }else{
                //增加
                $mdl_banner->insert($sqlArr);
            }
            if(!$mdl_banner->isError()){
                $mdl_banner ->commit();
                $this->formReturn['success'] = true;
                $this->formReturn['msg'] = '创建成功';
                $this->session( 'form-success-msg', '创建成功' );
                $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
            }else{
                $mdl_banner->rollback();
                $this->formReturn['success'] = false;
                $this->formReturn['msg'] = '创建失败';
            }

        }

        $this->formData = array_merge( $info, $this->formData );
        $this->setData( $this->formData, 'formData' );
        $this->setData( $this->formError, 'formError' );
        $this->setData( $this->formReturn, 'formReturn' );

        $this->setData( unserialize( LANGS ), 'langs' );
        $this->setData( $content, 'content' );
        $this->setData($this->cat, 'cat');
        $this->setData( $games, 'games' );

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

        $mdl_merchant = $this->db( 'index', 'dapps_banner', 'master' );
        $mdl_merchant->delete( $id );

        return true;
    }
}