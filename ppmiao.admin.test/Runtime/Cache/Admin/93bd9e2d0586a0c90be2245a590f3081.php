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
var URL = '/admin.php/Redenvelope';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main">

    <!-- 主体内容  -->
    <div class="content">
        <div class="title">红包核销</div>
        <!--  功能操作区域  -->
        <div class="operate">
            <!-- 查询区域 -->
            <div class="fRig">

                <form method='post' action="<?php echo C("ADMIN_ROOT");?>/redenvelope/cancel_out_index">
                <div class="fLeft">
                    手机号：
                    <input type="text" id="mobile" name="mobile" class="medium" value="<?php echo ($mobile); ?>">
                    姓名：
                    <input type="text" id="real_name" name="real_name" class="medium" value="<?php echo ($real_name); ?>">
                    <label for="start_time">核销状态：</label>
                    <select name="status">
                        <option value="1000" <?php if(($status) == "100"): ?>selected<?php endif; ?>>全部</option>
                        <option value="0" <?php if(($status) == "0"): ?>selected<?php endif; ?>>未核销</option>
                        <option value="1" <?php if(($status) == "1"): ?>selected<?php endif; ?>>已核销</option>
                        <option value="2" <?php if(($status) == "2"): ?>selected<?php endif; ?>>过期</option>
                        <option value="6" <?php if(($status) == "6"): ?>selected<?php endif; ?>>使用中</option>
                    </select>
                </div>
                <div class="impBtn hMargin fLeft shadow">
                    <input type="submit" value="查询" class="search imgButton">
                </div>
                </form>
            </div>
        </div>
        <!-- 列表显示区域  -->
        <div class="list">
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="14" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="2%" align="center">序号</th>
                    <th width="3%" align="center">用户姓名</th>
                    <th width="3%" align="center">手机号</th>
                    <th width="3%" align="center">标题</th>
                    <th width="4%" align="center">红包金额(元)</th>
                    <th width="4%" align="center">最小投资金额(元)</th>
                    <th width="2%" align="center">最小投资期限(天)</th>
                    <th width="7%" align="center">适用标签</th>
                    <th width="5%" align="center">生效时间</th>
                    <th width="5%" align="center">有效期至</th>
                    <th width="4%" align="center">状态</th>
                    <th width="4%" align="center">更新时间</th>
                    <th width="3%" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <th align="center"><?php echo ($item["id"]); ?></th>
                        <td align="center"><a href="<?php echo C('ADMIN_ROOT');?>/statistics/user_search/key/<?php echo ($mobile); ?>"><?php echo ($real_name); ?></a></td>
                        <td align="center"><?php echo ($mobile); ?></td>
                        <td align="center"><?php echo ($item["title"]); ?></td>
                        <td align="center"><?php echo (number_format($item["amount"],2)); ?></td>
                        <td align="center"><?php echo (number_format($item["min_invest"],2)); ?></td>
                        <td align="center"><?php echo ($item["min_due"]); ?></td>
                        <td align="center"><?php echo ($item["apply_tag"]); ?></td>
                        <td align="center"><?php echo ($item["create_time"]); ?></td>
                        <td align="center"><?php echo ($item["expire_time"]); ?></td>
                        <td align="center">
                            <?php if($item['status']==0){echo '未核销'; }elseif($item['status']==1){ echo '已核销'; }elseif($item['status']==2){ echo '过期'; }elseif($item['status']==6){ echo '锁住'; }else{ echo ''; }; ?></td>
                        <td align="center"><?php echo ($item["modify_time"]); ?></td>
                        <td align="center">
                            <?php if($item["status"] == 0 ): ?><button id="func15" onclick="cancel_out(<?php echo ($item["id"]); ?>)">核销</button><?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page">
            <?php echo ($showPage); ?>
        </div>
        <!-- 列表显示区域结束 -->
        <div style="clear: both;">
            <label>共:<b><?php echo ($params["totalCnt"]); ?></b>条记录;当前第<b><?php echo ($params["page"]); ?></b>页</label>
        </div>
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->


<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://apps.bdimg.com/libs/layer/2.1/layer.js"></script>
<script>
function cancel_out(_id) {
        layer.config({
            extend: 'extend/layer.ext.js'
        });
        layer.ready(function () {
            layer.prompt({
                title: '请输入相关订单号',
                formType: 0
            }, function (str) {
                if (str) {
                    $.post(ROOT + "/redenvelope/cancel_out_handle", {id: _id,recharge_no: str}, function(msg){
                        layer.msg(msg, {
                            time: 2000,
                            btn: ['确定   ']
                        });
                        setTimeout(function(){
                            window.location.reload();
                        },2000);
                    });

                }else{
                    layer.msg('请输入订单号，并确认');
                }
            });
        });
}
</script>