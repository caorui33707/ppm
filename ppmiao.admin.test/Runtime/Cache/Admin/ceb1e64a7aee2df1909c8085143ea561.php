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
var URL = '/admin.php/Project';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->

<style type="text/css">
    .my_red_class {
        color: red;
    }
</style>

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">银行卡管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <?php if(checkAuth('Admin/project/bank_add') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" name="add" value="新增" onclick="add()" class="add imgButton"></div><?php endif; ?>
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <span><input type="text" name="key" placeholder="输入银行卡编号/名称" class="medium" value="<?php echo ($params["key"]); ?>"></span>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
                </form>
            </div>
            <!-- 高级查询区域 -->
            <div  id="searchM" class=" none search cBoth" >
            </div>
        </div>
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="12" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="8px">&nbsp;</th>
                    <th width="50px">编号</th>
                    <th width="120px">银行编号</th>
                    <th width="200px">银行名称</th>
                    <th width="200px">支持范围</th>
                    <th width="80px">卡种</th>
                    <th width="80px">状态</th>
                    <th width="80px">单次限额</th>
                    <th width="80px">单日限额</th>
                    <th width="80px">单月限额</th>
                    <th width="80px">图片(小)</th>
                    <th width="80px">图片</th>
                    <th width="*">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td></td>
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["bank_code"]); ?></td>
                        <td><?php echo ($item["bank_name"]); ?></td>
                        <td><?php echo ($item["support_area"]); ?></td>
                        <td><?php echo ($item["support_card_type"]); ?></td>
                        <td align="center">
                            <?php if($item['status'] == 1): ?>正常
                            <?php elseif($item['status'] == 2): ?>
                                <span class="my_red_class">维护中</span>
                            <?php else: ?> 其他<?php endif; ?>
                        </td>
                        <td align="center"><?php echo ($item['limit_times'] ? $item['limit_times'] : '-'); ?></td>
                        <td align="center"><?php echo ($item['limit_day'] ? $item['limit_day'] : '-'); ?></td>
                        <td align="center"><?php echo ($item['limit_month'] ? $item['limit_month'] : '-'); ?></td>
                        <td align="right"><img src="<?php echo C('OSS_STATIC_ROOT');?>/Uploads/focus/<?php echo ($item["small_icon"]); ?>" style="max-height: 100px;max-width: 100px;" /></td>
                        <td align="right"><img src="<?php echo C('OSS_STATIC_ROOT');?>/Uploads/focus/<?php echo ($item["icon"]); ?>" style="max-height: 100px;max-width: 100px;" /></td>
                        
                        <td>
                            <?php if(checkAuth('Admin/project/bank_edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/project/bank_delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="12" class="bottomTd"></td>
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
    var _key = "<?php echo ($params["key"]); ?>";
    var _layerIndex = 0;
    function add(){
        window.location.href = ROOT + '/project/bank_add/p/' + _page + (_key != '' ? '/key/' + _key : '');
    }
    function edit(_id){
        window.location.href = ROOT + '/project/bank_edit/id/' + _id + '/p/' + _page + (_key != '' ? '/key/' + _key : '');
    }
    function del(_id){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('正在删除中...');
            $.post(ROOT + "/project/bank_delete", {id: _id}, function(msg){
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
</script>