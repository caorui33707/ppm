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
var URL = '/admin.php/Statistics';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">销售图表 -
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/daily">日数据</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/monthly">月数据</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/year">年数据</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/bk">爆款数据</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/hbfxztl">还本付息再次投资率</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/xsectz">购买新手标二次投资</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/cumulative">销售增量(产品)</a>&nbsp;&nbsp;
            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/sales_figures/target/cumulative_wallet">销售增量</a>&nbsp;&nbsp;
        </div>
        <div id="result" class="result none"></div>
    </div>
</div>