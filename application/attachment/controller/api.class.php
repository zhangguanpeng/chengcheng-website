<?php
defined('IN_YZMPHP') or exit('Access Denied');
		
//此处为解决Uploadify在火狐下出现http 302错误,重新设置SESSION
$session_name = session_name();
if(isset($_POST[$session_name])) session_id($_POST[$session_name]);

session_start();

yzm_base::load_sys_class('upload','',0);
yzm_base::load_sys_class('page','',0);

class api{
	
	private $isadmin;
	private $groupid;
	private $userid;
	private $username;
	
	function __construct() {
		
		$this->userid = isset($_SESSION['adminid']) ? $_SESSION['adminid'] : (isset($_SESSION['_userid']) ? $_SESSION['_userid'] : 0);
		$this->username = isset($_SESSION['adminname']) ? $_SESSION['adminname'] : (isset($_SESSION['_username']) ? $_SESSION['_username'] : '');
		$this->isadmin = isset($_SESSION['roleid']) ? 1 : 0;
		$this->groupid = get_cookie('_groupid') ? intval(get_cookie('groupid')) : 0;

		//判断是否登录
		if($this->userid==0){
			showmsg(L('login_website'),U('member/index/login'),1);
		}
		
		//判断是否有权限
		//if($this->isadmin==0 && !$grouplist[$this->groupid]['allowattachment']){
		//if($this->userid==0){
			//showmsg(L('no_permission_to_access'),U('admin/index/login'),1);
		//}
		
	}	
	
	
	
	public function init(){
		
	}

	
	
	/**
	 * 上传文件
	 */	
	public function upload(){ 

		//$filename = isset($_POST['filename']) ? $_POST['filename'] : 'Filedata';
		$filename = 'Filedata';
		$type = isset($_POST['type']) ? intval($_POST['type']) : 1;
		$module = isset($_POST['module']) ? htmlspecialchars($_POST['module']) : '';
		$option = array();
		if($type == 1){
			$option['allowtype'] = array('gif', 'jpg', 'png', 'jpeg');
		}elseif($type == 2){		
			$option['allowtype'] = array('zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf');
		}else{
			$option['allowtype'] = array('mp4', 'avi', 'wmv', 'rmvb', 'flv');
		}
		$upload = new upload($option);
		if($upload->uploadfile($filename)){
			$fileinfo = $upload->getnewfileinfo();
			$fileinfo['module'] = $module;
			$fileinfo['originname'] = safe_replace($fileinfo['originname']);
			$this->_att_write($fileinfo);
			$arr = array(
				'status' => 1,
				'msg' => $fileinfo['fileurl'].$fileinfo['filename'],
				'title' => $fileinfo['originname'],
				'size' => $fileinfo['filesize'],
				'filetype' => $fileinfo['filetype']
			);
			return_json($arr);
		}else{
			$arr = array(
				'status' => 0,
				'msg' => $upload->geterrormsg()
			);
			return_json($arr);
		} 
	}
	
	
	/**
	 * 上传框
	 */	
	public function upload_box(){ 
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 'uploadfile';
		$module = isset($_GET['module']) ? $_GET['module'] : '';
		$t = isset($_GET['t']) ? intval($_GET['t']) : 1; //上传类型，1为图片类型
		$n = isset($_GET['n']) ? 20 : 1;  //上传数量
		$s = round(get_config('upload_maxsize')/1024, 2).'MB';  //允许上传附件大小
		switch ($t){
			case 1:
			  $type = '*.jpg; *.jpeg; *.png; *.gif;';
			  break;  
			case 2:
			  $type = '*.zip; *.rar; *.doc; *.docx; *.xls; *.xlsx; *.ppt; *.pptx; *.pdf;';
			  break;
			case 3:
			  $type = '*.mp4; *.avi; *.wmv; *.rmvb; *.flv;';
			  break;
			//case 4:
			//  $type = '*.doc; *.docx; *.xls; *.xlsx; *.ppt; *.pptx; *.pdf; *.txt;';
			//  break;
			default:
			  $type = '*.jpg; *.jpeg; *.png; *.gif;';
		}
		
		//如果不是管理员，只列出自己上传的附件
		$where = array();
		if(!$this->isadmin) $where['userid'] = $this->userid;
		$attachment = D('attachment');
		$total = $attachment->where($where)->total();
		$parameter = $_GET;
		$parameter['tab'] = 1;
		$page = new page($total, 8, $parameter);
		$data = $attachment->field('isimage,originname,filename,filepath,fileext')->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('upload_box'); 
	}
		
	
	/**
	 * 上传附件写入数据库
	 */	
	private function _att_write($fileinfo){
		$arr = array();
		$arr['originname'] = strlen($fileinfo['originname'])<50 ? $fileinfo['originname'] : $fileinfo['filename'];
		$arr['filename'] = $fileinfo['filename'];
		$arr['filepath'] = $fileinfo['filepath'];
		$arr['filesize'] = $fileinfo['filesize'];
		$arr['fileext'] = $fileinfo['filetype'];
		$arr['module'] = $fileinfo['module'];
		$arr['isimage'] = in_array($fileinfo['filetype'], array('gif', 'jpg', 'png', 'jpeg')) ? 1 : 0;
		$arr['downloads'] = 0;
		$arr['userid'] = $this->userid;
		$arr['username'] = $this->username;
		$arr['uploadtime'] = SYS_TIME;
		$arr['uploadip'] = getip();
		D('attachment')->insert($arr);
	}

	
	public static function admin_tpl($file = 'Undefined', $m = '') {
		$m = empty($m) ? ROUTE_M : $m;
		if(empty($m)) return false;
		return APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$file.'.html';
	}

}