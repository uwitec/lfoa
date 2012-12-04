<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>河南省绿风文化发展有限公司登陆窗口</title>
<meta name="keywords" content="河南省绿风文化发展有限公司登陆窗口" />
<meta name="content-type" content="河南省绿风文化发展有限公司登陆窗口" />
<meta http-equiv='Refresh' content='{$waitSecond};URL={$jumpUrl}'>
<style>
body{ margin:0; padding:0;}
a{ color:#F60; text-decoration:none;}
a:hover{ color:#999; text-decoration:none;}
img{ border:0;}
p.span,li,ul,h1,h2,h3,h4,h5,h6{ margin:0; padding:0;}
/*main*/
#top{ background:url(__PUBLIC__/Theme/Admin/Dwz/themes/default/images/login/line.gif) bottom repeat-x; margin-top:230px;}
.top{ background:url(__PUBLIC__/Theme/Admin/Dwz/themes/default/images/login/logo_tz.png)no-repeat; width:842px; height:82px; margin:0 auto;;}
.top{filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="__PUBLIC__/Theme/Admin/Dwz/themes/default/images/login/logo_tz.png"); }
.content{ margin:0 auto; width:700px;}
.correct_tab{ width:700px; margin:60px auto;}
.correct_btn{ width:118px; height:118px; background:url(__PUBLIC__/Theme/Admin/Dwz/themes/default/images/login/correct.png) no-repeat; float:left;}
*html .correct_btn{filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="__PUBLIC__/Theme/Admin/Dwz/themes/default/images/login/correct.png"); background:none; }
.correct_text{ float:right; width:570px; text-align:center;}
.correct_text h1{ color:#038ad7;}
.correct_text span{ font-size:16px; margin:30px 0 0 0; float:left; display:inline; width:570px; text-align:center; color:#666;}
</style>
</head>
<body>
<div  id="top">
	<div class="top"></div>
</div>
<div class="content">
	<div class="correct_tab">
    	<div  class="correct_btn"></div>
        <div class="correct_text">
        	<h1>{$msgTitle}</h1>
            <span>
            	<!--页面将在1s内自动跳转！如果不想等待请<a href="#" target="_blank" title="点击直接进入">点击直接进入</a>-->
            	<present name="message" >

				<div class="row">{$message}</div>

				</present>
	
				<present name="error" >
	
					<div class="row">{$error}</div>
	
				</present>
	
				<present name="closeWin" >
	
					<div class="row">系统将在 {$waitSecond} 秒后自动关闭，如果不想等待,直接点击 <a href="{$jumpUrl}">这里</a> 关闭</div>
	
				</present>
	
				<notpresent name="closeWin" >
	
					<div class="row">系统将在 {$waitSecond} 秒后自动跳转,如果不想等待,直接点击 <a href="{$jumpUrl}">这里</a> 跳转</div>
	
				</notpresent>
            </span>
        </div>
    </div>
</div>
</body>
</html>
