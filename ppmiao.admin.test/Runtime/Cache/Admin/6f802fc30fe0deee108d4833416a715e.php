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
        <div class="title">每日数据统计</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <div class="fLeft">
                    <label for="start_time">统计日期：<input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($datetime); ?>" readonly /></label>
                </div>
                <div class="impBtn hMargin fLeft shadow"><input type="button" id="deal_statistics" name="search" value="处理数据" onclick="" class="search imgButton">&nbsp;<input type="button" id="reload_page" value="刷新页面"  class="search imgButton"></div>
            </div>
        </div>
        <!-- 功能操作区域结束 -->
        <div id="addPlus" class="search cBoth">
            <table>
                <tbody>
                <tr>
                    <td>
                        <input type="button" value="批量导出excel" onclick="batch_daily_data()" class="search imgButton" style="margin-left:10px;width:200px;" />
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="46" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th>日期</th>
                    <th>银行卡购买理财</th>
                    <th>银行卡充值钱包</th>
                    <th>钱包购买理财</th>
                    <th>理财还款到钱包</th>
                    <th>理财产品还款到银行卡</th>
                    <th>钱包提现到银行卡</th>
                    <th>理财转银行卡订单数</th>
                    <th>钱包已投金额对应线下利率</th>
                    <th>钱包存量</th>
                    <th>钱包线上利率</th>
                    <th>钱包提现次数</th>
                    <th>投资理财(包括钱包)用户总数</th>
                    <th>投资理财产品用户总数</th>
                    <th>充值钱包总数</th>
                    <th>理财产品首投用户数</th>
                    <th>钱包首投用户数</th>
                    <th>理财产品二投用户数</th>
                    <th>钱包二投用户数</th>
                    <th>理财产品三投用户数</th>
                    <th>钱包三投用户数</th>
                    <th>理财产品四投用户数</th>
                    <th>钱包四投用户数</th>
                    <th>理财产品五投用户数</th>
                    <th>钱包五投用户数</th>
                    <th>复投用户数</th>
                    <th>激活当日投理财产品用户数</th>
                    <th>激活当日投钱包用户数</th>
                    <th>投资理财产品金额</th>
                    <th>投资钱包金额</th>
                    <th>首投理财产品金额</th>
                    <th>首投钱包金额</th>
                    <th>二投理财产品金额</th>
                    <th>二投钱包金额</th>
                    <th>三投理财产品金额</th>
                    <th>三投钱包金额</th>
                    <th>四投理财产品金额</th>
                    <th>四投钱包金额</th>
                    <th>五投理财产品金额</th>
                    <th>五投钱包金额</th>
                    <th>复投金额</th>
                    <th>投资用户数量</th>
                    <th>首投用户数量</th>
                    <th>复投用户数量</th>
                    <th width="*">操作</th>
                </tr>
                <?php if(is_array($daily_statistics_list)): foreach($daily_statistics_list as $key=>$item): ?><tr class="row">
                        <td><?php echo ($item["dt"]); ?></td>
                        <td><?php echo ($item["b2p_money"]); ?></td>
                        <td><?php echo ($item["b2w_money"]); ?></td>
                        <td><?php echo ($item["w2p_money"]); ?></td>
                        <td><?php echo ($item["p2w_money"]); ?></td>
                        <td><?php echo ($item["p2b_money"]); ?></td>
                        <td><?php echo ($item["w2b_money"]); ?></td>
                        <td><?php echo ($item["p2b_count"]); ?></td>
                        <td><?php echo ($item["wallet_interest_offline"]); ?></td>
                        <td><?php echo ($item["wallet_money"]); ?></td>
                        <td><?php echo ($item["wallet_interest"]); ?></td>
                        <td><?php echo ($item["w2b_count"]); ?></td>
                        <td><?php echo ($item["pay_count"]); ?></td>
                        <td><?php echo ($item["pay_p_count"]); ?></td>
                        <td><?php echo ($item["pay_w_count"]); ?></td>
                        <td><?php echo ($item["first_pay_p_count"]); ?></td>
                        <td><?php echo ($item["first_pay_w_count"]); ?></td>
                        <td><?php echo ($item["second_pay_p_count"]); ?></td>
                        <td><?php echo ($item["second_pay_w_count"]); ?></td>
                        <td><?php echo ($item["third_pay_p_count"]); ?></td>
                        <td><?php echo ($item["third_pay_w_count"]); ?></td>
                        <td><?php echo ($item["fourth_pay_p_count"]); ?></td>
                        <td><?php echo ($item["fourth_pay_w_count"]); ?></td>
                        <td><?php echo ($item["fifth_pay_p_count"]); ?></td>
                        <td><?php echo ($item["fifth_pay_w_count"]); ?></td>
                        <td><?php echo ($item["repay_count"]); ?></td>
                        <td><?php echo ($item["activation_pay_p_count"]); ?></td>
                        <td><?php echo ($item["activation_pay_w_count"]); ?></td>
                        <td><?php echo ($item["pay_p_money"]); ?></td>
                        <td><?php echo ($item["pay_w_money"]); ?></td>
                        <td><?php echo ($item["first_pay_p_money"]); ?></td>
                        <td><?php echo ($item["first_pay_w_money"]); ?></td>
                        <td><?php echo ($item["second_pay_p_money"]); ?></td>
                        <td><?php echo ($item["second_pay_w_money"]); ?></td>
                        <td><?php echo ($item["third_pay_p_money"]); ?></td>
                        <td><?php echo ($item["third_pay_w_money"]); ?></td>
                        <td><?php echo ($item["fourth_pay_p_money"]); ?></td>
                        <td><?php echo ($item["fourth_pay_w_money"]); ?></td>
                        <td><?php echo ($item["fifth_pay_p_money"]); ?></td>
                        <td><?php echo ($item["fifth_pay_w_money"]); ?></td>
                        <td><?php echo ($item["repay_money"]); ?></td>
                        <td><?php echo ($item["pay_uid_daily_num"]); ?></td>
                        <td><?php echo ($item["first_pay_uid_daily_num"]); ?></td>
                        <td><?php echo ($item["repay_uid_daily_num"]); ?></td>
                        <td>
                            <a href="<?php echo C('ADMIN_ROOT');?>/statistics/statistics_daily_excel/batch/1/id/<?php echo ($item["id"]); ?>">导出Excel</a>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="46" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page"><?php echo ($show); ?></div>
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
    var _layerIndex = 0;
    var _page = "<?php echo ($params["page"]); ?>";
    var _start_time = "<?php echo ($params["start_time"]); ?>";
    var _datetime = "<?php echo ($datetime); ?>";
    var _interest = "<?php echo ((isset($interest) && ($interest !== ""))?($interest):7); ?>";

    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true,
        choose: function(datas){

        }
    };
    laydate(start);
    function do_interest(_obj, _id, _isBatch){
        _interest = $("#interest").val();
        if(isNaN(_interest)){
            layer.alert('年化利率只能是数字', -1);
            return;
        }
        _layerIndex = layer.load('操作中...');
        $.post(ROOT + "/wallet/interest_do", {id: _id, dt: _datetime, rate: _interest, isBatch: _isBatch}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('操作成功~', -1, function(){
                    window.location.reload();
                });
            }else{
                layer.alert(msg.info, -1);
            }
        });
    }
    function refresh(){
        window.location.reload();
    }
    function changeInterest(_obj){
        var _val = $(_obj).val();
        if(isNaN(_val)){
            layer.alert('年化利率只能是数字', -1);
            return;
        }
    }
    function set_rate(){
        _interest = $("#interest").val();
        if(isNaN(_interest)){
            layer.alert('年化利率只能是数字', -1);
            return;
        }

        _layerIndex = layer.load('操作中...');
        $.post(ROOT + "/wallet/set_rate", {dt: _datetime, rate: _interest}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                window.location.reload();
            }else{
                layer.alert(msg.info, -1);
            }
        });
    }
    $("#deal_statistics").click(function(){
        var start_time = $("#start_time").val();
        if(!start_time){
            layer.alert("请选择要处理数据的日期",-1,function(){window.location.reload();});
        }
        layer.load('数据处理中...,请耐心等待!');
        $.post(ROOT + "/statistics/statistics_daily_data",{date_time:start_time},function(result){
                if(result.status == 1){
                    layer.alert(result.msg,-1,function(){window.location.reload();});

                }else{
                    layer.alert(result.msg,-1,function(){window.location.reload();});
                }
        },'json');
    });
    $("#reload_page").click(function(){
        window.location.reload();
    })
    function batch_daily_data(){
        window.location.href="<?php echo C('ADMIN_ROOT');?>/statistics/statistics_daily_excel/batch/2";
    }
</script>