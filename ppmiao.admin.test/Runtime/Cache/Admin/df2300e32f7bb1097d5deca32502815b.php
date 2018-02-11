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
<div id="main" class="main" >
    <div class="content">
        <div class="title">产品详细 [ <a href="<?php echo C('ADMIN_ROOT');?>/Project/index/p/<?php echo ($params["page"]); ?>/status/<?php echo ($params["status"]); if(!empty($params["search"])): ?>/s/<?php echo ($params["search"]); endif; ?>">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" charset="utf-8" src="/Public/admin/auth/js/editor/kindeditor-all.js"></script>
        <form id="frmMain" method='post' action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo ($detail["id"]); ?>" />
            <input type="hidden" name="p" value="<?php echo ($params["page"]); ?>" />
            <input type="hidden" name="s" value="<?php echo ($params["search"]); ?>" />
            <input type="hidden" name="_status" value="<?php echo ($params["status"]); ?>" />
            <table cellpadding=3 cellspacing=3 >
                
                <tr>
                    <td class="tRight" width="150px">产品类型：</td>
                    <td class="tLeft">
                       		<span>票票喵</span> 
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">期数：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" id="stage" name="stage" datatype="n" value="<?php echo ($detail["stage"]); ?>" onchange="changeStage(this)" disabled /></td>
                </tr>
                <tr>
                    <td class="tRight">标题：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="title" name="title" value="<?php echo ($detail["title"]); ?>" readonly></td>
                </tr>
                <tr>
                    <td class="tRight">购买基数：</td>
                    <td class="tLeft"><input type="text" datatype="n" id="basecount" name="basecount" value="<?php echo ($detail["buy_base"]); ?>" readonly />&nbsp;<span style="color:#409DFE;">注：前端显示该项目购买数量为此基数+用户实际购买数之和</span></td>
                </tr>
                
                
                <tr>
                    <td class="tRight">显示分组：</td>
                    <td class="tLeft">
                        <select name="show_region" id="show_region" disabled>
                            <option value="0" <?php if(($detail["show_region"]) == "0"): ?>selected<?php endif; ?>>定期专区</option>
                            <option value="1" <?php if(($detail["show_region"]) == "1"): ?>selected<?php endif; ?>>活动专区</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">特殊标签：</td>
                    <td class="tLeft">
                        <!--<select name="new_preferential" disabled>-->
                            <!--<option value="0" <?php if(($detail["new_preferential"]) == "0"): ?>selected<?php endif; ?>>普通标</option> -->
                            <!--<option value="1" <?php if(($detail["new_preferential"]) == "1"): ?>selected<?php endif; ?>>新人特惠</option> -->
                            <!--<option value="2" <?php if(($detail["new_preferential"]) == "2"): ?>selected<?php endif; ?>>爆款</option> -->
                            <!--<option value="3" <?php if(($detail["new_preferential"]) == "3"): ?>selected<?php endif; ?>>HOT</option>-->
                            <!--<option value="6" <?php if(($detail["new_preferential"]) == "6"): ?>selected<?php endif; ?>>活动</option> -->
                            <!--<option value="8" <?php if(($detail["new_preferential"]) == "8"): ?>selected<?php endif; ?>>私人专享</option> -->
                            <!--<option value="9" <?php if(($detail["new_preferential"]) == "9"): ?>selected<?php endif; ?>>月月加薪标</option>-->
                        <!--</select>-->
                        <select name="new_preferential" disabled>
                            <?php if(is_array($special_tags)): foreach($special_tags as $key=>$t): ?><option value="<?php echo ($t["id"]); ?>" <?php if(($detail["new_preferential"]) == $t["id"]): ?>selected<?php endif; ?>  ><?php echo ($t["tag_title"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">融资方：</td>
                    <td class="tLeft"><input type="text" id="financing" name="financing" value="<?php echo ($detail["financing"]); ?>" readonly /></td>
                </tr>
                
                
                <tr>
                    <td class="tRight">自定义标签：</td>
                    <td class="tLeft"><input type="text" id="custom_label" name="custom_label" value="<?php echo ($detail["custom_label"]); ?>" readonly/></td>
                </tr>
                
                <tr>
                    <td class="tRight">自定义权重值：</td>
                    <td class="tLeft">
                    	<input type="text" datatype="n" id="custom_weight" name="custom_weight" value="<?php echo ($detail["custom_weight"]); ?>" readonly/>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">权重值：</td>
                    <td class="tLeft">
                    	<?php echo ($detail["weight"]); ?>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">产品期限：</td>
					 <td class="tLeft">
                    	<?php switch($detail["duration_type"]): case "1": ?>1周新手标<?php break;?> 
                        		<?php case "2": ?>1月标<?php break;?> 
                        		<?php case "3": ?>2月标<?php break;?> 
                        		<?php case "4": ?>3月标<?php break;?> 
                        		<?php case "5": ?>6月标<?php break; endswitch;?>
                    </td>
                </tr>
                
                
                <tr>
                    <td class="tRight">合同编号：</td>
                    <td class="tLeft"><input type="text" id="contract_no" name="contract_no" value="<?php echo ($detail["contract_no"]); ?>" readonly /></td>
                </tr>

                <tr>
                    <td class="tRight">承兑银行：</td>
                    <td class="tLeft"><input type="text" id="accepting_bank" name="accepting_bank" value="<?php echo ($detail["accepting_bank"]); ?>" readonly /></td>
                </tr>
                <tr>
                    <td class="tRight">验票托管：</td>
                    <td class="tLeft">                    	
                    	<textarea name="ticket_checking" id="ticket_checking" style="width:600px;height:70px;"><?php echo ($detail["ticket_checking"]); ?></textarea>
                    </td>
                </tr>
                 
                <tr>
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                <tr>
                    <td class="tRight tTop">可购买次数：</td>
                    <td class="tLeft"><input type="text" id="buy_times" name="buy_times" datatype="n" value="<?php echo ((isset($detail["buy_times"]) && ($detail["buy_times"] !== ""))?($detail["buy_times"]):0); ?>" readonly />&nbsp;<span style="color:#409DFE;">注：0为不限制</span></td>
                </tr>
                <tr>
                    <td class="tRight tTop">起购封顶金额(元)：</td>
                    <td class="tLeft">
                        <input type="text" id="money_min" name="money_min" datatype="semoney" value="<?php echo ($detail["money_min"]); ?>" nullmsg="请填写信息！" errormsg="必须为数字！" readonly /> -
                        <input type="text" id="money_max" name="money_max" datatype="semoney" value="<?php echo ($detail["money_max"]); ?>" nullmsg="请填写信息！" errormsg="必须为数字！" readonly />&nbsp;<span style="color:#409DFE;">注：0为不限制</span>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">购买方式：</td>
                    <td class="tLeft">
                        <label>
                            <input type="radio" disabled name="money_type" value="0" <?php if(($detail["money_type"]) == "0"): ?>checked<?php endif; ?> />起购金额的倍数购买
                        </label>
                        <label>
                            <input type="radio" disabled name="money_type" value="1" <?php if(($detail["money_type"]) == "1"): ?>checked<?php endif; ?> />大于起购金额且是100的倍数购买
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="tRight tTop">起止时间：</td>
                    <td class="tLeft">
                        <input type="text" class="bLeftRequire laydate-icon" name="start_time" id="start_time" value="<?php echo (date('Y-m-d H:i',strtotime($detail["start_time"]))); ?>" readonly /> -
                        <input type="text" class="bLeftRequire laydate-icon" name="end_time" id="end_time" value="<?php echo (date('Y-m-d H:i',strtotime($detail["end_time"]))); ?>" readonly />
                    <td>
                </tr>
                <tr>
                    <td class="tRight">计息方式：</td>
                    <td class="tLeft">
                        <select id="count_interest_type" name="count_interest_type" class="bLeftRequire" datatype="*" disabled>
                            <option value="">--请选择计息方式--</option>
                            <option value="1" <?php if(($detail["count_interest_type"]) == "1"): ?>selected<?php endif; ?>>T+0</option>
                            <option value="2" <?php if(($detail["count_interest_type"]) == "2"): ?>selected<?php endif; ?>>T+1</option>
                            <option value="3" <?php if(($detail["count_interest_type"]) == "3"): ?>selected<?php endif; ?>>T+2</option>
                            <option value="4" <?php if(($detail["count_interest_type"]) == "4"): ?>selected<?php endif; ?>>T+3</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight tTop">还款方式：</td>
                    <td class="tLeft">
                        <select id="repayment_type" name="repayment_type" class="bLeftRequire" datatype="*" onchange="changeRepayment(this)" disabled>
                            <option value="">--请选择还款方式--</option>
                            <option value="1" <?php if(($detail["repayment_type"]) == "1"): ?>selected<?php endif; ?>>一次性还本付息</option>
                            <option value="2" <?php if(($detail["repayment_type"]) == "2"): ?>selected<?php endif; ?>>按月付息，到期还本</option>
                        </select>
                    </td>
                </tr>
                <tr id="trRepayment" style="display:none;">
                    <td class="tRight tTop">每月固定还款日：</td>
                    <td class="tLeft"><input type="text" id="repayment_day" name="repayment_day" readonly datatype="n" value="<?php echo ((isset($detail["repayment_day"]) && ($detail["repayment_day"] !== ""))?($detail["repayment_day"]):20); ?>" class="bLeftRequire" disabled /><td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                <tr>
                    <td class="tRight">借款金额(元)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="amount" name="amount" readonly value="<?php echo (int)$detail['amount']; ?>" datatype="n" onchange="count_earn()" disabled><span id="formatAmount" style="color:green"></span></td>
                </tr>
                <tr>
                    <td class="tRight">合同上的利息(%)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="contract_interest" value="<?php echo ($detail["contract_interest"]); ?>" readonly datatype="f" errormsg="请输入数字" name="contract_interest" onchange="count_earn()" disabled></td>
                </tr>
                <tr>
                    <td class="tRight">给用户的利息(%)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="user_interest" value="<?php echo ($detail["user_interest"]); ?>" readonly datatype="f" errormsg="请输入数字" name="user_interest" onchange="count_earn()" disabled></td>
                </tr>
                
                
                <tr>
                    <td class="tRight">梯度加息：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="user_interest" value="<?php echo ($detail["monthly_increase_group"]); ?>" disabled></td>
                </tr>
                
                <tr>
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                <tr>
                    <td class="tRight tTop">投资方向：</td>
                    <td class="tLeft">
                        <input type="text" id="invest_direction_title" name="invest_direction_title" value="<?php echo ($detail["invest_direction_title"]); ?>" style="width:700px;margin-bottom:5px;" readonly />
                        <textarea id="invest_direction" name="invest_direction" style="width:700px;height:300px;" readonly><?php echo ($detail["invest_direction"]); ?></textarea>
                    <td>
                </tr>
                <tr>
                    <td class="tRight tTop">还款来源：</td>
                    <td class="tLeft">
                        <input type="text" id="repayment_source_title" name="repayment_source_title" value="<?php echo ($detail["repayment_source_title"]); ?>" readonly style="width:700px;margin-bottom:5px;" />
                        <textarea id="repayment_source" name="repayment_source" style="width:700px;height:300px;" readonly><?php echo ($detail["repayment_source"]); ?></textarea>
                    <td>
                </tr>
            </table>
        </form>
    </div>
</div>
<script>
    $(document).ready(function(){
        KindEditor.ready(function(K){
            _investDirectionEditor = K.create('#invest_direction', {
                uploadJson : ROOT + '/Common/uploadImageForEditor',
                fileManagerJson : ROOT + '/Common/fileManagerForEditor',
                allowFileManager : true,
                urlType : 'domain',
                items : [
                    'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'cut', 'copy', 'paste',
                    'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                    'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                    'superscript', 'clearhtml', 'selectall', '|', 'fullscreen', '/',
                    'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                    'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image',
                    'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                    'anchor', 'link', 'unlink', '|', 'about'
                ],
                afterBlur: function(){this.sync();}
            });
            _repaymentSourceEditor = K.create('#repayment_source', {
                uploadJson : ROOT + '/Common/uploadImageForEditor',
                fileManagerJson : ROOT + '/Common/fileManagerForEditor',
                allowFileManager : true,
                urlType : 'domain',
                items : [
                    'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'cut', 'copy', 'paste',
                    'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                    'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                    'superscript', 'clearhtml', 'selectall', '|', 'fullscreen', '/',
                    'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                    'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image',
                    'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                    'anchor', 'link', 'unlink', '|', 'about'
                ],
                afterBlur: function(){this.sync();}
            });
        });
    });
</script>