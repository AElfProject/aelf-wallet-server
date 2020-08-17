<?php
/**
 * dapps 游戏列表管理
 * User: Jett
 * Date: 2019/5/16
 * Time: 3:22 PM
 * @ctl_name = onchain_dapps_banner dapps游戏列表@
 */

class ctl_dapps_games extends adminPage{

    private $cat = [1=>"游戏",2=>"交易",3=>"工具",4=>"其他"];
    private $coin='';

    public function __construct()
    {
        parent::__construct();

        //获取币种
        $mdl_coins = $this->db("index","coin","master");
        $this->coin = $mdl_coins->getList(array('id','name'), array('status'=>1), "id desc", 100);

    }


    public function index_action(){ #act_name = 列表#

        //获取列表
        $mdl_banner = $this->db("index", 'dapps_games');

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

        //反序列处理名字
        foreach($list as $k=>$item){
            $names = unserialize($item['name']);
            $list[$k]['name'] = $names["zh-cn"];
        }

        $this->setData( $list, 'list' );
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

        $mdl_games = $this->db( 'index', 'dapps_games', 'master' );

        $info = $mdl_games->get( $id );

        if ( is_post() ) {
            $status = intval(post('status'));
            $status = $status?$status:2;
            $coin = trim(post('coin'));
            $isindex = intval(post('isindex'));
            $isindex = $isindex?$isindex:2;
            $url = trim( stripslashes(post( 'url' )) ) ? trim( stripslashes(post( 'url' )) ) : '{}';
            $sort = intval(post('sort'));
            $cat = intval(post('cat'));
            $logoDel = post('logoDel');


            //序列化数据
            $_lang = post('_lang');
            $name = post('name');
            $tag = post('tag');
            $desc = post('desc');

            //加载图片上传插件
            require_once 'core/v2.1/AliYun_OSS.php';
            $logoObj = $_FILES['ico'];

            if ($logoObj){
                $logo = '';
                if ( $logoObj['size'] > 0 ) {
                    $filepath = date( 'Y-m' );
                    $this->file->createdir( 'data/upload/'.$filepath );
                    $allowExts = array( 'jpg', 'jpeg', 'gif', 'png' );
                    $logo = $this->file->upfile( $allowExts, $logoObj, UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
                    AliYun_OSS::uploadFile( 'aelf', $logo, UPDATE_DIR.$logo );
                    $this->file->deletefile( UPDATE_DIR.$logo );
                }
            }

            $sqlArr = array(
                'coin'=>$coin,
                'tag'=>serialize($tag),
                'name'=>serialize($name),
                'desc'=>serialize($desc),
                'cat'=>$cat,
                'sort'=>$sort,
                'status'=>$status,
                'isindex'=>$isindex,
                'url'=>$url,
            );
            $logo && $sqlArr['ico'] = $logo;
            $logoDel && $sqlArr['ico']='';

            if(!$id){ $sqlArr['addtime'] = time(); }
            $mdl_games->begin();
            if($id){
                //编辑
                $mdl_games->update( $sqlArr, $id );
            }else{
                //增加
                $mdl_games->insert($sqlArr);
            }
            if(!$mdl_games->isError()){
                //删除图片逻辑
                if ( $logoDel ) {
                    AliYun_OSS::delFile( 'aelf', $info['ico'] );
                }

                $mdl_games ->commit();
                $this->formReturn['success'] = true;
                $this->formReturn['msg'] = '创建成功';
                $this->session( 'form-success-msg', '创建成功' );
                $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
            }else{
                //删除已上传
                if ( $logo ) {
                    AliYun_OSS::delFile( 'aelf', $logo );
                }

                $mdl_games->rollback();
                $this->formReturn['success'] = false;
                $this->formReturn['msg'] = '创建失败';
            }

        }

        $this->formData = array_merge( $info, $this->formData );
        $this->formData['url'] = stripslashes($this->formData['url']);
        $this->setData( $this->formData, 'formData' );
        $this->setData( $this->formError, 'formError' );
        $this->setData( $this->formReturn, 'formReturn' );

        $this->setData( unserialize( LANGS ), 'langs' );
        $this->setData($this->cat, 'cat');
        $this->setData($this->coin, 'coins');

        $this->setData(unserialize($this->formData['name']), 'name');
        $this->setData(unserialize($this->formData['tag']), 'tag');
        $this->setData(unserialize($this->formData['desc']), 'desc');


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

        $mdl_merchant = $this->db( 'index', 'dapps_games', 'master' );
        $mdl_merchant->delete( $id );

        return true;
    }
}