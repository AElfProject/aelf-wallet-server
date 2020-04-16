<?php

/*
 @ctl_name = 用户反馈@
*/

class ctl_feedback extends adminPage
{

    public function index_action() #act_name = 列表#
    {
        $mdl_feedback = $this->db('index', 'feedback');

        //查询数量
        $where = array();
        $search = array();

        $count = $mdl_feedback->getCount($where);
        list($sql, $params) = $mdl_feedback->getListSql(null, $where, 'id DESC');


        $pageSql = $sql;
        $pageSize = 10;
        $pageUrl = $this->parseUrl()->set('page');
        $page = $this->page($pageSql, $pageUrl, $pageSize, 10, '', $count);
        $list = $mdl_feedback->getListBySql($page['outSql']);

        foreach ($list as $k => $v) {
            $userInfo = $this->db('index', 'user_address')->get($v['user_id']);
            $v['userName'] = $userInfo['name'];
            $list[$k] = $v;
        }

        $this->setData($list, 'list');
        $this->setData($page['pageStr'], 'pager');
        $this->setData($search, 'search');
        $this->setData($this->parseUrl()->set('act'), 'doUrl');
        $this->setData($this->parseUrl(), 'refreshUrl');
        $this->display();

    }


    public function detail_action() #act_name = 详情#
    {
        $id = intval(get2('id'));

        $mdl_feedback = $this->db('index', 'feedback', 'master');
        $feedback_info = $mdl_feedback->get($id);

        $mdl_user_address = $this->db('index', 'user_address');
        $user_info = $mdl_user_address->get($feedback_info['user_id']);

        $this->setData($user_info, 'user_info');
        $this->setData($feedback_info, 'formData');

        $this->setData($this->parseUrl()->set('act')->set('id'), 'returnUrl');
        $this->display();
    }



}

?>