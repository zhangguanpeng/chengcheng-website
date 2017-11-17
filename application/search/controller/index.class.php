<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('page','',0);

class index{

	//普通搜索
	public function init(){	
		$site = get_config();
	
		$q = str_replace('%', '', new_html_special_chars(strip_tags(trim($_GET['q']))));
		if(strlen($q) <= 2 || strlen($q) >= 30){
			showmsg('你输入的字符过长或过短！');
		}
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$modelinfo = get_modelinfo();
		$modelarr = array();
		foreach($modelinfo as $val){
			$modelarr[$val['modelid']] = $val['tablename'];
		}
		if(!isset($modelarr[$modelid])) showmsg('模型不存在！');
		
		$seo_title = '‘'.$q.'’的搜索结果_'.$site['site_name'];
		$keywords = $q.','.$site['site_keyword'];
		$description = $site['site_description'];
		
		$where = "`title` LIKE '%$q%' AND `status` = 1";
		$db = D($modelarr[$modelid]);
		$total = $db->where($where)->total();
		$page = new page($total, 5);
		$search_data = $db->field('title,description,inputtime,updatetime,click,thumb,nickname,url')->where($where)->order('id DESC')->limit($page->limit())->select();
		
		$pages = '<span class="pageinfo">共<strong>'.$page->total().'</strong>页<strong>'.$total.'</strong>条记录</span>'.$page->getfull();
		include template('index','search');	
	}
	
	
	//TAG标签搜索
	public function tag(){	
		$site = get_config();
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;	
		$data = D('tag')->field('tag')->where(array('id'=>$id))->find();
		if(!$data) showmsg('TAG标签不存在！');
		$q = $data['tag'];	
			
		$tag_content = D('tag_content');
		$seo_title = $q.'_'.$site['site_name'];
		$keywords = $q.','.$site['site_keyword'];
		$description = $site['site_description'];		
	
		//为了提高搜索性能，这里只调用文章模型的tag
		$modelid = 1;
		$total = $tag_content->where(array('modelid'=>$modelid,'tagid'=>$id))->total();
		$page = new page($total, 5);
		$search_data = $tag_content->field('title,description,inputtime,updatetime,click,thumb,nickname,url')->join('`yzmcms_article` b ON yzmcms_tag_content.aid=b.id')->where(array('modelid'=>$modelid,'tagid'=>$id))->order('updatetime DESC')->limit($page->limit())->select();
		
		$pages = '<span class="pageinfo">共<strong>'.$page->total().'</strong>页<strong>'.$total.'</strong>条记录</span>'.$page->getfull();
		include template('index','search');	
	}

}