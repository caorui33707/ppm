<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo C('COMPANY_NAME');?>管理系统登录</title>
<link rel="stylesheet" type="text/css" href="/Public/admin/auth/css/style.css" />
<script type="text/javascript" src="/Public/admin/auth/js/Base.js"></script>
<script type="text/javascript" src="/Public/admin/auth/js/prototype.js"></script>
<script type="text/javascript" src="/Public/admin/auth/js/mootools.js"></script>
<script type="text/javascript" src="/Public/admin/auth/js/Think/ThinkAjax.js"></script>
<script type="text/javascript" src="/Public/admin/auth/js/common.js"></script>
 <script language="JavaScript">
<!--
var PUBLIC	 =	 '/Public';
function keydown(e){
	var e = e || event;
	if (e.keyCode==13)
	{
	    ThinkAjax.sendForm('form1','/admin.php/Public/checkLogin/',loginHandle,'result');
	}
}
function fleshVerify(){ 
	//重载验证码
	var timenow = new Date().getTime();
	$('verifyImg').src= '/admin.php/Public/verify/'+timenow;
}
//-->
</script>
</head>
<body onLoad="document.login.account.focus()" >
<form method='post' name="login" id="form1" action="">
    <div class="tCenter hMargin">
        <table id="checkList" class="login shadow" cellpadding=0 cellspacing=0 >
            <tr><td height="3" colspan="2" class="topTd" ></td></tr>
            <tr class="row" ><Th colspan="2" class="tCenter space" ><img src="/Public/admin/auth/images/admin_icon_login.png" width="32" height="32" border="0" alt="" align="absmiddle"> <?php echo C('COMPANY_NAME');?>管理系统登录</th></tr>
            <tr><td height="3" colspan="2" class="topTd" ></td></tr>
            <tr class="row" ><td class="tRight" width="25%">帐 号：</td><td><input type="text" class="medium bLeftRequire" check="Require" warning="请输入帐号" name="username"></td></tr>
            <tr class="row" ><td class="tRight">密 码：</td><td><input type="password" class="medium bLeftRequire" check="Require" warning="请输入密码" name="password" value="admin1024"></td></tr>
            <?php if((C("ALLOW_VERIFY")) == "true"): ?><tr class="row" ><td class="tRight">验证码：</td><td><input type="text" onKeyDown="keydown(event)" class="small bLeftRequire" check="Require" warning="请输入验证码" name="verify"> <img id="verifyImg" src="<?php echo C('ADMIN_ROOT');?>/public/verify" onClick="fleshVerify()" border="0" ALT="点击刷新验证码" style="cursor:pointer" align="absmiddle"></td></tr><?php endif; ?>
            <tr class="row" >
                <td class="tCenter" align="justify" colspan="2"><input type="submit" value="登 录" class="submit medium hMargin"></td>
            </tr>
            <tr><td height="3" colspan="2" class="bottomTd" ></td></tr>
        </table>
    </div>
</form>
</body>
</html>