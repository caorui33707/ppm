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
var URL = '/admin.php/Contract';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<style>
    .huge_tags {
        width: 200px;
    }
</style>
<div id="main" class="main" >
    <div class="content">
        <div class="title">新增合同 [ <a href="<?php echo C('ADMIN_ROOT');?>/Contract/index">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <form id="frmMain" method='post' action="">
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">合同编号：</td>
                    <td class="tLeft"><input type="text" class="huge bLeftRequire" datatype="*" id="name" name="name"></td>
                </tr>

                <tr>
                    <td class="tRight">汇票类型：</td>
                    <td class="tLeft">
                        <select name="draft_type" id="draft_type" class="bleftrequire">
                            <option value="0">银行承兑汇票</option>
                            <option value="1">电子银行承兑汇票</option>
                            <option value="2">商业承兑汇票</option>
                            <option value="3">电子商业承兑汇票</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">承兑机构：</td>
                    <td class="tLeft"><input type="text" class="huge_tags" id="accepting_institution" name="accepting_institution">
                        &nbsp;&nbsp;<span style="color:red;" id="accepting_tips"></span>
                    </td>
                </tr>


                <tr style="display: none">
                    <td class="tRight">担保类型：</td>
                    <td class="tLeft">
                        <select name="guaranty_type" id="guaranty_type" class="bleftrequire">
                            <!--<option value="1">电子银行承兑汇票</option>-->
                            <option value="1">担保机构担保</option>
                            <option value="0" selected >无需担保</option>
                        </select>
                    </td>
                </tr>
                <tr style="display: none">
                    <td class="tRight">担保机构：</td>
                    <td class="tLeft"><input type="text" class="huge_tags" id="guaranty_institution" name="guaranty_institution" onchange="changeGuaranty()">
                        &nbsp;&nbsp;<span style="color:red;" id="guaranty_tips" ></span>
                             <input type="hidden" name="tips_val" value="0" />
                            <input type="hidden" name="gid" />
                    </td>


                </tr>

                <tr style="display: none"  id="guaranty_agreement_tr"  >
                    <td class="tRight tTop">担保协议：</td>
                    <td class="tLeft">
                        <p>上传担保协议</p>
                        <input type="file" value="担保协议" name="guaranty_agreement" id="guaranty_agreement" class="bleftrequire" accept="" >
                        <input type="button" id="guaranty_agreement_submit" value="上传"  class="search imgButton">
                        <p>担保协议图片地址</p>
                        <textarea name="invest_direction_image" style="width:700px;height:100px;"></textarea>
                    </td>
                </tr>



                <tr>
                    <td class="tRight">票号：</td>
                    <td class="tLeft"><input type="text" class="huge" id="ticket_number" name="ticket_number">
                    &nbsp;&nbsp;<span style="color:red;" id="ticket_tips"></span>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">出票人：</td>
                    <td class="tLeft"><input type="text" class="huge bLeftRequire" datatype="*"  id="drawer" name="drawer" maxlength="25" ></td>
                </tr>                
                <tr>
                    <td class="tRight">承兑人信息：</td>
                    <td class="tLeft"><input type="text" class="huge bLeftRequire" datatype="*"  id="acceptor" name="acceptor" maxlength="35"></td>
                </tr>
                
                <tr>
                    <td class="tRight">票面金额：</td>
                    <td class="tLeft"><input type="text" id="price" name="price" value="0"></td>
                </tr>
                <tr>
                    <td class="tRight">出票日期：</td>
                    <td class="tLeft"><input type="text" id="start_time" name="start_time" readonly></td>
                </tr>
                <tr>
                    <td class="tRight">到期日期：</td>
                    <td class="tLeft"><input type="text" id="end_time" name="end_time" readonly></td>
                </tr>
                <tr>
                    <td class="tRight">合同利率(%)：</td>
                    <td class="tLeft"><input type="text" id="interest" name="interest" value="0" ></td>
                </tr>
                
                <tr>
                    <td class="tRight">手续费率(%)：</td>
                    <td class="tLeft"><input type="text" id="fee" name="fee" value="0" ></td>
                </tr>
                 <!-- 
                 <tr>
                    <td class="tRight">申请日期：</td>
                    <td class="tLeft"><input type="text" id="apply_time" name="apply_time" readonly></td>
                </tr>
               	<tr>
                    <td class="tRight">融资人：</td>
                    <td class="tLeft">
                    	<select name="fid">
                       		<option value="0">请选择融资方</option>
                       		<?php if(is_array($financing_list)): foreach($financing_list as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>"><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                 -->
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
    $(document).ready(function(){
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                if(_adding) return;
                _adding = true;
                _layerIndex = layer.load('数据提交中');
                $.ajax({
                    url: ROOT + '/contract/add',
                    data: $('#frmMain').serialize(),
                    type: "POST",
                    cache: false,
                    success: function(msg) {
                        layer.close(_layerIndex);
                        if(msg.status){
                            layer.alert('添加成功~!', -1, function(){
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
        
        $('#ticket_number').blur(function(){
        	var ticket_number = $(this).val();
        	if(ticket_number){
	        	$.post(ROOT + "/Common2/checkTicketNumber", {ticket_number: ticket_number}, function(msg){
	                if(msg.status == 0){
	                	$("#ticket_tips").css("color","red");
	                   	$('#ticket_tips').empty().html(msg.info);
	                } else {
	                	$("#ticket_tips").css("color","#71b83d");
	                	$('#ticket_tips').empty().html('通过信息验证！');
	                }
	                return false;
	            });
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
        choose: function(datas){
            end.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas //将结束日的初始值设定为开始日
        }
    };
    var end = {
        elem: '#end_time',
        format: 'YYYY-MM-DD',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: true,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    /*
    var apply_time = {
        elem: '#apply_time',
        format: 'YYYY-MM-DD hh:mm:ss',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: true,
    };*/
    laydate(start);
    laydate(end);
    //laydate(apply_time);

    $('#draft_type').change(function(){
        __obj = $(this);
        var draft_type = __obj.val();

      //  $('#accepting_institution').parent().parent().css('display','none');
        $('#guaranty_type').parent().parent().css('display','none');
        $('#guaranty_institution').parent().parent().css('display','none');
        $('#guaranty_agreement').parent().parent().css('display','none');

        if(draft_type==2 || draft_type==3){
           // $('#accepting_institution').parent().parent().css('display','');
            $('#guaranty_type').parent().parent().css('display','');
            //$('#guaranty_institution').parent().parent().css('display','');
            //$('#guaranty_agreement').parent().parent().css('display','');
        }
    });

    $('#guaranty_type').change(function(){
        __obj = $(this);
        var guaranty_type = __obj.val();

        $('#guaranty_institution').parent().parent().css('display','');
        $('#guaranty_agreement').parent().parent().css('display','');
        if(guaranty_type==0){
            $('#guaranty_institution').parent().parent().css('display','none');
            $('#guaranty_agreement').parent().parent().css('display','none');
        }

    });


    $('#guaranty_agreement_submit').click(function () {

        //创建FormData对象
        var data = new FormData();
        //为FormData对象添加数据
        $.each($('#guaranty_agreement')[0].files, function(i, file) {
            data.append('img', file);
        });

        var oss_url = "<?php echo C('OSS_STATIC_ROOT');?>";
        $.ajax({
            url: ROOT + '/Contract/load',
            data: data,
            type: "POST",
            cache: false,
            contentType: false,        /*不可缺*/
            processData: false,         /*不可缺*/
            success: function(msg){
                if(msg.status){
                    var text = $('[name=invest_direction_image]').text();
                    var enter = "\r\n";
//                    if(text.trim()){ alert(111);
//                        enter = "<br />";
//                    }

                    var invest_direction_image =  oss_url + '/Uploads/focus/' + msg.image + enter ;
                    $('[name=invest_direction_image]').append(invest_direction_image);
                }else{
                    layer.alert(msg.info);
                }
            }
        });
    });


</script>
<script type="application/javascript" src="/Public/admin/js/contract.js?v=0.121"></script>