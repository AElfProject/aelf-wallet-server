<?php

/**
 * web
 *
 * @Author Today Nie
 * @Date 2018-02-03
 */

//require_once 'core/coin/manager.php';

class web extends base {
	/**
	 * 当前模板
	 */
	protected $tpl;
	/**
	 * 当前版本号
	 */
	protected $version = '3';
	/**
	 * 当前会员
	 */
	protected $user;

	public $pageKeywords = '';
	public $pageDescription = '';

    /**
     * 每次请求中的基础参数
     */
    protected $basePostData = [
        'version' => '',
        'device' => '',
        'is_store' => '',
        'udid' => '',
    ];

	/**
	 * 构造函数
	 * 所有请求都要经过此方法检测，因此都要post传递以下参数
	 */
	public function __construct() {
		parent::__construct();
	}



	/**
	 * 执行某方法之前
	 *
	 * @param string device 设备，取值范围：iOS和Android
	 * @param string udid 设备ID
	 * @param string version APP版本号
	 */
	public function before() {
		foreach ( $_GET as $key => $get ) {
			$_GET[$key] = strip_tags( $get );
		}
		foreach ( $_POST as $key => $post ) {
			$_POST[$key] = strip_tags( $post );
		}

		$this->debug['startTime'] = microtime();
	}

	/**
	 * 执行某方法之后
	 */
	public function after() {
		if ( $this->tpl ) $this->display( $this->tpl );

		$this->debug['endTime'] = microtime();
	}

	protected function user() {
		if ( !$this->user ) {
			$this->initSession();
			$sessionId =   (int) @ $_SESSION['user_id'];
			$sessionToken = @  trim( $_SESSION['user_token'] );

			if ( $sessionId <= 0 ) return false;

			$user = $this->db( 'user' )->get( $sessionId );
			if ( !$user ) return false;
			if ( !$user['status'] ) return false;

			if ( $sessionToken != $this->md5( $user['id'].$user['username'].$user['password'] ) ) return false;
			$this->user = $user;
		}

		return $this->user;
	}

	protected function page( $sql, $pageUrl, $pageSize, $maxPage = 5, $count ) {
		$pageUrl		= preg_replace( '/&?perPageCount=\d+/', '', $pageUrl );
		$perPageCount	= (int)get2( 'perPageCount' );
		if ($perPageCount > 0)
		{
			$pageSize	= $perPageCount;
			$pageUrl	.= "perPageCount={$perPageCount}&";
		}
		$page			= (int)get2( 'page' );
		$pageUrl		= $pageUrl."page=";

		$recordCount	= $count;
		$pageCount		= ceil( $recordCount / $pageSize );
		$page			= limitInt( $page, 1, $pageCount );
		$prev_page = $page - 1;
		$next_page = $page + 1;
		if ( $prev_page < 1 ) {
			$prev_page = 1;
		}
		if ( $next_page > $pageCount ) {
			$next_page = $pageCount;
		}
		$page_l = ceil( $page - $maxPage / 2 );
		if ( $page_l < 1 ) $page_l = 1;
		$page_r = $page_l + $maxPage;
		if ( $page_r > $pageCount ) $page_r = $pageCount;

		$pageStr = '';
		if ( $pageCount > 1 ) {
			if ( $page > 1 ) {
				$pageStr .= '<a href="'.$pageUrl.( $page - 1 ).'">上一页</a>';
			}
			else {
				$pageStr .= '<em>上一页</em>';
			}
			if ( $page_l > 1 ) {
				$pageStr .= '<a href="'.$pageUrl.'1">1</a><b>...</b>';
			}
			while ( $page_l <= $page_r ) {
				$pageStr .= '<a href="'.$pageUrl.$page_l.'"'.($page_l == $page ? ' class="current"' : '').'>'.$page_l.'</a>';
				$page_l++;
			}
			if ( $page_r < $pageCount ) {
				$pageStr .= '<b>...</b><a href="'.$pageUrl.$pageCount.'">'.$pageCount.'</a>';
			}
			if ( $page < $pageCount ) {
				$pageStr .= '<a href="'.$pageUrl.( $page + 1 ).'">下一页</a>';
			}
			else {
				$pageStr .= '<em>下一页</em>';
			}
		}

		$pageStart = ( $page - 1 ) * $pageSize + 1;
		$pageEnd = ( $page - 1 ) * $pageSize + $pageSize;
		if ( $page == $pageCount ) {
			$pageEnd = $recordCount;
		}
		return array(
			'recordCount' => $recordCount,
			'perPageCount' => $pageSize,
			'pageStart' => $pageStart,
			'pageEnd' => $pageEnd,
			'pageStr' => $pageStr,
			'outSql' => $sql.' limit '.( ( $page - 1 ) * $pageSize ).','.$pageSize,
			'pc' => $pageCount,
			'cp' => $page
		);
	}
}