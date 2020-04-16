<?php

class ctl_menu extends adminPage
{

	public function index_action ()
	{
		$class_id	= '';
		$mm			= $this->loadModel('relation');
		$menuData	= $mm->getChild2($class_id);

		if ($this->user_id == '-1')
		{
			$menuData[] = array(
				'name'			=> $this->lang->super_management,
				'childCount'	=> 1,
				'child'			=> array(
					array(
						'name'	=> $this->lang->update_cache,
						'url'	=> '?con=admin&ctl=hidden/cls'
					),
					array(
						'name'	=> $this->lang->global_info_list_column,
						'url'	=> '?con=admin&ctl=hidden/column'
					),
					array(
						'name'	=> $this->lang->menu_manager,
						'url'	=> '?con=admin&ctl=hidden/relation'
					),
					array(
						'name'	=> $this->lang->generate_multiple_lang,
						'url'	=> '?con=admin&ctl=hidden/gml'
					)
				)
			);
		}
		else
		{
			$showMenus = array();
			foreach ($menuData as $key=>$value)
			{
				$i = 0;
				if (in_array($value['id'], $this->user['relation'])) {
					$showMenus[$key] = true;
					$i++;
				}
				foreach ($value['child'] as $sk=>$sv)
				{
					if (in_array($sv['id'], $this->user['relation'])) {
						$showMenus[$key] = true;
					}
					else
					{
						$menuData[$key]['child'][$sk]['hide'] = true;
						$i++;
					}
				}
				if ($value['childCount'] == $i) $menuData[$key]['hide'] = true;

			}
		}
		if ( count( $showMenus ) == 1 ) {
			foreach ( $showMenus as $key => $show ) {
				if ( $menuData[$key]['child'] ) $menuData = $menuData[$key]['child'];
			}
		}

		$all = 0;
		foreach ( $menuData as $key => $value ) {
			//print_r($value);
			//
			foreach ( $value['child'] as $sk => $sv ) {
				if ( !$sv['hide'] ) {
					if ( $sv['name'] == '转出审核' ) {
						$mdl_send_review = $this->db( 'index', 'send_review' );
						$where = array();
						$count = $mdl_send_review->getCount( $where );
						$menuData[$key]['child'][$sk]['num'] = $count;
						$all += $count;
					}
					if ( $sv['name'] == '转入审核' ) {

						$mdl_receipt_check = $this->db( 'index', 'receipt_check' );
						$where = array();
						$count = $mdl_receipt_check->getCount( $where );
						$menuData[$key]['child'][$sk]['num'] = $count;
						$all += $count;
					}
					if ( $sv['name'] == '转移审核' ) {
						$count = 0;
						foreach ( $this->getDatabases() as $db ) {
							$mdl_transfer = $this->db( $db['alias'], 'transfer_order' );
							$count += $mdl_transfer->getCount( array( 'audit' => 0, 'status' => 0 ) );
						}
						$menuData[$key]['child'][$sk]['num'] = $count;
						$all += $count;
					}
				}
			}

			if ( $value['name'] == '交易' ) {
				$menuData[$key]['num'] = $all;
			}
		}
		$this->setData($menuData);
		$this->setData(strlen($class_id), 'currentLevel');
		$this->display('common/menu');
	}


	public function ajax_data_action ()
	{
		//
		$mdl_send_review = $this->db( 'index', 'send_review' );
		$where = array();
		$transfer_out = $mdl_send_review->getCount( $where );
		//
		$mdl_receipt_check = $this->db( 'index', 'receipt_check' );
		$where = array();
		$transfer_receipt = $mdl_receipt_check->getCount( $where );
		//
		$transfer_order = 0;
		foreach ( $this->getDatabases() as $db ) {
			$mdl_transfer = $this->db( $db['alias'], 'transfer_order' );
			$transfer_order += $mdl_transfer->getCount( array( 'audit' => 0, 'status' => 0 ) );
		}
		//
		$all = (int)($transfer_out + $transfer_receipt + $transfer_order);

		$data = array(
			 'status' => 200,
			 'out' => $transfer_out,
			 'receipt' => $transfer_receipt,
			 'order' => $transfer_order,
			 'all' => $all,
		);

		echo json_encode( $data );
		exit();


	}

}

?>