<?php
/**
 * function.php   用户自定义函数库
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-07-10
 */


/**
 * 获取模板主题列表
 * @param string $m 模块
 * @return array
 */
function get_theme_list($m = 'index'){
	$theme_list = array();
	$list = glob(APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
	 
	foreach($list as $v){	 
		$theme_list[] = basename($v);
	}
	
	return $theme_list;
}


/**
 * 获取系统配置信息
 * @param $key 键值，可为空，为空获取整个数组
 * @return array|string
 */
function get_config($key = ''){
	if(!$configs = getcache('configs', 2)){
		$data = D('config')->select();
		$configs = array();
		foreach($data as $val){
			$configs[$val['name']] = $val['value'];
		}
		setcache('configs', $configs, 2);
	}
    if(!$key){
		return $configs;
	}else{
		return array_key_exists($key, $configs) ? $configs[$key] : '';
	}	
}


/**
 * 获取TAG标签
 * @param $tags  TAG，编辑内容时用
 * @return string
 */
function get_tags($tags = ''){
	$data = D('tag')->field('id,tag,total')->order('id DESC')->select();
	$string = '';
    if($data){
		foreach($data as $val){
			$str = strpos($tags, $val['id']) === false ? '' : ' checked="checked" ';
			$string .= '<label>'.$val['tag'].' <input name="tag[]" type="checkbox" '.$str.'value="'.$val['id'].'"/></label>&nbsp;&nbsp;';
		}
	}
	$string .= '<a href="javascript:;" onclick="yzm_open(\'添加TAG\',\''.U('admin/tag/add').'\',\'600\',\'300\')" class="yzmcms_a">点击添加</a>';
	return $string;	
}


/**
 * 获取模型信息
 * @return array
 */
function get_modelinfo(){
	if(!$modelinfo = getcache('modelinfo', 2)){
		$modelinfo = D('model')->order('modelid ASC')->select();
		setcache('modelinfo', $modelinfo, 2);
	}
    return $modelinfo;	
}


/**
 * 发送邮件    必须做好配置邮箱
 * @param $email    收件人邮箱
 * @param $title    邮件标题
 * @param $content     邮件内容
 * @param $mailtype    邮件内容类型
 * @return true or false
 */
function sendmail($email, $title = '', $content = '', $mailtype = 'HTML'){
	$mail_pass = get_config('mail_pass');
	if(!is_email($email) || empty($mail_pass)) return false;
	yzm_base::load_sys_class('smtp', '', 0);
	$smtp = new smtp(get_config('mail_server'),get_config('mail_port'),get_config('mail_auth'),get_config('mail_user'),get_config('mail_pass'));
	$state = $smtp->sendmail($email, get_config('mail_from'), $title.' - '.get_config('site_name'), $content, $mailtype);
	if(!$state) return false;
	return true;
}


/**
 * 返回json数组，默认提示 “数据未修改！” 
 * @param $arr
 * @return string
 */
function return_json($arr = array()){
	if(!$arr) exit(json_encode(array('status'=>0,'message'=>L('data_not_modified'))));
	exit(json_encode($arr));	
}


/**
 * 判断是否为手机访问
 * @return bool
 */
function ismobile(){ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){ 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])){ 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
        } 
    } 
    return false;
} 


/**
 * 返回附件类型图标
 * @param $file 附件名称
 */
function file_icon($file){
	$ext_arr = array('doc','docx','ppt','xls','txt','pdf','mdb','jpg','gif','png','bmp','jpeg','rar','zip','swf','flv');
	$ext = fileext($file);
	if(in_array($ext,$ext_arr)) return STATIC_URL.'images/ext/'.$ext.'.gif';
	else return STATIC_URL.'images/ext/hlp.gif';
}


/**
 * 会员登录跳转url
 * @param $referer
 * @return string
 */
function url_referer($referer){
	
	$referer = urlencode($referer);
	if(URL_MODEL != 0) return U('member/index/login').'?referer='.$referer;	
	return U('member/index/login').'&referer='.$referer;
}


/**
 * 获取栏目的select
 * @param $name     select的名称
 * @param $value    选中的id，用于修改
 * @param $root     顶级分类名称
 * @param $disabled 是否禁单页和外部链接
 * @param $modelid  modelid
 * @return string
 */
