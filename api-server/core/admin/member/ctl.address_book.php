<?php

/*
 @ctl_name = 会员地址簿@
*/

class ctl_address_book extends adminPage
{

    public function index_action() #act_name = 列表#
    {
        $mdl_address = $this->db('index', 'address_book');

        $search = array();
        $search['address'] = trim(get2('address'));
        $search['name'] = trim(get2('name'));
//        $search['chainid'] = trim(get2('chainid'));

        //查询数量
        $where = array();
        if ($search['address']) $where[] = "(`address` like '%" . $search['address'] . "%')";
        if ($search['name']) $where[] = "(`name` like '%" . $search['name'] . "%')";
//        if ($search['chainid']) $where[] = "(`chainid` = " . $search['chainid'] . ")";

        $count = $mdl_address->getCount($where);

        list($sql, $params) = $mdl_address->getListSql(null, $where, 'id DESC');

        $pageSql = $sql;
        $pageSize = 10;
        $pageUrl = $this->parseUrl()->set('page');
        $page = $this->page($pageSql, $pageUrl, $pageSize, 10, '', $count);
        $list = $mdl_address->getListBySql($page['outSql']);

//        $chainList = $this->getChainList();
//
//        foreach ($list as $k =>$v) {
//            $v['chainName'] = $chainList[$v['chainid']];
//            $list[$k] = $v;
//        }

        $this->setData($list, 'list');
        $this->setData($page['pageStr'], 'pager');
        $this->setData($search, 'search');
//        $this->setData($chainList, 'chainList');
        $this->setData($this->parseUrl()->set('act'), 'doUrl');
        $this->setData($this->parseUrl(), 'refreshUrl');
        $this->display();

    }

}

?>