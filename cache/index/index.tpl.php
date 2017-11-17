<?php defined('IN_YZMPHP') or exit('No permission resources.'); ?><!DOCTYPE html>
<html>
  <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	  <title><?php echo $seo_title;?></title>
	  <link href="<?php echo STATIC_URL;?>css/default_common.css" rel="stylesheet" type="text/css" />
	  <link href="<?php echo STATIC_URL;?>css/default_index.css" rel="stylesheet" type="text/css" />
	  <script type="text/javascript" src="<?php echo STATIC_URL;?>js/jquery-1.8.2.min.js"></script>
	  <script type="text/javascript" src="<?php echo STATIC_URL;?>js/js.js"></script>
	  <script type="text/javascript" src="<?php echo STATIC_URL;?>js/koala.min.1.5.js"></script> <!-- 焦点图js -->
	  <meta name="keywords" content="<?php echo $keywords;?>" />
	  <meta name="description" content="<?php echo $description;?>" />
	  <meta http-equiv="mobile-agent" content="format=xhtml;url=<?php echo $site['site_url'];?>index.php?m=mobile">
      <script type="text/javascript">if(window.location.toString().indexOf('pref=padindex') != -1){}else{if(/AppleWebKit.*Mobile/i.test(navigator.userAgent) || (/MIDP|SymbianOS|NOKIA|SAMSUNG|LG|NEC|TCL|Alcatel|BIRD|DBTEL|Dopod|PHILIPS|HAIER|LENOVO|MOT-|Nokia|SonyEricsson|SIE-|Amoi|ZTE/.test(navigator.userAgent))){if(window.location.href.indexOf("?mobile")<0){try{if(/Android|Windows Phone|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent)){window.location.href="<?php echo $site['site_url'];?>index.php?m=mobile";}else if(/iPad/i.test(navigator.userAgent)){}else{}}catch(e){}}}}</script>
  </head>
  <body>
	   <?php include template("index","header"); ?>
	   <!--网站容器-->
	   <div id="container">
           <div class="banner">
               <!-- 焦点图 开始 -->
               <div id="jiaodian" class="focus">
                   <div id="fpic" class="fpic">
                       <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,thumb,url','modelid'=>'1','thumb'=>'1','limit'=>'3',));}?>
                       <?php $total = count($data);?>
                       <?php if(is_array($data)) foreach($data as $v) { ?>
                       <div class="fcon" style="display: none;">
                           <a target="_blank" href="<?php echo $v['url'];?>"><img src="<?php echo $v['thumb'];?>" style="opacity: 1;" title="<?php echo $v['title'];?>"></a>
                           <span class="shadow"><a target="_blank" href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>"><?php echo str_cut($v['title'], 48);?></a></span>
                       </div>
                       <?php } ?>
                   </div>
                   <div class="fbg">
                       <div class="d1fbt" id="d1fbt">
                           <?php for($i=1; $i<=$total; $i++) { ?>
                           <a href="javascript:void(0)" hidefocus="true" target="_self"><i><?php echo $i;?></i></a>
                           <?php } ?>
                       </div>
                   </div>
                   <!--<span class="prev"></span>
                   <span class="next"></span>-->
               </div>
               <script type="text/javascript">
                   Qfast.add('widgets', { path: "<?php echo STATIC_URL;?>js/terminator2.2.min.js", type: "js", requires: ['fx'] });
                   Qfast(false, 'widgets', function () {
                       K.tabs({
                           id: 'jiaodian',     //焦点图包裹id
                           conId: 'fpic',      //大图域包裹id
                           tabId: 'd1fbt',
                           tabTn: 'a',
                           conCn: '.fcon',    //大图域配置class
                           auto: 1,           //自动播放 1或0
                           effect: 'fade',    //效果配置
                           eType: 'click',    //鼠标事件
                           pageBt: true,      //是否有按钮切换页码
                           bns: ['.prev', '.next'], //前后按钮配置class
                           interval: 6000     //停顿时间
                       })
                   })
               </script>
               <!-- 焦点图 结束 -->
               <div class="clearfix"></div>
           </div>
		<div class="box">
            <div class="left">
                <div class="left_top">
                    <h2 class="ind_bt"><a target="_blank" href="http://www.yzmcms.com/" class="gengduo">更多>></a>站点介绍</h2>
                    <a href="http://www.yzmcms.com/" target="_blank"><img src="<?php echo STATIC_URL;?>images/default.jpg"></a>
                    <a href="http://www.yzmcms.com/" target="_blank" class="ptitle">本站为YzmCMS的演示站点哈哈</a>
                    <p class="pic_p">YzmCMS采用OOP（面向对象）方式自主开发的框架。框架易扩展，是一款高效开源的内容管理系统，产品基于PHP+Mysql架构...<a href="http://www.yzmcms.com/" target="_blank">[详细]</a></p>
                </div>
                <div class="left_bottom">
                    <a href="http://blog.yzmcms.com/guanyu/" target="_blank"><img src="<?php echo STATIC_URL;?>images/default.jpg" title="关于YzmCMS" alt="关于YzmCMS"></a>
                    <a href="http://blog.yzmcms.com/guanyu/" target="_blank" class="ptitle ptitle12">关于YzmCMS</a>
                    <p class="pic_p">YzmCMS是一款轻量级免费开源的内容管理系统，基于PHP+Mysql架构的，可运行在Linux、Windows、MacOSX...<a href="http://blog.yzmcms.com/guanyu/" target="_blank">[详细]</a></p>
                </div>
            </div>
		  <!--<div class="left">
			  <div class="left_bottom">
		        	<h2><a href="#" target="_blank">更多>></a><span>TAG标签</span></h2>
					<div class="new_ct">
                    <ul>
					<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'tag')) {$data = $tag->tag(array('field'=>'id,tag,total','limit'=>'20',));}?>
					<?php if(is_array($data)) foreach($data as $v) { ?>
					   <li><a href="<?php echo U('search/index/tag',array('id'=>$v['id']));?>" target="_blank"><?php echo $v['tag'];?></a></li>	
					<?php } ?>						
					</ul>
					</div>
		      </div>			  
		  </div>-->
		  <div class="center pad_0">
			  <div class="center_top">
			  
				<strong class="top_title">特别推荐</strong>
				<ul>
				  <!-- 这里只做演示，选择modelid为1的特荐信息，您可以根据自己的需求进行更改 -->
				  <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,inputtime,url','modelid'=>'1','flag'=>'3','limit'=>'5',));}?>
				   <?php if(is_array($data)) foreach($data as $v) { ?>
				   <li>-<span><?php echo date('m-d',$v['inputtime']);?></span><a href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>"><?php echo str_cut($v['title'], 75);?></a></li>
				  <?php } ?>
                </ul>
		      </div>
			  <div class="center_bottom new_list">
			    <strong class="top_title top_bor">最近更新</strong>
				<ul>
				   <!-- 这里只做演示，选择modelid为1的最近更新，您可以根据自己的需求进行更改 -->
				  <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url','modelid'=>'1','limit'=>'10',));}?>
				   <?php if(is_array($data)) foreach($data as $v) { ?>
				   <li>-<a href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>"><?php echo str_cut($v['title'], 75);?></a></li>
				  <?php } ?>		 
				</ul>
		
		      </div>		
		  </div>

		</div>
         <div class="clearfix"></div>
         <div class="guanggao">
		  <!-- 这里只做演示，您可以添加自己的广告位 -->
		  <h1>免费又好用的CMS建站系统，就选YzmCMS!</h1>
		 </div>
		<div class="box">
			<div class="left">
              <div class="guanzhubox">
                 <h2 class="ind_bt"><a href="#" class="gengduo">更多>></a>栏目分类一</h2>
		      </div>
			  <div class="list_news">
					<ul>
					<!-- 这里只做演示，您可以根据自己的需求更改catid和其他参数 -->
					<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url','catid'=>'1','limit'=>'11',));}?>
					<?php if(is_array($data)) foreach($data as $v) { ?>
					   <li><a href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>" target="_blank"><?php echo $v['title'];?></a></li>	
					<?php } ?>					 
				    </ul>            
		      </div>
			</div>
		  <div class="center">
              <h2 class="ind_bt"><a href="#" class="gengduo">更多>></a>栏目分类二</h2>
			  <div class="center_bottom">
				<ul>
					<!-- 这里只做演示，您可以根据自己的需求更改catid和其他参数 -->
					<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url','catid'=>'2','limit'=>'11',));}?>
					<?php if(is_array($data)) foreach($data as $v) { ?>
					   <li>-<a href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>" target="_blank"><?php echo $v['title'];?></a></li>	
					<?php } ?>	
				</ul>
		
		      </div>		
		  </div>
		  <div class="right">
			  <div class="guanzhubox">
                 <h2 class="ind_bt"><a href="#"  class="gengduo">更多>></a>栏目分类三</h2>
		      </div>
			  <div class="list_news">
					<ul>
					<!-- 这里只做演示，您可以根据自己的需求更改catid和其他参数 -->
					<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url','catid'=>'3','limit'=>'11',));}?>
					<?php if(is_array($data)) foreach($data as $v) { ?>
					   <li><a href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>" target="_blank"><?php echo $v['title'];?></a></li>	
					<?php } ?>						 
				</ul>            
		      </div>		
		  </div>		  
		</div>		
         <div class="clearfix"></div>		 
         <div class="news_img">
		   <h2 class="ind_bt tit_img"><a  href="#" target="_blank" class="gengduo">更多>></a>图文资讯</h2>
			  <div class="w185_imgnews">
 				<ul>
				   <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,thumb,url','modelid'=>'1','thumb'=>'1','limit'=>'5',));}?>
				   <?php if(is_array($data)) foreach($data as $k=>$v) { ?>
				   <li<?php if($k==4) { ?> class="m_r0"<?php } ?>><a href="<?php echo $v['url'];?>"><img src="<?php echo $v['thumb'];?>" alt="<?php echo $v['title'];?>" title="<?php echo $v['title'];?>"><em><?php echo str_cut($v['title'], 45);?></em></a></li>
				   <?php } ?>										
				</ul>               
		      </div>		 
		 </div>		 
         <div class="clearfix"></div>		 
		<!--友情链接-->
		<div id="links">
			<p>友情链接：</p>
			 <ul>
			<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'link')) {$data = $tag->link(array('field'=>'url,logo,name','where'=>"status=1",'order'=>'listorder ASC','limit'=>'20',));}?>
			<?php if(is_array($data)) foreach($data as $v) { ?>
			   <li><a href="<?php echo $v['url'];?>" target="_blank"><?php echo $v['name'];?></a></li>	
			<?php } ?>			   
			 </ul>
		</div>

<?php include template("index","footer"); ?>