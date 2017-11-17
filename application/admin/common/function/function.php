<?php

/**
 * 设置config文件
 * @param $config 配属信息
 */
function set_config($config) {
	$configfile = YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.'config/config.php';
	if(!is_writable($configfile)) showmsg('Please chmod '.$configfile.' to 0777 !', 'stop');
	$pattern = $replacement = array();
	foreach($config as $k=>$v) {
		$pattern[$k] = "/'".$k."'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
		$replacement[$k] = "'".$k."' => \${1}".$v."\${2}\${3},";					
	}
	$str = file_get_contents($configfile);
	$str = preg_replace($pattern, $replacement, $str);
	return file_put_contents($configfile, $str, LOCK_EX);		
}


/**
 * 显示后台菜单
 */
function show_menu() {
	if(!$menu_string = getcache('menu_string')){
		//$iconfont = array('&#xe616;','&#xe60d;','&#xe6c0;','&#xe62d;','&#xe602;','&#xe62e;','&#xe6b5;');
		$menu = D('menu');
		$top_menu = $menu->field('`id`,`name`,`m`,`c`,`a`,`data`')->where(array('parentid'=>'0','display'=>'1'))->order('listorder ASC')->limit('10')->select();
		$menu_string = '';
		foreach($top_menu as $key => $val){
			$son_menu = $menu->field('`id`,`name`,`m`,`c`,`a`,`data`')->where(array('parentid'=>$val['id'],'display'=>'1'))->order('listorder ASC')->select();
			$s = $key ==0 ? ' style="display:block;"' : '';
			$menu_string .= '<div class="menu_dropdown">
			<dl id="'.$val['id'].'-menu">
				<dt class="selected"><i class="Hui-iconfont">'.$val['data'].'</i> '.$val['name'].'<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd '.$s.'>
					<ul>';
						foreach($son_menu as $v){
							$param = empty($v['data']) ? '' :  $v['data'];
							$menu_string .= '<li><a href="javascript:void(0)" _href="'.U($v['m'].'/'.$v['c'].'/'.$v['a'], $param).'" data-title="'.$v['name'].'">'.$v['name'].'</a></li>';
						}					
					$menu_string .= '</ul>
				</dd>
			</dl>
		</div>';
		}
		setcache('menu_string', $menu_string);
	}	
	return $menu_string;
}