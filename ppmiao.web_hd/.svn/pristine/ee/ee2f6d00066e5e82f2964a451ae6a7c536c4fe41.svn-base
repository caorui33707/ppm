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
    
    <div class="MCProductDetailsBigBoxBg">
    	<div class="MCProductDetailsBigBox w1003 clear">
        	<div class="MCProductDetailsBox01 fLe clear">
            	<div class="MCProductDetailsBox01Title fLe clear">
                	<h5><?php echo ($detail["title"]); ?></h5>
                </div>
                <div class="MCProductDetailsBox01Inner fLe clear">
                    <div class="MCProductDetailsBox01Left01 fLe clear">
                        <p>银行无条件承兑，资金第三方监管，票据真实无假票</p>
                        <div class="MCProductDetailsBox01LeftInner fLe clear">
                            <dl>
                                <dt style="width:195px;"><span>年化利率</span></dt>
                                <dt style="width:70px;"><span>理财期限</span></dt>
                                <dt style="width:125px;"><span>起投金额</span></dt>
                                <dt style="width:235px;"><span>投资金额</span></dt>
                            </dl>
                            <dl>
                                <dd style="width:195px;"><span class="span01"><?php echo (number_format($detail["user_interest"],2)); ?><em>%</em></span></dd>
                                <dd style="width:70px;"><span><?php echo ($detail["days"]); ?><em>天</em></span></dd>
                                <dd style="width:125px;"><span><?php echo (number_format($detail["money_min"])); ?><em>元</em></span></dd>
                                <dd style="width:235px;"><span><?php echo (number_format($detail["amount"])); ?><em>元</em></span></dd>
                            </dl>
                        </div>
                    </div>
                    <div class="ProDetailsBox01_content02 fRi clear">
                        <ul>
                        	<li class="li01 li04"><span>剩余金额：<?php echo ($detail["able"]); ?>元</span></li>
                            <li class="li01"><span>申购金额</span><input type="text" value="" class="input01"></li>
                            <li class="li02"><p>预期收益： ¥ 0.00</p></li>
                            <li class="li03"><input type="button" value="购买" class="button01" onclick="javascript:showDiv(1)"></li>
                        </ul>
                        <div class="erWeiMa fLe clear" style="display:none" id="layout01">
                            <div class="erWeiMaTop fLe clear"><a id="close"  onclick="javascript:closeDiv(1)" ></a></div>
                            <div class="erWeiMaCenter fLe clear">
                                <h5>扫一扫</h5>
                                <p>下载APP即可抢购</p>
                                <p><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/qrcode01.png" width="158px" height="160px"/></p>
                            </div>
                            <div class="erWeiMaBottom fLe clear"></div>
                        </div>
                    </div>
                    <div class="MCProductDetailsBox01Left02 fLe clear">
                        <ul>
                            <li><span class="span01">承兑银行：<?php echo ($detail["accepting_bank"]); ?></span><span class="span02">已投人数：<?php echo ($detail["total_due_num"]); ?>人</span></li>
                            <li><span class="span01">计息方式：
                            T（成交日）+<?php echo ($detail['count_interest_type']-1); ?>
                            </span><span class="span02">还款方式：
                            <?php if($detail['repayment_type'] == 1): ?>到期还本付息
                            <?php elseif($detail['repayment_type'] == 2): ?>
                                按月付息，到期还本<?php endif; ?>
                            </span></li>
                            <li>
                                <span class="span01">购买进度：</span>
                                <span class="span03">
                                    <div class="proListBox02Rate fLe clear">
                                        <div class="proListBox02RateInner" style="width:<?php echo ($detail["percent"]); ?>%;"></div>
                                    </div>
                               </span>
                               <span class="span04">
                                    <?php echo ($detail["percent"]); ?>%
                               </span>
                            </li>
                        </ul>
                    </div>

                    <script type="text/javascript">
                        function showDiv(id){
                            document.getElementById('layout0'+id).style.display='block';
                            document.getElementById('shadow').style.display='block';
                        }

                        function closeDiv(id){
                            document.getElementById('layout0'+id).style.display='none';
                            document.getElementById('shadow').style.display='none';
                        }

                    </script>
                </div>
            </div>
            
            <div class="MCProductDetailsBox02 fLe clear">
            	<div class="MCProductDetailsBox02Title01">
                	<h5>产品详情</h5>
                </div>
                <div class="MCProductDetailsBox02Title02">
                	<h5>资金保障</h5>
                </div>
                <div class="MCProductDetailsBox02Inner fLe clear">
                	<p>承兑银行：本息由<?php echo ($detail["accepting_bank"]); ?>兑付</p>
                    <p>票面金额：<?php echo ($detail["amount"]); ?>   票号：<?php echo ($detail["stone_no"]); ?></p>
                    <p>还款来源：<?php echo ($detail["invest_direction_descr"]); ?></p>
                    <p>验票托管：<?php echo ($detail["ticket_checking"]); ?></p>
                    <?php if(is_array($bank_image)): foreach($bank_image as $key=>$item): ?><p class="PImg"><img src="<?php echo ($item); ?>" /></p><?php endforeach; endif; ?>
                </div>
                <?php if(!empty($detail["repayment_source_image"])): ?><div class="MCProductDetailsBox02Title02">
                	<h5>电子银行承兑汇票</h5>
                </div>
                <div class="MCProductDetailsBox02Inner fLe clear">
                    <?php if(is_array($elec_bank_image)): foreach($elec_bank_image as $key=>$item): ?><p class="PImg"><img src="<?php echo ($item); ?>" /></p><?php endforeach; endif; ?>
                </div><?php endif; ?>                
				<div class="MCProductDetailsBox02Title02">
                    <h5>银行托管协议</h5>
                </div>
                <div class="MCProductDetailsBox02Inner fLe clear">                        
                    <p style="text-indent:0px;padding-top:20px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/bank/pa01.jpg" style="width:100%;" /></p>
                    <p style="text-indent:0px;padding-top:20px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/bank/pa02.jpg" style="width:100%;" /></p> 					
                     <!--<p class="PImg"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCProductDetailsImg03.png" /></p>-->
                </div>
				<div class="MCProductDetailsBox02Title02">
                    <h5>保险协议</h5>
                </div>
                <div class="MCProductDetailsBox02Inner fLe clear">
                       <p style="text-indent:0px;padding-top:20px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht01.png" style="width:100%;" /></p>
					   <p style="text-indent:0px;padding-top:20px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht02.png" style="width:100%;" /></p>
					   <p style="text-indent:0px;padding-top:20px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht03.png" style="width:100%;" /></p>	
                     <!--<p class="PImg"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCProductDetailsImg03.png" /></p>-->
                </div>
				<?php if(!empty($detail["invest_direction_image"])): ?><div class="MCProductDetailsBox02Title02">
                    <h5>相关协议</h5>
                </div>
                <div class="MCProductDetailsBox02Inner fLe clear">
                        <?php if(is_array($detail["images"])): foreach($detail["images"] as $key=>$item): ?><p style="text-indent:0px;padding-top:20px;"><img src="<?php echo ($item); ?>" style="width:100%;" /></p><?php endforeach; endif; ?>
                     <!--<p class="PImg"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCProductDetailsImg03.png" /></p>-->
                </div><?php endif; ?>
                <!--
                 <div class="MCProductDetailsBox02Title02">
                	<h5>常见问题</h5>
                </div>

                <div class="MCProductDetailsBox02Inner fLe clear">
                   <p class="PFont">1.什么是银行承兑汇票？</p>
                   <p>银行承兑汇票是由银行开具的到期兑付的书面凭证。根据《票据法》第七十三条规定：银行承兑汇票由银行承兑，银行承诺到期后会无条件兑付该票据金额给予该银承的所有人。在票票喵的银企众盈模式中，银承作为担保财产之一，在借款人不能按期清偿的情况下，抵押权人可以通过行使抵押权，实现权利。</p>
                </div>-->
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
    <div id="shadow" style="display:none"></div>
    
</body>
</html>