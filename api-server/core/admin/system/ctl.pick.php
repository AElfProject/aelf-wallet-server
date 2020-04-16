<?php

/*
 @ctl_name = 数据采集@
*/

class ctl_pick extends adminPage
{

	public function index_action () #act_name = 采集规则列表#
	{
		//排序
		$orderStr	= '';
		$order		= array(
			'order'		=> trim(get2('order')),
			'ordertype'	=> trim(get2('ordertype'))
		);
		if ($order['order'] == '') $order['order'] = 'id';
		if ($order['ordertype'] != 'desc' && $order['ordertype'] != 'asc') $order['ordertype'] = 'desc';

		//搜索处理
		$search	= array(
			'createTime'	=> get2('createTime'),
			'updateTime'	=> get2('updateTime'),
			'type'			=> get2('type'),
			'keyword'		=> get2('keyword')
		);

		$where	= array();
		switch ($search['createTime'])
		{
			case 'today'	:
				$tmpArr		= getIntervalDays('today');
				$where[]	= "t0.create_time>='".$tmpArr[0]."' and t0.create_time<='".$tmpArr[1]."'";
				break;
			case 'week'		:
				$tmpArr		= getIntervalDays('week');
				$where[]	= "t0.create_time>='".$tmpArr[0]."' and t0.create_time<='".$tmpArr[1]."'";
				break;
			case 'month'	:
				$tmpArr		= getIntervalDays('month');
				$where[]	= "t0.create_time>='".$tmpArr[0]."' and t0.create_time<='".$tmpArr[1]."'";
				break;
			case 'year'		: $where[]	= "t0.create_time>='".strtotime(date('Y', time()).'-01-01 00:00:00')."'"; break;
		}
		switch ($search['updateTime'])
		{
			case 'today'	:
				$tmpArr		= getIntervalDays('today');
				$where[]	= "t0.update_time>='".$tmpArr[0]."' and t0.update_time<='".$tmpArr[1]."'";
				break;
			case 'week'		:
				$tmpArr		= getIntervalDays('week');
				$where[]	= "t0.update_time>='".$tmpArr[0]."' and t0.update_time<='".$tmpArr[1]."'";
				break;
			case 'month'	:
				$tmpArr		= getIntervalDays('month');
				$where[]	= "t0.update_time>='".$tmpArr[0]."' and t0.update_time<='".$tmpArr[1]."'";
				break;
			case 'year'		: $where[]	= "t0.update_time>='".strtotime(date('Y', time()).'-01-01 00:00:00')."'"; break;
		}
		if ($search['keyword'] != '')
		{
			switch ($search['type'])
			{
				case 'name'			: $where[] = "t0.name like '%{$search['keyword']}%'"; break;
				case 'createUser'	: $where[] = "(t1.name like '%{$search['keyword']}%' or t1.displayName like '%{$search['keyword']}%')"; break;
			}
		}
		if ($order['order'] != '')
		{
			$orderStr = 't0.'.$order['order'].' '.$order['ordertype'];
		}

		$bll		= $this->loadModel('pick');
		$pageSql	= $bll->getListSql(null, $where, $orderStr);
		$pageUrl	= '?con=admin&ctl=system/pick&createTime='.$search['createTime'].'&updateTime='.$search['updateTime'].'&type='.$search['type'].'&keyword='.$search['keyword'].'&';
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		//echo $page['outSql'];exit;
		$data		= $bll->getListBySql($page['outSql']);

		//导出分类
		if ($this->user_id == '-1')
		{
			$bll	= $this->loadModel('infoClass');
			$class	= $bll->getChild();
		}
		else
		{
			$class = $this->getAllPermissionClass($this->user_id);
		}

		$this->setData($data);
		$this->setData($class, 'infoclasslist');
		$this->setData($search, 'search');
		$this->setData($order, 'order');
		$this->setData($page['pageStr'], 'pager');
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->setData($pageUrl.'&perPageCount='.get2('perPageCount').'&'.'page='.get2('page').'&', 'refreshUrl');
		$this->display();
	}

