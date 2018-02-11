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
        <div class="title">待还款记录</div>
        
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <span>
                        	<select name="financing" style="width:200px;">
                            <option value="0">所有融资方</option>
                            <?php if(is_array($financing)): foreach($financing as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($item["id"]) == $params['fid']): ?>selected<?php endif; ?>><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
						</span>
                        <label for="start_time">还款日期：<input type="text" class="laydate-icon" name="rtime" id="rtime" value="<?php echo ($params["rtime"]); ?>" readonly /></label>
                        
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" value="搜索" class="search imgButton">
                    	<!-- 
                    	<?php if(checkAuth('Admin/statistics/repayment_list_export_to_excel') == true): ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo C('ADMIN_ROOT');?>/statistics/repayment_list_export_to_excel" target="_blank">导出excel</a><?php endif; ?>
        				 --> 
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
                    <td height="5" colspan="6" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="15%" align='center'>期数</th>
                    <th width="15%" align='center'>总金额</th>
                    <th width="15%" align='center'>本金</th>
                    <th width="15%" align='center'>利息</th>
                    <th width="20%" align='center'>还款日期</th>
                    <th width="20%" align='center'>融资方名称</th>
                   
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td align='center'><?php echo ($item["title"]); ?></td>
                        <td align='center'><?php echo ($item["due_amount"]); ?></td>
                        <td align='center'><?php echo ($item["due_capital"]); ?></td>
                        <td align='center'><?php echo ($item["due_interest"]); ?></td>
                        <td align='center'><?php echo (date('Y-m-d',strtotime($item["end_time"]))); ?></td>
                        <td align='center'><?php echo ($item["f_name"]); ?></td>
                    </tr><?php endforeach; endif; ?>
                
                <tr>
                        <td align='center'>合计</td>
                        <td align='center'><?php echo ($params["total_due_amount"]); ?></td>
                        <td align='center'><?php echo ($params["total_due_capital"]); ?></td>
                        <td align='center'><?php echo ($params["total_due_interest"]); ?></td>
                        <td align='center'></td>
                        <td align='center'></td>
                    </tr>
                    
                <tr>
                    <td height="5" colspan="6" class="bottomTd"></td>
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