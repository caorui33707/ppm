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
var URL = '/admin.php/Event';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">编辑活动 [ <a href="<?php echo C('ADMIN_ROOT');?>/event/event_conf_index">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <form id="frmMain" method='post' action="">
            <input type="hidden" name="id" value="<?php echo ($detail["id"]); ?>" />
            <input type="hidden" name="coupon_id" value="<?php echo ($detail["coupon_id"]); ?>" />
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">活动名称：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="name" name="name" value="<?php echo ($detail["name"]); ?>" ></td>
                </tr>
                <tr>
                    <td class="tRight" width="150px">活动类型：</td>
                    <td class="tLeft">
                        <select id="type" name="type" class="bLeftRequire" datatype="*" >
                            <option value="0">请选择</option>
                            <option value="1" <?php if($detail["type"] == 1): ?>selected<?php endif; ?>>红包</option>
                            <option value="2" <?php if($detail["type"] == 2): ?>selected<?php endif; ?>>券包</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight" width="150px">活动事件：</td>
                    <td class="tLeft">
                        <select id="act" name="act" class="bLeftRequire" datatype="*" >
                            <option value="0">选择择</option>
                            <option value="1" <?php if($detail["act"] == 1): ?>selected<?php endif; ?>>注册</option>
                            <option value="2" <?php if($detail["act"] == 2): ?>selected<?php endif; ?>>登录</option>
                            <option value="10" <?php if($detail["act"] == 10): ?>selected<?php endif; ?>>其他</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight tTop">起止时间：</td>
                    <td class="tLeft">
                        <input type="text" class="bLeftRequire laydate-icon" name="start_time" id="start_time" value="<?php echo ($detail["begin_time"]); ?>" readonly /> -
                        <input type="text" class="bLeftRequire laydate-icon" name="end_time" id="end_time" value="<?php echo ($detail["end_time"]); ?>"readonly />
                    <td>
                </tr>
                
                <tr>
                    <td class="tRight">备注：</td>
                    <td class="tLeft" ><textarea name="memo" id="memo" class="huge" style="height:100px;" maxlength="255"><?php echo ($detail["memo"]); ?></textarea></td>
                </tr>
                
                <tr>
                    <td class="tRight">标题：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" value="<?php echo ($detail["title"]); ?>" id="title" name="title" maxlength="45" readonly="true"></td>
                </tr>
				<tr>
                    <td class="tRight">红包内容(券包子标题)：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" value="<?php echo ($detail["content"]); ?>" id="content" name="content" maxlength="150"></td>
                </tr>				
                <tr>
                    <td class="tRight">红包金额(券包利率)：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" datatype="*" value="<?php echo ($detail["amount"]); ?>"  id="amount" name="amount" value="" />&nbsp;<span style="color:#409DFE;">单位：元</span></td>
                </tr>
				
				 <tr>
                    <td class="tRight">红包最小投资金额：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" datatype="n" id="min_invest" value="<?php echo ($detail["min_invest"]); ?>" name="min_invest" value="" />&nbsp;<span style="color:#409DFE;">单位：元</span></td>
                </tr>
				
				 <tr>
                    <td class="tRight">红包最小投资期限：</td>
                    <td class="tLeft"><input type="text"  class="bLeftRequire"datatype="n" id="min_due" name="min_due"  value="<?php echo ($detail["min_due"]); ?>" />&nbsp;<span style="color:#409DFE;">单位:天(比如30天，60天，90天)</span></td>
                </tr>
				
				<tr>
                    <td class="tRight">红包有效天数：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" datatype="n" id="due_date" name="due_date" value="<?php echo ($detail["min_day"]); ?>" />&nbsp;<span style="color:#409DFE;">单位:天（默认60天）</span></td>
                </tr>
                
                
                <tr>
                    <td></td>
                    <td class="center">
                        <div style="width:85%;margin:5px">
                            <input type="submit" value="保 存"  class="button small">
                            <!--<input type="reset" class="button small" onclick="javascript:if(!confirm('确认重置吗?')) return false;resetEditor()" value="重 置" >-->
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<script>

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
        }
    };
    
    laydate(start);
    laydate(end);

    var _adding = false;
    var _layerIndex = 0;
    $(document).ready(function(){
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                if(_adding) return;
                _adding = true;
                _layerIndex = layer.load('数据提交中...');
                $.ajax({
                    url: ROOT + '/event/event_conf_edit',
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