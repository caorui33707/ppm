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
var URL = '/admin.php/Lottery';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>


<script type="text/javascript" src="/Public/admin/layer-v2.4/layer.js"></script>

<!-- 菜单区域  -->

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">抽奖活动列表</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <?php if(checkAuth('Admin/lottery/lottery_base_add') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" name="add" value="新增" onclick="add()" class="add imgButton"></div><?php endif; ?>
        	<input type="button" value="刷新页面" onclick="window.location.reload();" class="add imgButton">
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
                    <th width="5%" align="center">编号</th>
                    <th width="12%" align="center">活动名称</th>
                    <th width="10%" align="center">开始时间</th>
                    <th width="10%" align="center">结束时间</th>
                    <th width="10%" align="center">已配条件/奖励(个数)</th>
                    <th width="6%" align="center">标签</th>
                    <th width="6%" align="center">状态</th>
                    <th width="14%" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr style="color:<?php echo ($item["color"]); ?>">
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["name"]); ?></td>
                        <td><?php echo (date('Y-m-d H:i:s',$item["start_time"])); ?></td>
                        <td><?php echo (date('Y-m-d H:i:s',$item["end_time"])); ?></td>
                        <td align="center"><?php echo ($item["cond"]); ?>/<?php echo ($item["award"]); ?></td>
                        <td>
                        	<?php switch($item["tag"]): case "0": ?>普通标<?php break;?>
                        		<?php case "2": ?>爆款<?php break;?>
                        		<?php case "3": ?>HOT<?php break;?>
                        		<?php case "5": ?>预售<?php break;?>
                        		<?php case "6": ?>活动<?php break;?>
                        		<?php case "8": ?>私人专享<?php break; endswitch;?>
                        </td>
                        <td><?php echo ($item["state"]); ?></td>
                        <td>
                        	<?php if(checkAuth('Admin/lottery/lottery_cond_index') == true): ?><a href="javascript:;" onclick="lottery_cond_index(<?php echo ($item["id"]); ?>,'<?php echo ($item["name"]); ?>')">条件管理</a>&nbsp;<?php endif; ?>
                        	<?php if(checkAuth('Admin/lottery/lottery_award_index') == true): ?><a href="javascript:;" onclick="lotter_award_index(<?php echo ($item["id"]); ?>,'<?php echo ($item["name"]); ?>')">奖励管理</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/lottery/lottery_base_edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/lottery/lottery_base_delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
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
    	
    	window.location.href = ROOT + '/lottery/lottery_base_add';
    	
    	/*
    	layer.open({
    		  type: 2,
    		  skin: 'layui-layer-rim', //加上边框
    		  area: ['520px', '440px'], //宽高
    		  title:'新增抽奖活动',
    		  content: '<?php echo C('ADMIN_ROOT');?>/lottery/lottery_base_add'
    		  });
    	
    	*/
    }
    
    function edit(_id){
        window.location.href = ROOT + '/lottery/lottery_base_edit/id/' + _id ;
    }
    function del(_id){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('正在删除中...');
            $.post(ROOT + "/lottery/lottery_base_delete", {id: _id}, function(msg){
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
    }
    
	function lottery_cond_index(_id,_title){
		layer.open({
  			type: 2,
  		  	skin: 'layui-layer-rim', //加上边框
  		  	area: ['620px', '480px'], //宽高
  		  	title: ''+_title,
  		  	content: '<?php echo C('ADMIN_ROOT');?>/lottery/lottery_cond_index/base_id/'+_id,
  		  	cancel: function(index){
  		  		//layer.close(index);
  		  		//window.location.reload();
  		  	}
  		  });
	}
	
	function lotter_award_index(_id,_title){
		layer.open({
  			type: 2,
  		  	skin: 'layui-layer-rim', //加上边框
  		  	area: ['880px', '560px'], //宽高
  		  	title: ''+_title,
  		  	maxmin: true,
  		  	content: '<?php echo C('ADMIN_ROOT');?>/lottery/lottery_award_index/base_id/'+_id,
  		  	cancel: function(index){
  		  		//layer.close(index);
  		  		//window.location.reload();
  		  	}
  		  });

	}
    
</script>