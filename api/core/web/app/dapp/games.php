<?php
/**
 * 获取游戏列表 by PhpStorm.
 * User: Jett
 * Date: 2019/5/17
 * Time: 11:45 AM
 */

require_once __DIR__.'/base.php';
class app_dapp_games extends app_dapp_base{


    public function doRequest(){
        $p = post('p')?intval(post('p')):1;
        $pagesize = 10;
        $isindex = post('isindex')?intval(post('isindex')):0;
        $cat = post('cat')?intval(post('cat')):0;
        $coin = trim(post('coin'));
        $name = trim(post('name'));
        $popular = intval(post('popular'));

        $mdl_games = $this->db('index', 'dapps_games');
        $games = [];
        if($popular) {
            //popular search
            $cacheName = "onchain:app_dapp_games:{$this->lang}:popular";
            //$cache = $this->redis()->get( $cacheName );

            if ($cache) {
                $games = $cache;
            } else {
                //popular search
                $mdl_search = $this->db('index', 'dapps_search');
                $list = $mdl_search->getList(array('gid'), null, 'rank asc', 100);
                $popular_arr =[];
                if($list) {
                    $popular_arr = array_map(function ($item) {
                        return $item['gid'];
                    }, $list);
                }
                //$popular_arr = array(3,2,1);
                $popular_ids = implode(",", $popular_arr);

                $where = "where g.`status`=1 and g.`id` in({$popular_ids})";
                $sql = "select 1 as popouar,g.`id`,g.`ico`,g.`coin`,g.`tag`,g.`name`,g.`desc`,g.`cat`,g.`url`,g.`isindex` 
                        from `#@_dapps_games` g 
                        left join  `#@_dapps_search` s
                        on g.id=s.gid
                        {$where} 
                        order by s.`rank` asc ";
                $games = $mdl_games->query($sql);
                $games = $this->handleData($games);

                $where = "where `status`=1 and `id` not in({$popular_ids})";
                $sql = "select 0 as popouar,`id`,`ico`,`coin`,`tag`,`name`,`desc`,`cat`,`url`,`isindex` from `#@_dapps_games` {$where} order by `sort` desc ";
                $games2 = $mdl_games->query($sql);
                $games2 = $this->handleData($games2);
                $games = array_merge($games, $games2);

                !$games && $games = array();
                $this->redis()->set($cacheName, $games, 1 * 60);
            }
        }else{
            $start = ($p - 1) * $pagesize;
            $where = "where `status`=1";
            if ($isindex) $where .= " and `isindex`={$isindex}";
            if ($cat) $where .= " and `cat`={$cat}";
            if ($coin) $where .= " and `coin`='{$coin}'";
            if ($name) $where .= " and (`name` like '%{$name}%' OR `url` like '%{$name}%')";

            $cacheName = "onchain:app_dapp_games:{$this->lang}:{$isindex}:{$cat}:{$coin}:{$name}:{$p}";
            //$cache = $this->redis()->get( $cacheName );

            if ($cache) {
                $games = $cache;
            } else {
                $sql = "select `id`,`ico`,`coin`,`tag`,`name`,`desc`,`cat`,`url`,`isindex` from `#@_dapps_games` {$where} order by `sort` desc limit {$start},{$pagesize}";
                $games = $mdl_games->query($sql);
                $games = $this->handleData($games);

                !$games && $games = array();
                $this->redis()->set($cacheName, $games, 1 * 60);
            }
        }

        $this->returnSuccess('', array('dapps'=>$games));
    }



}