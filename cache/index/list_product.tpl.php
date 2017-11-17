<?php defined('IN_YZMPHP') or exit('No permission resources.'); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	  <title><?php echo $seo_title;?></title>
	  <link href="<?php echo STATIC_URL;?>css/default_common.css" rel="stylesheet" type="text/css" />
	  <link href="<?php echo STATIC_URL;?>css/default_style.css" rel="stylesheet" type="text/css" />
	  <script type="text/javascript" src="<?php echo STATIC_URL;?>js/jquery-1.8.2.min.js"></script>
	  <script type="text/javascript" src="<?php echo STATIC_URL;?>js/js.js"></script>
	  <meta name="keywords" content="<?php echo $keywords;?>" />
	  <meta name="description" content="<?php echo $description;?>" />
  </head>
  <body>
<?php include template("index","header"); ?> 
	<div class="location">当前位置：<?php echo get_location($catid);?></div>
	
	<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url,thumb,brand,price,stock','catid'=>$catid,'limit'=>'10','page'=>'page',));$pages = $tag->pages();}?>
	<?php if(is_array($data)) foreach($data as $v) { ?>	
	<div class="main_product">
		<div class="product_list">
			<img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>" title="<?php echo $v['title'];?>" alt="<?php echo $v['title'];?>">
			<div class="pro_list_right">
				<a class="pro_name" href="<?php echo $v['url'];?>"><?php echo str_cut($v['title'], 69);?></a>
				<span class="block pro_rmb">￥<?php echo $v['price'];?>元</span>
				<span class="block pro_kc">库存：<?php echo $v['stock'];?>个</span>
				<a class="block pro_gm" href="<?php echo $v['url'];?>" target="_blank">查看详情</a>
			</div>
		</div>		
	</div>
	<?php } ?>
	<div class="clearfix"></div>
	<div id="page"><?php echo $pages;?></div>	
<?php include template("index","footer"); ?> 