<?php

/*
 @ctl_name = 生成Sitemap@
*/

class ctl_sitemap extends adminPage
{

	private $urlCnt = 0;
	private $xmlCnt = 1;
	private $perXmlCnt = 1000;

	public function index_action () #act_name = 生成#
	{
		if ( is_post() ) {
			$xmlDoc = null;
			$root = null;
			self::_newDom( $xmlDoc, $root );

			/*//栏目
			$mdl_info = $this->loadModel( 'info' );
			$mdl_infoclass = $this->loadModel( 'infoClass' );
			foreach ( $mdl_infoclass->getListBySql( "select id from cc_infoclass where id like '___' order by ordinal asc" ) as $category ) {
				//栏目列表页
				self::_createElement( $xmlDoc, $root, 'url', HTTP_ROOT.'list-'.$category['id'].'.html', '0.5' );
				$this->_saveDom( $xmlDoc, $root );
				foreach ( $mdl_infoclass->getListBySql( "select id from cc_infoclass where id like '".$category['id']."___' order by ordinal asc" ) as $book ) {
					//书首页
					self::_createElement( $xmlDoc, $root, 'url', HTTP_ROOT.'book-'.$book['id'].'.html', '0.8' );
					$this->_saveDom( $xmlDoc, $root );
					//书目录页
					self::_createElement( $xmlDoc, $root, 'url', HTTP_ROOT.'table-'.$book['id'].'.html', '0.8' );
					$this->_saveDom( $xmlDoc, $root );
					foreach ( $mdl_info->getListBySql( "select id from cc_info where classId='".$book['id']."' and isApproved=1 order by id asc" ) as $chap ) {
						//章节
						self::_createElement( $xmlDoc, $root, 'url', HTTP_ROOT.'read-'.$chap['id'].'.html', '0.5' );
						$this->_saveDom( $xmlDoc, $root );
					}
				}
			}*/
			if ( $this->cnt > 0 ) {
				$xmlDoc->save(DOC_DIR.'sitemap_'.$this->xmlCnt.'.xml');
				$xmlDoc = null;
				$root = null;
			}

			//检测生成的文件数量，如果大于2个，就生成索引文件，如果等于1个，就重命名
			if ( $this->xmlCnt > 1 ) {
				$xmlDoc = new DOMDocument( '1.0' );
				$xmlDoc->formatOutput = true;
				$root = $xmlDoc->createElement( 'sitemapindex' );
				$xmlDoc->appendChild( $root );
				for ( $i = 1; $i <= $this->xmlCnt; $i++ ) {
					$sitemap = $xmlDoc->createElement( 'sitemap' );
					$loc = $xmlDoc->createElement( 'loc' );
					$locText = $xmlDoc->createTextNode( HTTP_ROOT.'sitemap_'.$i.'.xml' );
					$loc->appendChild( $locText );
					$sitemap->appendChild( $loc );
					$root->appendChild( $sitemap );
				}
				$xmlDoc->save(DOC_DIR.'sitemap.xml');
				$xmlDoc = null;
				$root = null;
			}
			else {
				@unlink( DOC_DIR.'sitemap.xml' );
				rename( DOC_DIR.'sitemap_1.xml', DOC_DIR.'sitemap.xml' );
			}

			$this->sheader( '?con=admin&ctl=system/sitemap', $this->lang->generate_sitemap_success );
		}
		else {
			$this->display();
		}
	}

	private function _saveDom( & $xmlDoc, & $root ) {
		$this->cnt++;
		if ( $this->cnt >= $this->perXmlCnt ) {
			$this->cnt = 0;
			$xmlDoc->save(DOC_DIR.'sitemap_'.$this->xmlCnt.'.xml');
			$xmlDoc = null;
			$root = null;
			self::_newDom( $xmlDoc, $root );
			$this->xmlCnt++;
		}
	}

	private function _newDom( & $xmlDoc, & $root ) {
		$xmlDoc = new DOMDocument( '1.0' );
		$xmlDoc->formatOutput = true;
		$root = $xmlDoc->createElement( 'urlset' );
		$xmlDoc->appendChild( $root );
		//首页
		self::_createElement( $xmlDoc, $root, 'url', HTTP_ROOT, '1.0' );
	}

	private function _createElement( & $xmlDoc, & $root, $eleName, $locValue, $priorityValue ) {
		$url = $xmlDoc->createElement( $eleName );
		$root->appendChild( $url );
		$loc = $xmlDoc->createElement( 'loc' );
		$locText = $xmlDoc->createTextNode( $locValue );
		$loc->appendChild( $locText );
		$url->appendChild( $loc );
		$priority = $xmlDoc->createElement( 'priority' );
		$priorityText = $xmlDoc->createTextNode( $priorityValue );
		$priority->appendChild( $priorityText );
		$url->appendChild( $priority );
	}

}

?>