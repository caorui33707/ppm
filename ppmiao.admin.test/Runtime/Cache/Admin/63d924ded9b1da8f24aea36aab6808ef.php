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
var URL = '/admin.php/InterestCoupon';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">券包发放 [ <a href="<?php echo C('ADMIN_ROOT');?>/InterestCoupon/index">返回列表</a> ] </div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" charset="utf-8" src="/Public/admin/auth/js/editor/kindeditor-all.js"></script>
        <link rel="stylesheet" type="text/css" href="/Public/admin/css/autocomplete.css" />
        <script type="text/javascript" src="/Public/admin/js/jquery.autocomplete.js"></script>
        <form id="frmMain" method='post' action="">
            <table cellpadding='3' cellspacing='3' >
                <tr>
                    <td class="tRight">标题：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="title" name="title" maxlength="45"></td>
                </tr>
				<tr>
                    <td class="tRight">子标题：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="subtitle" name="subtitle" maxlength="150"></td>
                </tr>
                <tr>
                    <td class="tRight">活动名称：</td>
                    <td class="tLeft"><input type="text" class="huge bLeftRequire " datatype="*" id="act_name" name="act_name" maxlength="45" >
                    </td>
                </tr>
                <tr>
                    <td class="tRight">券包利率：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" datatype="*" id="interest_rate" name="interest_rate" value="" />&nbsp;<span style="color:#409DFE;">单位：% ,如，5% ，就写 5</span></td>
                </tr>
				
				 <tr>
                    <td class="tRight">最小投资金额：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" datatype="n" id="min_invest" name="min_invest" value="" />&nbsp;<span style="color:#409DFE;">单位：元</span></td>
                </tr>
				
				 <tr>
                    <td class="tRight">最小投资期限：</td>
                    <td class="tLeft"><input type="text"  class="bLeftRequire"datatype="n" id="min_due" name="min_due" value="" />&nbsp;<span style="color:#409DFE;">单位:天(比如30天，60天，90天)</span></td>
                </tr>
				
				<tr>
                    <td class="tRight">券包有效天数：</td>
                    <td class="tLeft"><input type="text" class="bLeftRequire" datatype="n" id="due_date" name="due_date" value="60" />&nbsp;<span style="color:#409DFE;">单位:天（默认60天）</span></td>
                </tr>
				
				<tr>
                    <td class="tRight">生效时间：</td>
                    <td class="tLeft">
                    	<input type="text" class="laydate-icon" name="start_time" id="start_time" readonly />
                    	&nbsp;<span style="color:#409DFE;">默认为当前日期</span>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">产品标签：</td>
                    <td class="tLeft">
                    	<input type="checkbox" class="laydate-icon" name="tag_id[]" id="tag_id_0" value="0"/>普通标&nbsp;&nbsp;
         	            <input type="checkbox" class="laydate-icon" name="tag_id[]" id="tag_id_1" value="1"/>新人特惠&nbsp;&nbsp;
         	            <input type="checkbox" class="laydate-icon" name="tag_id[]" id="tag_id_2" value="2"/>爆款&nbsp;&nbsp;
         	            <input type="checkbox" class="laydate-icon" name="tag_id[]" id="tag_id_6" value="6"/>活动&nbsp;&nbsp;
         	            <input type="checkbox" class="laydate-icon" name="tag_id[]" id="tag_id_8" value="8"/>私人专享&nbsp;&nbsp;
               	
                    </td>
                </tr>
                
				<tr>
                    <td class="tRight">备注：</td>
                    <td class="tLeft" ><input type="text" class="huge"  id="memo" name="memo" maxlength="12"></td>
                </tr>
                
                <tr>
                    <td class="tRight">券包标识：</td>
                    <td class="tLeft" ><input type="text" id="source" name="source" maxlength="20">
                    &nbsp;<span style="color:#409DFE;">默认为空，技术有需要在填写</span>
                    </td>
                </tr>

                <!--<tr>-->
                    <!--<td class="tRight">券包发放渠道：</td>-->
                    <!--<td class="tLeft">-->
                        <!--<select name="type" id="type">-->
                            <!--<option value="1">个人</option>-->
                            <!--<option value="2">平台活动</option>-->
                            <!--<option value="3">微信</option>-->
                            <!--<option value="4">暗道</option>-->
                        <!--</select>-->
                    <!--</td>-->
                <!--</tr>-->
                
                <tr>
                    <td class="tRight">券包发放范围：</td>
                    <td class="tLeft">
                        <select name="send_scope" id="sendScopeId">                           
                            <option value="1">全部</option>
                            <option value="2">指定用户</option>
                        </select>
                    </td>
                </tr>
											
				<tr class="mytr" style="display:none">
					<td class="tRight">指定用户：</td>
					<td class="tLeft"><input type="text" id="username" name="username" value="" />&nbsp;<span style="color:#409DFE;">&nbsp;&nbsp;<a href="###" id="checkUser">添加</a></span></td>
				</tr>
				
				<tr class="mytr" style="display:none">
					<td class="tRight tTop">用户列表：</td>
					<td class="tLeft">          
						<textarea name="user_list" id="user_list_id" style="width:700px;height:100px;" readonly="true"></textarea>
					</td>
					<input type="hidden" name="userId"/>
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
    
	$("#sendScopeId").change(function(){
		var v = ($('#sendScopeId').val());
		if(v==1) {
			$(".mytr").css('display','none');
		} else {
			$(".mytr").removeAttr("style");
		}
		return;
	});
	
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
                    url: ROOT + '/InterestCoupon/add',
                    data: $('#frmMain').serialize(),
                    type: "POST",
                    cache: false,
                    success: function(msg) {
                        layer.close(_layerIndex);
                        if(msg.status == 1){
                            layer.alert('发放成功~!', -1,function (){
                            	window.location.href=ROOT+"/InterestCoupon/history_index"; 
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
		//
	$("#checkUser").click(function(){
	
		var username = $("#username").val();
		if(username == "" || username.length != 11) {
			layer.alert("请输入正确的手机号码");
			return;
		}
				
		$.ajax({
			url: ROOT + '/InterestCoupon/findUser',
			data: {"username":username},
			type: "POST",
			cache: false,
			success: function(msg) {								
				if(msg.status == 1){
					
					$("#username").attr("value","");
					
					var userId= msg.info.id;
					
					var _username = msg.info.username;					
					
					var text = $("#user_list_id").val();					
					
					var bool = text.indexOf(_username);
					if(bool >= 0) {
						layer.alert("该用户已经已添加，请不要重复");
						return false;
					}
										
					if(text !="") {
						_username = text+"#"+_username;
					}
					
					$("#user_list_id").text(_username);	
					
										
					var _userId = $("input[name='userId']").val(); 
					
					if(_userId !="") {
						userId = _userId+"#"+userId;
					}
					
					$("input[name='userId']").val(userId); 	
					
				} else {
					layer.alert(msg.info);
				}				
				return;				
			}
        });
		
	});
		
	var start = {
      elem: '#start_time',
      format: 'YYYY-MM-DD',
      min: '1970-00-00 00:00:00', //设定最小日期为当前日期
      max: '2099-06-16 23:59:59', //最大日期
      istime: true,
      istoday: true,
  	};
  	laydate(start);
	
</script>