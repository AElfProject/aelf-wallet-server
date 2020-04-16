<?php

/*
 @ctl_name = 会员@
*/

class ctl_address extends adminPage
{

    public function index_action() #act_name = 列表#
    {
        $mdl_address = $this->db('index', 'user_address');

        $search = array();
        $search['address'] = trim(get2('address'));
        $search['name'] = trim(get2('name'));

        //查询数量
        $where = array();
        if ($search['address']) $where[] = "(`address` like '%" . $search['address'] . "%')";
        if ($search['name']) $where[] = "(`name` like '%" . $search['name'] . "%')";

        $count = $mdl_address->getCount($where);

        list($sql, $params) = $mdl_address->getListSql(null, $where, 'id DESC');

        $pageSql = $sql;
        $pageSize = 10;
        $pageUrl = $this->parseUrl()->set('page');
        $page = $this->page($pageSql, $pageUrl, $pageSize, 10, '', $count);
        $list = $mdl_address->getListBySql($page['outSql']);

        foreach ($list as $k => $address) {
            $address['img'] = $this->imgToOssImgUrl($address['img']);
            $list[$k] = $address;
        }

        $this->setData($list, 'list');
        $this->setData($page['pageStr'], 'pager');
        $this->setData($search, 'search');
        $this->setData($this->parseUrl()->set('act'), 'doUrl');
        $this->setData($this->parseUrl(), 'refreshUrl');
        $this->display();

    }

}

?>