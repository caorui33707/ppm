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
        <div class="title">编辑产品 [ <a href="<?php echo C('ADMIN_ROOT');?>/Project/index/p/<?php echo ($params["page"]); ?>/status/<?php echo ($params["status"]); if(!empty($params["search"])): ?>/s/<?php echo ($params["search"]); endif; ?>">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" charset="utf-8" src="/Public/admin/auth/js/editor/kindeditor-all.js"></script>
        <form id="frmMain" method='post' action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo ($detail["id"]); ?>" />
            <input type="hidden" name="p" value="<?php echo ($params["page"]); ?>" />
            <input type="hidden" name="s" value="<?php echo ($params["search"]); ?>" />
            <input type="hidden" name="_status" value="<?php echo ($params["status"]); ?>" />
            <input type="hidden" name="days" value="<?php echo ($detail["duration"]); ?>" />
            <table cellpadding=3 cellspacing=3 >
            	
                 
                <tr>
                    <td class="tRight" width="150px">产品类型：</td>
                    <td class="tLeft">
                       		<span>票票喵</span> 
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">期数：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" id="stage" name="stage" datatype="n" value="<?php echo ($detail["stage"]); ?>" onchange="changeStage(this)" <?php switch($detail["status"]): case "1": case "6": break; default: ?>disabled<?php endswitch;?> /></td>
                </tr>
                <tr>
                    <td class="tRight">标题：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="title" name="title" value="<?php echo ($detail["title"]); ?>"></td>
                </tr>
                             
                 
                <tr>
                    <td class="tRight">显示分组：</td>
                    <td class="tLeft">
                        <select name="show_region" id="show_region">
                            <option value="0" <?php if(($detail["show_region"]) == "0"): ?>selected<?php endif; ?>>定期专区</option>
                            <option value="1" <?php if(($detail["show_region"]) == "1"): ?>selected<?php endif; ?>>活动专区</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="tRight">特殊标签：</td>
                    <?php if((!in_array($detail['status'], [1,6])) ): ?><input type="hidden" name="new_preferential" value="<?php echo ($detail["new_preferential"]); ?>"/><?php endif; ?>
                    <td class="tLeft">
                        <select name="new_preferential" id="new_preferential" <?php if((!in_array($detail['status'], [1,6])) ): ?>disabled<?php endif; ?> onchange="changenewPreferential()" >
                            <!--<option value="0" <?php if(($detail["new_preferential"]) == "0"): ?>selected<?php endif; ?>>普通标</option>-->
                            <!--<option value="1" <?php if(($detail["new_preferential"]) == "1"): ?>selected<?php endif; ?>>新人特惠</option>-->
                            <!--<option value="2" <?php if(($detail["new_preferential"]) == "2"): ?>selected<?php endif; ?>>爆款</option>-->
                            <!--<option value="3" <?php if(($detail["new_preferential"]) == "3"): ?>selected<?php endif; ?>>HOT</option>-->
                            <!--<option value="6" <?php if(($detail["new_preferential"]) == "6"): ?>selected<?php endif; ?>>活动</option>-->
                            <!--<option value="8" <?php if(($detail["new_preferential"]) == "8"): ?>selected<?php endif; ?>>私人专享</option>-->
                            <!--<option value="9" <?php if(($detail["new_preferential"]) == "9"): ?>selected<?php endif; ?>>月月加薪标</option>-->

                            <?php if(is_array($special_tags)): foreach($special_tags as $key=>$t): ?><option value="<?php echo ($t["id"]); ?>" <?php if(($detail["new_preferential"]) == $t["id"]): ?>selected<?php endif; ?>  ><?php echo ($t["tag_title"]); ?></option><?php endforeach; endif; ?>

                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="tRight">等级限制：</td>
                    <td class="tLeft">
                        <select name="vip_id" id="vip_id" onchange="changeVipId()" >
                            <?php if(is_array($vip)): foreach($vip as $key=>$v): $vid = $v['id']; ?>
                                <option value="<?php echo ($vid); ?>" data-value="<?php echo ($v["name"]); ?>"  <?php if(($detail["vip_id"]) == $vid): ?>selected<?php endif; ?> ><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="tRight">可使用券包：</td>
                    <td class="tLeft">
                        <input type="checkbox" id="use_redenvelope" name="use_redenvelope" value="1" <?php if(($detail["use_redenvelope"]) == "1"): ?>checked<?php endif; ?> > 红包
                        <input type="checkbox" id="use_interest_coupon" name="use_interest_coupon" value="1" <?php if(($detail["use_interest_coupon"]) == "1"): ?>checked<?php endif; ?> > 加息券
                    </td>
                </tr>


                <tr>
                    <td class="tRight">产品公告标签：</td>
                    <td class="tLeft">
                        <input type="checkbox" id="is_notice_tag" name="is_notice_tag" <?php if($detail["tag_id"] > 9): ?>value="0" <?php else: ?> value="1"  checked<?php endif; ?>  > 与特殊标签一致
                    </td>
                </tr>
                <tr id="notice_tag" <?php if($detail["tag_id"] <= 9): ?>style="display: none"<?php endif; ?> >
                    <td class="tRight"></td>
                    <td class="tLeft">
                        <select name="tag_id">
                            <?php if(is_array($notice_tags)): foreach($notice_tags as $key=>$tag): ?><option value="<?php echo ($tag["id"]); ?>" <?php if(($detail["tag_id"]) == $tag["id"]): ?>selected<?php endif; ?> ><?php echo ($tag["tag_title"]); ?></option><?php endforeach; endif; ?>
                        </select>

                    </td>
                </tr>
                 
                <!-- 20160802 add -->
                <tr>
                    <td class="tRight">产品分组：</td>
                    <td class="tLeft">
                        <select name="project_group_id" id="project_group_id">
                            <option value="0">无</option>
                            <?php if(is_array($project_group_list)): foreach($project_group_list as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($detail["project_group_id"]) == $item['id']): ?>selected<?php endif; ?> ><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                        <span style="color:#409DFE;">&nbsp;&nbsp;&nbsp; 注:选 ‘无’的选项，不会关联自动上标功能</span>
                    </td>
                </tr>
                
                             
                <tr>
                    <td class="tRight">融资方：</td>
                    <td class="tLeft">                    	
                    	<select name="financing" id="financing">                            
                            <?php if(is_array($financing_list)): foreach($financing_list as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($detail["fid"]) == $item['id']): ?>selected<?php endif; ?> ><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">自定义标签：</td>
                    <td class="tLeft">
                    	<input type="text" id="custom_label" name="custom_label" maxlength="5" value="<?php echo ($detail["custom_label"]); ?>" />
                    	<span style="color:#409DFE;">&nbsp;&nbsp;&nbsp; 五个字以内</span>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">自定义权重值：</td>
                    <td class="tLeft">
                    	<input type="text" datatype="n" id="custom_weight" name="custom_weight" value="<?php echo ($detail["custom_weight"]); ?>" />
                    	<span style="color:#409DFE;">&nbsp;&nbsp;&nbsp; 填写纯数字</span>
                    </td>
                    
                </tr>
                
                <tr>
                    <td class="tRight">产品期限：</td>
                    <td class="tLeft">
                    	<select name="duration_type" id="duration_type">
                            <option value="1" <?php if(($detail["duration_type"]) == "1"): ?>selected<?php endif; ?>>1周标</option>
                            <option value="2" <?php if(($detail["duration_type"]) == "2"): ?>selected<?php endif; ?>>1月标</option>
                            <option value="3" <?php if(($detail["duration_type"]) == "3"): ?>selected<?php endif; ?>>2月标</option>
                            <option value="4" <?php if(($detail["duration_type"]) == "4"): ?>selected<?php endif; ?>>3月标</option>
                            <option value="5" <?php if(($detail["duration_type"]) == "5"): ?>selected<?php endif; ?>>6月标</option>
                        </select>
					</td>
                </tr>
                
                <tr>
                    <td class="tRight">合同编号：</td>
                    <td class="tLeft"><input type="text" id="contract_no" name="contract_no" value="<?php echo ($detail["contract_no"]); ?>" /></td>
                </tr>
                
                <!--<tr>
                    <td class="tRight">承兑银行：</td>
                    <td class="tLeft"><input type="text" id="accepting_bank" name="accepting_bank" value="<?php echo ($detail["accepting_bank"]); ?>" /></td>
                </tr>-->
                <tr>
                    <td class="tRight">验票托管：</td>
                    <td class="tLeft">                    	
                    	<textarea name="ticket_checking" id="ticket_checking" style="width:600px;height:70px;"><?php echo ($detail["ticket_checking"]); ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                <!-- 
                <tr>
                    <td class="tRight">成标方式：</td>
                    <td class="tLeft">
                        <label>
                            <input type="radio" name="establish_type" value="1" <?php if(($detail["establish_type"]) == "1"): ?>checked<?php endif; ?>/>满额成标
                        </label>
                        <label>
                            <input type="radio" name="establish_type" value="2" <?php if(($detail["establish_type"]) == "2"): ?>checked<?php endif; ?> />定时成标
                        </label>
                        <label>
                            <input type="radio" name="establish_type" value="3" <?php if(($detail["establish_type"]) == "3"): ?>checked<?php endif; ?> />定额成标
                        </label>
                        
                        <span id="establish_type_2" style="display:none" >
                        	选择日期：<input type="text" class="bLeftRequire laydate-icon" name="establish_time" id="establish_time" value="<?php echo ($detail["establish_time"]); ?>" readonly />
                        </span>
                        
                        <span id="establish_type_3" style="display:none" >
							设置剩余金额：<input type="text" style="width:80px;" name="establish_amt" value="<?php echo ($detail["establish_amt"]); ?>">  &nbsp;&nbsp;单位 ‘元’
                        </span>
                        
                    </td>
                </tr>
                 -->
                <tr>
                    <td class="tRight tTop">可购买次数：</td>
                    <td class="tLeft"><input type="text" id="buy_times" name="buy_times" datatype="n" value="<?php echo ((isset($detail["buy_times"]) && ($detail["buy_times"] !== ""))?($detail["buy_times"]):0); ?>" />&nbsp;<span style="color:#409DFE;">注：0为不限制</span></td>
                </tr>
                <tr>
                    <td class="tRight tTop">起购封顶金额(元)：</td>
                    <td class="tLeft">
                        <input type="text" id="money_min" name="money_min" datatype="semoney" value="<?php echo ($detail["money_min"]); ?>" nullmsg="请填写信息！" errormsg="必须为数字！" <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?> /> -
                        <input type="text" id="money_max" name="money_max" datatype="semoney" value="<?php echo ($detail["money_max"]); ?>" nullmsg="请填写信息！" errormsg="必须为数字！" <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?> />&nbsp;<span style="color:#409DFE;">注：0为不限制</span>
                    </td>
                </tr>

                <tr>
                    <td class="tRight">购买方式：</td>
                    <td class="tLeft">
                        <label>
                            <input type="radio" name="money_type" value="0" <?php if(($detail["money_type"]) == "0"): ?>checked<?php endif; ?> />起购金额的倍数购买
                        </label>
                        <label>
                            <input type="radio" name="money_type" value="1" <?php if(($detail["money_type"]) == "1"): ?>checked<?php endif; ?> />大于起购金额且是100的倍数购买
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="tRight tTop">起止时间：</td>
                    <td class="tLeft">
                        <input type="text" class="bLeftRequire laydate-icon" <?php if(($detail["status"] == 1) OR ($detail["status"] == 6) ): ?>name="start_time"<?php endif; ?>  id="start_time" value="<?php echo (date('Y-m-d H:i',strtotime($detail["start_time"]))); ?>" readonly <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?> />
                        
                        <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?><input type="hidden" value="<?php echo (date('Y-m-d H:i',strtotime($detail["start_time"]))); ?>" name="start_time"><?php endif; ?>
                        
                        <input type="text" class="bLeftRequire laydate-icon" <?php if(($detail["status"] == 1) OR ($detail["status"] == 6) ): ?>name="end_time"<?php endif; ?> id="end_time" value="<?php echo (date('Y-m-d H:i',strtotime($detail["end_time"]))); ?>" readonly <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?> />
                        
                        <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?><input type="hidden" value="<?php echo (date('Y-m-d H:i',strtotime($detail["end_time"]))); ?>" name="end_time"><?php endif; ?>
                        
                    <td>
                </tr>
                <tr>
                    <td class="tRight tTop">借款天数：</td>
                    <td class="tLeft"><span id="duration">0 天</span><td>
                </tr>
                <tr>
                    <td class="tRight">计息方式：</td>
                    <td class="tLeft">
                        <select id="count_interest_type" name="count_interest_type" class="bLeftRequire" datatype="*">  <!--disabled="disabled"-->
                            <option value="">--请选择计息方式--</option>
                            <!--<option value="1" <?php if(($detail["count_interest_type"]) == "1"): ?>selected<?php endif; ?>>T+0</option>-->
                            <option value="2" <?php if(($detail["count_interest_type"]) == "2"): ?>selected<?php endif; ?>>T+1</option>
                            <!--<option value="3" <?php if(($detail["count_interest_type"]) == "3"): ?>selected<?php endif; ?>>T+2</option>-->
                            <!--<option value="4" <?php if(($detail["count_interest_type"]) == "4"): ?>selected<?php endif; ?>>T+3</option>-->
                            <option value="5" <?php if(($detail["count_interest_type"]) == "5"): ?>selected<?php endif; ?> >满标起息</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight tTop">还款方式：</td>
                    <td class="tLeft">
                        <select id="repayment_type" name="repayment_type" class="bLeftRequire" datatype="*" onchange="changeRepayment(this)" disabled="disabled">
                            <option value="">--请选择还款方式--</option>
                            <option value="1" <?php if(($detail["repayment_type"]) == "1"): ?>selected<?php endif; ?>>一次性还本付息</option>
                            <option value="2" <?php if(($detail["repayment_type"]) == "2"): ?>selected<?php endif; ?>>按月付息，到期还本</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                <tr>
                    <td class="tRight">借款金额(元)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="amount" name="amount" value="<?php echo (int)$detail['amount']; ?>" datatype="n" onchange="count_earn()" <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?>><span id="formatAmount" style="color:green"></span></td>
                </tr>
                <tr>
                    <td class="tRight">合同上的利息(%)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="contract_interest" value="<?php echo ($detail["contract_interest"]); ?>" datatype="f" errormsg="请输入数字" name="contract_interest" onchange="count_earn()" <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?>></td>
                </tr>
                
                <tr>
                    <td class="tRight">给用户的利息(%)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="user_interest" value="<?php echo ($detail["user_interest"]); ?>" datatype="f" errormsg="请输入数字" name="user_interest" onchange="count_earn()" <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?>></td>
                </tr>
                <tr>
                    <td class="tRight">平台补贴的利息(%)：</td>
                    <td class="tLeft" ><input type="text" class="bLeftRequire" id="user_platform_subsidy" value="<?php echo ($detail["user_platform_subsidy"]); ?>" datatype="f" errormsg="请输入数字" name="user_platform_subsidy" onchange="count_earn()" <?php if(($detail["status"] != 1) AND ($detail["status"] != 6) ): ?>disabled<?php endif; ?>>注：此注填值,特殊标签必须选“加息”</td>
                </tr>
                
                
                <tr>
                    <td class="tRight">梯度加息：</td>
                    <td class="tLeft" >
                    	<select id="monthly_increase_group" name="monthly_increase_group" class="bLeftRequire" datatype="*" disabled >
                            <option value="0" >无</option>
                            <?php if(is_array($mConfigList)): foreach($mConfigList as $key=>$item): ?><option value="<?php echo ($item["group_id"]); ?>" <?php if(($detail["monthly_increase_group"]) == $item['group_id']): ?>selected<?php endif; ?> ><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                
                

                <tr class="hidden_descr" >
                    <td class="tRight tTop">上传相关图片操作区：</td>
                    <td class="tLeft">
                        <textarea id="invest_direction" name="upload_image" style="width:700px;height:200px;"><?php echo ($detail["invest_direction"]); ?></textarea>
                    <td>
                </tr>
                <tr class="hidden_descr" >
                    <td class="tRight tTop">相关信息：</td>
                    <td class="tLeft">
                        <p>还款来源</p>
                        <textarea name="invest_direction_descr" style="width:700px;height:50px;"><?php echo ($detail["invest_direction_descr"]); ?></textarea>
                        <p>相关协议(每行一个图片完整链接)</p>
                        <textarea name="invest_direction_image" style="width:700px;height:100px;"><?php echo ($detail["invest_direction_image"]); ?></textarea>
                    </td>
                </tr>
                <tr class="hidden_descr" >
                    <td class="tRight tTop">资金保障：</td>
                    <td class="tLeft">
                        <input type="text" id="repayment_source_title" name="repayment_source_title" value="<?php echo ($detail["repayment_source_title"]); ?>" style="width:700px;margin-bottom:5px;" />
                    <td>
                </tr>
                
                <tr>
                    <td class="tRight">汇票类型：</td>
                    <td class="tLeft" id="draft_type">

                        <!--<label>-->
                        <!--<input type="radio" name="draft_type" value="0" checked />银行承兑汇票-->
                        <!--</label>-->
                        <!--<label>-->
                        <!--<input type="radio" name="draft_type" value="1" />电子银行承兑汇票-->
                        <!--</label>-->
                    </td>
                </tr>
                <tr>
                    <td class="tRight">承兑机构：</td>
                    <td class="tLeft" >
                        <input type="text" name="accepting_bank" value="<?php echo ($detail["accepting_bank"]); ?>" >
                    </td>
                </tr>
                <tr style="display: none">
                    <td class="tRight">担保方：</td>
                    <td class="tLeft">

                    </td>
                </tr>

                <!--<input type="hidden" name="gid" value="0">-->

                <tr>
                    <td class="tRight">担保类型：</td>
                    <td class="tLeft" id="guaranty_type">
                    </td>
                </tr>
                <tr>
                    <td class="tRight">担保机构：</td>
                    <td class="tLeft" >
                        <input type="text" name="guaranty_institution"  value="<?php echo ($detail["guaranty_institution"]); ?>" >
                    </td>
                </tr>
                
                <tr class="hidden_descr">
                    <td class="tRight tTop">汇票信息：</td>
                    <td class="tLeft">
                        <p>银行承兑汇票(每行一个图片完整链接)</p>
                        <textarea name="repayment_source_descr" style="width:700px;height:100px;"><?php echo ($detail["repayment_source_descr"]); ?></textarea>
                        <!-- <p>电子银行承兑汇票(每行一个图片完整链接)</p>
                        <textarea name="repayment_source_image" style="width:700px;height:100px;"><?php echo ($detail["repayment_source_image"]); ?></textarea>
                    	 -->
                    </td>
                </tr>
                <tr class="hidden_dxg">
                    <td colspan="2" style="border-bottom:1px #F0F0FF solid;"></td>
                </tr>
                <tr>
                    <td class="tRight">预售倒计时：</td>
                    <td class="tLeft" >
                        <select id="countdown" name="countdown" onchange="showTime(this.options[this.options.selectedIndex].value)">
                            <option value="0" <?php if(($detail["is_countdown"]) == "0"): ?>selected<?php endif; ?>>关闭</option>
                            <option value="1" <?php if(($detail["is_countdown"]) == "1"): ?>selected<?php endif; ?>>开启</option>
                        </select>
                        <span id="showtime" style="display:<?php if(($detail["is_countdown"]) == "0"): ?>none<?php endif; ?>;">开始前<input type="text" style="width:50px;" name="countdown_show_min" value="<?php echo ($detail["countdown_show_min"]); ?>">分钟在App中显示</span>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">幽灵账户自动购买：</td>
                    <td class="tLeft" >
                        <select id="autobuy" name="autobuy" onchange="autoBuyTime(this.options[this.options.selectedIndex].value)">
                            <option value="0"  <?php if(($detail["is_autobuy"]) == "0"): ?>selected<?php endif; ?>>关闭</option>
                            <option value="1" <?php if(($detail["is_autobuy"]) == "1"): ?>selected<?php endif; ?>>开启</option>
                        </select>
                        <span id="buytime" style="display:<?php if(($detail["is_autobuy"]) == "0"): ?>none<?php endif; ?>;">开始时间<input type="text" class="bLeftRequire laydate-icon" <?php if(($detail["is_autobuy"]) == "1"): ?>value="<?php echo (date('Y-m-d H:i',strtotime($detail["start_auto_buy_time"]))); ?>"<?php endif; ?>  name="auto_buy_min" id="auto_buy_min" readonly /></span>
                    </td>
                </tr>
                <tr id="buyuser" style="display:<?php if(($detail["is_autobuy"]) == "0"): ?>none<?php endif; ?>;">
                    <td class="tRight">幽灵账户选择<input type="button" onclick="checkOrUnCheck()" value="全选/反选">：</td>
                    <td class="tLeft" >
                        <?php if(is_array($users)): $k = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($k % 2 );++$k;?><lable for="user_<?php echo ($user["2"]); ?>">
                                <input  class="input_users" <?php if(in_array(($user["2"]), is_array($auto_buy_users)?$auto_buy_users:explode(',',$auto_buy_users))): ?>checked<?php endif; ?> id="user_<?php echo ($user["2"]); ?>" name="users[]" type="checkbox" value="<?php echo ($user["2"]); ?>"><?php echo ($user["2"]); ?>
                            </lable>
                            &nbsp;<?php endforeach; endif; else: echo "" ;endif; ?>
                    </td>
                </tr>
                               
                <tr>
                    <td></td>
                    <td class="center">
                        <div style="width:85%;margin:5px">
                            
                            <?php if(checkAuth('Admin/project/project_copy') == true): if($detail["status"] == 7): ?><input type="button" value="复制标" style="color:green;" onclick="copy(this, <?php echo ($detail["id"]); ?>)" class="button" ><?php endif; endif; ?>
                            
                            <input type="submit" value="保 存"  class="button small">
                            <input type="reset" class="button small" onclick="javascript:if(!confirm('确认重置吗?')) return false;resetEditor()" value="重 置" >
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php $status_detail = !in_array($detail['status'], [1,6])?1:0; ?>

<script>

    var status_el = "<?php echo ($status_detail); ?>";

    if(status_el>0){
        $('#monthly_increase_group').attr("disabled",true);
        $('#monthly_increase_group').attr('id','monthly_increase');
    }

    $('#is_notice_tag').click(function() {
        if ($(this).val() == 1){
            $('#notice_tag').show();
            $(this).val(0);
        }else{
            $('#notice_tag').hide();
            $(this).val(1);
        }
    });

    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD hh:mm:ss',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: true,
        istoday: true,
        choose: function(datas){
            end.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas //将结束日的初始值设定为开始日
            count_duration();
        }
    };
    var end = {
        elem: '#end_time',
        format: 'YYYY-MM-DD hh:mm:ss',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: true,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
            count_duration();
        }
    };
    
    var auto_buy_min = {
            elem: '#auto_buy_min',
            format: 'YYYY-MM-DD hh:mm',
            min: laydate.now(),
            max: '2099-06-16 23:59:59',
            istime: true,
            istoday: true,
            choose: function(datas){
               
            }
        };
    
	/*
    var establish_time = {
  	    elem: '#establish_time',
  	    format: 'YYYY-MM-DD hh:mm',
  	    min: laydate.now(),
  	    max: '2099-06-16 23:59:59',
  	    istime: true,
  	    istoday: true,
  	    choose: function(datas){

  	    }
  	};
      */
      
    //laydate(establish_time);
    laydate(auto_buy_min);
    laydate(start);
    laydate(end);

    function changeHideDescription(_obj){
        if($(_obj).is(':checked')){
            $(".hidden_descr").css('display', 'none');
        }else{
            $(".hidden_descr").css('display', '');
        }
    }
    function showTime(opt){
        if (opt == 1) {
            $('#showtime').show();
        } else {
            $('#showtime').hide();
        }
    }
    function autoBuyTime(opt){
        if (opt == 1) {
            $('#buyuser').show();
            $('#buytime').show();
        } else {
            $('#buyuser').hide();
            $('#buytime').hide();
        }
    }
    function checkOrUnCheck(){
        $('.input_users').each(function(){
//            console.log(1);
            console.log($(this).attr("checked"));

            if($(this).attr("checked") == 'checked'){
                $(this).attr("checked", false);
            } else {
                $(this).attr("checked", true);
            }

        })
    }
    
    changeHideDescription($("#hide_description"));

    function changenewPreferential(){
    	var v = $("#new_preferential").val();
    	if(v == 1) {
    		$("#duration_type").empty().append('<option value="1">1周标</option><option value="2">1月标</option><option value="3">2月标</option><option value="4">3月标</option><option value="5">6月标</option>');
    		//购买次数设为  1
    		$("#buy_times").empty().val('1');
    		$("#money_min").empty().val(100);
    		$("#money_max").empty().val(10000);
    	} else {
    		$("#duration_type").empty().append('<option value="2">1月标</option><option value="3">2月标</option><option value="4">3月标</option><option value="5">6月标</option>');
    		$("#buy_times").empty().val('0');
    		$("#money_min").empty().val('0');
    		$("#money_max").empty().val('0');
    	}
    	
    	if(v == 9){
    		$('#monthly_increase_group').attr("disabled",false);
    		$("#user_platform_subsidy").attr('value',0);
    		$("#user_platform_subsidy").attr('disabled',true); 
    	} else {
    		$('#monthly_increase_group').attr("disabled",true);
    		$("#user_platform_subsidy").attr('value','');
    		$("#user_platform_subsidy").attr('disabled',false);
    	}
    	
    	$.post(ROOT + "/Common2/getProjectGroupInfo", {tga_id: v}, function(msg){
    		if(msg.status){
    			console.log(msg.info);
                var s = "<option value='0'>无</option>";
                for (item in msg.info) {
                	s += "<option value='"+msg.info[item].id+"'>"+msg.info[item].name+"</option>";
                }
                $("#project_group_id").empty().html(s);
            }
        });
    }
    
    var tag = "<?php echo ($detail["new_preferential"]); ?>";
    var pgid = "<?php echo ($detail["project_group_id"]); ?>";


    function changeVipId() {
        var vipName = $("#vip_id option:selected").attr('data-value');

        $('input[name=custom_label]').val(vipName + '及以上');
    }

    function initGroup(){
    	
    	var v = $("#new_preferential").val();
    	if(9 == v){
    		$('#monthly_increase_group').attr("disabled",false);
    		$("#user_platform_subsidy").attr('value',0);
    		$("#user_platform_subsidy").attr('disabled',true); 
    	}
    	
    	$.post(ROOT + "/Common2/getProjectGroupInfo", {tga_id: tag}, function(msg){
    		if(msg.status){
    			//console.log(msg.info);
                var s = "";
                
                if(pgid == '0' ){
                	s += "<option value='0' selected>无</option>";
                } else {
                	s += "<option value='0'>无</option>";
                }
                
                for (item in msg.info) {
                	if(pgid == msg.info[item].id) {
                		s += "<option value='"+msg.info[item].id+"' selected >"+msg.info[item].name+"</option>";
                	} else {
                		s += "<option value='"+msg.info[item].id+"'>"+msg.info[item].name+"</option>";
                	}
                	
                }
                $("#project_group_id").empty().html(s);
            }
        });
    }
    
    initGroup();
    
    /*
    
    var establish_type = "<?php echo ($detail["establish_type"]); ?>";
	function initEstablishType(){
    	if(establish_type == 2){
    		$("#establish_type_2").css('display', 'block');
    		$("#establish_type_3").css('display', 'none');
    	} else if(establish_type == 3){
    		$("#establish_type_2").css('display', 'none');
    		$("#establish_type_3").css('display', 'block');
    	}
    } 
	$('input:radio[name="establish_type"]').change( function(e){
    	var value = $(e.target).val();
    	if(value == 1){
    		$("#establish_type_2").css('display', 'none');
    		$("#establish_type_3").css('display', 'none');
    	}else if(value == 2){
    		$("#establish_type_2").css('display', 'block');
    		$("#establish_type_3").css('display', 'none');
    	} else {
    		$("#establish_type_2").css('display', 'none');
    		$("#establish_type_3").css('display', 'block');
    	}
    });
    
    initEstablishType();
    */
    
</script>
<script type="application/javascript" src="/Public/admin/js/project_edit.js?v=0.121"></script>