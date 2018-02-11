<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo ($meta_title); ?></title>
    <meta name="keywords" content="<?php echo ($meta_keywords); ?>" />
    <meta name="description" content="<?php echo ($meta_description); ?>" />
    <meta name="author" content="<?php echo C('META_AUTHOR');?>" />
    <link rel="shortcut icon" href="<?php echo C('WEB_ROOT');?>/favicon.ico">
    <link href="<?php echo C('STATIC_ROOT');?>/ppmiao/css/reset.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo C('STATIC_ROOT');?>/ppmiao/css/css.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo C('STATIC_ROOT');?>/ppmiao/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo C('STATIC_ROOT');?>/ppmiao/js/flash.js"></script>
</head>

<body>

    <div class="headerBg">
    <div class="header w1003 clear">
        <div class="MCLogo fLe clear">
            <img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCLogo.png" />
        </div>
        <div class="MCPhone fLe clear">
            <img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCphone.png" />
        </div>
        <div class="header fRi clear">
            <ul>
                <?php if($path == 'index'): ?><li class="active"><a href="<?php echo C('WEB_ROOT');?>">首页</a></li> <?php else: ?> <li><a href="<?php echo C('WEB_ROOT');?>">首页</a></li><?php endif; ?>
                <?php if($path == 'product'): ?><li class="active"> <a href="<?php echo C('WEB_ROOT');?>/product/">理财产品</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/product/">理财产品</a></li><?php endif; ?>
                <?php if($path == 'security'): ?><li class="active"><a href="<?php echo C('WEB_ROOT');?>/security.html">安全保障</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/security.html">安全保障</a></li><?php endif; ?>
                <?php if($path == 'about'): ?><li class="active"> <a href="<?php echo C('WEB_ROOT');?>/about.html">关于我们</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/about.html">关于我们</a></li><?php endif; ?>
                <?php if($path == 'contact'): ?><li class="active"> <a href="<?php echo C('WEB_ROOT');?>/contact.html">联系我们</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/contact.html">联系我们</a></li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $(".userHeader").hover(function(){
            $(this).find(".pulldown-nav").addClass("hover");
            $(this).find(".uderHeaderDl").show();
        },function(){
            $(this).find(".uderHeaderDl").hide();
            $(this).find(".pulldown-nav").removeClass("hover");
        });
    });
</script>
    
     <div class="bannerInner">
    	<ul>
            <li style="background:url(<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityBanner01.png) no-repeat scroll center top; display:block"><a href="#"></a></li>
        </ul>
    </div>
    
    <div class="MCSecurityBox01 w1003 clear">
    	<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg01.png" />
    </div>
    
    <div class="MCSecurityBox02Bg fLe clear">
    	<div class="MCSecurityBox02 w1003 clear">
            <div class="MCSecurityBox02Fle fLe clear">
                <h2>什么是银行承兑汇票？</h2>
                <div class="MCSecurityBox02FleTxt fLe clear">
                	<p> 银行承兑汇票是由银行家开具的到期兑付的书面凭证。根
                        据《票据法》第七十三条规定：银行承兑汇票由银行承兑，银
                        行承诺到期后会无条件兑付该票据金额给及该银承的所有人。
                        在票票喵理财项目中，银承作为担保财产之一，在借款人不能按
                        期清偿的情况下，抵押权人可以通过行使抵押权，实现权利。
                    </p>
                </div>
            </div>
            <div class="MCSecurityBox02Fle fLe clear">
                <h2>什么叫无条件兑付？</h2>
                <div class="MCSecurityBox02FleTxt fLe clear">
                	<p> 根据《票据法》相关规定：商业汇票是出票人签发的，委托
                        付款人在指定日期无条件支付确定的金额给收款人或者持票人的
                        票据。在票票喵理财项目中，该票据是银行承兑汇票，所以根据
                        该条文规定，由银行无条件兑付。票据到期后，银行将按照票据
                        金额无条件支付给该票据的所有人。
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="MCSecurityBox03Bg fLe clear">
    	<i class="arrow03"></i>
        <div class="MCSecurityBox03TitleBg fLe clear">
        	<div class="MCSecurityBox03Title w1003 clear"><h5>票据理财本息安全有保障</h5></div>
        </div>
    	<div class="MCSecurityBox03 w1003 clear">
            <h6>票票喵以银行未到期的承兑汇票为投资产品，到期收益和本金承兑行无条件100%兑换</h6>
            <p><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg02.png" /></p>
        </div>
    </div>
    
    <div class="MCSecurityBox04Bg fLe clear">
    	<div class="MCSecurityBox04TitleBg fLe clear">
        	<i class="arrow04"></i>
        	<div class="MCSecurityBox04Title w1003 clear">
                <h5>资金账户安全无忧</h5>
            </div>
        </div>
        <div class="MCSecurityBox04 w1003 clear">
        	<h6>姓名，身份证，银行卡，银行预留手机号四重验证，资金同卡进出，免除资金被他人盗用之忧;资金账户安全由中国人保承保</h6>
            <p><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg03.png" /></p>
        </div>
    </div>
    
     <div class="MCSecurityBox04Bg fLe clear">
    	<div class="MCSecurityBox04TitleBg fLe clear">
        	<i class="arrow04"></i>
        	<div class="MCSecurityBox04Title w1003 clear">
                <h5>自动返还，安全更便捷</h5>
            </div>
        </div>
         <div class="MCSecurityBox04 MCSecurityBox05 w1003 clear">
        	<h6>姓名，身份证，银行卡，银行预留手机号四重验证，资金同卡进出，免除资金被他人盗用之忧;资金账户安全由中国人保承保</h6>
            <p><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg04.png" /></p>
        </div>
     </div>
     
     <div class="MCSecurityBox04Bg fLe clear">
    	<div class="MCSecurityBox04TitleBg fLe clear">
        	<i class="arrow04"></i>
        	<div class="MCSecurityBox04Title w1003 clear">
                <h5>银行级别数据加密技术</h5>
            </div>
        </div>
         <div class="MCSecurityBox04 MCSecurityBox06 w1003 clear">
        	<h6>三层防火墙隔离技术，杜绝系统入侵，双重容灾备份。所有隐私信息经严格加密，防止任何人盗取用户信息，严格保护用户账户信息安全</h6>
            <p><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg05.png" /></p>
        </div>
     </div>
     

     <!--footer begin-->
    <script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "//hm.baidu.com/hm.js?b4c8a1efe52094b6a6e6c9b6c12ebdeb";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
<div class="MCSecurityBox05Bg fLe clear">
    <div class="MCSecurityBox05 w1003 clear">
        <h5>票票喵APP</h5>
        <a href="<?php echo C('WEB_ROOT');?>/download/android-debug.apk" style="margin-left:254px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg07.png" /></a>
        <a href="#" style="margin-left:65px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg08.png" /></a>
    </div>
</div>
<div class="footerInnerBg fLe clear">
    <div class="footerInner w1003 clear">
        <ul>
            <a href="<?php echo C('WEB_ROOT');?>/about.html">关于我们&nbsp;&nbsp;|&nbsp;&nbsp;</a>
            <a href="<?php echo C('WEB_ROOT');?>/join.html">加入我们&nbsp;&nbsp;|&nbsp;&nbsp;</a>
            <a href="<?php echo C('WEB_ROOT');?>/contact.html">联系我们&nbsp;&nbsp;</a>
        </ul>
        <p>浙ICP备16003202号-1 |  Copyrights      2015 PiaoPiaoMiaopiaojulicaitouziguanli</p>
    </div>
</div>
    <!--footer end-->
    
    <div id="top"></div>
    
</body>
</html>