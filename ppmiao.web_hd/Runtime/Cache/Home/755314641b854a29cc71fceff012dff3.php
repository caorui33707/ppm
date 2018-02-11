<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>页面找不到了~</title>
    <link href="<?php echo C('STATIC_ROOT');?>/v2/css/reset.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo C('STATIC_ROOT');?>/v2/css/css.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo C('STATIC_ROOT');?>/v2/js/jquery.js"></script>
</head>

<body id="noneBody">
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

<div class="noneBox fLe clear">
    <div class="none w978 clear">
        <img src="<?php echo C('STATIC_ROOT');?>/v2/images/noneImg.png" />
    </div>
</div>

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

</body>
</html>