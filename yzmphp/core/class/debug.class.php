<?php
/**
 * debug.class.php   debug类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-08-19 
 */
 
class debug {
	static $includefile = array();
	static $info = array();
	static $sqls = array();
	static $stoptime; 
	
	static $msg = array(
				E_WARNING => '运行时警告',
				E_NOTICE => '运行时提醒',
				E_STRICT => '编码标准化警告',
				E_USER_ERROR => '自定义错误',
				E_USER_WARNING => '自定义警告',
				E_USER_NOTICE => '自定义提醒',
				'Unkown ' => '未知错误'
	        );
	
	/**
	 *在脚本结束处调用获取脚本结束时间的微秒值
	 */
	static function stop(){
		self::$stoptime= microtime(true);  
	}

	/**
	 *返回同一脚本中两次获取时间的差值
	 */
	static function spent(){
		return round((self::$stoptime - SYS_START_TIME) , 4);  //计算后以4舍5入保留4位返回
	}

	/**
	 * 错误 handler
	 */
	static function catcher($errno, $errstr, $errfile, $errline){
		if(APP_DEBUG){
			if(!isset(self::$msg[$errno])) 
				$errno='Unkown';

			if($errno==E_NOTICE || $errno==E_USER_NOTICE)
				$color="#151515";
			else
				$color="red";

			$mess = '<span style="color:'.$color.'">';
			$mess .= '<b>'.self::$msg[$errno].'</b> [文件 '.$errfile.' 中,第 '.$errline.' 行] ：';
			$mess .= $errstr;
			$mess .= '</span>'; 		
			self::addmsg($mess);			
		}else{
			if($errno==8) return '';
			error_log('<?php exit;?> Error : '.date('m-d H:i:s').' | '.$errno.' | '.str_pad($errstr,30).' | '.$errfile.' | '.$errline."\r\n", 3, YZMPHP_PATH.'cache/error_log.php');
		}
	}
	
	/**
	 * 添加调试消息
	 * @param	string	$msg	调试消息字符串
	 * @param	int	    $type	消息的类型
	 */
	static function addmsg($msg, $type=0) {
		switch($type){
			case 0:
				self::$info[] = $msg;
				break;
			case 1:
				self::$sqls[] = $msg.';';
				break;
			case 2:
				self::$includefile[] = '<b>'.$msg.'.class.php</b> ';
				break;
		}
	}
	
	/**
	 * 输出调试消息
	 */
	static function message(){
		include(YP_PATH.'core'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'debug.tpl');	
	}
}
