<?php
/**
 * 判断移动设备类型
 * @return string
 */
function get_device_type(){
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    if(strpos($agent, 'iphone') || strpos($agent, 'ipad')){
        $type = 'ios';
    }
    if(strpos($agent, 'android')){
        $type = 'android';
    }
    return $type;
}
/**
 * 判断是否是微信客户端访问
 * @return bool
 */
function is_weixin(){
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
        return true;
    }
    return false;
}
$target = strip_tags($_GET['target']);
$androidDownload = 'http://www.stlc.cn/download/stlc.apk';
if(is_weixin()){
	$iosDownload = 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.stlc.app&ckey=CK1298922809009';
    $androidDownload = 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.stlc.app&ckey=CK1298922809009';
}else{
    if(get_device_type() == 'android'){
        switch($target){
            case 'gumi1':
                $androidDownload = 'http://www.stlc.cn/download/stlc-gumi1.apk';
                break;
            case 'gumi2':
                $androidDownload = 'http://www.stlc.cn/download/stlc-gumi2.apk';
                break;
            case 'gumi3':
                $androidDownload = 'http://www.stlc.cn/download/stlc-gumi3.apk';
                break;
        }
    }else if(get_device_type() == 'ios'){
		$iosDownload = 'http://itunes.apple.com/cn/app/id981786432?mt=8';
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>石打实</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no,target-densitydpi = medium-dpi">
<meta name="format-detection" content="telephone=no">
<meta name="apple-touch-fullscreen" content="YES">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link href="css/css.css" rel="stylesheet" type="text/css" media="all"/>
</head>

<body>
	<section class="activeBox">
    	<div class="activeBoxImg">
    		<img src="images/img01.jpg">
       		<img src="images/img02.jpg">
        </div>
        <?php
            $to = $_GET['to'];
            if($to != 'app'){
                echo '<section class="Button">';
                switch(get_device_type()){
                    case 'ios':
                        echo '<a href="'.$iosDownload.'"><img src="images/button.png"></a>';
                        break;
                    case 'android':
                        echo '<a href="'.$androidDownload.'"><img src="images/button.png"></a>';
                        break;
                    default:
                        echo '<a href="http://www.stlc.cn/download/stlc.apk" target="_blank"><img src="images/button.png"></a>';
                        break;
                }
                echo '</section>';
            }
        ?>
    </section>
</body>
</html>
