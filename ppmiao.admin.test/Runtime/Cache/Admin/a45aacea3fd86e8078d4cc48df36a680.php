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
        <div class="title">广告管理 - 新增产品公告标签 [ <a href="<?php echo C('ADMIN_ROOT');?>/Advertisement/project_notic_tag_index">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" src="/Public/admin/js/jquery.form.js"></script>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <form id="frmMain" method='post' action="">
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">产品公告标签：</td>
                    <td class="tLeft">
                        <input type="text" class="" name="tag_title" >
                    </td>
                </tr>
                <tr id="tag_time">
                    <td class="tRight">标签有效期：</td>
                    <td class="tLeft">
                        <input type="text" class="laydate-icon" name="start_time" id="start_time" />
                        <input type="text" class="laydate-icon" name="end_time" id="end_time" />
                    </td>
                </tr>
                <tr>
                    <td class="tRight">备注：</td>
                    <td class="tLeft"><textarea id="description" name="description" class="huge" maxlength="255" style="height:80px;"></textarea></td>
                </tr>
                <tr>
                    <td class="tRight">标签类型：</td>
                    <td class="tLeft">
                        <select name="tag_type" id="tag_type" >
                            <option value="1">特殊标签</option>
                            <option value="2" selected >公告标签</option>
                        </select>
                    </td>
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
    var _layerIndex = 0;
    var _action = 0;
    var _urlObj, _extObj;


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

    $(document).ready(function(){
        _urlObj = $("#url");
        _extObj = $("#ext");
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                _layerIndex = layer.load('数据提交中...');
                $("#frmMain").ajaxSubmit({
                    url: ROOT + '/advertisement/project_notic_tag_add',
                    type: "post",
                    dataType: "json",
                    success: function(msg){
                        layer.close(_layerIndex);
                        if(msg.status){
                            layer.alert('添加成功~!', -1, function(){
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

    $('#tag_type').change(function () {
       var tag_type =  $(this).val();

       if(tag_type==1){
           $('#tag_time').css('display','none');
       }else{
           $('#tag_time').css('display','');
       }
    });

</script>