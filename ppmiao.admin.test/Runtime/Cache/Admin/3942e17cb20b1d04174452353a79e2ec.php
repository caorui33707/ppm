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
var URL = '/admin.php/Advertisement';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">广告管理 - 编辑广告 [ <a href="<?php echo C('ADMIN_ROOT');?>/Advertisement/index/p/<?php echo ((isset($params["page"]) && ($params["page"] !== ""))?($params["page"]):1); ?>">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" src="/Public/admin/js/jquery.form.js"></script>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <form id="frmMain" method='post' action="">
            <input type="hidden" name="id" value="<?php echo ($detail["id"]); ?>" />
            <input type="hidden" name="page" value="<?php echo ((isset($params["page"]) && ($params["page"] !== ""))?($params["page"]):1); ?>" />
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">广告位：</td>
                    <td class="tLeft" >
                        <select disabled>
                            <?php if(is_array($position)): foreach($position as $key=>$item): ?><option value="<?php echo ($key); ?>" <?php if(($key) == $detail['position']): ?>selected<?php endif; ?>><?php echo ($item); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">动作：</td>
                    <td class="tLeft" >
                        <select name="action" id="action" onchange="changeAction(this)">
                            <?php if(is_array($action)): foreach($action as $key=>$item): ?><option value="<?php echo ($key); ?>" <?php if(($detail["action"]) == $key): ?>selected<?php endif; ?>><?php echo ($item); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr style="display:none;">
                    <td class="tRight">URL：</td>
                    <td class="tLeft"><textarea id="url" name="url" class="huge"></textarea></td>
                </tr>
                <tr <?php if($detail['action'] < 1): ?>style="display:none;"<?php endif; ?>>
                    <td class="tRight">扩展内容：</td>
                    <td class="tLeft"><textarea id="ext" name="ext" class="huge"><?php echo ($detail["ext"]); ?></textarea></td>
                </tr>
                <tr>
                    <td class="tRight">状态：</td>
                    <td class="tLeft">
                        <select name="status" id="status" class="bleftrequire">
                            <option value="1" <?php if(($detail["status"]) == "1"): ?>selected<?php endif; ?>>未上架</option>
                            <option value="2" <?php if(($detail["status"]) == "2"): ?>selected<?php endif; ?>>已上架</option>
                            <option value="3" <?php if(($detail["status"]) == "3"): ?>selected<?php endif; ?>>已下架</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">图片：</td>
                    <td class="tLeft" >
                        <input type="hidden" name="image" value="<?php echo ($detail["image"]); ?>" />
                        <img src="<?php echo C('OSS_STATIC_ROOT');?>/Uploads/focus/<?php echo ($detail["image"]); ?>" style="max-width: 100px;" /><br>
                        <input type="file" class="bleftrequire" name="img" id="img" />
                    </td>
                </tr>
                <tr>
                    <td class="tRight">标题：</td>
                    <td class="tLeft"><input type="text" name="title" id="title" class="huge" value="<?php echo ($detail["title"]); ?>" /></td>
                </tr>
                <tr>
                    <td class="tRight">摘要：</td>
                    <td class="tLeft"><textarea id="summary" name="summary" class="huge" maxlength="255" style="height:80px;"><?php echo ($detail["summary"]); ?></textarea></td>
                </tr>
                
                <tr>
                    <td class="tRight">分享Id：</td>
                    <td class="tLeft"><input type="text" name="share_id" id="share_id" value="<?php echo ($detail["share_id"]); ?>"/></td>
                </tr>
                
                <tr>
                    <td class="tRight">是否大banner：</td>
                    <td class="tLeft">
                    	<input type="checkbox" class="laydate-icon" name="big_banner" id="big_banner" value="1"/>
                    </td>
                </tr>
                
                <tr style="display:none;">
                    <td class="tRight">用户组：</td>
                    <td class="tLeft">
                        <input type="checkbox" class="laydate-icon" name="old_user" id="old_user" value="1"/>老用户
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;排序:<input type="text" name="old_rank" id="old_rank" value="<?php echo ($detail["old_rank"]); ?>"/>
                    </td>
                </tr>
                
                <tr style="display:none;">
                    <td class="tRight">用户组：</td>
                    <td class="tLeft">
                        <input type="checkbox" class="laydate-icon" name="new_user" id="new_user" value="1"/>新用户
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;排序:<input type="text" name="new_rank" id="new_rank" value="<?php echo ($detail["new_rank"]); ?>"/>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">是否活动：</td>
                    <td class="tLeft">
                    	<input type="checkbox" class="laydate-icon" name="is_activity" id="is_activity" value="1"/>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">起止时间：</td>
                    <td class="tLeft">
                    	<input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($detail["start_time"]); ?>"/>
                    	<input type="text" class="laydate-icon" name="end_time" id="end_time" value="<?php echo ($detail["end_time"]); ?>"/>
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
    var _layerIndex = 0;
    var _action = 0;
    var _urlObj, _extObj;
    
    var big_banner = "<?php echo ($detail["big_banner"]); ?>";
    var is_activity = "<?php echo ($detail["is_activity"]); ?>";
    
    var new_user = "<?php echo ($detail["new_user"]); ?>";
    var old_user = "<?php echo ($detail["old_user"]); ?>";
    
    $(document).ready(function(){
        _urlObj = $("#url");
        _extObj = $("#ext");
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                _layerIndex = layer.load('数据提交中...');
                $("#frmMain").ajaxSubmit({
                    url: ROOT + '/advertisement/edit',
                    type: "post",
                    dataType: "json",
                    success: function(msg){
                        layer.close(_layerIndex);
                        if(msg.status){
                            layer.alert('编辑成功~!', -1, function(){
                                window.location.href = msg.info;
                            });
                        }else{
                            layer.alert(msg.info);
                        }
                    }
                });
                return false;
            }
        });
        
        init();
    });
    
    
    function init(){
    	if(big_banner == "1") {
    		 $("#big_banner").attr("checked","checked");
    		 $("#big_banner").change();
    	}
    	if(is_activity == "1"){
    		$("#is_activity").attr("checked","checked");
    	} 
    	console.log(new_user);
    	console.log(old_user);
    	new_user>0 ? $("#new_user").attr("checked","checked") : '';
    	old_user>0 ? $("#old_user").attr("checked","checked") : '';
    }
    
    $("#big_banner").change(function() { 
    	if ($("#big_banner").attr("checked")) {
    		  $("#old_rank").parent().parent().css('display', '');
    		  $("#new_rank").parent().parent().css('display', '');
    	  } else{
    		  $("#old_rank").parent().parent().css('display', 'none');
    		  $("#new_rank").parent().parent().css('display', 'none');
    	  }
    });
    
    
    function changeAction(_obj){
        _action = $(_obj).val();
        switch(_action){
            case '0':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '1':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '2':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '3':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '4':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '5':
                _extObj.parent().parent().css('display', '');
                _extObj.val('{"url":"","islogin":"0"}');
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                break;
            case '6':
                _extObj.parent().parent().css('display', '');
                _extObj.val('{"id":""}');
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                break;
            case '7':
                _extObj.parent().parent().css('display', '');
                _extObj.val('{"id":""}');
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                break;
            case '8':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '9':
                _urlObj.parent().parent().css('display', '');
                _urlObj.val('{"cardNo":""}');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '10':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '11':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
            case '12':
                _urlObj.parent().parent().css('display', 'none');
                _urlObj.val('');
                _extObj.parent().parent().css('display', 'none');
                _extObj.val('');
                break;
        }
    }
    
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
    
    
    $("#status").change(function() { 
    	
    	var val = $(this).val();
    	if(3==val){
    		$("#old_user").removeAttr("checked");
    		$("#new_user").removeAttr("checked");
    		$("#big_banner").removeAttr("checked");
    		$("#big_banner").change();
    		
    	}
    });
    
    
</script>