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
var URL = '/admin.php/Auth';
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
        <div class="title">权限组管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <?php if(checkAuth('Admin/auth/group_add') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" name="add" value="新增" onclick="add()" class="add imgButton"></div><?php endif; ?>
        </div>
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="6" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="8px">&nbsp;</th>
                    <th width="50px">编号</th>
                    <th width="200px">权限组名称</th>
                    <th width="200px">权限组描述</th>
                    <th width="80px">状态</th>
                    <th width="*">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td></td>
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["title"]); ?></td>
                        <td><?php echo ($item["description"]); ?></td>
                        <td><?php if(($item["status"]) == "1"): ?><span style="color:green;">正常</span><?php else: ?><span style="color:red;">禁用</span><?php endif; ?></td>
                        <td>
                            <?php if(checkAuth('Admin/auth/group_edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="6" class="bottomTd"></td>
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
    function add(){
        window.location.href = ROOT + '/auth/group_add/p/' + _page;
    }
    function edit(_id){
        window.location.href = ROOT + '/auth/group_edit/id/' + _id + '/p/' + _page;
    }
</script>