	public function add_action () #act_name = 添加采集规则#
	{
		$bllPick	= $this->loadModel('pick');
		$copyfrom	= (int)post('copyfrom');

		if (is_post())
		{
			if ($data = self::_filter(post('data'), post('list'), post('item')))
			{
				if ($copyfrom > 0)  //复制自其它规则
				{
					$oldPick			= $bllPick->get($copyfrom);
					$data['charset']	= $oldPick['charset'];
					$data['list']		= str_replace('\'', '\\\'', $oldPick['list']);
					$data['item']		= str_replace('\'', '\\\'', $oldPick['item']);
				}
				$data['create_time']	= time();
				$data['createUserId']	= $this->user_id == -1 ? 1 : $this->user_id;
				if ($bllPick->add($data)) $this->sheader('?con=admin&ctl=system/pick');
				else $this->sheader(null, $this->lang->add_pick_failed);
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			if ($this->user_id == '-1')
			{
				$bll	= $this->loadModel('infoClass');
				$class	= $bll->getChild();
			}
			else
			{
				$class = $this->getAllPermissionClass($this->user_id);
			}
			//输出已存在的采集规则，方便直接复制
			$picks = $bllPick->getList();
			$this->setData($picks, 'picks');
			$this->setData($class, 'class');
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑采集规则#
	{
		$id			= (int)get2('id');
		$bllPick	= $this->loadModel('pick');
		$data		= $bllPick->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		$copyfrom	= (int)post('copyfrom');

		if (is_post())
		{
			if ($data = self::_filter(post('data'), post('list'), post('item')))
			{
				if ($copyfrom > 0)  //复制自其它规则
				{
					$oldPick			= $bllPick->get($copyfrom);
					$data['charset']	= $oldPick['charset'];
					$data['list']		= str_replace('\'', '\\\'', $oldPick['list']);
					$data['item']		= str_replace('\'', '\\\'', $oldPick['item']);
					//$data['list']		= $oldPick['list'];
					//$data['item']		= $oldPick['item'];
				}
				$data['update_time']	= time();
				if ($bllPick->update($data, $id)) $this->sheader('?con=admin&ctl=system/pick');
				else $this->sheader(null, $this->lang->edit_pick_failed);
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			$data['outClassId']	= unserialize($data['outClassId']);
			$data['list']		= unserialize(str_replace('\\\'', '\'', $data['list']));
			$data['item']		= unserialize(str_replace('\\\'', '\'', $data['item']));
			//$data['list']		= unserialize($data['list']);
			//$data['item']		= unserialize($data['item']);
			if ($this->user_id == '-1')
			{
				$bll	= $this->loadModel('infoClass');
				$class	= $bll->getChild();
			}
			else
			{
				$class = $this->getAllPermissionClass($this->user_id);
			}
			//输出已存在的采集规则，方便直接复制
			//$this->dump($data);
			$picks = $bllPick->getListWithoutById($id);
			$this->setData($picks, 'picks');
			$this->setData($data);
			$this->setData($class, 'class');
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除采集规则#
	{
		$id		= (int)get2('id');
		$bll	= $this->loadModel('pick');
		if ($bll->delete($id)) $this->sheader('?con=admin&ctl=system/pick');
		else $this->sheader(null, $this->lang->delete_pick_failed);
	}

	public function doTest_action () #act_name = 测试采集规则#
	{
		set_time_limit(0);

		$url	= get2('url');
		$id		= (int)get2('id');
		$bll	= $this->loadModel('pick');
		$pick	= $bll->get($id);
		if (!$pick) $this->sheader(null, $this->lang->current_record_not_exists);
		$pick['list'] = unserialize($pick['list']);
		$pick['item'] = unserialize($pick['item']);
		for ($i = 0; $i < count($pick['list']['start']); $i++)
			$pick['list']['list'][] = self::_list($pick['list']['url'][$i], $pick['list']['start'][$i], $pick['list']['end'][$i], $pick['list']['step'][$i], $pick['list']['extend'][$i]);
		$list = array();
		$item = array();

		//开始测试采集
		$bll->doTest($pick, $list, $item, $url);
		foreach ($item as $key=>$value)
		{
			$item[$key] = htmlspecialchars($value);
		}

		$tmpList = array();
		foreach ($list as $key=>$value)
		{
			$tmpList[] = array($value, urlencode($value));
		}
		$list = $tmpList;

		$this->setData($pick);
		$this->setData($list, 'list');
		$this->setData($item, 'item');
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->display();
	}

	public function doBulk_action () #act_name = 批量采集#
	{
		if ( is_post() ) {
			ob_implicit_flush( true );
			set_time_limit(0);

			$ids = post( 'ids' );
			foreach ( $ids as $id ) {
				$this->do_action( $id, true );
			}
			echo '<h2 style="text-align:center; padding:20px;">'.$this->lang->bulk_pick_complete.'</h2>';
			echo '<script type="text/javascript"> window.scrollTo(0, document.body.scrollHeight); </script>';
			exit;
		}
	}

	public function do_action ( $id = 0, $bulk = false ) #act_name = 开始采集#
	{
		$confirm	= (int)get2('confirm');
		if ( $id == 0 ) {
			$id		= (int)get2('id');
		}
		$bllPickHis	= $this->loadModel('pickhis');
		$bllUser	= $this->loadModel('user');

		if ($confirm)
		{
			$data	= $bllPickHis->getList($id);
			if (is_array($data))
			{
				foreach ($data as $k=>$d) {
					$data[$k]['outClassId'] = unserialize($data[$k]['outClassId']);
					$data[$k]['str'] = str_replace( array( '$1', '$2', '$3', '$4' ), array( $d['userName'] == '' && $d['admin_id'] == -1 ? $this->lang->hide : $d['userName'], date( 'Y-m-d H:i:s', $d['pick_time'] ), $d['intro'], implode( ',', $data[$k]['outClassId'] ) ), $this->lang->pick_result_detail_for_database );
				}
			}
			$this->setData($data);
			$this->setData($id, 'pick_id');
			$this->display('system/pick/confirm');
		}
		elseif (is_post())
		{
			ob_implicit_flush( true );
			set_time_limit(0);
			$skin_path = HTTP_ROOT."core/common/skin/admin/";

			//头部输出
			echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="'.$skin_path.'images/global.css">
<link rel="stylesheet" type="text/css" href="'.$skin_path.'images/main.css">
<script type="text/javascript" src="'.$skin_path.'js/jquery.js"></script>
</head>

<body>';
			ob_flush();

			//配置数据
			$autoCheck	= limitInt((int)post('autoCheck'), 0, 1);
			$perCount	= (int)post('perCount');
			$maxCount	= (int)post('maxCount');
			if (post('htmlList'))
				$htmlList = explode("\r\n", post('htmlList'));
			else
				$htmlList = null;

			$bll	= $this->loadModel('pick');
			$pick	= $bll->get($id);
			if (!$pick) $this->sheader(null, $this->lang->current_record_not_exists);
			if (!$pick['isOpen'])
			{
				if ( $bulk ) {
					return;
				}
				else $this->sheader(null, $this->lang->current_pick_has_closed);
			}

			$pick['list'] = unserialize($pick['list']);
			$pick['item'] = unserialize($pick['item']);
			$pick['outClassId'] = unserialize($pick['outClassId']);
			foreach ($pick['outClassId'] as $k=>$p)
			{
				if (trim($p) == '')
					exit($this->lang->current_pick_has_last_one_not_specify_out_category);
			}
			if (trim($pick['outClassId']) == '') $this->sheader(null, $this->lang->current_pick_not_specify_out_category);
			for ($i = 0; $i < count($pick['list']['start']); $i++)
				$pick['list']['list'][] = self::_list($pick['list']['url'][$i], $pick['list']['start'][$i], $pick['list']['end'][$i], $pick['list']['step'][$i], $pick['list']['extend'][$i]);
			$urlList = array();  //所有URL
			$urlItem = array();  //所有信息
			$urlItemImg = array();  //所有图片
			$urlListCount = 0;

			$urlCount = $maxCount;
			if ( $urlCount == 0 ) {
				if ( $pick['list']['list'][0][0] != '' && empty( $htmlList ) ) {
					$urlCount = $bll->getPickUrlCount( $pick );
				}
				else if ( ! empty( $pick['item']['url'] ) || ! empty( $htmlList ) ) {
					if ( ! is_array( $htmlList ) ) {
						$htmlList = array($pick['item']['url']);
					}
					$urlCount = count( $htmlList );
				}
			}
			echo '<table class="listTable" id="preloader">
	<tr class="listHdTr">
		<td align="left" style="padding-left:20px;">'.$this->lang->pick_rate.'</td>
	</tr>
	<tr>
		<td align="center">'.$pick['name'].'</td>
	</tr>
	<tr>
		<td'.( $urlCount == 0 ? ' style="background:pink;"' : '' ).'>
			<div class="pick-result"><span></span><strong><em>0</em>/'.$urlCount.'</strong></div>
			<script type="text/javascript"> var pickResult = $(\'script:last\').prev(); var pickResultPreloader = pickResult.find(\'span\'); var pickResultPreloaderText = pickResult.find(\'em\'); </script>';
			ob_flush();

			//开始采集
			$bll->doPick($pick, $urlList, $urlItem, $urlItemImg, $urlListCount, $autoCheck, $perCount, $maxCount, $htmlList, $urlCount);

			echo '<script type="text/javascript"> pickResultPreloader.css( \'width\', \'100%\' ); pickResultPreloaderText.html( '.$urlCount.' ); </script>';

			//入库
			$infoCnt	= 0;
			$info		= $this->loadModel('info');
			$infoclass	= $this->loadModel('infoClass');
			$mdl_pickHtml	= $this->loadModel('pickhtml');
			foreach ($urlItem as $key=>$value)
			{
				if (trim($value['title']) != '' && trim($value['content']) != '')
				{
					$value['publishedDate']	= $value['publishdate'] == '1970-01-01' ? $value['publishdate'] = date('Y-m-d') : $value['publishdate'];
					//$value['classId']		= $pick['outClassId'];
					$value['createdUserId']	= !$this->user ? 1 : $this->user_id;
					$value['createdDate']	= time();
					$value['ordinal']		= $info->getInfoOrdinal($value['classId']);

					$value = $this->array_splice($value, 'publishdate', 1);
					if ($insert_id = $info->add($value)) {
						$infoCnt++;
						$infoclass->updateById( array( 'lastUpdateTime' => time() ), $value['classId'] );
						$mdl_pickHtml->insert( array( 'infoId' => $insert_id, 'url' => $value['sourceHtml'] ) );
					}
				}
			}

			//下载图片
			$urlItemImgCount = 0;
			foreach ($urlItemImg as $key=>$value)
			{
				foreach ($value[0] as $sk=>$sv)
				{
					$sv = str_replace('\'', '', str_replace('"', '', $sv));
					if (!empty($value[1][$sk]))
					{
						$this->download($sv, str_replace('//', '/', UPDATE_DIR.$value[1][$sk]));
						$urlItemImgCount++;
					}
				}
			}

			//记录采集历史
			$his = array(
				'pid'			=> $id,
				'admin_id'		=> $this->user_id,
				'outClassId'	=> serialize($pick['outClassId']),
				'intro'			=> str_replace( array( '$1', '$2', '$3' ), array( $urlListCount, $infoCnt, $urlItemImgCount ), (string)$this->lang->pick_result_detail ),
				'pick_time'		=> time()
			);
			$bllPickHis->add($his);

			/*$this->setData($pick);
			$this->setData($infoCnt, 'infoCnt');
			$this->setData($urlListCount, 'listCount');
			$this->setData($urlItemImgCount, 'urlItemImgCount');
			$this->display();*/
			echo '</td>
	</tr>
</table>';
			if ( ! $bulk ) {
				echo '<script type="text/javascript"> $(\'.preloader, .presult\').hide(); </script>';
			}
			echo '<table class="listTable presult">
	<tr class="listHdTr">
		<td align="left" style="padding-left:20px;">'.$this->lang->pick_result.'</td>
	</tr>
	<tr>
		<td align="center">'.$pick['name'].'</td>
	</tr>
	<tr>
		<td'.( $urlCount == 0 ? ' style="background:pink;"' : '' ).'>'.str_replace( array( '$1', '$2', '$3' ), array( $urlListCount, $infoCnt, $urlItemImgCount ), (string)$this->lang->pick_result_detail ).'</td>
	</tr>
</table>';
			if ( $bulk ) {
				echo '<script type="text/javascript"> window.scrollTo(0, document.body.scrollHeight); </script>';
			}
			if ( ! $bulk ) exit;
		}
	}

	private function _filter ($data, $list, $item)
	{
		$tmpData = array();

		$tmpData['name']		= trim($data['name']);
		$tmpData['charset']		= trim($data['charset']);
		$tmpData['outClassId']	= serialize($data['outClassId']);
		if ($tmpData['charset'] != 'GB2312' && $tmpData['charset'] != 'UTF8') $tmpData['charset'] = 'GB2312';

		if ($tmpData['name'] == '') return false;

		//不在保存时分析计算待采集网址，改为在采集前计算，减少数据库的浪费
		//for ($i = 0; $i < count($list['start']); $i++)
		//	$list['list'][]		= self::_list($list['url'][$i], $list['start'][$i], $list['end'][$i], $list['step'][$i], $list['extend'][$i]);
		$tmpData['list']		= str_replace('\'', '\\\'', serialize($list));
		$tmpData['item']		= str_replace('\'', '\\\'', serialize($item));
		//$tmpData['list']		= serialize($list);
		//$tmpData['item']		= serialize($item);
		//echo $tmpData['item'];exit;

		return $tmpData;
	}

	private function _list ($url, $start, $end, $step, $extend)
	{
		$list	= array();
		if (trim($url) != '')
		{
			$start	= (int)$start;
			$end	= (int)$end;
			$step	= (int)$step;

			if ($start < 1) $start = 1;
			if ($end < 1) $end = 1;
			if ($step < 1) $step = 1;
			$i		= $start;
			while ($i <= $end)
			{
				$list[] = str_replace('(*)', $i, $url);
				$i += $step;
			}
		}
		foreach (explode("\n", $extend) as $key=>$value)
		{
			$list2[] = $value;
		}
		return array_distinct(array_merge($list, $list2));
	}

	private function _columnChk ()
	{
		$hide = array(
			'pick_add'		=> $this->chkAction('system/pick/add'),
			'pick_edit'		=> $this->chkAction('system/pick/edit'),
			'pick_delete'	=> $this->chkAction('system/pick/delete'),
			'pick_doTest'	=> $this->chkAction('system/pick/doTest'),
			'pick_do'		=> $this->chkAction('system/pick/do')
		);
		return $hide;
	}

}

?>