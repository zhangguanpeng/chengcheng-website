<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_common('lib/content_form'.EXT, 'admin');
yzm_base::load_sys_class('page','',0);

class content extends common {
	
	private $content;
	public function __construct() {
		parent::__construct();
		$this->content = M('content_model');
	}

	/**
	 * 内容列表
	 */
	public function init() {
		$modelinfo = $this->content->modelarr;
		$content = D('article'); //默认加载文章列表
		$modelid = 1; //默认加载文章模型
		$catid = 0; //默认加载全部分类
		$total = $content->total();
		$page = new page($total, 10);
		$data = $content->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('content_list');
	}


	/**
	 * 内容搜索
	 */
	public function search() {
		$modelinfo = $this->content->modelarr;
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$content = D($this->content->tabname);
		$where = '1=1';
		if(isset($_GET['dosubmit'])){	
		
			$searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;


			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND title LIKE \'%'.$searinfo.'%\'';
				else
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
			}

			if($catid != '0'){
				$where .= ' AND catid='.$catid;
			}

			if(isset($_GET["start"]) && $_GET["start"] != '' && $_GET["end"]){		
				$where .= ' AND updatetime BETWEEN '.strtotime($_GET["start"]).' AND '.strtotime($_GET["end"]);
			}

			if(isset($_GET["flag"]) && $_GET["flag"] != '0'){
				$where .= ' AND FIND_IN_SET('.intval($_GET["flag"]).',flag)';
			}

			if(isset($_GET["status"]) && $_GET["status"] != '99'){
				$where .= ' AND status = '.intval($_GET["status"]);
			}	
			
		}
		$total = $content->where($where)->total();
		$page = new page($total, 10);
		$data = $content->where($where)->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('content_list');
	}
	
	
	/**
	 * 添加内容
	 */
	public function add() {

		if(isset($_POST['dosubmit'])) {
			$r = $this->content->content_add($_POST);
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
			if($r){
				showmsg(L('operation_success'), U('search', array('modelid'=>$modelid)), 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}else{
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : intval(get_cookie('catid'));
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
			$content_form = new content_form($modelid);
			$string = $content_form->content_add();
			$member_group = get_groupinfo();
			include $this->admin_tpl('content_add');
		}
	}
	
	
	/**
	 * 修改内容
	 */
	public function edit() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(isset($_POST['dosubmit'])) {
			$r = $this->content->content_edit($_POST, $id);
			if($r){
				echo '<script type="text/javascript">parent.location.reload();</script>';
			}else{
				showmsg(L('data_not_modified'));
			}
		}else{
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
			$content = D($this->content->tabname);
			$data = $content->where(array('id'=>$id))->find();
			$data['tag'] = $this->get_tags($id);
			$content_form = new content_form($_GET['modelid']);
			$string = $content_form->content_edit($data);
			$member_group = get_groupinfo();
			include $this->admin_tpl('content_edit');	
		}
	}
	
	
	/**
	 * 删除内容
	 */
	public function del() {
		if($_POST && is_array($_POST['ids'])){
			$tag_content = D('tag_content'); 
			$member_content = D('member_content'); 
			foreach($_POST['ids'] as $id){
				$this->content->content_delete($id);
				$tag_content->delete(array('modelid' => $this->content->modelid, 'aid'=>$id));
				$member_content->delete(array('checkid' => $this->content->modelid.'_'.$id));  //删除会员内容表
			}
		}
		showmsg(L('operation_success'),'',1);
	}
	
	
	/**
	 * 批量移动分类
	 */
	public function remove() {
		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 1;
		if(isset($_POST['dosubmit'])) {
			$ids = safe_replace($_POST['ids']);
			$ids_arr = explode(',', $ids);
			$ids_arr = array_map('intval', $ids_arr);
			$ids = join(',', $ids_arr);
			$catid = intval($_POST['catid']);
			$content = D($this->content->tabname);
			$affected = $content->update(array('catid' => $catid), 'id IN ('.$ids.')');
			showmsg('成功移动 '.$affected.' 个内容！', U('search', array('modelid' => $modelid)));
		}else{
			$ids = is_array($_POST['ids']) ? join(',', $_POST['ids']) : '';
			include $this->admin_tpl('content_remove');	
		}
	}
	
	
	/**
	 * 获取TAG
	 */
	public function get_tags($id) {
		$r = D('tag_content')->field('tagid')->where(array('modelid' => $this->content->modelid, 'aid' => $id))->select();
		$str = '';
		foreach($r as $val){
			$str .= $val['tagid'].',';
		}
		return $str;
	}

}