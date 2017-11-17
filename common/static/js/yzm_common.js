/**
 * YzmCMS 公共js文件 
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-09-30
 */

 
//删除一条记录
function yzm_del(url){
	layer.confirm('确认要删除吗？',function(index){
		window.location.href = url;
	});
}


//删除多条记录
function yzm_dels(name){
	if ($("input[name='"+name+"[]']:checked").length<1){
	   layer.alert('请勾选信息！');
	   return false;
	}	
	layer.confirm('确认要删除吗？',function(index){
		document.getElementById('myform').submit();
	});
}


//打开页面
function yzm_open(title,url,w,h){
	layer_show(title,url,w,h);
} 

 
//大窗口打开页面
function yzm_open_full(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url
	});
	layer.full(index);
}


//关闭弹出层
function yzmcms_close(){
	var index = parent.layer.getFrameIndex(window.name);
	parent.layer.close(index);
}


//上传附件
function yzm_upload_att(url){
	layer.open({
      type: 2,
      title: '上传附件',
      area: ['500px', '430px'],
      content: url
    });
}


//图片预览
function yzm_img_preview(id, src){
	if(src == '') return;
	layer.tips('<img src="'+src+'" height="100">', '#'+id, {
	  tips: [1, '#fff']
	});	
}

//删除多文件上传
function remove_li(obj){
	 $(obj).parent().remove();
}
