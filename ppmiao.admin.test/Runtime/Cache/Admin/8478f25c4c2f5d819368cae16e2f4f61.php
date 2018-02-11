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
var URL = '/admin.php/Public';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<style>
    .moneynotice{
        width: 100%;
        background: orange;
        color: #fff;
        height: 30px;
        line-height: 40px;
        font-size: 15px;
        margin-bottom: 5px;
    }
</style>
<div class="main" >
    <div id="m_notice">
    </div>
    <div class="content">
        <TABLE id="checkList" class="list" cellpadding=0 cellspacing=0 >
            <tr><td height="3" colspan="2" class="topTd" ></td></tr>
            <?php if(checkAuth('Admin/main/datastatistics') == true): ?><tr class="row" ><th colspan="2" class="space">数据统计 <a href="javascript:;" onclick="loadBaseInfo(this)">刷新</a></th></tr>
            <tr class="row"><td width="15%" align="right">今日已售</td><td id="tdTodayTotleMoney"><?php echo (number_format($today_totle_money,2)); ?>&nbsp;元</td></tr>
            <tr class="row"><td width="15%" align="right">定期总销售额</td><td id="tdProjectTotleMoney"><?php echo (number_format($totle_money,2)); ?>&nbsp;元</td></tr>
            <tr class="row"><td width="15%" align="right">总销售额</td><td id="tdAllTotleMoney"><?php echo (number_format($totle_money,2)); ?>&nbsp;元</td></tr>
            <tr class="row"><td width="15%" align="right">定期存量</td><td id="total_profit"><?php echo (number_format($totle_money,2)); ?>&nbsp;元</td></tr>
            <tr class="row"><td width="15%" align="right">用户数量</td><td id="tdUserCount"><?php echo ($user_count["totle"]); ?> （ios：<?php echo ($user_count["ios_count"]); ?>，android：<?php echo ($user_count["android_count"]); ?>，未购买：<?php echo ($user_count["not_count"]); ?>）&nbsp;&nbsp;&nbsp;<em style="font-size:10px;color:gray;">下次更新:<?php echo (date('H:i:s',$user_count["update_time"])); ?></em></td></tr>
            <?php if(checkAuth('Admin/common/getSmsQueryBalance') == true): ?><tr class="row"><td width="15%" align="right">短信剩余</td><td><span id="sms">-</span>&nbsp;条</td></tr><?php endif; endif; ?>
            <?php if(checkAuth('Admin/main/latestbuyrecord') == true): ?><tr class="row" id="trLastestBuyRecord">
                    <th colspan="2" class="space">最新购买记录 <a href="javascript:;" onclick="loadLatestBuyRecord(this)">刷新</a></th>
                </tr><?php endif; ?>
            <!-- 
            <?php if(checkAuth('Admin/main/walletrechargerecord') == true): ?><tr class="row" id="trWalletRechargeRecord">
                    <th colspan="2" class="space">钱包充值记录 <a href="javascript:;" onclick="loadWalletRechargeRecord(this)">刷新</a></th>
                </tr><?php endif; ?>
            -->
            <?php if(checkAuth('Admin/main/lastesterrorpayment') == true): ?><tr class="row" id="trLastestErrorPayment"><th colspan="2" class="space">最新购买失败记录 <a href="javascript:;" onclick="loadLastestErrorPayment(this)">刷新</a></th></tr><?php endif; ?>
            <tr><td height="3" colspan="2" class="bottomTd"></td></tr>
        </TABLE>
    </div>
