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
var URL = '/admin.php/Statistics';
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
        <div class="title">渠道管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <?php if(checkAuth('Admin/statistics/channel_add') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" id="" name="add" value="新增" style="color:green;" onclick="add()" class="add imgButton"></div><?php endif; ?>
        </div>
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="9" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="8px"><input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"></th>
                    <th width="50px">编号</th>
                    <th width="200px">渠道名称</th>
                    <th width="200px">渠道标识</th>
                    <th width="300px">地址</th>
                    <th width="*">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["cons_value"]); if(($item["new_preferential"]) == "1"): ?><span style="color:red;">[新人特惠]</span><?php endif; ?></td>
                        <td><?php echo ($item["cons_key"]); ?></td>
                        <td><notempty><a href="<?php echo ($item["cons_desc"]); ?>" target="_blank"><?php echo ($item["cons_desc"]); ?></a></notempty></td>
                        <td>
                            <?php if(checkAuth('Admin/statistics/channel_edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/statistics/channel_delete') == true): ?><a href="javascript:;" style="color:red;" onclick="deletes(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="9" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page"><?php echo ($show); ?></div>
        <!-- 列表显示区域结束 -->
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<script>
    var _page = "<?php echo ($params["page"]); ?>";
    
    function deletes(_id){
    	
    	layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('正在删除中...');
            $.post(ROOT + "/statistics/channel_delete", {id: _id}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('删除成功~!', -1, function(){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info);
                }
            });
        });
    	
        //layer.alert('暂不开放');
    }
</script>
<script type="application/javascript" src="<?php echo C('STATIC_ADMIN');?>/js/statistics_channel.js"></script>