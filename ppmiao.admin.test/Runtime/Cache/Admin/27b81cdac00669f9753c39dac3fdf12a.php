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
var URL = '/admin.php/UserFund';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">查询接口调试</div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <script type="text/javascript" src="/Public/admin/js/jquery.form.js"></script>
        <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
        <script type="text/javascript" src="/Public/admin/layer3/layer.js"></script>
        <style>
            pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
            .string { color: green; }
            .number { color: darkorange; }
            .boolean { color: blue; }
            .null { color: magenta; }
            .key { color: red; }
        </style>
        <form id="frmMain" method='post' action="">
            <table id cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">一、接口类型：</td>
                    <td class="tLeft" >
                        <select onchange="changeType(this)">
                            <?php if(is_array($params)): foreach($params as $key=>$item): ?><option value="<?php echo ($item["name"]); ?>" ><?php echo ($item["name"]); ?></option>
                                存管<?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">二、接口列表：</td>
                    <td class="tLeft" >
                        <select id="methods" onchange="changeParams(this)">
                            <?php if(is_array($params["0"]["methods"])): foreach($params["0"]["methods"] as $key=>$item): ?><option value="<?php echo ($item["sub_name"]); ?>" ><?php echo ($item["sub_name"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tRight">三、参数列表：</td>
                    <td class="tLeft" >
                    </td>
                </tr>
            </table>
            <table id="params_table">
                <tr>
                    <td>query_order_no:</td>
                    <td class="center">
                        <div style="width:85%;margin:5px">
                            <input type="text"style="width:320px;"  name="query_order_no" placeholder="订单号">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="center">
                        <div style="width:85%;margin:5px">
                            <input type="submit" value="执行"  class="button small">
                        </div>
                    </td>
                </tr>
                <input type="hidden" name="key" value="trade/orderinfo">
            </table>
        </form>
        <pre id="result">
        </pre>
    </div>
</div>
<script>
    var _layerIndex = 0;
    var _layerIndex2 = 0;
    var _action = 0;
    var _urlObj, _extObj;


    var _adding = false;

    $(document).ready(function(){
        _urlObj = $("#url");
        _extObj = $("#ext");
        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                _layerIndex = layer.load('请求中...');
                $("#frmMain").ajaxSubmit({
                    url: ROOT + '/UserFund/request',
                    type: "post",
                    dataType: "json",
                    success: function(msg){
                        layer.close(_layerIndex);
                        $('#result').html(syntaxHighlight(msg));
                    }
                });
                return false;
            }
        });
    });


    function changeType(_obj){
        _action = $(_obj).val();
        $("#methods").html("");
        $.get('<?php echo C("ADMIN_ROOT");?>/UserFund/select_search',{type: _action},function(msg){

            $("#methods").html(msg);
            changeParam($('#methods').val());
        });
    }

    function changeParams(_obj){
        var _methods = $(_obj).val();
        $.get('<?php echo C("ADMIN_ROOT");?>/UserFund/select_params',{methods: _methods},function(msg){

            $("#params_table").html(msg);
        });
    }
    function changeParam(_methods){
        $.get('<?php echo C("ADMIN_ROOT");?>/UserFund/select_params',{methods: _methods},function(msg){

            $("#params_table").html(msg);
        });
    }
    function syntaxHighlight(json) {
        if (typeof json != 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }


</script>