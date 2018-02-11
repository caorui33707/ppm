<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
    <link rel='stylesheet' type='text/css' href='/Public/admin/auth/css/style.css'>
    <script type="text/javascript" src="/Public/admin/auth/js/jquery.js"></script>
    <style>
        html{overflow-x : hidden;}
    </style>
    <base target="main" />
</head>

<body >
    <div id="menu" class="menu">
        <table class="list shadow" cellpadding=0 cellspacing=0 >
            <tr>
                <td height='3' colspan=1 class="topTd" ></td>
            </tr>
            <tr class="row" >
                <th class="tCenter space">
                    <a href="<?php echo C('ADMIN_ROOT');?>/Public/main"><img src="/Public/admin/auth/images/admin_icon_home.png" WIDTH="16" HEIGHT="16" BORDER="0" alt="" align="absmiddle"> 后台首页</a>
                </th>
            </tr>
            <?php if(is_array($menu)): foreach($menu as $key=>$item): if(count($item['sub']) > 0): ?><tr class="row">
                        <th class="tCenter space">
                            <a href="javascript:;" onclick="toggle_sub('<?php echo ($item["name"]); ?>')"><img src="/Public/admin/auth/images/admin_icon_<?php echo ($item["icon"]); ?>.png" WIDTH="16" HEIGHT="16" BORDER="0" alt="" align="absmiddle"> <?php echo ($item['title']); ?> </a>
                        </th>
                    </tr>
                    <?php if(is_array($item["sub"])): foreach($item["sub"] as $key=>$sub): if(($sub["show"]) == "1"): ?><tr class="row sub_<?php echo ($item["name"]); ?>" style="display:none;">
                                <td>
                                    <div style="margin-left: 55px;"><img src="/Public/admin/auth/images/admin_icon_node.png" WIDTH="9" HEIGHT="9" BORDER="0" align="absmiddle" alt="">
                                        <?php if(($sub["url"]) == "message/add"): ?><a href="javascript:;" rel="shareit"><?php echo ($sub['title']); ?></a>
                                        <?php else: ?>
                                            <a href="<?php echo C('ADMIN_ROOT');?>/<?php echo ($sub["url"]); ?>"><?php echo ($sub['title']); ?></a><?php endif; ?>
                                    </div>
                                </td>
                            </tr><?php endif; endforeach; endif; endif; endforeach; endif; ?>
            <tr>
                <td height='3' colspan=1 class="bottomTd"></td>
            </tr>
        </table>
    </div>

    <div id="shareit-box">
        <div id="shareit-header"></div>
        <div id="shareit-body">
            <div id="shareit-blank"></div>
            <a href="<?php echo C('ADMIN_ROOT');?>/message/add/type/1" style="line-height: 45px;margin-left:7px;">系统消息</a>
            <a href="<?php echo C('ADMIN_ROOT');?>/message/add/type/2">活动(单条)</a>
            <a href="<?php echo C('ADMIN_ROOT');?>/message/add/type/3" style="margin-left:7px;">活动(多条)</a>
            <a href="<?php echo C('ADMIN_ROOT');?>/message/add/type/4">项目消息</a>
        </div>
    </div>

<script language="JavaScript">
<!--
	function refreshMainFrame(url) {
		parent.main.document.location = url;
	}

	if (document.getElementsByTagName("a")[0].href) {
        refreshMainFrame(document.getElementsByTagName("a")[0].href);
	}

    function toggle_sub(_parent){
        $(".sub_" + _parent).toggle();
    }
//-->
</script>
    <style>
        #shareit-box {position:absolute;display:none;}
        #shareit-header {width:138px;}
        #shareit-body {width:138px; height:100px;background:url(/Public/admin/images/shareit.png);}
        #shareit-blank {height:20px;}
        #shareit-icon ul {list-style:none;width:130px;margin:0; padding:0 0 0 8px;}
        #shareit-icon ul  li{float:left;padding:0 2px;}
        #shareit-icon ul  li img{border:none;}
    </style>
<script>
    $(document).ready(function() {
        //grab all the anchor tag with rel set to shareit
        $('a[rel=shareit], #shareit-box').mouseenter(function() {

            //get the height, top and calculate the left value for the sharebox
            var height = $(this).height();
            var top = $(this).offset().top;

            //get the left and find the center value
            var left = $(this).offset().left + ($(this).width() /2) - ($('#shareit-box').width() / 2);

            //grab the href value and explode the bar symbol to grab the url and title
            //the content should be in this format url|title
            var value = $(this).attr('href').split('|');

            //assign the value to variables and encode it to url friendly
            var field = value[0];
            var url = encodeURIComponent(value[0]);
            var title = encodeURIComponent(value[1]);

            //assign the height for the header, so that the link is cover
            $('#shareit-header').height(height);

            //display the box
            $('#shareit-box').show();

            //set the position, the box should appear under the link and centered
            $('#shareit-box').css({'top':top, 'left':left});
        });

        $('#shareit-box').mouseleave(function () {
            $(this).hide();
        });

        $('#shareit-field').click(function () {
            $(this).select();
        });
    });
</script>
</body>
</html>