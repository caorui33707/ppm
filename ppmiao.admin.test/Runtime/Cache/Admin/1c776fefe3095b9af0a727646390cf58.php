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
var URL = '/admin.php/Guaranty';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">担保机构添加/编辑 [ <a href="<?php echo C('ADMIN_ROOT');?>/Guaranty/list">返回列表</a> ]</div>
        <!--  功能操作区域开始 -->
        <div class="list" >
            <form  method='post' action="">
                <input type="hidden" name="id"  value="<?php echo ($id); ?>" />
                <table cellpadding=3 cellspacing=3 >
					<tr>
                        <td class="tRight">担保机构名称：</td>
                        <td class="tLeft"><input type="text" class="huge bLeftRequire" name="name" datatype="*" value="<?php echo ($detail["name"]); ?>" /></td>
                    </tr>
                    
                    <!-- <tr>
                        <td class="tRight">担保机构简称：</td>
                        <td class="tLeft"><input type="text" class="huge bLeftRequire" name="intro" datatype="*" value="<?php echo ($detail["intro"]); ?>" /></td>
                    </tr> -->
                    
                    <tr>
                        <td class="tRight">法人姓名：</td>
                        <td class="tLeft"><input type="text" class="bLeftRequire" name="legal_person" datatype="*" value="<?php echo ($detail["legal_person"]); ?>" /></td>
                    </tr>
                    
                    <tr>
                        <td class="tRight">营业执照：</td>
                        <td class="tLeft"><input type="text" class="huge bLeftRequire" name="license" datatype="*" value="<?php echo ($detail["license"]); ?>" /></td>
                    </tr>
                    
                    <tr>
                        <td class="tRight">担保机构所在地：</td>
                        <td class="tLeft"><input type="text" class="huge bLeftRequire" name="address" datatype="*" value="<?php echo ($detail["address"]); ?>" /></td>
                    </tr>
                    
                    <tr>
                        <td class="tRight">平台客户号：</td>
                        <td class="tLeft"><input type="text" class="huge bLeftRequire" name="platform_account" datatype="*" value="<?php echo ($detail["platform_account"]); ?>" /></td>
                    </tr>
                    
                    <tr>
                        <td class="tRight">银行编号：</td>
                        <td class="tLeft">
                        	<input type="text" class="huge bLeftRequire" name="bank_code" datatype="*" value="<?php echo ($detail["bank_code"]); ?>" />
                        	<span style="color:#409DFE;">&nbsp;&nbsp;&nbsp; </span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tRight">绑卡开户行：</td>
                        <td class="tLeft">
                        	<select name="bank_id" id="bank_id">
                        		<option value="0" >请选择开户行</option>	
	                    	<?php if(is_array($bank_list)): foreach($bank_list as $key=>$item): ?><option value="<?php echo ($item["bank_no"]); ?>" <?php if(($detail["bank_id"]) == $item['bank_no']): ?>selected<?php endif; ?>><?php echo ($item["bank_name"]); ?></option><?php endforeach; endif; ?>
	                    	</select>
                        </td>
                    </tr>
                   
                    <tr>
                        <td class="tRight">绑定银行卡号：</td>
                        <td class="tLeft"><input type="text" class="huge bLeftRequire" name="bank_card_no" datatype="n" value="<?php echo ($detail["bank_card_no"]); ?>" /></td>
                    </tr>
                    
                    <tr>
                        <td class="tRight">绑卡人姓名：</td>
                        <td class="tLeft"><input type="text" class="bLeftRequire" name="acct_name" datatype="*" value="<?php echo ($detail["acct_name"]); ?>"/></td>
                    </tr>
                    
                    <tr>
                        <td></td>
                        <td class="center">
                            <div style="width:100%;margin:5px">
                                <input type="button" value="保 存" id="savedata" class="button small">
                                <input type="reset" class="button small" onclick="javascript:if(!confirm('确认重置吗?')) return false;resetEditor()" value="重 置">
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <!-- 功能操作区域结束 -->
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
    
    var _adding = false;
    var _layerIndex = 0;
    
    $("#savedata").click(function(){
    	
    	var id = $.trim($("input[name='id']").val());  
    	var name = $.trim($("input[name='name']").val());  
    	// var intro = $.trim($("input[name='intro']").val());      	
    	var legal_person = $.trim($("input[name='legal_person']").val());
    	var license = $.trim($("input[name='license']").val());
    	var address = $.trim($("input[name='address']").val());
    	var platform_account = $.trim($("input[name='platform_account']").val());      	
    	var bank_id = $.trim($("#bank_id").val());
    	var bank_card_no = $.trim($("input[name='bank_card_no']").val());
    	var acct_name = $.trim($("input[name='acct_name']").val());
    	var bank_code = $.trim($("input[name='bank_code']").val());
    	
    	if(name == "" ) {
    		layer.alert('请填写担保机构');
    		return;
    	} 
     //    else if(intro == '') {
    	// 	layer.alert('请填写担保机构简称'); 
    	// 	return;
    	// } 
        else if(legal_person == ''){
    		layer.alert('请填写法人');
    		return;
    	} else if(license == ''){
    		layer.alert('请填写营业执照');
    		return;
    	} else if( address == '') {
    		layer.alert('请填写担保机构所在地');
    		return;
    	} else if( platform_account == '') {
    		layer.alert('请填写平台客户号');
    		return;
    	} else if(bank_code == '') {
            layer.alert('请填写银行编号');
            return;
        } else if( bank_id <=0) {
    		layer.alert('请填选择开户行');
    		return;
    	} else if( bank_card_no == '') {
    		layer.alert('请填写银行卡号');
    		return;
    	} else if( acct_name == '') {
    		layer.alert('请填写绑卡人姓名');
    		return;
    	}
    	
    	$.ajax({
            url: ROOT + '/Guaranty/edit',
            type: "POST",
            data: { 'id':id,
                    'name':name,
            		// 'intro':intro,
                    'legal_person':legal_person,
            		'license':license,
                    'address':address,            		
            		'platform_account':platform_account,
            		'bank_id':bank_id,
            		'bank_card_no':bank_card_no,
            		'acct_name':acct_name,
            		'bank_code':bank_code},
            cache: false,
            success: function(msg) {
                layer.close(_layerIndex);
                if(msg.status == 1){
                    layer.confirm('添加成功~!是否继续添加?', function(){
                        window.location.reload();
                    }, function(){
                        window.location.href = msg.info;
                    });
                }else if(msg.status == 2){
                	                	
                    layer.alert('修改成功！', -1, function(){
                    	window.location.href = msg.info;
                    });
                	
                }else{
                    layer.alert(msg.info);
                }
                _adding = false;
            }
        });
    });

</script>