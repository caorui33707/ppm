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
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">担保机构管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
            	<?php if(checkAuth('Admin/Guaranty/edit') == true): ?><input type="button" value="添加机构" onclick="edit_redirect()" class="search imgButton" style="width:150px;" /><?php endif; ?>
            </div>
        </div>
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="10" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="5%">编号</th>
                    <th width="20%">担保机构名称</th>
                    <th width="20%">状态</th>
					<th width="10%">担保中</th>
					<th width="10%">已代偿</th>
					<th width="10%">融资方已还款</th>
					<th width="10%">添加时间</th>
                    <th width="10%">操作</th>
                </tr>
                <?php $temp = 1;?>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td><?php echo ($temp++); ?></td>
                        <td><?php echo ($item["name"]); ?></td>
                        <td>

                            <?php
 if($item['status']==0){ echo '初始'; } elseif($item['status']==1){ echo '平台审核中'; }elseif($item['status']==2){ echo '平台审核拒绝'; } elseif($item['status']==3){ echo '审核通过'; } elseif($item['status']==4){ echo '禁用'; } elseif($item['status']==5){ echo '银行审核拒绝'; } elseif($item['status']==6){ echo '银行审核中'; } elseif($item['status']==7){ echo '开户失败'; } elseif($item['status']==8){ echo '绑卡失败'; }else{ echo ''; } ?>


                        </td>
                        <td><?php echo ($item["project_status_2"]); ?></td>
                        <td><?php echo ($item["project_status_6"]); ?></td>
						<td><?php echo ($item["project_status_5"]); ?></td>
                        <td><?php echo ($item["add_time"]); ?></td>
                        <td>
                        
                        	<?php if(checkAuth('Admin/Guaranty/edit') == true): ?><a href="javascript:;" onclick="rate_edit_redirect(<?php echo ($item["id"]); ?>)">编辑</a>
                        	&nbsp;&nbsp;<?php endif; ?>
                        	
                        	<?php if(checkAuth('Admin/Guaranty/del') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a><?php endif; ?>
                        	
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="10" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page">
            <?php echo ($show); ?>
        </div>
        <!-- 列表显示区域结束 -->
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
    
    function edit_redirect(){
        var _redirect_url = ROOT +"/Guaranty/edit";
        window.location.href =_redirect_url;
    }
    function rate_edit_redirect(id){
        if(id){
            var _rate_edit_redirect_url = ROOT +"/Guaranty/edit/id/"+id;
            window.location.href =_rate_edit_redirect_url;
        }
    }
    
    function del(_id){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('数据删除中...');
            $.post(ROOT + "/Guaranty/del", {id: _id}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    window.location.reload();
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        });
    }
</script>