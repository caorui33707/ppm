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
        <div class="title">每日统计</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <select name="chn" style="width:200px;">
                            <option value="0">全部渠道</option>
                            <?php if(is_array($channel_list)): foreach($channel_list as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($item["id"]) == $params['chn']): ?>selected<?php endif; ?>><?php echo ($item["cons_value"]); ?>(<?php echo ($item["cons_key"]); ?>)</option><?php endforeach; endif; ?>
                        </select>
                        <label for="start_time">开始时间：<input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($params["st"]); ?>" readonly /></label>
                        <label for="end_time">结束时间：<input type="text" class="laydate-icon" name="end_time" id="end_time" value="<?php echo ($params["et"]); ?>" readonly /></label>
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
                    <td height="5" colspan="17" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="150px" align="center">日期</th>
                    <th width="100px" align="center">注册用户数</th>
                    <th width="100px" align="center">投资次数</th>
                    <th width="100px" align="center">复投次数</th>
                    <th width="150px" align="center">投资总额</th>
                    <th width="100px" align="center">总投用户</th>
                    <th width="100px" align="center">首投用户</th>
                    <th width="100px" align="center">激活设备数</th>
                    <th width="100px" align="center">当日激活当日投</th>
                    <th width="100px" align="center">新增二次用户</th>
                    <th width="100px" align="center">复投用户</th>
                    <th width="100px" align="center">新增钱包二投</th>
                    <th width="150px" align="center">首投总额</th>
                    <th width="150px" align="center">还款总额</th>
                    <th width="150px" align="center">三投用户</th>
                    <th width="150px" align="center">四投用户</th>
                    <th width="150px" align="center">五投用户</th>
                    <th width="*">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td align="center"><?php echo ($item["datetime"]); ?></td>
                        <td align="right"><?php echo ($item["registered_users"]); ?></td>
                        <td align="right"><?php echo (number_format($item["totle_count"])); ?></td>
                        <td align="right"><?php echo (number_format($item["next_pay_times"])); ?></td>
                        <td align="right"><?php echo (number_format($item['first_pay_sum'] + $item['next_pay_sum'])); ?></td>
                        <td align="right"><?php echo (number_format($item["totle_pay_person_count"])); ?></td>
                        <td align="right"><?php echo (number_format($item["first_pay_person_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($item["activation_count"])); ?></td>
                        <td align="right"><?php echo (number_format($item["activation_pay_count"])); ?></td>
                        <td align="right"><?php echo (number_format($item["new_second_pay_person_count"])); ?></td>
                        <td align="right"><?php echo (number_format($item["next_pay_person_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($item["totle_wallet_second_pay"])); ?></td>
                        <td align="right"><?php echo (number_format($item["first_pay_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($item["back_pay_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($item["three_pay_person_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($item["four_pay_person_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($item["five_pay_person_sum"])); ?></td>
                        <td></td>
                    </tr><?php endforeach; endif; ?>
                <tr class="row" style="background-color:aliceblue;">
                    <td align="center">合计</td>
                    <td align="right"><?php echo ($sumParams["sumRegistered_users"]); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumTotleCount"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumNextPayTimes"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams['sumFirstPay'] + $sumParams['sumNextPay'])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumTotlePayPersonCount"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumFirstPayPerson"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumActivation"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumActivationPay"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumNewSecondPayPersonCount"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumNextPayPerson"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumWalletSecondPay"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumFirstPay"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumBackPay"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumThreePayPerson"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumFourPayPerson"])); ?></td>
                    <td align="right"><?php echo (number_format($sumParams["sumFivePayPerson"])); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td height="5" colspan="17" class="bottomTd"></td>
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
<script>
    var _page = "<?php echo ($params["page"]); ?>";
    var _chn = "<?php echo ($params["chn"]); ?>";
    var _st = "<?php echo ($params["start_time"]); ?>";
    var _et = "<?php echo ($params["end_time"]); ?>";

    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true,
        choose: function(datas){
            end.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas //将结束日的初始值设定为开始日
        }
    };
    var end = {
        elem: '#end_time',
        format: 'YYYY-MM-DD',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: false,
        istoday: true,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);
</script>