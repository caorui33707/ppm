<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>『<?php echo C('COMPANY_NAME');?>管理平台』</title>
<link rel='stylesheet' type='text/css' href='/Public/admin/auth/css/style.css'>
<base target="main" />
</head>
<body>
<!-- 头部区域 -->
<div id="header" class="header">
<div class="headTitle" style="margin:8pt 10pt"> <?php echo C('COMPANY_NAME');?>管理平台 </div>
	<!-- 功能导航区 -->
	<div class="topmenu">
<ul>
<li><span><a href="#" onClick="sethighlight(0); parent.menu.location='/admin.php/Public/menu/title/后台首页';parent.main.location='/admin.php/Public/main/';return false;">后台首页 - localhost</a><?php if((C("DB_NAME")) == "stone_test"): ?>(测试环境)<?php endif; ?></span></li>

<?php if(is_array($nodeGroupList)): $i = 0; $__LIST__ = $nodeGroupList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><li><span><a href="#" onClick="sethighlight(<?php echo ($i); ?>); parent.menu.location='/admin.php/Public/menu/tag/<?php echo ($key); ?>/title/<?php echo ($tag); ?>';return false;"><?php echo ($tag); ?></a></span></li><?php endforeach; endif; else: echo "" ;endif; ?>
</ul>
</div>
	<div class="nav">
        <img src="/Public/admin/auth/images/admin_icon_user.png" width="16" height="16" border="0" alt="" align="absmiddle">
        欢迎你！<?php echo ($_SESSION['user_auth']['username']); ?>
        <a href="<?php echo C('ADMIN_ROOT');?>/Index/password"><img src="/Public/admin/auth/images/admin_icon_lock.png" width="16" height="16" border="0" alt="" align="absmiddle"> 修改密码</a>
        <!--<a href="/admin.php/Public/profile"><img SRC="/Public/admin/auth/images/write.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT="" align="absmiddle"> 修改资料</a>-->
        <a href="/admin.php/Public/logout" target="_top"><img SRC="/Public/admin/auth/images/admin_icon_logout.png" WIDTH="20" HEIGHT="20" BORDER="0" ALT="" align="absmiddle"> 退 出</a></div>
    </div>
<script>
function sethighlight(n) {
	var lis = document.getElementsByTagName('span');
	for(var i = 0; i < lis.length; i++) {
		lis[i].className = '';
	}
	lis[n].className = 'current';
}
sethighlight(0);
</script>
</body>
</html>