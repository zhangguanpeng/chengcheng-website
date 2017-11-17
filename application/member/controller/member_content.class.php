<?php
/**
 * 会员中心内容操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-18
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'member', 0);
yzm_base::load_sys_class('page','',0);

class member_content extends common{
	
	function __construct() {
		parent::__construct();
	}

	
	/**
	 * 在线投稿
	 */	
	public function init(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$this->_check_group_auth($groupid); 
		yzm_base::load_sys_class('form','',0);
		$category_data = D('category')->field('catid,catname')->where(array('member_publish'=>1))->select(); //只查询允许投稿的栏目
		
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$modelid = !$catid ? 1 : get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('illegal_operation'), 'stop');
			
		$fieldstr = $this->_get_model_str($modelid);
		
		include template('member', 'publish');
	}


	/**
	 * 在线投稿-发布稿件
	 */	
	public function publish(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$this->_check_group_auth($groupid);	
		
		//会员中心可发布的字段
		$fields = array('title','copyfrom','catid','thumb','description','content');
	
		if(isset($_POST['dosubmit'])) {
			
			$catid = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$catid))->find();
			if(!$data['member_publish']) showmsg(L('illegal_operation'), 'stop');
			
			//支持不同栏目自动实例化不同的model
			$modelid = get_category($catid, 'modelid');
			
			yzm_base::load_sys_class('form','',0);
			$model_field = $this->_get_model_str($modelid, true);
			$fields = array_merge($fields, $model_field);
			
			foreach($_POST as $_k=>$_v) {
				if(!in_array($_k, $fields)){
					unset($_POST[$_k]);
					continue;
				} 
				if($_k == 'content') {
					$_POST[$_k] = remove_xss(strip_tags($_v, '<p><a><br><img><ul><li><div>'));
				}else{
					$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
				}
			}
			
			$_POST['seo_title'] = $_POST['title'].'_'.get_config('site_name');
			$_POST['system'] = '0';
			$_POST['status'] = '0';	    //发布状态：1正常0隐藏
			$_POST['listorder'] = '10';	//为内容置顶做准备
			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),200) : $_POST['description'];
			$_POST['inputtime'] = SYS_TIME;
			$_POST['updatetime'] = SYS_TIME;
			$_POST['catid'] = $catid;
			$_POST['userid'] = $userid;	
			$_POST['username'] = $username;	
			$_POST['nickname'] = $nickname;		
			
			
			$content_tabname = D(get_model($modelid));
			
			$id = $content_tabname->insert($_POST);
			
			//发布到用户内容列表中
			$_POST['checkid'] = $modelid.'_'.$id;
			D('member_content')->insert($_POST);
				
			showmsg('发布成功，等待管理员审核！', U('not_pass'));
		}
		
	}
	
	
	/**
	 * 编辑稿件
	 */	
	public function edit_through(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$this->_check_group_auth($groupid);
		yzm_base::load_sys_class('form','',0);
		
		//会员中心可发布的字段
		$fields = array('title','copyfrom','catid','thumb','description','content');
		
		//可以根据catid获取model模型，来加载不同模板
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');  
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
	
		if(isset($_POST['dosubmit'])) {
			
			$_POST['catid'] = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$_POST['catid']))->find();
			if(!$data['member_publish']) showmsg(L('illegal_operation'), 'stop');
			
			//根据POST传回的参数再次判断一下modelid（必须）
			$modelid = get_category($_POST['catid'], 'modelid');
			if(!$modelid){
				showmsg(L('operation_failure'), 'stop');
			}
			$content_tabname = D(get_model($modelid));			
			
			$member_content = D('member_content');
			$data = $member_content->field('username,status')->where(array('checkid' =>$modelid.'_'.$id))->find();
			//只能编辑自己的 且 未通过审核的
			if(!$data || $data['username'] != $username || $data['status'] == 1){
				showmsg(L('illegal_operation'), 'stop');
			}
			
			$model_field = $this->_get_model_str($modelid, true);
			$fields = array_merge($fields, $model_field);
			
			foreach($_POST as $_k=>$_v) {
				if(!in_array($_k, $fields)){
					unset($_POST[$_k]);
					continue;
				}
				if($_k == 'content') {
					$_POST[$_k] = remove_xss(strip_tags($_v, '<p><a><br><img><ul><li><div>'));
				}else{
					$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
				}
			}
			
			$_POST['seo_title'] = $_POST['title'].'_'.get_config('site_name');
			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),200) : $_POST['description'];
			$_POST['updatetime'] = SYS_TIME;
			$_POST['status'] = 0;	
			
			if($content_tabname->update($_POST, array('id' => $id))){
				$member_content->update($_POST, array('checkid' =>$modelid.'_'.$id));	//更新会员内容表
				showmsg(L('operation_success'), U('not_pass'));
			}
		}
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('lose_parameters'), 'stop');  
		$content_tabname = D(get_model($modelid));
		
		$data = $content_tabname->where(array('id' => $id))->find(); 
		
		$fieldstr = $this->_get_model_str($modelid, false, $data);
		
		include template('member', 'edit_through');
	}

	
	
	//已通过的稿件
	public function pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>1))->total();
		$page = new page($total, 10);
		$res = $member_content->field('checkid,catid,title,inputtime')->where(array('userid' =>$userid,'status' =>1))->order('inputtime DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$val['url'] = SITE_URL.'index.php?m=index&c=index&a=show&catid='.$val['catid'].'&id='.$val['id'];
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'publish_through');
	}



	//未通过的稿件
	public function not_pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>0))->total();
		$page = new page($total, 10);
		$res = $member_content->field('checkid,catid,title,inputtime,status')->where(array('userid' =>$userid,'status!=' =>1))->order('inputtime DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'publish_not_through');
	}
	
	
	
	//删除未通过的稿件
	public function del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid){
			showmsg(L('operation_failure'), 'stop');
		}
		$content_tabname = D(get_model($modelid));
		$member_content = D('member_content');
		$data = $member_content->field('username,status')->where(array('checkid' =>$modelid.'_'.$id))->find();
		//只能删除自己的 且 未通过审核的
		if($data && $data['username'] == $username && $data['status'] != 1){
			$member_content->delete(array('checkid' =>$modelid.'_'.$id));	//删除会员内容表
			$content_tabname->delete(array('id' => $id));	 //删除model内容表
		}
		showmsg(L('operation_success'));
	}
	
	
	
	//收藏夹
	public function favorite(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$favorite = D('favorite');
		$total = $favorite->where(array('userid' =>$userid))->total();
		$page = new page($total, 10);
		$data = $favorite->where(array('userid' =>$userid))->order('id DESC')->limit($page->limit())->select();
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'favorite');
	}
	
	
	
	//删除收藏夹
	public function favorite_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		if(!isset($_POST['fx'])) showmsg('您没有选择项目！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$favorite = D('favorite');
		foreach($_POST['fx'] as $v){
			$favorite->delete(array('id' => intval($v), 'userid' => $userid));
		}
		showmsg(L('operation_success'));
	}

	
	//检查会员组权限
	private function _check_group_auth($groupid){
		$groupinfo = get_groupinfo($groupid);
		if(strpos($groupinfo['authority'], '3') === false) 
		showmsg('你没有权限投稿，请升级会员组！', 'stop'); 
	}
	
	
	//获取不同模型获取HTML表单
	private function _get_model_str($modelid, $field = false, $data = array()) {
		$modelinfo = getcache($modelid.'_model', 2);
		if($modelinfo === false){
			$modelinfo = D('model_field')->where(array('modelid' => $modelid, 'disabled' => 0))->order('listorder ASC')->select();
			setcache($modelid.'_model', $modelinfo, 2);
		}
		
		$fields = $fieldstr = array();
		foreach($modelinfo as $val){
			if($val['isadd'] == 0) continue;
			$fieldtype = $val['fieldtype'];
			if($data){
				$val['defaultvalue'] = isset($data[$val['field']]) ? $data[$val['field']] : '';
			}
			$setting = $val['setting'] ? string2array($val['setting']) : 0;
			$required = $val['isrequired'] ? '<span class="red">*</span>' : '';
			$fieldstr[] = '<td>'.$val['name'].'：</td><td>'.form::$fieldtype($val['field'], $val['defaultvalue'], $setting).$required.'</td>';	
			$fields[] = $val['field'];
		}
		
		return $field ? $fields : $fieldstr;
	}
	
	
	/**
	 * 内容处理
	 * @param $content 
	 */	
	private function _content_dispose($content) {
		$is_array = false;
		foreach($content as $val){
			if(is_array($val)) $is_array = true;
			break;
		}
		if(!$is_array) return safe_replace(implode(',', $content));
		
		//这里认为是多文件上传
		$arr = array();
		foreach($content['url'] as $key => $val){
			$arr[$key]['url'] = safe_replace($val);
			$arr[$key]['alt'] = safe_replace($content['alt'][$key]);
		}		
		return array2string($arr);
	}
}