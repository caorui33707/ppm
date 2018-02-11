<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
var URL = '/admin.php/Statistics';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">还款历史记录</div>
        
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <span><input type="text" name="key" placeholder="期数" class="medium" value="<?php echo ($params["key"]); ?>"></span>
                        <label for="start_time">还款日期：<input type="text" class="laydate-icon" name="rtime" id="rtime" value="<?php echo ($params["rtime"]); ?>" readonly /></label>
                        
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" value="搜索" class="search imgButton">
                    	<?php if(checkAuth('Admin/statistics/repayment_list_export_to_excel') == true): ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo C('ADMIN_ROOT');?>/statistics/repayment_list_export_to_excel" target="_blank">导出excel</a><?php endif; ?> 
                    </div>
                </form>
            </div>
            <!-- 高级查询区域 -->
            <div  id="searchM" class=" none search cBoth" >
            </div>
        </div>
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan=10 class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="5%">Id</th>
                    <th width="15%" align='center'>期数</th>
                    <th width="13%" align='center'>账户本金</th>
                    <th width="11%" align='center'>账户利息</th>
                    <th width="10%" align='center'>银行卡本金</th>
                    <th width="10%" align='center'>银行卡利息</th>
                    <th width="10%" align='center'>钱包本金</th>
                    <th width="10%" align='center'>钱包利息</th>
                    <th width="10%" align='center'>还款日期</th>
                    <th width="14%" align='center'>操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["title"]); ?></td>
                        <td><?php echo ($item["fund_due_capital"]); ?></td>
                        <td><?php echo ($item["fund_due_interest"]); ?></td>
                        <td><?php echo ($item["to_bank_due_capital"]); ?></td>
                        <td><?php echo ($item["to_bank_due_interest"]); ?></td>
                        <td><?php echo ($item["to_wallet_due_capital"]); ?></td>
                        <td><?php echo ($item["to_wallet_due_interest"]); ?></td>
                        <td><?php echo (date('Y-m-d',strtotime($item["repayment_time"]))); ?></td>
                        <td align="center">
                        	<?php if(checkAuth('Admin/statistics/repayment_list_export_to_excel') == true): ?><a href="<?php echo C('ADMIN_ROOT');?>/statistics/exporttoexcel/id/<?php echo ($item["id"]); ?>/rid/<?php echo ($item["rid"]); ?>" target="_blank">账户导出</a>
                        	<a href="<?php echo C('ADMIN_ROOT');?>/project/exportToExcelRb/id/<?php echo ($item["id"]); ?>/rid/<?php echo ($item["rid"]); ?>" target="_blank">银行卡导出</a>
                        	<a href="<?php echo C('ADMIN_ROOT');?>/project/exporttoexcel/id/<?php echo ($item["id"]); ?>/rid/<?php echo ($item["rid"]); ?>/act/2" target="_blank">钱包导出</a><?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="10" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page"><?php echo ($show); ?></div>
        <!-- 列表显示区域结束 -->
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<style>
    .icon_add{
        background-image: url('<?php echo C("STATIC_ADMIN");?>/auth/images/icon_open.png');
        background-size: 20px 20px;
        background-repeat: no-repeat;
        padding-left: 25px;
    }
    .icon_close{
        background-image: url('<?php echo C("STATIC_ADMIN");?>/auth/images/icon_close.png');
    }
    .subitem{background-color: whitesmoke!important;}
</style>

<script>

	var start = {
        elem: '#rtime',
        format: 'YYYY-MM-DD',
        min: '1970-00-00', //设定最小日期为当前日期
        max: '2099-06-16', //最大日期
        istime: false,
        istoday: true,
        choose: function(datas){
        }
    };
	
	laydate(start);
	
</script>