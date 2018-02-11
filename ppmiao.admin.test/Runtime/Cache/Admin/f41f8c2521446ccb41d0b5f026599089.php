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
var URL = '/admin.php/Advertisement';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<style>
    .medium_key {
        width: 230px;
    }
</style>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">广告管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <div class="impBtn hMargin fLeft shadow"><input type="button" name="add" value="发布" onclick="add()" class="add imgButton"></div>
            <!-- 查询区域 -->
        	<div class="fRig">
                <form method='get' action="">
                    <div class="fLeft">
                       	<select name="userType">
                       		<option value ="" <?php if(($userType) == ""): ?>selected<?php endif; ?>>所有用户组</option>
  							<option value ="1" <?php if(($userType) == "1"): ?>selected<?php endif; ?>>老用户</option>
  							<option value ="2" <?php if(($userType) == "2"): ?>selected<?php endif; ?>>新用户</option>
                       	</select>
                        <span><input type="text" name="userKey" placeholder="输入关键字" class="medium_key" value="<?php echo ($params["userKey"]); ?>"></span>
                    </div>

                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" value="搜索" class="search imgButton">
                    </div>
                </form>
                
            </div>
        </div>
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="15" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="10px"><input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"></th>
                    <th width="5%">编号</th>
                    <th width="12%" align="center">图片</th>
                    <th width="10%" align="center">广告位</th>
                    <th width="8%" align="center">状态</th>
                    <!--<th width="12%" align="center">发表时间</th>-->
                    <th width="12%" align="center">上架时间</th>
                    <th width="12%" align="center">下架时间</th>
                    <th width="15%" align="center">标题</th>
                    <th width="8%" align="center">是否新用户</th>
                    <th width="8%" align="center">新用户排序</th>
                    <th width="8%" align="center">是否老用户</th>
                    <th width="8%" align="center">老用户排序</th>
                    <!--<th width="8%" align="center">是否大banner</th>-->
                    <th width="6%" align="center">是否活动</th>
                    <!--<th width="6%" align="center">分享Id</th>-->
                    <th width="12%" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td></td>
                        <td><?php echo ($item["id"]); ?></td>
                        <td>
                        	<a href="<?php echo C('OSS_STATIC_ROOT');?>/Uploads/focus/<?php echo ($item["image"]); ?>" target="_blank">
                        		<img src="<?php echo C('OSS_STATIC_ROOT');?>/Uploads/focus/<?php echo ($item["image"]); ?>" style="max-height: 100px;max-width: 100px;" />
                        	</a>
                        </td>
                        <td><?php echo ($item["positionStr"]); ?></td>
                        <td align="center"><?php switch($item["status"]): case "1": ?><span style="color:red;"><?php break; case "2": ?><span style="color:green;"><?php break; case "3": ?><span style="color:gray;"><?php break; endswitch; echo ($item["statusStr"]); ?><span></td>
                        <!--<td v><?php echo (date('Y-m-d H:i:s',strtotime($item["add_time"]))); ?></td>-->
                        <td v><?php echo (date('Y-m-d H:i:s',strtotime($item["start_time"]))); ?></td>
                        <td v><?php echo (date('Y-m-d H:i:s',strtotime($item["end_time"]))); ?></td>
                        <td><?php echo ($item["title"]); ?></td>
                        
						<td>
							<?php if(($item["new_user"]) == "1"): ?>是<?php else: ?>否<?php endif; ?>
						</td>
						<td><?php echo ($item["new_rank"]); ?></td>
						<td><?php if(($item["old_user"]) == "1"): ?>是<?php else: ?>否<?php endif; ?></td>
						<td><?php echo ($item["old_rank"]); ?></td>
						
						
						<!--<td align="center"><?php switch($item["big_banner"]): case "0": ?>否<?php break; case "1": ?>是<?php break; endswitch;?></td>-->
						
						<td align="center">
                        	<?php switch($item["is_activity"]): case "0": ?>否<?php break;?>
                        		<?php case "1": ?>是<?php break; endswitch;?>
						</td>
						<!--<td><?php echo ($item["share_id"]); ?></td>-->
                        <td align="center">
                            <?php if(checkAuth('Admin/advertisement/edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/advertisement/delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="15" class="bottomTd"></td>
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
        window.location.href = ROOT + "/advertisement/add";
    }
    function edit(_id) {
        window.location.href = ROOT + "/advertisement/edit/id/" + _id + "/p/" + _page;
    }
    function del(_id){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('数据删除中...');
            $.post(ROOT + "/advertisement/delete", {id: _id}, function(msg){
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