function select_category($name="parentid", $value="0", $root="", $disabled=true, $modelid=0){
	if($root == '') $root="≡ 作为一级栏目 ≡";
	$where = $modelid ? 'modelid = '.$modelid : '';
	$data = D('category')->field('catid,parentid,catname,type,concat(arrparentid,",",catid) AS abspath')->where($where)->order('abspath,catid ASC')->select();
	$html='<select id="select" name="'.$name.'" class="select">';
	$html.='<option value="0">'.$root.'</option>';
	foreach($data as $val){			
		$str = $value != $val['catid'] ? '' : ' selected="selected" ';
		if($disabled) $str .= $val['type'] == 0 ? '' : ' disabled="disabled" ';
		$html .= '<option '.$str.'value="'.$val['catid'].'">';
		$num = count(explode(',', $val['abspath']))-2;
		$space = str_repeat("|&nbsp;&nbsp;&nbsp;&nbsp;",$num);	
		$name = $val['catname'];
		$html .= $space."|-&nbsp;".$name;
		$html .= '</option>';	
	}
	$html .= '</select>';

	return $html;
}


/**
 * 获取栏目信息
 *
 * @param  int $catid
 * @param  string $parameter
 * @return array or string
 */
function get_category($catid = '', $parameter = ''){
    if(!$categoryinfo = getcache('categoryinfo', 2)){
		$data = D('category')->order('listorder ASC, catid ASC')->select();
		$categoryinfo = array();
		foreach($data as $val){
			$categoryinfo[$val['catid']] = $val;
		}
		setcache('categoryinfo', $categoryinfo, 2);
	}
	if($catid){
		if(empty($parameter))
			return array_key_exists($catid,$categoryinfo) ? $categoryinfo[$catid] : array();
		else
			return array_key_exists($catid,$categoryinfo) ? $categoryinfo[$catid][$parameter] : '';
	}else{
		return $categoryinfo;	
	}
    
}


/**
 * 根据栏目ID获取栏目名称
 *
 * @param  int $catid
 * @return string
 */
function get_catname($catid){
	$catid = intval($catid);
    $data = get_category($catid);
	if(!$data) return '';
    return $data['catname']; 	
}


/**
 * 根据栏目ID获取子栏目信息
 *
 * @param  int $catid
 * @return array
 */
function get_childcat($catid){
	$catid = intval($catid);
    $data = get_category();
	$r = array();
	foreach($data as $v){
		if($v['parentid'] == $catid) $r[] = $v;
	}
    return $r; 	
}


/**
 * 根据栏目ID获取当前位置
 *
 * @param  int $catid
 * @param  bool $self 是否包含本身 0为不包含
 * @param  string $symbol 栏目间隔符
 * @return string
 */
function get_location($catid, $self=true, $symbol=' &gt; '){
	$catid = intval($catid);
    $catdata = get_category();
	$data = explode(',', $catdata[$catid]['arrparentid']);
	$str = '<a href="'.SITE_URL.'">首页</a>';
	foreach($data as $v){
		if($v) $str .= $symbol.'<a href="'.$catdata[$v]['pclink'].'" title="'.$catdata[$v]['catname'].'">'.$catdata[$v]['catname'].'</a>';
	}
	if($self) $str .= $symbol.'<a href="'.$catdata[$catid]['pclink'].'" title="'.$catdata[$catid]['catname'].'">'.$catdata[$catid]['catname'].'</a>';
    return $str;	
}



/**
 * 根据模型ID获取model信息
 *
 * @param  int $modelid
 * @param  bool $parameter 获取键名称
 * @return string
 */
function get_model($modelid, $parameter = 'tablename'){
	$modelinfo = get_modelinfo();
	$modelarr = array();
	foreach($modelinfo as $val){
		$modelarr[$val['modelid']] = $val;
	}
	if(!isset($modelarr[$modelid])) return false;
	return $modelarr[$modelid][$parameter];
}


/**
 * 获取组别信息
 *
 * @param  int $groupid
 * @return array
 */
function get_groupinfo($groupid = ''){
    if(!$member_group = getcache('member_group', 2)){
		$data = D('member_group')->select();
		$member_group = array();
		foreach($data as $val){
			$member_group[$val['groupid']] = $val;
		}
		setcache('member_group', $member_group, 2);
	}
	if($groupid){
		return array_key_exists($groupid,$member_group) ? $member_group[$groupid] : array();
	}else{
		return $member_group;	
	}
    
}


/**
 * 根据组别ID获取组别名称
 *
 * @param  int $catid
 * @return string
 */
function get_groupname($groupid){
	$groupid = intval($groupid);
    $data = get_groupinfo($groupid);
	if(!$data) return '';
    return $data['name']; 	
}


