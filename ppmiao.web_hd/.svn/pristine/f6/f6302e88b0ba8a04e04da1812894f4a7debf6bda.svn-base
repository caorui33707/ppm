<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title><?php echo ($title); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no,target-densitydpi = medium-dpi">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-touch-fullscreen" content="YES">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="<?php echo C('STATIC_ROOT');?>/mobile/css/css.css" rel="stylesheet" type="text/css" media="all"/>
    <script>
    document.documentElement.style.webkitTouchCallout = "none";//禁止弹出菜单
    document.documentElement.style.webkitUserSelect = "none";//禁止选中
    document.onselectstart=function(){return false;}
    document.oncontextmenu=function(){return false;}
</script>
</head>

<body id="hkfx02">
    <div class="wrap">
            <div class="hkfxListBox">
               <section class="hkfxListTitle">
                    <h3>验票托管</h3>
                </section>
                <section class="hkfxListTxt">
                    <?php echo ($descr); ?>
                </section>
            </div>
        <div class="hkfxListBox">
            <section class="hkfxListTitle">
                <h3>还款来源</h3>
            </section>
            <section class="hkfxListTxt">
                <?php echo ($repayment_from); ?>
            </section>
        </div>
        <?php if(!empty($bank_image)): ?><div class="hkfxListBox">
                <section class="hkfxListTitle">
                    <h3>承兑汇票</h3>
                </section>
                <section class="hkfxListTxt">
                    <?php if(is_array($bank_image)): foreach($bank_image as $key=>$item): ?><img src="<?php echo ($item); ?>" onclick="bigPic('<?php echo ($item); ?>')" /><?php endforeach; endif; ?>
                </section>
            </div><?php endif; ?>        
		<div class="hkfxListBox">
			<section class="hkfxListTitle">
				<h3>银行托管协议</h3>
			</section>
			<section class="hkfxListTxt">
				
				<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/bank/pa01.jpg" onclick="bigPic('<?php echo C('STATIC_ROOT');?>/ppmiao/bank/pa01.jpg" />
				<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/bank/pa02.jpg" onclick="bigPic('<?php echo C('STATIC_ROOT');?>/ppmiao/bank/pa02.jpg" />
				
			</section>
		</div>
		<div class="hkfxListBox">
			<section class="hkfxListTitle">
				<h3>保险协议</h3>
			</section>
			<section class="hkfxListTxt">
				<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht01.png" onclick="bigPic('<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht01.png" />
				<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht02.png" onclick="bigPic('<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht02.png" />
				<img src="<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht03.png" onclick="bigPic('<?php echo C('STATIC_ROOT');?>/ppmiao/contract/ht03.png" />
			</section>
		</div>
		<?php if(!empty($image)): ?><div class="hkfxListBox">
                <section class="hkfxListTitle">
                    <h3>相关协议</h3>
                </section>
                <section class="hkfxListTxt">
                    <?php if(is_array($image)): foreach($image as $key=>$item): ?><img src="<?php echo ($item); ?>" onclick="bigPic('<?php echo ($item); ?>')" /><?php endforeach; endif; ?>
                </section>
            </div><?php endif; ?>
    </div>
<script>
    var dType = "<?php echo ($device_type); ?>";
    function bigPic(_url){
        if(dType == 'android'){
            window.stlc.open(_url);
        }else if(dType == 'ios'){
            document.location = 'stlc:open:' + encodeURI(_url);
        }
    }
</script>
</body>
</html>