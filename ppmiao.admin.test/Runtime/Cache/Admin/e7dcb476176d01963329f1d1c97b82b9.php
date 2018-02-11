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
var URL = '/admin.php/Lottery';
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
        <div class="title">抽奖活动中奖名单</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='get' action="<?php echo C("ADMIN_ROOT");?>/lottery/lottery_log">
                    <div class="fLeft">
                        <label for="start_time">选择抽奖活动：</label>
                        <select name="lotteryId">
                            <option value="0" <?php if(($params["lottery_id"]) == "0"): ?>selected<?php endif; ?>>请选择</option>                           
                            <?php if(is_array($params["lottery_list"])): foreach($params["lottery_list"] as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($params["lottery_id"]) == $item["id"]): ?>selected<?php endif; ?>><?php echo ($item["name"]); ?>  -  <?php echo ($item["lottery_status"]); ?>
                            </option><?php endforeach; endif; ?>                     
                        </select>    
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" id="" name="search" value="查询" onclick="" class="search imgButton">
                    	<?php if(checkAuth('Admin/lottery/lottery_log_add') == true): ?><input type="button" name="add" value="假数据" onclick="Add();" class="add imgButton"><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
       
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="7" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="3%" align="center">编号</th>
                    <th width="8%" align="center">用户名</th>
                    <th width="8%" align="center">真实姓名</th>
                    <th width="8%" align="center">奖品名称</th>
                    <th width="8%" align="center">活动名称</th>
                    <th width="8%" align="center">中奖时间</th>
                    <!-- <th width="8%" align="center">操作</th> -->
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td align="center"><?php echo ($item["id"]); ?></td>
                        <td align="center"><?php echo ($item["user_name"]); ?></td>
                        <td align="center"><?php echo ($item["real_name"]); ?></td>
                        <td align="center"><?php echo ($item["lottery_award_name"]); ?></td>
                        <td align="center"><?php echo ($item["lottery_name"]); ?></td>
                        <td align="center"><?php echo (date('Y-m-d H:i:s',$item["create_time"])); ?></td>
                        <!-- 
                        <td align="center">
                        </td> -->
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="7" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page">
            <?php echo ($show); ?>
        </div>
        <!-- 列表显示区域结束 -->
        <div style="clear: both;"></div>
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
	function Add(){
		window.location.href = ROOT + '/lottery/lottery_log_add';
	}
</script>