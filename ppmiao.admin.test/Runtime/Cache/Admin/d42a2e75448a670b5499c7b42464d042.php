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
var URL = '/admin.php/Auth';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">编辑权限组 [ <a href="<?php echo C('ADMIN_ROOT');?>/auth/group/p/<?php echo ($params["page"]); ?>">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <form id="frmMain" method='post' action="">
            <input type="hidden" name="id" value="<?php echo ($detail["id"]); ?>" />
            <input type="hidden" name="page" value="<?php echo ($params["page"]); ?>" />
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">组名称：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="title" name="title" value="<?php echo ($detail["title"]); ?>"></td>
                </tr>
                <tr>
                    <td class="tRight">组描述：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" id="description" name="description" value="<?php echo ($detail["description"]); ?>"></td>
                </tr>
                <tr>
                    <td class="tRight">状态：</td>
                    <td class="tLeft" >
                        <select name="status">
                            <option value="0" <?php if(($detail["status"]) == "0"): ?>selected<?php endif; ?>>禁用</option>
                            <option value="1" <?php if(($detail["status"]) == "1"): ?>selected<?php endif; ?>>正常</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">规则：</td>
                    <td class="tLeft" >
                        <p>
                            <label><input type="checkbox" name="rules[]" value="1" />后台首页</label>
                            <label><input type="checkbox" name="rules[]" value="25" />修改密码</label>
                            <label><input type="checkbox" name="rules[]" value="89" />最新购买记录</label>
                            <label><input type="checkbox" name="rules[]" value="90" />仪表盘数据统计</label>
                            <label><input type="checkbox" name="rules[]" value="91" />短信剩余量</label>
                            <label><input type="checkbox" name="rules[]" value="135" />后台首页异步数据</label>
                            <label><input type="checkbox" name="rules[]" value="136" />最新购买失败记录</label>
                            <label><input type="checkbox" name="rules[]" value="138" />最新钱包充值</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="2" />产品管理</label>
                            <label><input type="checkbox" name="rules[]" value="3" />添加产品</label>
                            <label><input type="checkbox" name="rules[]" value="71" />产品详细</label>
                            <label><input type="checkbox" name="rules[]" value="4" />编辑产品</label>
                            <!-- 
                            <label><input type="checkbox" name="rules[]" value="125" />添加产品(基金类)</label>
                            <label><input type="checkbox" name="rules[]" value="167" />添加产品(搏息宝)</label>
                            <label><input type="checkbox" name="rules[]" value="169" />添加产品(增值类)</label>
                            <label><input type="checkbox" name="rules[]" value="126" />编辑产品(基金类)</label>
                            <label><input type="checkbox" name="rules[]" value="168" />编辑产品(搏息宝)</label>
                            <label><input type="checkbox" name="rules[]" value="170" />编辑产品(增值类)</label>
                             -->
                                                         
                             
                            <label><input type="checkbox" name="rules[]" value="406" />产品管理-银行审核</label>
                            <label><input type="checkbox" name="rules[]" value="407" />产品管理-复制标</label>                             
                            <label><input type="checkbox" name="rules[]" value="410" />产品管理-出账记录</label>
                            <label><input type="checkbox" name="rules[]" value="411" />产品管理-出账记录导出</label>
                            <label><input type="checkbox" name="rules[]" value="417" />产品管理-标的成废记录</label>
                            
                            <label><input type="checkbox" name="rules[]" value="418" />产品管理-财务审核列表</label>
                            <label><input type="checkbox" name="rules[]" value="419" />产品管理-财务审核</label>
                            <label><input type="checkbox" name="rules[]" value="432" />产品管理-财务批量审核</label>
                            <label><input type="checkbox" name="rules[]" value="440" />产品管理-财务审核-导出</label>
                                                          
                             
                            <label><input type="checkbox" name="rules[]" value="5" />删除产品</label>
                            <label><input type="checkbox" name="rules[]" value="10" />产品审核</label>
                            <label><input type="checkbox" name="rules[]" value="11" />还本付息</label>
                            <label><input type="checkbox" name="rules[]" value="12" />购买列表</label>
                            <label><input type="checkbox" name="rules[]" value="85" />购买列表(独立)</label>
                            <label><input type="checkbox" name="rules[]" value="33" />付息列表</label>
                            <label><input type="checkbox" name="rules[]" value="79" />付息短信预告</label>
                            <label><input type="checkbox" name="rules[]" value="128" />付息短信达到率检查</label>
                            <label><input type="checkbox" name="rules[]" value="133" />还款补发短信</label>
                            <label><input type="checkbox" name="rules[]" value="60" />日销售额</label>
                            <label><input type="checkbox" name="rules[]" value="146" />日销售额导出</label>
							<label><input type="checkbox" name="rules[]" value="173" />日销售额连连对账</label>
                            <label><input type="checkbox" name="rules[]" value="58" />导出付息Excel(连连)</label>
                            <label><input type="checkbox" name="rules[]" value="78" />导出付息Excel(盛付通)</label>
                            <label><input type="checkbox" name="rules[]" value="14" />项目预览</label>
                            <label><input type="checkbox" name="rules[]" value="15" />产品回收站</label>
                            <label><input type="checkbox" name="rules[]" value="16" />恢复删除产品</label>
                            <label><input type="checkbox" name="rules[]" value="99" />更新产品备注</label>
                            <label><input type="checkbox" name="rules[]" value="127" />基金历史年化率</label>
                            <label><input type="checkbox" name="rules[]" value="144" />到期转入钱包记录</label>
                            <label><input type="checkbox" name="rules[]" value="145" />操作到期转入钱包</label>
							<label><input type="checkbox" name="rules[]" value="171" />批量操作到期转入钱包</label>
                            <label><input type="checkbox" name="rules[]" value="201" />导出指定时间段内产品还本付息表</label>
                            
                            <label><input type="checkbox" name="rules[]" value="291" />生成产品期数</label>
                            <label><input type="checkbox" name="rules[]" value="292" />获取合同信息</label>
                            
                            <label><input type="checkbox" name="rules[]" value="340" />付息列表 - 新</label>
                            <label><input type="checkbox" name="rules[]" value="341" />付息列表 - 新 - 批量转入钱包</label>
                            <label><input type="checkbox" name="rules[]" value="342" />付息列表 - 新 - (支付)批量转入银行卡</label>
                            
                            <label><input type="checkbox" name="rules[]" value="354" />产品管理-平台时实存量统计</label>

                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="83" />幽灵账户</label>
                            <label><input type="checkbox" name="rules[]" value="286" />产品管理-幽灵账户-指定账户购买记录汇总</label>

                            <label><input type="checkbox" name="rules[]" value="84" />幽灵账户购买</label>
                            <label><input type="checkbox" name="rules[]" value="81" />爆款订阅推送</label>
                            <label><input type="checkbox" name="rules[]" value="59" />预告推送</label>
                            <label><input type="checkbox" name="rules[]" value="77" />系统消息</label>
                            <label><input type="checkbox" name="rules[]" value="46" />产品类型管理</label>
                            <label><input type="checkbox" name="rules[]" value="47" />添加产品类型</label>
                            <label><input type="checkbox" name="rules[]" value="48" />编辑产品类型</label>
                            <label><input type="checkbox" name="rules[]" value="49" />删除产品类型</label>
                            <label><input type="checkbox" name="rules[]" value="42" />银行卡管理</label>
                            <label><input type="checkbox" name="rules[]" value="43" />添加银行卡</label>
                            <label><input type="checkbox" name="rules[]" value="44" />编辑银行卡</label>
                            <label><input type="checkbox" name="rules[]" value="45" />删除银行卡</label>
                            
                            <label><input type="checkbox" name="rules[]" value="330" />银行限额管理</label>
                            <label><input type="checkbox" name="rules[]" value="331" />银行限额管理 -新增</label>
                            <label><input type="checkbox" name="rules[]" value="332" />银行限额管理 -编辑</label>
                            <label><input type="checkbox" name="rules[]" value="333" />银行限额管理 -删除</label>
                            
                            <label><input type="checkbox" name="rules[]" value="343" />银行提现限额管理</label>
                            <label><input type="checkbox" name="rules[]" value="344" />银行提现限额管理 - 编辑</label>
                            
                        </p>
                        
                        <p>
                            <label><input type="checkbox" name="rules[]" value="119" />合同管理</label>
                            <label><input type="checkbox" name="rules[]" value="120" />添加合同</label>
                            <label><input type="checkbox" name="rules[]" value="121" />编辑合同</label>
                            <label><input type="checkbox" name="rules[]" value="122" />添加合同产品</label>
                            <label><input type="checkbox" name="rules[]" value="123" />编辑合同产品</label>
                            <label><input type="checkbox" name="rules[]" value="124" />文件管理</label>                            
                            <label><input type="checkbox" name="rules[]" value="326" />导出合同</label>
                        </p>
                        
                        <p>
							<label><input type="checkbox" name="rules[]" value="174" />转入/转出(导)</label>
							<label><input type="checkbox" name="rules[]" value="175" />转入/转出(Excel)</label>
                            <label><input type="checkbox" name="rules[]" value="96" />钱包转入/转出(查询)</label>
                            <label><input type="checkbox" name="rules[]" value="147" />钱包转入/转出(审核)</label>
                            <label><input type="checkbox" name="rules[]" value="100" />审核金额</label>
                            <label><input type="checkbox" name="rules[]" value="101" />审核金额(批量)</label>
                            <label><input type="checkbox" name="rules[]" value="97" />钱包用户提现</label>
                            <label><input type="checkbox" name="rules[]" value="102" />提现金额支付</label>
                            <label><input type="checkbox" name="rules[]" value="103" />提现金额支付完成</label>
                            <label><input type="checkbox" name="rules[]" value="98" />钱包计息列表</label>
                            <label><input type="checkbox" name="rules[]" value="104" />操作计息</label>
                            <label><input type="checkbox" name="rules[]" value="105" />设置年化利率</label>
                            <label><input type="checkbox" name="rules[]" value="139" />提现导出</label>
                            <label><input type="checkbox" name="rules[]" value="140" />提现导出(盛付通)</label>
                            <label><input type="checkbox" name="rules[]" value="141" />每日流水</label>
                            <label><input type="checkbox" name="rules[]" value="148" />钱包投资记录</label>
                            <label><input type="checkbox" name="rules[]" value="149" />添加钱包投资</label>
                            <label><input type="checkbox" name="rules[]" value="150" />编辑钱包投资</label>
							<label><input type="checkbox" name="rules[]" value="172" />预设钱包年化利率</label>
                            <label><input type="checkbox" name="rules[]" value="180" />钱包线下资金</label>
                            <label><input type="checkbox" name="rules[]" value="200" />导出指定时间段内钱包利息表</label>
                            
                            <label><input type="checkbox" name="rules[]" value="293" />钱包计息异常</label>
                            <label><input type="checkbox" name="rules[]" value="329" />钱包每天15点存量</label>
							
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="6" />消息管理</label>
                            <label><input type="checkbox" name="rules[]" value="7" />发布消息</label>
                            <label><input type="checkbox" name="rules[]" value="8" />编辑消息</label>
                            <label><input type="checkbox" name="rules[]" value="9" />删除消息</label>
                            <label><input type="checkbox" name="rules[]" value="87" />恢复消息</label>
                            <label><input type="checkbox" name="rules[]" value="19" />审核消息</label>
                            <label><input type="checkbox" name="rules[]" value="72" />消息评论</label>
                            <label><input type="checkbox" name="rules[]" value="75" />评论列表</label>
                            <label><input type="checkbox" name="rules[]" value="73" />审核评论</label>
                            <label><input type="checkbox" name="rules[]" value="74" />删除评论</label>
                            <label><input type="checkbox" name="rules[]" value="13" />推送消息</label>
                            <label><input type="checkbox" name="rules[]" value="76" />发送短信</label>
                            <label><input type="checkbox" name="rules[]" value="50" />意见建议</label>
                            <label><input type="checkbox" name="rules[]" value="51" />意见建议详细</label>
                            <label><input type="checkbox" name="rules[]" value="52" />删除意见建议</label>
                            <label><input type="checkbox" name="rules[]" value="82" />更改意见建议状态</label>
                            <label><input type="checkbox" name="rules[]" value="134" />意见建议回复</label>

                            <label><input type="checkbox" name="rules[]" value="467" />推送列表</label>
                            <label><input type="checkbox" name="rules[]" value="468" />推送列表添加</label>
                            <label><input type="checkbox" name="rules[]" value="469" />推送列表编辑</label>
                            <label><input type="checkbox" name="rules[]" value="470" />推送列表删除</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="92" />任务列表</label>
                            <label><input type="checkbox" name="rules[]" value="93" />添加任务</label>
                            <label><input type="checkbox" name="rules[]" value="94" />编辑任务</label>
                            <label><input type="checkbox" name="rules[]" value="95" />更新任务状态</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="21" />渠道管理</label>
                            <label><input type="checkbox" name="rules[]" value="22" />添加渠道</label>
                            <label><input type="checkbox" name="rules[]" value="23" />编辑渠道</label>
                            <label><input type="checkbox" name="rules[]" value="24" />删除渠道</label>
                            <label><input type="checkbox" name="rules[]" value="26" />渠道统计</label>
                            <label><input type="checkbox" name="rules[]" value="20" />用户统计</label>
                            <label><input type="checkbox" name="rules[]" value="63" />导出用户统计</label>
                            <label><input type="checkbox" name="rules[]" value="62" />每日统计</label>
                            <label><input type="checkbox" name="rules[]" value="107" />还款数据</label>
                            <label><input type="checkbox" name="rules[]" value="142" />钱包统计</label>
                            <label><input type="checkbox" name="rules[]" value="151" />钱包统计明细</label>
                            <label><input type="checkbox" name="rules[]" value="152" />钱包提现记录</label>
                            <label><input type="checkbox" name="rules[]" value="153" />钱包充值记录</label>
                            <label><input type="checkbox" name="rules[]" value="80" />销售图表</label>
                            <label><input type="checkbox" name="rules[]" value="61" />用户查询</label>
                            <label><input type="checkbox" name="rules[]" value="143" />历史订单</label>
                            <label><input type="checkbox" name="rules[]" value="86" />更新用户手机号码</label>
                            <label><input type="checkbox" name="rules[]" value="106" />更新银行卡支行</label>
                            <label><input type="checkbox" name="rules[]" value="166" />收益统计</label>
							<label><input type="checkbox" name="rules[]" value="176" />每日数据统计</label>
							<label><input type="checkbox" name="rules[]" value="177" />每日数据导出(Excel)</label>
							<label><input type="checkbox" name="rules[]" value="178" />查看用户银行卡信息</label>
							<label><input type="checkbox" name="rules[]" value="179" />修改用户银行卡信息</label>								
							<label><input type="checkbox" name="rules[]" value="185" />用户钱包利息列表</label>
							<label><input type="checkbox" name="rules[]" value="181" />每日收支</label>
							<label><input type="checkbox" name="rules[]" value="182" />用户银行卡投资记录</label>
							<label><input type="checkbox" name="rules[]" value="183" />核查用户是否掉单</label>
							<label><input type="checkbox" name="rules[]" value="184" />核实用户补单</label>
                            <label><input type="checkbox" name="rules[]" value="188" />钱包转入或者转出补记录</label>

                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="88" />行为跟踪</label>
                            <label><input type="checkbox" name="rules[]" value="190" />录入用户跟踪记录</label>
                            <label><input type="checkbox" name="rules[]" value="191" />录入用户跟踪类型</label>
                            <label><input type="checkbox" name="rules[]" value="192" />录入用户跟踪内容</label>
                            <label><input type="checkbox" name="rules[]" value="193" />获取用户跟踪类型列表</label>
                            <label><input type="checkbox" name="rules[]" value="194" />查询用户具体跟踪内容</label>
                            <label><input type="checkbox" name="rules[]" value="195" />负责人跟踪问题列表</label>
                            <label><input type="checkbox" name="rules[]" value="196" />删除跟踪问题列表</label>
                            <label><input type="checkbox" name="rules[]" value="198" />查询负责人跟踪内容清单</label>
                            <label><input type="checkbox" name="rules[]" value="199" />编辑指定跟踪内容</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="56" />首次购买返现</label>
                            <label><input type="checkbox" name="rules[]" value="57" />支付购买返现</label>
                            <label><input type="checkbox" name="rules[]" value="129" />新手标购买记录</label>
                            <label><input type="checkbox" name="rules[]" value="130" />新手标购买百分比</label>
                            <label><input type="checkbox" name="rules[]" value="131" />爆款标购买记录</label>
                            <label><input type="checkbox" name="rules[]" value="132" />爆款标购买百分比</label>
                            <label><input type="checkbox" name="rules[]" value="137" />赢收益,送豪礼</label>
							<label><input type="checkbox" name="rules[]" value="186" />活动送现金券</label>
                            <label><input type="checkbox" name="rules[]" value="187" />批量送现金券</label>
                            <label><input type="checkbox" name="rules[]" value="189" />送现金券导出excel表</label>
                            <label><input type="checkbox" name="rules[]" value="197" />获取用户现金券记录列表</label>
                            <label><input type="checkbox" name="rules[]" value="202" />A轮融资活动回馈用户活动</label>
                            <label><input type="checkbox" name="rules[]" value="203" />导出A轮融资大回馈活动获奖用户</label>
                            <label><input type="checkbox" name="rules[]" value="204" />批量发送获奖用户短信通知</label>
                            
                            <label><input type="checkbox" name="rules[]" value="334" />新手标活动单日数据统计</label>
                            <label><input type="checkbox" name="rules[]" value="335" />新手标活动单日数据统计 - 导出</label>
                            <label><input type="checkbox" name="rules[]" value="336" />新手标活动单日数据统计 - 明细(五万)</label>
                            <label><input type="checkbox" name="rules[]" value="337" />新手标活动单日数据统计 - 明细(五万) - 导出</label>
                            <label><input type="checkbox" name="rules[]" value="338" />新手标活动单日数据统计 - 明细</label>
                            <label><input type="checkbox" name="rules[]" value="339" />新手标活动单日数据统计 - 明细 - 导出</label>
                            
                            <label><input type="checkbox" name="rules[]" value="413" />邀请好友活动数据复盘</label>
                            
                            <label><input type="checkbox" name="rules[]" value="414" />好友邀请活动(客服)</label>
                            <label><input type="checkbox" name="rules[]" value="415" />好友邀请活动(运营)</label>
                            <label><input type="checkbox" name="rules[]" value="416" />好友邀请活动(运营)-导出</label>
                            

                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="65" />广告管理</label>
                            <label><input type="checkbox" name="rules[]" value="66" />添加广告</label>
                            <label><input type="checkbox" name="rules[]" value="67" />编辑广告</label>
                            <label><input type="checkbox" name="rules[]" value="68" />删除广告</label>
                            
                            <label><input type="checkbox" name="rules[]" value="453" />产品公告列表</label>
                            <label><input type="checkbox" name="rules[]" value="454" />产品公告添加</label>
                            <label><input type="checkbox" name="rules[]" value="455" />产品公告编辑</label>

                            <label><input type="checkbox" name="rules[]" value="464" />公告标签列表</label>
                            <label><input type="checkbox" name="rules[]" value="465" />公告标签添加</label>
                            <label><input type="checkbox" name="rules[]" value="466" />公告标签编辑</label>
                            
                            <label><input type="checkbox" name="rules[]" value="456" />启动页列表</label>
                            <label><input type="checkbox" name="rules[]" value="457" />启动页添加</label>
                            <label><input type="checkbox" name="rules[]" value="458" />启动页编辑</label>
                            
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="34" />版本管理</label>
                            <label><input type="checkbox" name="rules[]" value="35" />添加版本</label>
                            <label><input type="checkbox" name="rules[]" value="36" />编辑版本</label>
                            <label><input type="checkbox" name="rules[]" value="37" />删除版本</label>

                            <label><input type="checkbox" name="rules[]" value="472" /> 批量强制升级</label>

                            <label><input type="checkbox" name="rules[]" value="38" />版本渠道管理</label>
                            <label><input type="checkbox" name="rules[]" value="39" />添加版本渠道</label>
                            <label><input type="checkbox" name="rules[]" value="40" />编辑版本渠道</label>
                            <label><input type="checkbox" name="rules[]" value="41" />删除版本渠道</label>
                            <label><input type="checkbox" name="rules[]" value="69" />Android历史版本</label>
                            <label><input type="checkbox" name="rules[]" value="70" />IOS历史版本</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="27" />管理员管理</label>
                            <label><input type="checkbox" name="rules[]" value="28" />新增管理员</label>
                            <label><input type="checkbox" name="rules[]" value="29" />编辑管理员</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="17" />登录日志</label>
                            <label><input type="checkbox" name="rules[]" value="64" />编辑器文件上传</label>
                        </p>
                        <p>
                            <!-- <label><input type="checkbox" name="rules[]" value="156" />石头API接口</label>
                            <label><input type="checkbox" name="rules[]" value="157" />石头API调用</label> -->
                            <label><input type="checkbox" name="rules[]" value="154" />百度API接口</label>
                            <label><input type="checkbox" name="rules[]" value="155" />百度API调用</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="159" />自动拉下产品名</label>
                        </p>
                        
                        <p>
                            <label><input type="checkbox" name="rules[]" value="209" />红包发放</label>
                            <label><input type="checkbox" name="rules[]" value="210" />红包数据管理</label>
                            <label><input type="checkbox" name="rules[]" value="211" />红包发放历史记录</label>
                            <label><input type="checkbox" name="rules[]" value="212" />红包每日数据查询</label>
                            <label><input type="checkbox" name="rules[]" value="213" />红包使用人数列表</label>
                            <label><input type="checkbox" name="rules[]" value="214" />红包导出Excel</label>
                            <label><input type="checkbox" name="rules[]" value="328" />红包发放用户列表</label>
                            <label><input type="checkbox" name="rules[]" value="451" />红包批量指定发放</label>
                            <label><input type="checkbox" name="rules[]" value="460" />红包管理 - 红包发放 -添加用户</label>
                        </p>
                        
                        <!-- 2016.04.14 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="215" />钱包管理 - 用户提现（导出【融宝】）</label>
                        	<label><input type="checkbox" name="rules[]" value="216" />产品管理  - 产品管理  - 购买列表（导出【融宝】） 、 产品管理  - 付息列表  - 购买列表（导出【融宝】）</label>
                        </p>
                        
                        <!-- 2016.04.28 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="217" />产品管理  - 日销售额(明细)</label>
                        	<label><input type="checkbox" name="rules[]" value="218" />产品管理  - 日销售额(明细) - 导出</label>
                        </p>
                        <!-- 2016.05.04 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="219" />统计分析  - 用户统计 - 导出只进行注册的用户清单</label>
                        </p>
                        
                        <!-- 2016.05.30 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="220" />钱包管理  - 转入转出(查) - 导出excel</label>
                        </p>
                        
                        <!-- 2016.05.31 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="221" />红包管理  - 红包每日数据查询 - 导出excel</label>
                        </p>
                        
                       <!-- 2016.06.06 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="222" />统计分析  - 支付渠道统计</label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="223" />统计分析  - 支付渠道统计 - 明细</label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="224" />统计分析  - 支付渠道统计 - 导出excel</label>
                        </p>
                        
                        <!-- 2016.06.08 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="225" />统计分析  - 销售额(支付渠道)</label>
                        </p>
                        
                        <!-- 2016.06.17 新增 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="226" />统计分析  - 定期存量(图表)</label>
                        	<label><input type="checkbox" name="rules[]" value="323" />统计分析  - 定期存量(列表)</label>
                        	<label><input type="checkbox" name="rules[]" value="325" />统计分析  - 渠道存量查询</label>
                        	
                        	<label><input type="checkbox" name="rules[]" value="412" />统计分析 - 待还款列表</label>
                        	
                        </p>
                        
                        <!-- 2016.06.27 新增 券包管理 -->
                        <p>
                            <label><input type="checkbox" name="rules[]" value="227" />券包发放</label>
                            <label><input type="checkbox" name="rules[]" value="228" />券包数据管理</label>
                            <label><input type="checkbox" name="rules[]" value="229" />券包使用人数列表</label>
                            <label><input type="checkbox" name="rules[]" value="230" />券包使用人数列表导出Excel</label>
                            <label><input type="checkbox" name="rules[]" value="231" />券包发放历史记录</label>
                            <label><input type="checkbox" name="rules[]" value="232" />券包发放历史记录删除</label>
                            <label><input type="checkbox" name="rules[]" value="233" />券包每日数据查询</label>
                            <label><input type="checkbox" name="rules[]" value="234" />券包每日数据查询导出Excel</label>
                            <label><input type="checkbox" name="rules[]" value="327" />券包发放用户列表</label>
                            <label><input type="checkbox" name="rules[]" value="450" />券包批量指定发放</label>
                        </p>
                        
                        <!-- 2016.07.01 新增 奖励管理 -->
                        <p>
                            <label><input type="checkbox" name="rules[]" value="235" />奖励管理 - 现金券发放</label>
                            <label><input type="checkbox" name="rules[]" value="236" />奖励管理 - 现金券发放记录</label>
                            <label><input type="checkbox" name="rules[]" value="471" />奖励管理 - 现金券发放记录-导出excel</label>

                            <label><input type="checkbox" name="rules[]" value="237" />奖励管理 - 现金券删除</label>
                            <label><input type="checkbox" name="rules[]" value="238" />奖励管理 - 推荐发放记录</label>
                            <label><input type="checkbox" name="rules[]" value="239" />奖励管理 - 推荐发放记录 - 推荐明细</label>
                            
                            <label><input type="checkbox" name="rules[]" value="435" />奖励管理 - 现金券审核</label>
                            <label><input type="checkbox" name="rules[]" value="436" />奖励管理 - 现金券审核 - 通过</label>
                            <label><input type="checkbox" name="rules[]" value="437" />奖励管理 - 现金券审核 - 不通过</label>
                            <label><input type="checkbox" name="rules[]" value="438" />奖励管理 - 现金券审核 - 批量处理</label>
                            <label><input type="checkbox" name="rules[]" value="439" />奖励管理 - 现金券审核 - 导出</label>
                            
                            <label><input type="checkbox" name="rules[]" value="459" />奖励管理 - 现金券发放 -添加用户</label>
                        </p>
                        
                        <!-- 2016.08.02 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="240" />产品分组管理</label>
                            <label><input type="checkbox" name="rules[]" value="241" />添加产品分组</label>
                            <label><input type="checkbox" name="rules[]" value="242" />编辑产品分组</label>
                            <label><input type="checkbox" name="rules[]" value="243" />删除产品分组</label>
                        </p>
                        
                        <!-- 2016.08.16 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="244" />钱包管理 - 每日应付利息</label>
                        </p>
                        
                        <!-- 2016.08.19 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="245" />产品管理 - 每日还本付息</label>
                        	<label><input type="checkbox" name="rules[]" value="246" />产品管理 - 预设协议利率</label>
                        	<label><input type="checkbox" name="rules[]" value="247" />产品管理 - 融资方管理</label>
                        	<label><input type="checkbox" name="rules[]" value="248" />产品管理 - 融资方管理 - 增加(编辑)</label>
                        	<label><input type="checkbox" name="rules[]" value="249" />产品管理 - 融资方管理 - 删除</label>
                        	<label><input type="checkbox" name="rules[]" value="250" />产品管理 - 预设协议利率 - 增加(编辑)</label>
                        </p>
                        
                        <!-- 2016.08.23 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="251" />活动管理 - 活动配置 </label>
                        	<label><input type="checkbox" name="rules[]" value="252" />活动管理 - 活动配置 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="253" />活动管理 - 活动配置 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="254" />活动管理 - 活动配置 - 删除</label>
                        </p>
                        
                        <!-- 2016.10.28 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="255" />广告管理 - 弹窗列表 </label>
                        	<label><input type="checkbox" name="rules[]" value="256" />广告管理 - 弹窗列表 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="257" />广告管理 - 弹窗列表 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="258" />广告管理 - 弹窗列表 - 删除</label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="259" />广告管理 - 悬浮ICON列表 </label>
                        	<label><input type="checkbox" name="rules[]" value="260" />广告管理 - 悬浮ICON - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="261" />广告管理 - 悬浮ICON - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="262" />广告管理 - 悬浮ICON - 删除</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="263" />活动管理 - 返券活动列表 </label>
                        	<label><input type="checkbox" name="rules[]" value="264" />活动管理 - 返券活动  - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="265" />活动管理 - 返券活动  - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="266" />活动管理 - 返券活动  - 删除</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="267" />活动管理 - 活动奖励列表 </label>
                        	<label><input type="checkbox" name="rules[]" value="268" />活动管理 - 活动奖励- 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="269" />活动管理 - 活动奖励 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="270" />活动管理 - 活动奖励 - 删除</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="271" />统计分析 - 总销售额 </label>
                        </p>
                        
                        <!-- 2016.11.18 -->
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="272" />活动管理 - 抽奖活动配置 </label>
                        	<label><input type="checkbox" name="rules[]" value="273" />活动管理 - 抽奖活动配置 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="274" />活动管理 - 抽奖活动配置 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="275" />活动管理 - 抽奖活动配置 - 删除</label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="276" />活动管理 - 抽奖活动 - 条件管理 - 列表</label>
                        	<label><input type="checkbox" name="rules[]" value="277" />活动管理 - 抽奖活动 - 条件管理- 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="278" />活动管理 - 抽奖活动 - 条件管理 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="279" />活动管理 - 抽奖活动 - 条件管理 - 删除</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="280" />活动管理 - 抽奖活动 - 奖励管理 - 列表</label>
                        	<label><input type="checkbox" name="rules[]" value="281" />活动管理 - 抽奖活动 - 奖励管理- 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="282" />活动管理 - 抽奖活动 - 奖励管理 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="283" />活动管理 - 抽奖活动 - 奖励管理 - 删除</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="284" />活动管理 - 抽奖活动日志</label>
                        	<label><input type="checkbox" name="rules[]" value="285" />活动管理 - 抽奖活动日志 - 增加</label>
                        </p>
                        <!-- 2016-12-08 -->
                       	<!--<p>-->
                        	<!--<label><input type="checkbox" name="rules[]" value="286" />指定账户购买记录汇总 年末狂欢活动 12.1 -12.31</label>-->
                        <!--</p>-->
                        
                        
                        <!-- 2016-12-13 -->
                       	<p>
                        	<label><input type="checkbox" name="rules[]" value="287" />每日还本付息 - 导出excel</label>
                        </p>
                        
                        <!-- 2016-12-14 -->
                       	<p>
                        	<label><input type="checkbox" name="rules[]" value="288" />统计分析 - 还款列表</label>
                        	<label><input type="checkbox" name="rules[]" value="289" />统计分析 - 还款列表导出excel</label>
                        	<label><input type="checkbox" name="rules[]" value="290" />统计分析 - 还款列表 产品明细导出excel</label>
                        </p>
                        
                        <!-- 2017-02-20 -->
                       	<p>
                        	<label><input type="checkbox" name="rules[]" value="294" />广告管理 - 首页公告</label>
                        	<label><input type="checkbox" name="rules[]" value="295" />广告管理 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="296" />广告管理 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="297" />广告管理 - 删除</label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="298" />活动管理 - 签到奖励配置</label>
                        	<label><input type="checkbox" name="rules[]" value="299" />活动管理 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="300" />活动管理 - 编辑</label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="301" />活动管理 - 会员积分兑换</label>
                        	<label><input type="checkbox" name="rules[]" value="302" />会员积分兑换 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="303" />会员积分兑换 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="304" />会员积分兑换 - 删除</label>
                        	<label><input type="checkbox" name="rules[]" value="305" />会员积分兑换 - 兑换明细 </label>
                        </p>
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="306" />活动管理 - 会员特权领取奖励</label>
                        	<label><input type="checkbox" name="rules[]" value="307" />会员特权领取奖励 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="308" />会员特权领取奖励 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="309" />会员特权领取奖励 - 删除</label>
                        	<label><input type="checkbox" name="rules[]" value="310" />会员特权领取奖励 - 领取明细 </label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="311" />活动管理 - 会员vip等级配置</label>
                        	<label><input type="checkbox" name="rules[]" value="312" />会员vip等级配置 - 增加</label>
                        	<label><input type="checkbox" name="rules[]" value="313" />会员vip等级配置 - 编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="314" />会员vip等级配置 - 删除</label>
                        	<label><input type="checkbox" name="rules[]" value="315" />会员vip等级配置 - 查看任务列表 </label>
                        	<label><input type="checkbox" name="rules[]" value="316" />会员vip等级配置 - VIP任务 添加 </label>
                        	<label><input type="checkbox" name="rules[]" value="317" />会员vip等级配置 - VIP任务 编辑 </label>
                        	<label><input type="checkbox" name="rules[]" value="318" />会员vip等级配置 - VIP任务 删除 </label>                         	                       	
                        	<label><input type="checkbox" name="rules[]" value="319" />任务类型配置 - 列表 </label>
                        	<label><input type="checkbox" name="rules[]" value="320" />任务类型配置 - 添加 </label>
                        	<label><input type="checkbox" name="rules[]" value="321" />任务类型配置 - 编辑 </label>
                        	<label><input type="checkbox" name="rules[]" value="322" />任务类型配置 - 删除 </label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="345" />分享管理-列表</label>
                        	<label><input type="checkbox" name="rules[]" value="346" />分享管理-添加</label>
                        	<label><input type="checkbox" name="rules[]" value="347" />分享管理-编辑</label>
                        	<label><input type="checkbox" name="rules[]" value="348" />分享管理-删除</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="349" />产品管理-产品管理-购买列表-导出连连</label>
                        </p>

                        <p>
                            <label><input type="checkbox" name="rules[]" value="350" />用户分层-月统计</label>
                            <label><input type="checkbox" name="rules[]" value="351" />用户分层-月统计-导出</label>
                            <label><input type="checkbox" name="rules[]" value="352" />用户分层-日统计</label>
                            <label><input type="checkbox" name="rules[]" value="353" />用户分层-日统计-下载</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="355" />会员体系 - VIP配置</label>
                            <label><input type="checkbox" name="rules[]" value="405" />会员体系 - VIP积分加倍</label>
                            <label><input type="checkbox" name="rules[]" value="383" />会员体系 - VIP积分加倍-修改</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="356" />会员体系 - Banner管理</label>
                            <label><input type="checkbox" name="rules[]" value="362" />会员体系 - Banner管理-新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="357" />会员体系 - Banner管理-新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="359" />会员体系 - Banner管理-修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="360" />会员体系 - Banner管理-修改权限</label>
                            <label><input type="checkbox" name="rules[]" value="361" />会员体系 - Banner管理-排序</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="363" />会员体系 - 生日礼包</label>
                            <label><input type="checkbox" name="rules[]" value="364" />会员体系 - 生日礼包 - 新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="365" />会员体系 - 生日礼包 - 新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="366" />会员体系 - 生日礼包 - 修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="367" />会员体系 - 生日礼包 - 修改权限</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="368" />会员体系 - 商品管理</label>
                            <label><input type="checkbox" name="rules[]" value="370" />会员体系 - 商品管理 - 新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="376" />会员体系 - 商品管理 - 新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="371" />会员体系 - 商品管理 - 修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="372" />会员体系 - 商品管理 - 修改权限</label>
                            <label><input type="checkbox" name="rules[]" value="373" />会员体系 - 商品管理 - 使用列表</label>
                            <label><input type="checkbox" name="rules[]" value="374" />会员体系 - 商品管理 - 单条查询权限</label>
                            <label><input type="checkbox" name="rules[]" value="375" />会员体系 - 商品管理 - 列表查询权限</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="377" />会员体系 - 每月福利</label>
                            <label><input type="checkbox" name="rules[]" value="378" />会员体系 - 每月福利 - 新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="379" />会员体系 - 每月福利 - 新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="380" />会员体系 - 每月福利 - 修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="381" />会员体系 - 每月福利 - 修改权限</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="385" />会员体系 - 票票商城</label>
                            <label><input type="checkbox" name="rules[]" value="386" />会员体系 - 票票商城 - 新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="387" />会员体系 - 票票商城 - 新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="388" />会员体系 - 票票商城 - 修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="389" />会员体系 - 票票商城 - 修改权限</label>
                            <label><input type="checkbox" name="rules[]" value="390" />会员体系 - 票票商城 - 排序</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="391" />会员体系 - 积分任务</label>
                            <label><input type="checkbox" name="rules[]" value="392" />会员体系 - 积分任务 - 新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="393" />会员体系 - 积分任务 - 新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="395" />会员体系 - 积分任务 - 修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="396" />会员体系 - 积分任务 - 修改权限</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="397" />会员体系 - 特权管理</label>
                            <label><input type="checkbox" name="rules[]" value="398" />会员体系 - 特权管理 - 新增页面</label>
                            <label><input type="checkbox" name="rules[]" value="399" />会员体系 - 特权管理 - 新增权限</label>
                            <label><input type="checkbox" name="rules[]" value="400" />会员体系 - 特权管理 - 修改页面</label>
                            <label><input type="checkbox" name="rules[]" value="401" />会员体系 - 特权管理 - 修改权限</label>
                            <label><input type="checkbox" name="rules[]" value="402" />会员体系 - 特权管理 - 排序</label>
                        </p>
                        <p>
                            <label><input type="checkbox" name="rules[]" value="403" />会员体系 - 删除权限</label>
                            <label><input type="checkbox" name="rules[]" value="404" />会员体系 - 上下架权限</label>
                        </p>
                        
                        <p>
                            <label><input type="checkbox" name="rules[]" value="408" />存管账户 - 充值提现记录</label>
                            <label><input type="checkbox" name="rules[]" value="409" />存管账户 - 充值提现导出</label>
                        	<label><input type="checkbox" name="rules[]" value="420" />存管账户 - 账户总览</label>                        	
                            <label><input type="checkbox" name="rules[]" value="421" />存管账户 - 充值主页</label>
                            <label><input type="checkbox" name="rules[]" value="422" />存管账户 - 充值主页-提交</label>
                            <label><input type="checkbox" name="rules[]" value="423" />存管账户 - 转账主页</label>
                            <label><input type="checkbox" name="rules[]" value="424" />存管账户 - 转账主页-提交</label>  
                            <label><input type="checkbox" name="rules[]" value="425" />存管账户 -(自有子账户)查询</label>
                            <label><input type="checkbox" name="rules[]" value="452" />存管账户-子账户记录</label>
                            <label><input type="checkbox" name="rules[]" value="426" />存管查询页</label>
                            <label><input type="checkbox" name="rules[]" value="427" />存管查询页 - 查询</label>
                            <label><input type="checkbox" name="rules[]" value="433" />存管查询页 - 接口类型</label>
                            <label><input type="checkbox" name="rules[]" value="434" />存管查询页 - 接口列表</label>                      
                            <label><input type="checkbox" name="rules[]" value="461" />存管账户 -(抵用金子账户 )查询</label>
                            <label><input type="checkbox" name="rules[]" value="462" />存管账户 -(奖励金子账户)查询</label>
                            <label><input type="checkbox" name="rules[]" value="463" />存管账户 -(子账户)查询</label>                            
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="428" />存管对账- 充值资金对账页</label>
                        	<label><input type="checkbox" name="rules[]" value="429" />存管对账- 充值资金对-查询</label>
                            <label><input type="checkbox" name="rules[]" value="430" />存管对账 - 提现资金对账</label>
                            <label><input type="checkbox" name="rules[]" value="431" />存管对账 - 提现资金对账-查询</label>
                        </p>
                        
                        <p>
                        	<label><input type="checkbox" name="rules[]" value="441" />浮动加息管理-产品统计</label>
                        	<label><input type="checkbox" name="rules[]" value="449" />浮动加息管理-产品统计-购买明细</label>
                        	<label><input type="checkbox" name="rules[]" value="442" />浮动加息管理-产品统计-购买明细-导出</label>
                            <label><input type="checkbox" name="rules[]" value="443" />浮动加息管理-每日数据</label>
                            <label><input type="checkbox" name="rules[]" value="444" />浮动加息管理-每日数据-导出</label>
                            <label><input type="checkbox" name="rules[]" value="445" />浮动加息管理-月月加薪列表</label>
                            <label><input type="checkbox" name="rules[]" value="446" />浮动加息管理-月月加薪-增加</label>
                            <label><input type="checkbox" name="rules[]" value="447" />浮动加息管理-月月加薪-编辑</label>
                            <label><input type="checkbox" name="rules[]" value="448" />浮动加息管理-月月加薪-删除</label>
                        </p>
                                                
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="center">
                        <div style="width:85%;margin:5px">
                            <input type="submit" value="保 存"  class="button small">
                            <input type="reset" class="button small" onclick="javascript:if(!confirm('确认重置吗?')) return false;resetEditor()" value="重 置" >
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<script>
    var _adding = false;
    var _layerIndex = 0;
    var _rules = "<?php echo ($detail["rules"]); ?>";
    var _ruleArr;
    $(document).ready(function(){
        _ruleArr = _rules.split(',');
        $.each($("input[name='rules[]']"), function(i, n){
            if(_ruleArr.indexOf($(n).val()) > -1){
                $(n).attr('checked', 'checked');
                $(n).parent().css('color','#409DFE');
            }
        });
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                if(_adding) return;
                _adding = true;
                _layerIndex = layer.load('数据提交中...');
                $.ajax({
                    url: ROOT + '/auth/group_edit',
                    data: $('#frmMain').serialize(),
                    type: "POST",
                    cache: false,
                    success: function(msg) {
                        layer.close(_layerIndex);
                        if(msg.status){
                            layer.alert('编辑成功~!', -1, function(){
                                window.location.href = msg.info;
                            });
                        }else{
                            layer.alert(msg.info);
                        }
                        _adding = false;
                    }
                });
                return false;
            }
        });
    });
</script>