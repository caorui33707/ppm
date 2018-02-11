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

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">产品公告管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <div class="impBtn hMargin fLeft shadow"><input type="button" name="add" value="发布" onclick="add()" class="add imgButton"></div>
            <!-- 查询区域 -->
        	<div class="fRig">
                <form method='get' action="">
                    <div class="fLeft">
                       	<select name="tag">
                       		<option value="-1" <?php if(($tag) == "-1"): ?>selected<?php endif; ?>>全部</option>
                            <!--<?php if(is_array($params["tag_list"])): foreach($params["tag_list"] as $key=>$item): ?>-->
                                <!--<option value="<?php echo ($key); ?>" tag_type = "1" <?php if(($params["tag_type"] == 1 ) AND ($params["tag"] == $key) ): ?>selected<?php endif; ?> > <?php echo ($item); ?></option>-->
                            <!--<?php endforeach; endif; ?>-->

                            <?php if(is_array($tags)): foreach($tags as $key=>$tg): ?><option value="<?php echo ($tg["id"]); ?>"  <?php if( ($params["tag"] == $tg["id"] ) ): ?>selected<?php endif; ?> ><?php echo ($tg["tag_title"]); ?></option><?php endforeach; endif; ?>
                       	</select>
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                        <input type="hidden" name="tag_type" >
                    	<input type="submit" value="搜索" class="search imgButton" onclick="searchTag()">
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
                    <td height="5" colspan="7" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="5%">编号</th>
                    <th width="6%" align="center">产品公告标签</th>
                    <th width="8%" align="center">状态</th>
                    <th width="20%" align="center">摘要</th>
                    <th width="10%" align="center">开始时间</th>
                    <th width="10%" align="center">结束时间</th>
                    <th width="8%" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td><?php echo ($item["id"]); ?></td>

                        <?php $tag_id = $item['tag_id']; foreach($tags as $t){ $id = $t['id']; $tagsArr[$id] = $t['tag_title']; } $tagTitle = isset($tagsArr[$tag_id])?trim($tagsArr[$tag_id]):'普通标'; ?>
                        <td>
                            <?php echo ($tagTitle); ?>
                        </td>
						<td align="center">
							<?php switch($item["status"]): case "1": ?><span style="color:red;"><?php break;?>
								<?php case "2": ?><span style="color:green;"><?php break;?>
								<?php case "3": ?><span style="color:gray;"><?php break; endswitch; echo ($item["statusStr"]); ?>
						</td>
						
                        <td><?php echo ($item["summary"]); ?></td>
						
						<td>
							<?php if(!empty($item["start_time"])): echo (date('Y-m-d H:i:s',strtotime($item["start_time"]))); endif; ?>
						</td>
						<td>
							<?php if(!empty($item["end_time"])): echo (date('Y-m-d H:i:s',strtotime($item["end_time"]))); endif; ?>
						</td>						
                        <td align="center">
                            <?php if(checkAuth('Admin/advertisement/edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/advertisement/delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="7" class="bottomTd"></td>
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
        window.location.href = ROOT + "/advertisement/project_notic_add";
    }
    function edit(_id) {
        window.location.href = ROOT + "/advertisement/project_notic_edit/id/" + _id + "/p/" + _page;
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

    
    function searchTag() {
        var tag_type = $("select[name='tag'] option:selected").attr('tag_type');

        $("input[name='tag_type']").val(tag_type);
    }
</script>