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
var URL = '/admin.php/Message';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title"> 消息管理  - 推送消息  [ <a href="<?php echo C('ADMIN_ROOT');?>/message/msg_push_index">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <form id="frmMain" method='post' action="">
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight" width="150px">正文内容：</td>
                    <td class="tLeft">
                        <textarea name="content" id="content" maxlength="58" style="width:600px;height:150px;"></textarea>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">接收对象</td>
                    <td class="tLeft">
                    	<label for="filter_bk">
                    	<input type="radio" name="target" value="1" checked/>&nbsp;按用户推送
                    	<input type="radio" name="target" value="0"/>&nbsp;按版本推送
                        <input type="radio" name="target" value="2"/>&nbsp;按手机号推送
                        </label>
                    </td>
                </tr>
                <tr class="mobiles_class" style="display:none">
                    <td class="tRight" width="150px">手机号：</td>
                    <td class="tLeft">
                        <textarea name="mobiles" id="mobiles_content" placeholder="XXXXXXXXXXX#XXXXXXXXXXX"   style="width:600px;height:150px;"></textarea>
                    </td>
                </tr>
                <tr class="registration_class">
                    <td class="tRight">Registration ID</td>
                    <td class="tLeft"><input type="text" name="regid" class="huge"/></td>
                </tr>
                
                <tr class="ver_controller" style="display:none">
                    <td class="tRight">安卓版本</td>
                    <td class="tLeft">
                    	<label for="filter_bk">
                    	<input type="radio" name="android_ver" value="0" checked/>&nbsp;全部
                    	<input type="radio" name="android_ver" value="1"/>&nbsp;指定版本</label>
                    </td>
                </tr>
                
                <tr id="android_ver_list" style="display:none" class="android_ver_list_controller">
                	<td class="tRight">安卓版本列表</td>
                    <td class="tLeft">
                    	<label for="filter_bk">
                        <input type="checkbox" class="laydate-icon" name="_android_all"  value="0" />全选
                    	<?php if(is_array($android_list)): foreach($android_list as $key=>$item): ?><input type="checkbox" class="laydate-icon" name="_android_ver[]" value="<?php echo ($item["app_version"]); ?>"/><?php echo ($item["app_version"]); ?>&nbsp;&nbsp;<?php endforeach; endif; ?>
                    	
                    	</label>
                    </td>
                </tr>
                
                <tr class="ver_controller" style="display:none">
                    <td class="tRight">苹果版本</td>
                    <td class="tLeft">
                    	<label for="filter_bk">
                    	<input type="radio" name="ios_ver" value="0" checked/>&nbsp;全部
                    	<input type="radio" name="ios_ver" value="1"/>&nbsp;指定版本</label>
                    </td>
                </tr>
                
                <tr id="ios_ver_list" style="display:none" class="ios_ver_list_controller">
                	<td class="tRight">苹果版本列表</td>
                    <td class="tLeft">
                    	<label for="filter_bk">
                        <input type="checkbox" class="laydate-icon" name="_ios_all" value="0" />全选
                    	<?php if(is_array($ios_list)): foreach($ios_list as $key=>$item): ?><input type="checkbox" class="laydate-icon" name="_ios_ver[]" value="<?php echo ($item["app_version"]); ?>"/><?php echo ($item["app_version"]); ?> &nbsp;&nbsp;<?php endforeach; endif; ?>
                    	</label>
                    </td>
                </tr>
                 
                <tr>
                    <td class="tRight">定时推送</td>
                    <td class="tLeft">
                    	<select name="push_type" onchange="pushType(this)">
                            <option value="0">关闭</option>
                            <option value="1">开启</option>
                        </select>
                        
                       <input type="text" class="bLeftRequire laydate-icon" style="display:none" name="time" id="time" readonly />
                        
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">推送动作</td>
                    <td class="tLeft">
                        <select name="action" onchange="pushAction(this)">
                            <option value="0">无动作</option>
                            <option value="5">URL</option>
                            <option value="6">产品详细</option>
                            <!-- 
                            <option value="1">精品推荐</option>
                            <option value="2">理财产品</option>
                            <option value="3">发现</option>
                            <option value="4">我</option>
                            <option value="7">立即购买</option>
                            <option value="8">账户中心</option>
                            <option value="9">完善银行卡</option>
                            <option value="10">我的钱包</option>
                            <option value="11">邀请好友</option>
                             -->
                        </select>
                    </td>
                </tr>
                <tr id="is_login_display" style="display: none">
                    <td class="tRight">是否需要登录</td>
                    <td class="tLeft">
                        <label for="filter_bk">
                            <input type="radio" name="is_login" value="1" />&nbsp;是
                            <input type="radio" name="is_login" value="0" checked/>&nbsp;否</label>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">参数数组</td>
                    <td class="tLeft">
                        <p><input type="text" name="key[]"  readonly /> - <input type="text" name="value[]" class="huge"/></p>
                        <p><input type="text" name="key[]"  readonly /> - <input type="text" name="value[]" class="huge"/></p>
                        <!-- 
                        <p><input type="text" name="key[]" /> - <input type="text" name="value[]" class="huge"/></p>
                        <p><input type="text" name="key[]" /> - <input type="text" name="value[]" class="huge"/></p>
                        <p><input type="text" name="key[]" /> - <input type="text" name="value[]" class="huge"/></p>
                         -->
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="center">
                        <div style="width:85%;margin:5px">
                            <input type="submit" value="推 送"  class="button small">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>


	var end = {
        elem: '#time',
        format: 'YYYY-MM-DD hh:mm:ss',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: true,
        choose: function(datas){
            
        }
    };
    
    laydate(end);
    $('#time').val(laydate.now(1, 'YYYY-MM-DD 10:00:00'));


    var _layerIndex = 0;
    $("#frmMain").Validform({ // 表单验证
        tiptype: 3,
        callback: function(form){
            _layerIndex = layer.load('消息推送中...');
            $.ajax({
                url: ROOT + '/message/msg_push_add',
                data: $('#frmMain').serialize(),
                type: "POST",
                cache: false,
                success: function(msg) {
                    layer.close(_layerIndex);
                    if(msg.status){
                       layer.alert(msg.message_info, -1, function(){
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
    function pushAction(_obj){ // 转换推送动作
        var _act = $(_obj).val();
        $("#is_login_display").css('display','none');
        resetExtra();
        switch(_act){
            case '1':
                $("input[name='key[]']").val('');
                break;
            case '2':
                $("input[name='key[]']").val('');
                break;
            case '3':
                $("input[name='key[]']").val('');
                break;
            case '4':
                $("input[name='key[]']").val('');
                break;
            case '5':
                $("input[name='key[]']:first").val('url');
                $("#is_login_display").css('display','');
                break;
            case '6':
                $("input[name='key[]']:first").val('id');
                $("input[name='key[]']:eq(1)").val('title');
                break;
            case '7':
                $("input[name='key[]']:first").val('id');
                break;
            case '8':
                $("input[name='key[]']:first").val('id');
                break;
            case '9':
                $("input[name='key[]']:first").val('cardNo');
                break;
            default:
                $("input[name='key[]']").val('');
                break;
        }
    }
    function resetExtra(){ // 重置参数
        $("input[name='key[]'],input[name='value[]']").val('');
    }
    
    $("input[name='target']").click(function(){
    	var val = $(this).val();
    	if(val=='0'){
    		$("input[name='regid']").parent().parent().css('display', 'none');
    		$(".ver_controller").css('display', '');
    		
    		$("input[name='android_ver']").get(0).checked=true; 
    		$("input[name='ios_ver']").get(0).checked=true;
            $(".mobiles_class").css('display', 'none');
    		
    	}

    	if(val=='1'){
    		$("input[name='regid']").parent().parent().css('display', '');
    		$(".ver_controller").css('display', 'none');
    		
    		if($("input[name='android_ver']").val() == 0){
    			$('.android_ver_list_controller').css('display', 'none');
    		} else{
    			$('.android_ver_list_controller').css('display', '');
    		}
    		
    		if($("input[name='ios_ver']").val() == 0){
    			$('.ios_ver_list_controller').css('display', 'none');
    		} else{
    			$('.ios_ver_list_controller').css('display', '');
    		}
            $(".mobiles_class").css('display', 'none');
    	}

        if(val=='2'){
            $(".mobiles_class").css('display', '');
            $(".registration_class").css('display', 'none');
            $(".ver_controller").css('display', 'none');
            $('.android_ver_list_controller').css('display', 'none');
            $('.ios_ver_list_controller').css('display', 'none');
        }

    });

    $("input[name='android_ver']").click(function(){
        var val = $(this).val();
        if(val=='1'){
            $("#android_ver_list").css('display', '');
        } else{
            $("#android_ver_list").css('display', 'none');
        }
    });

    // 全选
    $("input[name='_android_all']").click(function(){
        //$('input[name=\'_android_ver[]\']').attr('checked',true);
        var id = $("input[name='_android_all']").val();
        if(id==0){
            $('input[name=\'_android_ver[]\']').attr('checked',true);
            $("input[name='_android_all']").val(1);
        }else{
            $('input[name=\'_android_ver[]\']').attr('checked',false);
            $("input[name='_android_all']").val(0);
        }
    });

    $("input[name='_ios_all']").click(function(){
        var id = $("input[name='_ios_all']").val();
        if(id==0){
            $('input[name=\'_ios_ver[]\']').attr('checked',true);
            $("input[name='_ios_all']").val(1);
        }else{
            $('input[name=\'_ios_ver[]\']').attr('checked',false);
            $("input[name='_ios_all']").val(0);
        }
    });

    //end 全选

    
    $("input[name='ios_ver']").click(function(){
    	var val = $(this).val();
    	if(val=='1'){
    		$("#ios_ver_list").css('display', '');
    	} else{
    		$("#ios_ver_list").css('display', 'none');
    	}
    });

    
    function pushType(_obj){
    	var val = $(_obj).val();
    	if(val=='0'){
    		$("input[name='time']").css('display', 'none');
    	} else{
    		$("input[name='time']").css('display', '');
    	}
    }
    
</script>