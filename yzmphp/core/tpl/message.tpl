<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>	
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, maximum-scale=1.0, initial-scale=1.0, user-scalable=no">
	<?php if(!$stop){?>
    <meta http-equiv="refresh" content="<?php echo $limittime;?>;URL=<?php echo $gourl;?>" />
	<?php }?>
    <title>YzmCMS提示信息</title>
	<link rel="Shortcut Icon" href="<?php echo STATIC_URL;?>admin/yzm_admin/images/favicon.ico" />
    <style>
	  *{padding:0;margin:0;}
	  body{background:#fff;font-family:'微软雅黑';color:#333;}
	  #msg{border:1px solid #19a97b;width:500px;position:absolute;top:44%;left:50%;margin:-87px 0 0 -250px;padding:1px;line-height:30px;text-align:center;font-size:16px;background:#fff;}
	  #msgtit{height:30px;line-height:30px;color:#fff;background:#19a97b;font-weight:bold;}
	  #msgbody{margin:20px 0;text-align:center}
	  #info{font-weight:bold;margin-bottom:10px;}
	  #msgbody p{font-size:14px;color:#333;}
	  #msgbody p a{font-size:14px;color:#333;text-decoration:none;}
	  #msgbody p a:hover{color:#5a98de;}
	</style>
</head>
<body>
    <div id="msg" class="ss">        	
     <div id="msgtit">提示信息</div>
	 <div id="msgbody">
     <div id="info"><?php echo $msg;?></div>
	 <?php if(!$stop){?>
        <p>本页面将在<span style="color:red; font-weight:bold;margin:0 5px;"><?php echo $limittime;?></span>秒后跳转...</p>
	 <?php }else{?>
		<p><a href="javascript:history.back(-1)" title="点击返回上一页">点击返回上一页</a></p>
	 <?php }?>
     </div>
    </div> 
</body>
</html>