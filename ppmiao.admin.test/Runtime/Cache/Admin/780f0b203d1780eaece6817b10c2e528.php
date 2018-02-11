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
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">日销售额 <a href="<?php echo C('ADMIN_ROOT');?>/project/daysales_export/dt/<?php echo ($datetime); ?>" target="_blank">导出Excel</a>&nbsp;&nbsp;日销售额宝付对账 <a href="<?php echo C('ADMIN_ROOT');?>/project/daysales_lianlian_export/dt/<?php echo ($datetime); ?>" target="_blank">导出Excel</a></div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <label for="dt">开始时间：<input type="text" class="laydate-icon" name="dt" id="dt" value="<?php echo ($datetime); ?>" readonly /></label>
                        <label for="flushcache"><input type="checkbox" id="flushcache" name="flushcache" value="1" />更新缓存</label>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
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
                    <td height="5" colspan="14" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="200px" align="center">产品名称</th>
                    <th width="80px" align="center">期数</th>
                    <th width="70px" align="center">利率(%)</th>
                    <th width="100px" align="center">标签</th>
                    <th width="100px" align="center">分组名称</th>
                    <th width="80px" align="center">合同利率(%)</th>
                    <th width="100px" align="center">合同手续费(%)</th>
                    <th width="120px" align="center">募集款数(元)</th>
                    <th width="120px" align="center">超过部分(元)</th>
                    <th width="120px" align="center">幽灵账户(元)</th>
                    <th width="100px" align="center">期限</th>
                    <th width="150px" align="center">融资人</th>
                    <th width="150px" align="center">红包金额(元)</th>
					<th align="center">备注</th>
                    <th width="*" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td><?php echo ($item["project"]["title"]); ?></td>
                        <td><?php echo ($item["project_title_id"]); ?></td>
                        <td align="right"><?php echo ($item["project"]["user_interest"]); ?></td>
                        <td align="left">
                        	<?php switch($item["project"]["new_preferential"]): case "0": ?>普通标<?php break;?> 
	                            <?php case "1": ?>新人特惠<?php break;?> 
	                            <?php case "2": ?>爆款<?php break;?> 
	                            <?php case "3": ?>HOT<?php break;?>
	                            <?php case "6": ?>活动<?php break;?> 
	                            <?php case "8": ?>私人专享<?php break;?> 
	                            <?php case "9": ?>月月加薪<?php break; endswitch;?>
						</td>
						<td align="left">
                        	<?php echo ($item["group_name"]); ?>
						</td>
                        <td align="right"><?php if(!empty($item["contract_info"])): echo ($item["contract_info"]["interest"]); endif; ?></td>
                        <td align="right"><?php if(!empty($item["contract_info"])): echo ($item["contract_info"]["fee"]); endif; ?></td>
                        <td align="right"><?php echo (number_format($item["totlecapital"],2)); ?></td>
                        <td align="right"><?php echo (number_format($item["money_more"],2)); ?></td>
                        <td align="right"><?php echo (number_format($item["ghost_money"],2)); ?></td>
                        <td align="center"><?php echo ($item["project"]["days"]); ?></td>
                        <td align="center"><?php echo ($item["project"]["financing"]); ?></td>
                        <td align="center"><?php echo ($item["red_amount"]); ?></td>
                        <?php if(($item["project"]["id"]) > "0"): ?><td><span id="remark_<?php echo ($item["project"]["id"]); ?>" style="color:red;"><?php echo ($item["project"]["remark"]); ?></span><a href="javascript:;" onclick="update_remark(<?php echo ($item["project"]["id"]); ?>)">[更新备注]</a></td>
                        <?php else: ?>
                            <td></td><?php endif; ?>
                         <td align="center"></td>
                    </tr><?php endforeach; endif; ?>
                <tr class="row">
                    <td align="right">合计</td>
                    <td align="right"></td>
                    <td align="right"></td>
                    <td align="right"></td>
                    <td align="center"></td>
                    <td align="center"></td>
                    <td align="right"></td>
                    <td align="right"><?php echo (number_format($totle_money,2)); ?></td>
                    <td align="right"><?php echo (number_format($totle_money_more,2)); ?></td>
                    <td align="right"><?php echo (number_format($totle_ghost_money,2)); ?></td>
                    <td align="center"></td>
                    <td align="center"></td>
                    <td align="right"><?php echo ($totle_red_amount); ?></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td height="5" colspan="14" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<script>
    var _layerIndex = 0;
    var _dt = "<?php echo ($datetime); ?>";

    var datetime = {
        elem: '#dt',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true
    };
    laydate(datetime);
    function update_remark(_id){
        var _remark = prompt('请输入备注信息:');
        if(_remark != '' && _remark != null){
            _layerIndex = layer.load('操作中...');
            $.post(ROOT + "/project/update_project_remark", {id: _id, dt: _dt, remark: _remark}, function(msg){
                if(msg.status){
                    $("#remark_" + _id).text(_remark);
                    layer.msg('更新成功~', 1, -1);
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        }
    }
</script>