<?php
return array(

//系统配置
'site_theme' => 'default',    //站点默认主题目录
'url_html_suffix' => '.html', //URL伪静态后缀
'set_pathinfo' => false,       //Nginx默认不支持PATHINFO模式，需配置此项为true，则Nginx可支持PATHINFO，系统默认为false

//数据库配置
'db_type'   => 'mysqli',     // 数据库链接扩展【暂支持 mysql 和 mysqli】
'db_host' => '127.0.0.1',  // 服务器地址
'db_name' => 'yzmcms',    // 数据库名
'db_user' => 'root',      // 用户名
'db_pwd' => 'root',          // 密码
'db_port' => 3306,        // 端口
'db_prefix' => 'yzm_',      // 数据库表前缀 

//路由配置
'route' => array('m'=>'index', 'c'=>'index', 'a'=>'init'),  //默认加载配置，基中“m”为模块,“c”为控制器，“a”为方法
'route_mapping' => true,  //是否开启路由映射
//路由映射规则
'route_rules' => array (

), 

//Cookie配置
'cookie_domain' => '', //Cookie 作用域
'cookie_path' => '/',  //Cookie 作用路径
'cookie_ttl' => 0,     //Cookie 生命周期，0 表示随浏览器进程
'cookie_pre' => 'yzmphp_', //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
'cookie_secure' => false,  //是否通过安全的 HTTPS 连接来传输 cookie

//系统语言
'language' => 'zh_cn', //【暂支持 简体中文zh_cn 和 美式英语en_us】

//附件相关配置
'upload_file' => 'uploads',  //上传文件目录，后面一定不要加斜杠（“/”）
'watermark_enable' => '1', //是否开启图片水印
'watermark_name' => 'mark.png', //水印名称
'watermark_position' => '9', //水印位置

//其他设置
'sql_execute' => false, //是否允许在线执行SQL命令

);
?>