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
        <div class="title">广告管理 - 产品公告管理 [ <a href="<?php echo C('ADMIN_ROOT');?>/Advertisement/project_notic_index">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" src="/Public/admin/js/jquery.form.js"></script>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <form id="frmMain" method='post' action="">
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight" width="20%">广告位：</td>
                    <td class="tLeft" >
                        <label>产品公告</label>
                        <input type="hidden" name="position" value="6"/>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">动作：</td>
                    <td class="tLeft" >
                        <select name="action" id="action" onchange="changeAction(this)">
                            <?php if(is_array($action)): foreach($action as $key=>$item): ?><option value="<?php echo ($key); ?>"><?php echo ($item); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr style="display:none;">
                    <td class="tRight">URL：</td>
                    <td class="tLeft">
                    	<textarea id="url" name="url" class="huge"></textarea><span style="color:#409DFE;">islogin:0 可以不登录 ，1 要求登录</span>
                    </td>
                </tr>
                <tr style="display:none;">
                    <td class="tRight">扩展内容：</td>
                    <td class="tLeft"><textarea id="ext" name="ext" class="huge"></textarea></td>
                </tr>
                <tr>
                    <td class="tRight">状态：</td>
                    <td class="tLeft">
                        <select name="status" id="status" class="bleftrequire">
                            <option value="1">未上架</option>
                            <option value="2">已上架</option>
                            <option value="3">已下架</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">摘要：</td>
                    <td class="tLeft"><textarea id="summary" name="summary" class="huge" maxlength="255" style="height:80px;"></textarea></td>
                    <td> <span style="color: #c92c2c">* 建议输入30个字以内</span></td>
                </tr>
                
                <!--<tr>-->
                    <!--<td class="tRight">产品公告标签：</td>-->

                    <!--<td class="tLeft">-->
                        <!--&lt;!&ndash;<input type="radio" name="tag_type" id ='radio1' value="1">&ndash;&gt;-->
                        <!--<select name="tag" >-->
                            <!--<option value="0">普通标</option>-->
                            <!--<option value="1">新人特惠</option>-->
                            <!--<option value="2">爆款</option>-->
                            <!--<option value="3">HOT</option>-->
                            <!--<option value="6">活动</option>-->
                            <!--<option value="8">私人专享</option>-->
                            <!--<option value="9">月月加薪</option>-->
                        <!--</select>-->
                    <!--</td>-->
                <!--</tr>-->

                <tr>
                    <td class="tRight">产品公告标签：</td>
                    <td>
                        <!--<input type="radio" name="tag_type" id ='radio2' value="2">-->
                        <select name="tag_id" >
                            <!--<option value="0">&#45;&#45;选择其他标签&#45;&#45;</option>-->
                            <?php if(is_array($tags)): foreach($tags as $key=>$tag): ?><option   value="<?php echo ($tag["id"]); ?>"><?php echo ($tag["tag_title"]); ?></option><?php endforeach; endif; ?>
                        </select>

                    </td>
                </tr>
                
                <tr>
                    <td class="tRight">起止时间：</td>
                    <td class="tLeft">
                    	<input type="text" class="laydate-icon" name="start_time" id="start_time" />
                    	<input type="text" class="laydate-icon" name="end_time" id="end_time" />
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

        $("select[name=\"tag\"]").click(function(){ // radio1点击 选中
            $('#radio1').attr("checked",true);
        });


        $("select[name=\"tag_id\"]").click(function(){ // radio2点击 选中
            $('#radio2').attr("checked",true);
        });

        _urlObj = $("#url");
        _extObj = $("#ext");
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                _layerIndex = layer.load('数据提交中...');
                $("#frmMain").ajaxSubmit({
                    url: ROOT + '/advertisement/project_notic_add',
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


</script>