</div>
<script>
    var _loadErrorList = false;
    var _loadBuyList = false;
    var _loadWalletList = false;
    var _loadBaseInfo = false;
    var _loadProfit = false;
    
    $(document).ready(function(){
        <?php if(checkAuth('Admin/common/getSmsQueryBalance') == true): ?>$.post(ROOT + "/common/getSmsQueryBalance", {}, function(msg){
            $("#sms").text(msg.info);
        });<?php endif; ?>
        <?php if(checkAuth('Admin/main/datastatistics') == true): ?>loadBaseInfo();<?php endif; ?>
        <?php if(checkAuth('Admin/main/latestbuyrecord') == true): ?>loadLatestBuyRecord();<?php endif; ?>
        <?php if(checkAuth('Admin/main/datastatistics') == true): ?>loadProfit();<?php endif; ?>
        //<?php if(checkAuth('Admin/main/walletrechargerecord') == true): ?>loadWalletRechargeRecord();<?php endif; ?>
        
        
    });
    function loadBaseInfo(_obj){
        if(!_loadBaseInfo){
            $(_obj).text('正在刷新...');
            _loadBaseInfo = true;
            $.post(ROOT + "/Index/main_ajax", {act: 'data_statistics'}, function(msg){
                $(_obj).text('刷新');
                _loadBaseInfo = false;
                if(msg.status){
                    $("#tdTodayTotleMoney").html(msg.data.todayTotleMoney + '&nbsp;元');
                    $("#tdProjectTotleMoney").html(msg.data.projectTotleMoney + '&nbsp;元');
                    $("#tdAllTotleMoney").html(msg.data.allTotleMoney + '&nbsp;元');
                    $("#tdUserCount").html(msg.data.userCountCache.totle + ' （ios：'+msg.data.userCountCache.ios_count+'，android：'+msg.data.userCountCache.android_count+'，未购买：'+msg.data.userCountCache.not_count+'）&nbsp;&nbsp;&nbsp;<em style="font-size:10px;color:gray;">下次更新:'+msg.data.userCountCache.update_time+'</em>');
                    $('#m_notice').html(msg.data.fundData);
                }
            });
        }
    }
    function loadLatestBuyRecord(_obj){
        if(!_loadBuyList){
            _loadBuyList = true;
            $(_obj).text('正在刷新...');
            $(".lbr").remove();
            $.post(ROOT + "/Index/main_ajax", {act: 'latest_buy_record'}, function(msg){
                _loadBuyList = false;
                $(_obj).text('刷新');
                if(msg.status){
                    $("#trLastestBuyRecord").after(msg.info);
                }
            });
        }
    }
    /*
    function loadWalletRechargeRecord(_obj){
        if(!_loadWalletList){
            _loadWalletList = true;
            $(_obj).text('正在刷新...');
            $(".wrr").remove();
            $.post(ROOT + "/Index/main_ajax", {act: 'wallet_recharge_record'}, function(msg){
                _loadWalletList = false;
                $(_obj).text('刷新');
                if(msg.status){
                    $("#trWalletRechargeRecord").after(msg.info);
                }
            });
        }
    }
    */
    function loadLastestErrorPayment(_obj){
        if(!_loadErrorList){
            _loadErrorList = true;
            $(".lep").remove();
            $(_obj).text('正在刷新...');
            $.post(ROOT + "/Index/main_ajax", {act: 'lastest_error_payment'}, function(msg){
                _loadErrorList = false;
                $(_obj).text('刷新');
                if(msg.status){
                    $("#trLastestErrorPayment").after(msg.info);
                }
            });
        }
    }
    
    function loadProfit(_obj){
    	
        if(!_loadProfit){
            $(_obj).text('正在刷新...');
            _loadProfit = true;
            $.post(ROOT + "/Index/main_ajax", {act: 'system_profit'}, function(msg){
                $(_obj).text('刷新');
                _loadProfit = false;
                if(msg.status){
                    $("#total_profit").html(msg.data.system_profit.total_money+'元 ，人数：'+msg.data.system_profit.buy_user+'，笔数：'+msg.data.system_profit.buy_count+'&nbsp;&nbsp;<em style="font-size:10px;color:gray;">下次更新:'+msg.data.system_profit.update_time+'</em>');
                }
            });
        }
    }
</script>
<!-- 主页面结束 -->