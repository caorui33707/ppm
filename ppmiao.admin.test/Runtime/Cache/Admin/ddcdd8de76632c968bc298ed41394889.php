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
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton">

                   <a class="search imgButton" href="<?php echo C('ADMIN_ROOT');?>/statistics/daily_statistics_excel?chn_id=<?php echo ($params['chn']); ?>&st=<?php echo ($params["st"]); ?>&et=<?php echo ($params["et"]); ?>" target="_blank">导出excel</a></div>
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
                    <td height="5" colspan="22" class="topTd"></td>
                </tr>

                <tr class="row">
                    <th width="150px" align="center">渠道</th>
                    <th width="200px" align="center">日期</th>
                    <th width="100px" align="center">注册用户数</th>
                    <th width="100px" align="center">首投用户数</th>
                    <th width="100px" align="center">首投次数</th>
                    <th width="150px" align="center">首投总额</th>
                    <th width="100px" align="center">复投用户数</th>
                    <th width="100px" align="center">复投次数</th>
                    <th width="100px" align="center">复投总额</th>
                    <th width="100px" align="center">总投用户数</th>
                    <th width="100px" align="center">总投资次数</th>
                    <th width="100px" align="center">累计投资总额</th>
                    <th width="100px" align="center">首次提现用户数</th>
                    <th width="150px" align="center">首次提现金额</th>
                    <th width="150px" align="center">累计提现用户数</th>
                    <th width="150px" align="center">累计提现次数</th>
                    <th width="150px" align="center">累计提现总金额</th>
                    <th width="150px" align="center">还款用户数</th>
                    <th width="150px" align="center">还款总额</th>
                    <th width="150px" align="center">流失用户数</th>
                    <th width="150px" align="center">沉默用户数</th>
                </tr>

                <?php $sum_registered_users = $sum_first_pay_person_sum = $sum_first_pay_count =0; $sum_first_pay_sum = $sum_next_pay_person_sum = $sum_next_pay_times = 0; $sum_next_pay_sum = $sum_totle_pay_person_count = $sum_totle_count = 0; $sum_totle_pay_sum = $sum_first_withdraw_user_sum = $sum_first_withdraw_sum = 0; $sum_total_withdraw_user_sum = $sum_total_withdraw_user_count = $sum_total_withdraw_pay_sum = 0; $sum_total_repayment_user = $sum_total_repayment_pay_sum = $sum_loss_users = $sum_loss_users =0; ?>

                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td align="center"><?php echo ($item["cons_value"]); ?></td>
                        <td align="center"><?php echo ($item["datetime"]); ?></td>
                        <td align="right"><?php echo ($registered_users = $item["registered_users"]); ?></td>
                        <td align="right"><?php echo (number_format($first_pay_person_sum = $item["first_pay_person_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($first_pay_count = $item["first_pay_count"])); ?></td>
                        <td align="right"><?php echo (number_format($first_pay_sum = $item["first_pay_sum"],2)); ?></td>
                        <td align="right"><?php echo (number_format($next_pay_person_sum = $item["next_pay_person_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($next_pay_times = $item["next_pay_times"])); ?></td>
                        <td align="right"><?php echo (number_format($next_pay_sum = $item["next_pay_sum"],2)); ?></td>
                        <td align="right"><?php echo (number_format($totle_pay_person_count = $item["totle_pay_person_count"])); ?></td>
                        <td align="right"><?php echo (number_format($totle_count = $item["totle_count"])); ?></td>
                        <td align="right"><?php echo (number_format($totle_pay_sum = $item["totle_pay_sum"],2)); ?></td>
                        <td align="right"><?php echo (number_format($first_withdraw_user_sum = $item["first_withdraw_user_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($first_withdraw_sum = $item["first_withdraw_sum"],2)); ?></td>
                        <td align="right"><?php echo (number_format($total_withdraw_user_sum = $item["total_withdraw_user_sum"])); ?></td>
                        <td align="right"><?php echo (number_format($total_withdraw_user_count = $item["total_withdraw_user_count"])); ?></td>
                        <td align="right"><?php echo (number_format($total_withdraw_pay_sum = $item["total_withdraw_pay_sum"],2)); ?></td>
                        <td align="right"><?php echo (number_format($total_repayment_user = $item["total_repayment_user"])); ?></td>
                        <td align="right"><?php echo (number_format($total_repayment_pay_sum = $item["total_repayment_pay_sum"],2)); ?></td>

                        <td align="right"><?php echo (number_format($loss_users = $item["loss_users"])); ?></td>

                        <td align="right"><?php echo (number_format($silent_users = $item["silent_users"])); ?></td>
                        <td></td>
                    </tr>

                    <?php $sum_registered_users += $registered_users; $sum_first_pay_person_sum += $first_pay_person_sum; $sum_first_pay_count += $first_pay_count; $sum_first_pay_sum += $first_pay_sum; $sum_next_pay_person_sum += $next_pay_person_sum; $sum_next_pay_times += $next_pay_times; $sum_next_pay_sum += $next_pay_sum; $sum_totle_pay_person_count += $totle_pay_person_count; $sum_totle_count += $totle_count; $sum_totle_pay_sum += $totle_pay_sum; $sum_first_withdraw_user_sum += $first_withdraw_user_sum; $sum_first_withdraw_sum += $first_withdraw_sum; $sum_total_withdraw_user_sum += $total_withdraw_user_sum; $sum_total_withdraw_user_count += $total_withdraw_user_count; $sum_total_withdraw_pay_sum += $total_withdraw_pay_sum ; $sum_total_repayment_user += $total_repayment_user ; $sum_total_repayment_pay_sum += $total_repayment_pay_sum ; $sum_loss_users += $loss_users ; $sum_silent_users += $silent_users ; endforeach; endif; ?>



                <tr class="row" style="background-color:aliceblue;">
                    <td align="center">合计</td>
                    <td align="center"></td>
                    <td align="right"><?php echo ($sum_registered_users); ?></td>
                    <td align="right"><?php echo ($sum_first_pay_person_sum); ?></td>
                    <td align="right"><?php echo ($sum_first_pay_count); ?></td>
                    <td align="right"><?php echo (number_format($sum_first_pay_sum,2)); ?></td>
                    <td align="right"><?php echo ($sum_next_pay_person_sum); ?></td>
                    <td align="right"><?php echo ($sum_next_pay_times); ?></td>
                    <td align="right"><?php echo (number_format($sum_next_pay_sum,2)); ?></td>
                    <td align="right"><?php echo ($sum_totle_pay_person_count); ?></td>
                    <td align="right"><?php echo ($sum_totle_count); ?></td>
                    <td align="right"><?php echo (number_format($sum_totle_pay_sum,2)); ?></td>
                    <td align="right"><?php echo ($sum_first_withdraw_user_sum); ?></td>
                    <td align="right"><?php echo (number_format($sum_first_withdraw_sum,2)); ?></td>
                    <td align="right"><?php echo ($sum_total_withdraw_user_sum); ?></td>
                    <td align="right"><?php echo ($sum_total_withdraw_user_count); ?></td>
                    <td align="right"><?php echo (number_format($sum_total_withdraw_pay_sum,2)); ?></td>
                    <td align="right"><?php echo ($sum_total_repayment_user); ?></td>
                    <td align="right"><?php echo (number_format($sum_total_repayment_pay_sum,2)); ?></td>
                    <td align="right"><?php echo ($sum_loss_users); ?></td>
                    <td align="right"><?php echo ($sum_silent_users); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td height="5" colspan="22" class="bottomTd"></td>
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
    var _st = "<?php echo ($params["st"]); ?>";
    var _et = "<?php echo ($params["et"]); ?>";

    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max:laydate.now(0, 'YYYY-MM-DD 23:59:59'),// '2099-06-16 23:59:59', //最大日期
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
        min: _st,
        max: laydate.now(0, 'YYYY-MM-DD 23:59:59'),//'2099-06-16 23:59:59',
        istime: false,
        istoday: true,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);

    $('.search').click(function () {
        var start_time = $('start_time').val();
        var end_time = $('end_time').val();
    })
</script>