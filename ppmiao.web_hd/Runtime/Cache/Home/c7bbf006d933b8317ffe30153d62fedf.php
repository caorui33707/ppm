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
    
    <div class="MCProductListBoxBg">
    	<div class="MCProductListBox01 w1003 clear">
        	<div class="MCProductListBox01Title fLe clear">
            	<h2 class="active"><i><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCProductListIcon01.png" /></i>热销产品</h2>
                <h2><i><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCProductListIcon02.png" /></i>已起息</h2>
                <h2><i><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCProductListIcon03.png" /></i>已还款</h2>
            </div>
            
            <div class="MCProductListBox01Content fLe clear">
            	<div class="MCProductListBox01ContentTitle fLe clear">
                	<ul>
                    	<li style="width:238px;"><span>产品名称</span></li>
                        <li style="width:120px;"><span>年化收益</span></li>
                        <li style="width:110px;"><span>理财期限</span></li>
                        <li style="width:170px;"><span>当前进度</span></li>
                        <li style="width:180px;"><span>剩余金额</span></li>
                        <li style="width:180px;"><span>操作</span></li>
                    </ul>
                </div>

                <?php if(is_array($list)): foreach($list as $key=>$v): ?><div class="MCProductListBox01ContentList fLe clear">
                	<ul>
                    	<li style="width:238px; text-align:left;"><span class="span01"><?php echo ($v["title"]); ?></span></li>
                        <li style="width:120px;"><span class="span02"><?php echo ($v["user_interest"]); ?><em>%</em></span></li>
                        <li style="width:110px;"><span class="span03"><?php echo ($v["duration"]); ?>天</span></li>
                        <li style="width:170px;">
                        	<span class="span04">
                                <div class="proListBox02Rate fLe clear">
                                    <div class="proListBox02RateInner" style="width:<?php echo ($v["percent"]); ?>%;"></div>
                                </div>
                           </span>
                           <span class="span05">
                           		<?php echo ($v["percent"]); ?>%
                           </span>
                        </li>
                        <li style="width:180px;"><span class="span03"><?php echo ($v["able"]); ?></span></li>
                        <li style="width:180px;"><a href="<?php echo C('WEB_ROOT');?>/product/detail/<?php echo ($v["id"]); ?>.html" target="_blank"><span class="span06">立即抢购</span></a></li>
                    </ul>
                    <p>资金账户安全由太平洋保险承保;本息保障，平安银行无条件承兑，票据真实无假票</p>
                </div><?php endforeach; endif; ?>

            </div>
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