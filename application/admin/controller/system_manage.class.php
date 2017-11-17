<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class system_manage extends common {

	/**
	 * 配置信息
	 */
	public function init() {
		$data = get_config();
		$theme_list = get_theme_list();
		include $this->admin_tpl('system_set');
	}
	

	/**
	 * 会员中心设置
	 */
	public function member_set() {
		$data = get_config();
		include $this->admin_tpl('member_set', 'member');
	}
	
	
	
	/**
	 * 保存配置信息
	 */
	public function save() {
		yzm_base::load_common('function/function.php', 'admin');
		if(isset($_POST['dosubmit'])){
			if(isset($_POST['mail_inbox']) && $_POST['mail_inbox']){
				if(!is_email($_POST['mail_inbox'])) showmsg(L('mail_format_error'));
			}
			$arr = array();
			$config = D('config');
			foreach($_POST as $key => $value){
				if(in_array($key, array('site_theme','watermark_enable','watermark_name','watermark_position'))) {
					$value = safe_replace(trim($value));
					$arr[$key] = $value;
				}elseif($key!='site_code'){
					$value = htmlspecialchars($value);
				}
				$config->update(array('value'=>$value), array('name'=>$key));
			}
			set_config($arr);
			delcache('configs');
			showmsg(L('operation_success'));
		}
	}		

	/**
	 * 屏蔽词管理
	 */
	public function prohibit_words() {
		if(isset($_POST['dosubmit'])){
			D('config')->update(array('value'=>$_POST['prohibit_words']), array('name'=>'prohibit_words'), true);
			delcache('configs');
			showmsg(L('operation_success'));
		}
		include $this->admin_tpl('prohibit_words');
	}
	
	/*
	 * 测试邮件配置
	 */
	public function public_test_mail() {
		if(sendmail($_POST['mail_to'], 'YzmCMS邮件测试', '这是一封测试邮件，如果您成功接收此邮件，说明您的邮件配置正确！')){
			exit('发送邮件成功！');
		}else{
			exit('发送邮件失败！');
		}	
	}
	
	/*
	 * 用户自定义配置列表
	 */
	public function user_config_list() {
		$config = D('config');
		$total = $config->where(array('status' => 0))->total();
		$page = new page($total, 10);
		$data = $config->where(array('status' => 0))->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('user_config_list');
	}

	/*
	 * 用户自定义配置添加
	 */
	public function user_config_add() {
		if(isset($_POST['dosubmit'])){
			$config = D('config');
			$res = $config->where(array('name' => $_POST['name']))->find();
			if($res) return_json(array('status'=>0,'message'=>'配置名称已存在！'));
			$_POST['type'] = 99;
			$_POST['status'] = 0;
			if($config->insert($_POST)){
				delcache('configs');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('data_not_modified')));
			}			
		}
		include $this->admin_tpl('user_config_add');
	}

	/*
	 * 用户自定义配置编辑
	 */
	public function user_config_edit() {
		if(isset($_POST['dosubmit'])) {
			if(D('config')->update(array('title' => $_POST['title'], 'value' => $_POST['value']), array('id' => intval($_POST['id'])))){
				delcache('configs');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
						
		} else {
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('config')->where(array('id' => $id))->find();
			include $this->admin_tpl('user_config_edit');			
		}
	}

	/*
	 * 用户自定义配置删除
	 */
	public function user_config_del() {
		if($_POST && is_array($_POST['id'])){
			if(D('config')->delete($_POST['id'], true)){
				delcache('configs');
				showmsg(L('operation_success'));
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}	
}