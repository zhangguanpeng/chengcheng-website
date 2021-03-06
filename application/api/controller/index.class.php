<?php
/**
 * 系统API接口类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-18
 */
 
class index{
	
	
	/**
	 * 验证码图像
	 */
	public function code(){	
		session_start();
		yzm_base::load_sys_class('code', '', 0);
		$width = isset($_GET['width']) ? intval($_GET['width']) : 90;
		$height = isset($_GET['height']) ? intval($_GET['height']) : 30;
		$code = new code($width, $height);
		$code->showimage();
		$_SESSION['code'] = $code->getcheckcode(); 
	}
	

	/**
	 * 收藏文档，必须登录
	 * @param url 地址
	 * @param title 标题
	 * @return {1:成功;-1:未登录;-2:缺少参数}
	 */	
	public function favorite(){	
		session_start();
		if(isset($_POST['title']) && isset($_POST['url'])) {
			$title = htmlspecialchars(addslashes($_POST['title']));
			$url = safe_replace(addslashes($_POST['url']));
		} else {
			return_json(array('status' => -2));
		}

		//检查是否是否有存在已登录的用户
		if(!isset($_SESSION['_userid'])){
			return_json(array('status' => -1));
		}

		$data = array('title'=>$title, 'url'=>$url, 'inputtime'=>SYS_TIME, 'userid'=>$_SESSION['_userid']);

		$favorite = D('favorite');

		//根据url判断是否已经收藏过。
		$is_exists = $favorite->where(array('url'=>$url, 'userid'=>$_SESSION['_userid']))->find();
		if(!$is_exists) {
			$favorite->insert($data);
		}

		return_json(array('status' => 1));
	}

}