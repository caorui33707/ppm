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
    <script type="text/javascript">
        $(function(){
            $('.aboutBox06Inner ul li').hover(function(){
                $(this).addClass('cur');
            },function(){
                $(this).removeClass('cur');
            })
        });
    </script>
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
    
     <div class="bannerInner bannerInner02">
    	<ul>
            <li style="background:url(<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCAboutBanner.png) no-repeat scroll center top; display:block"><a href="#"></a></li>
        </ul>
        <div class="MCAboutBox01">
        	<h5>公司简介</h5>
            <p>票票喵理财平台是诞生于传统票据业务行业，发展于互
联网时代。是互联网与金融理财的一次完美的邂逅。其创始人和核
心管理团队均由银行、金融、法律、互联网领域的资深专业人士构
成。团队凭借多年扎根票据市场的人脉和资源，引入银行风险管理
模式，去除中间环节，将现行产品与网络思维深度结合，专注致力
于为广大用户打造一个在您身边为您服务的、信息透明、风险可控、
收益稳定的互联网家庭投资理财平台。</p>
        </div>
    </div>
    
    <div class="MCPrivacyBox01Bg">
    	<div class="MCPrivacyBox01TitleBg fLe clear">
        	<i class="arrow03"></i>
        	<div class="MCPrivacyBox01Title w1003 clear"><h5>票票喵是什么？</h5></div>
        </div>
        <div class="MCAboutBox01Txt01 w1003 clear">
        	<p>票票喵的创始人和核心管理团队均由金融、银行、法律、互联网领域的资深专业人士构成。
凭借全球领先的金融证券交易技术、人工智能技术、计算金融学技术，
我们开创新地研发出了公司旗下第一核心产品——票票喵，
即将革命性地改变中国互联网金融理财行业的发展模式。
通过将互联网思维、金融产品更深度联结，去除中间环节，打通通道，产生聚合，让广大用户能够买到国内最优质理财产品。
平台贯彻以安全、简单、透明、高收益的服务理念。</p>
        </div>
    </div>
    
    <div class="MCSecurityBox04TitleBg fLe clear">
        <i class="arrow04"></i>
        <div class="MCSecurityBox04Title w1003 clear">
            <h5>我的资质</h5>
        </div>
    </div>
    
    <div class="MCAboutBox02Bg">
    	<div class="MCAboutBox02 w1003 clear">
    		<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCAboutImg01.png" />
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