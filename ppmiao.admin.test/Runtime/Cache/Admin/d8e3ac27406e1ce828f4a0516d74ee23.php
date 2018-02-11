<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>『<?php echo C('COMPANY_NAME');?>管理平台』</title>
<link rel="stylesheet" type="text/css" href="/Public/admin/auth/css/blue.css" />
<!--<script type="text/javascript" src="/Public/admin/auth/js/Base.js"></script>-->
<!--<script type="text/javascript" src="/Public/admin/auth/js/prototype.js"></script>-->
<script type="text/javascript" src="/Public/admin/auth/js/jquery.js"></script>
<!--<script type="text/javascript" src="/Public/admin/auth/js/mootools.js"></script>-->
<!--<script type="text/javascript" src="/Public/admin/auth/js/Think/ThinkAjax.js"></script>-->
<!--<script type="text/javascript" src="/Public/admin/auth/js/Form/CheckForm.js"></script>-->
<script type="text/javascript" src="/Public/admin/auth/js/common.js"></script>
<script type="text/javascript" src="/Public/admin/layer/layer.min.js"></script>
<script language="JavaScript">
<!--
//指定当前组模块URL地址
var SITE ="<?php echo C('SITE_ROOT');?>";
var ROOT = '<?php echo C("ADMIN_ROOT");?>';
var URL = '/admin.php/Project';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">购买用户 - <?php echo ($title); ?>
        	<?php if(checkAuth('Admin/project/exporttoexcel') == true): ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo C('ADMIN_ROOT');?>/project/exporttoexcel/id/<?php echo ($id); ?>/rid/<?php echo ($rid); ?>">导出(宝付)</a>
        	&nbsp;&nbsp;&nbsp;<a href="<?php echo C('ADMIN_ROOT');?>/project/exportToExcelRb/id/<?php echo ($id); ?>/rid/<?php echo ($rid); ?>">导出(融宝)</a><?php endif; ?>
        	
        	
        	</div>

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="6" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="100px" align="center">用户姓名</th>
                    <th width="100px" align="center">购买日期</th>
                    <th width="180px" align="center">银行卡</th>
                    <th width="180px" align="center">发卡银行</th>
                    <th width="150px" align="center">支付利息(元)</th>
                    <th width="150px" align="center">支付本金(元)</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td align="center">
                        	<?php if($item["user_id"] <= 0): ?><em style="color:gray;"><?php echo ($item["real_name"]); ?></em>
                        	<?php else: ?>
                        		<?php echo ($item["real_name"]); endif; ?>
                        </td>
                        <td align="center"><?php echo ($item["add_time"]); ?></td>
                        <td align="center"><?php echo ($item["card_no"]); ?></td>
                        <td align="center"><?php echo ($item["bank_name"]); ?></td>
                        <td align="right"><?php echo (number_format((isset($item["due_interest"]) && ($item["due_interest"] !== ""))?($item["due_interest"]):0,2)); ?></td>
                        <td align="right"><?php echo (number_format((isset($item["due_capital"]) && ($item["due_capital"] !== ""))?($item["due_capital"]):0,2)); ?></td>
                    </tr><?php endforeach; endif; ?>
                <tr class="row">
                    <td colspan = '4' align='center'>当页总计<?php if(C('GHOST_ACCOUNT') == 'true'): ?>(不包括幽灵账号)<?php endif; ?></td>
                    <td align="right"><?php echo (number_format((isset($page_totle_interest) && ($page_totle_interest !== ""))?($page_totle_interest):0,2)); ?></td>
                    <td align="right"><?php echo (number_format((isset($page_totle_capital) && ($page_totle_capital !== ""))?($page_totle_capital):0,2)); ?></td>
                </tr>
                <tr>
                    <td height="5" colspan="6" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page">
            <div style="float:left;">本期支付利息总计：<b><?php echo (number_format((isset($totle_interest) && ($totle_interest !== ""))?($totle_interest):0,2)); ?></b>(元); 本期支付本金总计：<b><?php echo (number_format((isset($totle_capital) && ($totle_capital !== ""))?($totle_capital):0,2)); ?></b>(元)
            		
            		<?php if(C('GHOST_ACCOUNT') == 'true'): ?>;本期幽灵总计：<b><?php echo (number_format((isset($robot_total_amount) && ($robot_total_amount !== ""))?($robot_total_amount):0,2)); ?></b>(元)<?php endif; ?>
            </div>
        </div>
        
        <div class="page">
            <div style="float:left;">购买笔数：<?php echo ($count); ?></div>
            <?php echo ($show); ?>
        </div>
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->