/**
 * 获取用户头像
 * @param $user userid或者username
 * @param $type 1为根据userid查询，其他为根据username查询, 建议根据userid查询
 * @param default 如果用户头像为空，是否显示默认头像
 * @return string
 */
function get_memberavatar($user, $type=1, $default=true) {
	global $member_detail;
	$member_detail = isset($member_detail) ? $member_detail : D('member_detail');
	if($type == 1){
		$data = $member_detail->field('userpic')->where(array('userid' => $user))->find();
	}else{
		$data = $member_detail->field('userpic')->join('yzmcms_member b ON yzmcms_member_detail.userid=b.userid')->where(array('username' => $user))->find();
	}	
	return $data['userpic'] ? $data['userpic'] : ($default ? STATIC_URL.'images/default.gif' : '');
}


/**
 * 设置路由映射
 */
function set_mapping() {
    if(!$mapping = getcache('mapping', 2)){
		$data = D('category')->field('type,catid,catdir')->where(array('display' => 1, 'type!=' => 2))->select();
		$mapping = array();
		foreach($data as $val){
			$mapping['^'.$val['catdir'].'$'] = 'index/index/lists/catid/'.$val['catid'];
			if(!$val['type']){
			$mapping['^'.$val['catdir'].'\/list_(\d+)$'] = 'index/index/lists/catid/'.$val['catid'].'/page/$1';
			$mapping['^'.$val['catdir'].'\/(\d+)$'] = 'index/index/show/catid/'.$val['catid'].'/id/$1';				
			}
		}
		//结合自定义URL规则
		$route_rules = get_urlrule();
		if(!empty($route_rules)) $mapping = array_merge($route_rules, $mapping); 
		setcache('mapping', $mapping, 2);
	}
	return $mapping;
}



/**
 * 获取自定义URL规则
 * @return array
 */
function get_urlrule() {
    if(!$urlrule = getcache('urlrule', 2)){
		$data = D('urlrule')->select();
		$urlrule = array();
		foreach($data as $val){
			$val['urlrule'] = '^'.str_replace('/', '\/', $val['urlrule']).'$';
			$urlrule[$val['urlrule']] = $val['route'];
		}
		setcache('urlrule', $urlrule, 2);
	}
	return $urlrule;	
}


/**
 * 获取用户所有信息
 * @param $userid 
 * @param $additional  是否获取附表信息
 * @return array or false
 */
function get_memberinfo($userid, $additional=false){	
	$memberinfo = array();
	global $member;
	$member = isset($member) ? $member : D('member');
	$memberinfo = $member->field('username,regdate,lastdate,lastip,loginnum,email,groupid,amount,experience,point,status,vip,overduedate,email_status')->where(array('userid' => $userid))->find();
	if(!$memberinfo) return false;
	
	if($additional){
		global $member_detail;
		$member_detail = isset($member_detail) ? $member_detail : D('member_detail');
		$data = $member_detail->field('sex,realname,nickname,qq,mobile,phone,userpic,birthday,industry,area,motto,introduce,guest')->where(array('userid' => $userid))->find();
		$memberinfo = array_merge($memberinfo, $data);
	}
	return $memberinfo;
}


/**
 * 模板调用
 *
 * @param $module
 * @param $template
 * @return unknown_type
 */
function template($module = '', $template = 'index'){
	if(!$module) $module = 'index';
	$template_c = YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR;
	$template_path = !defined('MODULE_THEME') ? APP_PATH.$module.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.C('site_theme').DIRECTORY_SEPARATOR : APP_PATH.$module.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.MODULE_THEME.DIRECTORY_SEPARATOR;;
    $filename = $template.'.html';
	$tplfile = $template_path.$filename;   
	if(!is_file($tplfile)) {
		showmsg(str_replace(YZMPHP_PATH, '', $tplfile).L('template_does_not_exist'),'stop');			                      
	}	
	if(!is_dir(YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR)){
		@mkdir(YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR, 0777, true);
	}	
	$template_c = $template_c.$template.'.tpl.php'; 		
	if(!is_file($template_c) || filemtime($template_c) < filemtime($tplfile)) {
		$yzmtpl = yzm_base::load_sys_class('yzmtpl');
		$compile = $yzmtpl->tpl_replace(@file_get_contents($tplfile));
		file_put_contents($template_c, $compile);
	}
	return $template_c;
}


/**
 * 对用户的密码进行加密
 * @param $pass 字符串
 * @return string 字符串
 */
function password($pass) {
	return substr(md5($pass), 3, 26);
}