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
var URL = '/admin.php/Vip';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<script type="text/javascript" src="/Public/admin/layer-v2.4/layer.js"></script>

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">会员vip等级配置</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <div class="impBtn hMargin fLeft shadow">
            <?php if(checkAuth('Admin/vip/level_add') == true): ?><input type="button" name="add" value="添加" onclick="add()" class="add imgButton"><?php endif; ?>
            
            <?php if(checkAuth('Admin/vip/mission_add') == true): ?><input type="button" name="add" value="VIP任务添加" onclick="mission_add()" class="imgButton" style="width:180px;"><?php endif; ?>
            
            </div>
            <!-- 查询区域 -->
            <!-- 高级查询区域 -->
        </div>
        <!-- 功能操作区域结束 -->
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="8" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="5%">编号</th>
                    <th width="15%" align="center">vip等级</th>
                    <th width="15%" align="center">vip等级昵称</th>
                    <th width="15%" align="center">升至下一级所需成长值</th>
                    <th width="15%" align="center">已添加升级任务个数</th>
                    <th width="15%" align="center">等级人数</th>
                    <th width="20%" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["name"]); ?></td>
                        <td><?php echo ($item["mission_name"]); ?></td>
                        <td><?php echo ($item["grow_val"]); ?></td>
                        <td><?php echo ($item["mission_count"]); ?></td>
                        <td><?php echo ($item["people"]); ?></td>
                        <td align="center">
                            <a href="###" onclick="show_mission_list(<?php echo ($item["level"]); ?>,'<?php echo ($item["name"]); ?>')">查看任务列表</a>&nbsp;
                        	<!-- <a href="<?php echo C('ADMIN_ROOT');?>/vip/user_index/level_id/<?php echo ($item["level"]); ?>" target="_blank">用户列表</a>&nbsp; -->
                            <?php if(checkAuth('Admin/vip/level_edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/vip/level_delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>,<?php echo ($item["level"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="8" class="bottomTd"></td>
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
    var _page = "<?php echo ($params["page"]); ?>";
    var _layerIndex = 0;
    function add(){
        window.location.href = ROOT + "/vip/level_add";
    }
    
    function mission_add(){
    	window.location.href = ROOT + "/vip/mission_add";
    }
    
    function edit(_id) {
        window.location.href = ROOT + "/vip/level_edit/id/" + _id + "/p/" + _page;
    }
    function del(_id,_level){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('数据删除中...');
            $.post(ROOT + "/vip/level_delete", {id: _id,level_id:_level}, function(msg){  
                layer.close(_layerIndex);
                if(msg.status){
                    window.location.reload();
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        });
    }
    
	function show_mission_list(_id,_title){
		
		window.location.href = ROOT + '/vip/mission_index/level_id/'+_id;
		return;
		layer.open({
  			type: 2,
  		  	skin: 'layui-layer-rim', //加上边框
  		  	area: ['960px', '480px'], //宽高
  		  	title: ''+_title,
  		  	maxmin: true,
  		  	content: '<?php echo C('ADMIN_ROOT');?>/vip/mission_index/level_id/'+_id,
  		  	cancel: function(index){
  		  		//layer.close(index);
  		  		//window.location.reload();
  		  	}
  		  });
	}

</script>