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
var URL = '/admin.php/ProjectGroupTag';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <div class="content">
        <div class="title">编辑Tab标签 [ <a href="<?php echo C('ADMIN_ROOT');?>/projectGroupTag/index/p/<?php echo ($params["page"]); ?>">返回列表</a> ]</div>
        <div id="result" class="result none"></div>
        <script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
        <form id="frmMain" method='post' action="">
            <input type="hidden" name="id" value="<?php echo ($detail["id"]); ?>" />
            <input type="hidden" name="page" value="<?php echo ($params["page"]); ?>" />
            <table cellpadding=3 cellspacing=3 >
                <tr>
                    <td class="tRight">标签名称：</td>
                    <td class="tLeft" ><input type="text" class="huge bLeftRequire" datatype="*" id="tag" name="tag" value="<?php echo ($detail["tag"]); ?>"></td>
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
                    url: ROOT + '/projectGroupTag/edit',
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