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
var URL = '/admin.php/Project';
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
        <div class="title">成标记录</div>
        
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
            <!-- 
                <form method='post' action="">
                    <div class="fLeft">
                        <span>
                        	<select name="financing" style="width:200px;">
                            <option value="0">所有融资方</option>
                            <?php if(is_array($financing)): foreach($financing as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($item["id"]) == $params['fid']): ?>selected<?php endif; ?>><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
						</span>
						<label for="start_time"><input type="text" name="title" value="<?php echo ($params["title"]); ?>" placeholder="请输入产品名称"/></label>
                        <label for="start_time">开始日期 <input type="text" class="laydate-icon" name="startTime" id="startTime" value="<?php echo ($params["startTime"]); ?>" readonly />
                       	结束时期 <input type="text" class="laydate-icon" name="endTime" id="endTime" value="<?php echo ($params["endTime"]); ?>" readonly /></label>
                        &nbsp;&nbsp;
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" value="搜索" class="search imgButton">
                    	
                    	<?php if(checkAuth('Admin/project/chargeoff_log_export') == true): ?>&nbsp;&nbsp;&nbsp;<span>&nbsp;&nbsp;<a href="###" id="exportExcel">导出Excel</a></span><?php endif; ?>
        				  
                    </div>
                </form>
                 -->
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
                    <td height="5" colspan="7" class="topTd"></td>
                </tr>
                <tr class="row">
                	<th width="10%" align='center'>产品Id</th>
                    <th width="15%" align='center'>产品名称</th>
                    <th width="15%" align='center'>成废时间</th>
                    <th width="10%" align='center'>类型</th>
                    <th width="12%" align='center'>金额(元)</th>                    
                    <th width="12%" align='center'>状态</th>
                    <th width="20%" align='center'>融资方名称</th>                   
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                    	<td align='center'><?php echo ($item["project_id"]); ?></td>
                        <td align='center'><?php echo ($item["p_title"]); ?></td>
                        <td align='center'><?php echo ($item["create_time"]); ?></td>
                        <td align='center'>
							<?php switch($item["flag"]): case "3": ?>废标<?php break;?>
    							<?php case "2": ?>成标<?php break; endswitch;?>
						</td>
                        <td align='center'><?php echo ($item["amt"]); ?></td>
                        <td align='center'>
							<?php if($item["code"] == 0): ?>成功<?php else: echo ($item["mess"]); endif; ?>
                        </td>
                        <td align='center'><?php echo ($item["f_name"]); ?></td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="7" class="bottomTd"></td>
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