<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class category extends common {
	
	private $db;
	function __construct() {
		parent::__construct();
		$this->db = D('category');
	}
	
	
	/**
	 * 栏目列表
	 */
	public function init() {
		$modelinfo = get_modelinfo();
        $modelarr = array();
		foreach($modelinfo as $val){
			$modelarr[$val['modelid']] = $val['name'];
		}
		$data = $this->db->field('catid,parentid,catname,type,concat(arrparentid,",",catid) AS abspath,modelid,listorder,member_publish,pclink')->order('abspath,catid ASC')->select();
		include $this->admin_tpl('category_list');
	}
	

	/**
	 * 排序栏目
	 */
	public function order() {
		if(isset($_POST["dosubmit"])){
			foreach($_POST['catid'] as $key=>$val){
				$this->db->update(array('listorder'=>$_POST['listorder'][$key]),array('catid'=>intval($val)));
			}
			showmsg(L('operation_success'),'',1);
		}
	}

	
	/**
	 * 删除栏目
	 */
	public function delete() {
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
		
		$data = $this->db->field('arrparentid,arrchildid')->where(array('catid'=>$catid))->find();
		if(strpos($data['arrchildid'],',')){
			 showmsg('分类删除失败：该分类下有子分类！');
		}		
		if($this->db->delete(array('catid'=>$catid))){
			 if($type==1) D('page')->delete(array('catid' => $catid));    //删除单页数据
			 $this->repairs($data['arrparentid']);
			 delcache('categoryinfo');
			 delcache('mapping');
			 showmsg('分类删除成功！','',1);
		}else{
			 showmsg('分类删除失败！');
		}
	}
	
	
	/**
	 * 添加栏目
	 */
	public function add() {	
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$type = isset($_GET['type']) ? intval($_GET['type']) : intval($_POST['type']);

		if(isset($_POST['dosubmit'])) { 

			if($type != 2){   //非外部链接
				$res = $this->db->where(array('catdir' => $_POST['catdir']))->find();
				if($res) return_json(array('status'=>0,'message'=>'栏目目录已存在！'));	
			}		


			if(!$_POST['mobname']) $_POST['mobname'] = $_POST['catname']; 
			if($_POST['parentid']=='0') {
				$_POST['arrparentid'] = '0';
			}else{
				$data = $this->db->field('arrparentid,arrchildid')->where(array('catid'=>$_POST['parentid']))->find();
				$_POST['arrparentid'] = $data["arrparentid"].','.$_POST['parentid'];
			}
			
			$catid = $this->db->insert($_POST, true);

			if($type != 2){   //非外部链接
			
				if($type == 1){   //单页类型
					$arr = array();
					$arr['catid'] = $catid;					
					$arr['pagedir'] = $_POST['catdir'];										
					$arr['keywords'] = $_POST['seo_keywords'];										
					$arr['description'] = $_POST['seo_description'];										
					$arr['template'] = $_POST['category_template'];										
					$arr['updatetime'] = SYS_TIME;										
					D('page')->insert($arr); 
				}
			
				//如果系统设置是伪静态模式
				if(get_config('url_rule')){
					$_POST['pclink'] = URL_MODEL == 1 ? SITE_URL.'index.php/'.$_POST['catdir'].'/' : SITE_URL.$_POST['catdir'].'/';
				}else{  
					$_POST['pclink'] = U('index/index/lists','catid='.$catid);
				}
			}					
			
			$this->db->update(array('arrchildid' => $catid, 'pclink' => $_POST['pclink']), array('catid' => $catid));  //更新本类的子分类及更新URL
			if($_POST['parentid']!='0') $this->repairs($_POST['arrparentid']);
			delcache('categoryinfo');
			delcache('mapping');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$modelinfo = get_modelinfo();
			$tem_style = APP_PATH.'index'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.C('site_theme').DIRECTORY_SEPARATOR.'config.php';
			if(!is_file($tem_style)) showmsg($tem_style."文件不存在，请检查！", 'stop');
			$templets = require($tem_style);
			$category_temp = $templets['category_temp'];
			$list_temp = $templets['list_temp'];
			$show_temp = $templets['show_temp'];
			if($type == 0){
				include $this->admin_tpl('category_add');
			}else if($type == 1){
				include $this->admin_tpl('category_page');
			}else{
				include $this->admin_tpl('category_link');
			}			
		}
		
	}
	
	/**
	 * 编辑栏目
	 */
	public function edit() {				

		if(isset($_POST['dosubmit'])) {
			$catid = isset($_POST['catid']) ? strval(intval($_POST['catid'])) : 0;  
			
			if($_POST['parentid']=='0') {
				$_POST['arrparentid'] = '0';
			}else{
				$data = $this->db->field('arrparentid,arrchildid')->where(array('catid'=>$_POST['parentid']))->find();
				if(strpos($data['arrparentid'],$catid) !== false || $_POST['parentid']==$catid) return_json(array('status'=>0,'message'=>'不能将类别移动到自己或自己的子类别中！'));
				$_POST['arrparentid'] = $data["arrparentid"].','.$_POST['parentid'];
			}
			
			//如果有修改分类的动作
			if($_POST['arrparentid']!=$_POST['cpath']){
				$_POST['cpath'] = safe_replace($_POST['cpath']);
				$_POST['arrparentid'] = safe_replace($_POST['arrparentid']);
				$cpath = $_POST['cpath'].','.$catid;	//和现有的操作的分类id相连
				$this->db->query("UPDATE yzmcms_category SET arrparentid=REPLACE(arrparentid, '{$_POST['cpath']}','{$_POST['arrparentid']}') WHERE arrparentid like '{$cpath}%'"); 
			}
			
			if(!isset($_POST['type'])){   //非外部链接，只有外部链接设置了type字段
				//如果系统设置是伪静态模式
				if(get_config('url_rule')){
					$_POST['pclink'] = URL_MODEL == 1 ? SITE_URL.'index.php/'.$_POST['catdir'].'/' : SITE_URL.$_POST['catdir'].'/';
				}else{  
					$_POST['pclink'] = U('index/index/lists','catid='.$catid);
				}
			}
		
			if($this->db->update($_POST, array('catid' => $catid))){		
				if($_POST['arrparentid']!=$_POST['cpath']) $this->repairs($_POST['arrparentid'], $_POST['cpath']);
				delcache('categoryinfo');
				delcache('mapping');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
			
			$modelinfo = get_modelinfo();
			$tem_style = APP_PATH.'index'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.C('site_theme').DIRECTORY_SEPARATOR.'config.php';
			if(!is_file($tem_style)) showmsg($tem_style."文件不存在，请检查！", 5);
			$templets = require($tem_style);
			$category_temp = $templets['category_temp'];
			$list_temp = $templets['list_temp'];
			$show_temp = $templets['show_temp'];
			
			$data = $this->db->where(array('catid' => $catid))->find();
			if($type == 0){
				include $this->admin_tpl('category_edit');
			}else if($type == 1){
				include $this->admin_tpl('category_page_edit');
			}else{
				include $this->admin_tpl('category_link_edit');
			}			
		}
		
	}


	/**
	* 单页内容
	*/
	public function page_content() {
		if(isset($_POST['dosubmit'])) {
			$catid = isset($_POST['catid']) ? intval($_POST['catid']) : showmsg(L('illegal_operation'), 'stop');
			
			foreach($_POST as $_k=>$_v) {
				if($_k == 'content') continue;
				$_POST[$_k] = strip_tags($_v);
			}
			
			$_POST['updatetime'] = SYS_TIME;
			if(D('page')->update($_POST, array('catid' => $catid))){		
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg(L('operation_failure'));
			}

		}
		
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('illegal_operation'), 'stop');
		$data = D('page')->where(array('catid' => $catid))->find();
		yzm_base::load_sys_class('form','',0);
		include $this->admin_tpl('page_content');
	}


	
	/**
	* 批量修复栏目数据
	*/
	private function repairs($arrparentid, $cpath=null) {  
		$data1 = explode(',', $arrparentid);
		$data2 = $cpath ? explode(',', $cpath) : array();
		$data = array_merge($data1, $data2);
		foreach($data as $val){
			if($val) $this->repair($val);
		}
	}
	
	
	
	/**
	* 修复栏目数据
	*/
	private function repair($catid) {
		$this->db->update(array('arrchildid' => $this->get_arrchildid($catid)), array('catid' => $catid));  //更新本类的子分类
	}
	
	
	/**
	 * 
	 * 获取子栏目ID列表
	 * @param $catid 栏目ID
	 */
	private function get_arrchildid($catid) {
		$arrchildid = $catid;
		$data = $this->db->field('catid')->where("FIND_IN_SET('$catid',arrparentid)")->order('catid ASC')->select();
		foreach($data as $val) {
			$arrchildid .= ','.$val['catid'];
		}
		return $arrchildid;
	}	
	
}