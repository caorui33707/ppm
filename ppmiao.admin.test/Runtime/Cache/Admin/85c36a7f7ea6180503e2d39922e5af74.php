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
var URL = '/admin.php/Member';
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
        <div class="title">生日礼包</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <div class="fRig">
                <form method='get' action="<?php echo C("ADMIN_ROOT");?>/Member/birthday">
                <div class="fLeft">
                    <span><input type="text" id="key" name="title" placeholder="名称" class="medium" value="<?php echo ($params["title"]); ?>"></span>

                </div>
                <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
                </form>

            </div>
            <div class="impBtn hMargin fLeft shadow"><input type="button" id="" name="add" value="新增" style="color:green;" onclick="add()" class="add imgButton"></div>

        </div>
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="10" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="3%" align="center">编号</th>
                    <th width="5%" align="center">名称</th>
                    <th width="5%" align="center">适用等级</th>
                    <th width="5%" align="center">奖品名称</th>
                    <th width="3%" align="center">奖品类型</th>
                    <th width="8%" align="center">已发放数</th>
                    <th width="8%" align="center">已使用数</th>
                    <th width="8%" align="center">添加时间</th>
                    <th width="8%" align="center">状态</th>
                    <th width="8%" align="center">操作</th>
                </tr>
                <?php $temp = 1;?>
                <?php if(is_array($commodities)): foreach($commodities as $key=>$item): ?><tr>
                        <td align="center"><?php echo ($temp++); ?></td>
                        <td><?php echo ($item["title"]); ?></td>
                        <td align="center"><?php echo ($item["vip_name"]); ?></td>
                        <td><?php echo ($item["commodity"]["name"]); ?></td>
                        <td align="center">
                            <?php switch($item["commodity"]["type"]): case "1": ?><span style="color:red;">红包<?php break;?>
                                <?php case "2": ?><span style="color:green;">加息券<?php break;?>
                                <?php case "3": ?><span style="color:blue;">现金券<?php break;?>
                                <?php case "4": ?><span style="color:yellow;">第三方券<?php break;?>
                                <?php case "5": ?><span style="color:gray;">实物<?php break; endswitch;?>
                        </td>
                        <td align="center">
                            <?php echo ($item["total"]); ?>
                        </td>
                        <td align="center">
                            <?php echo ($item["use_count"]); ?>
                        </td>
                        <td align="center">
                            <?php echo ($item["add_time"]); ?>
                        </td>
                        <td align="center">
                            <?php if(($item["status"]) == "1"): ?><a href="javascript:;" style="color:green;" onclick="changeStatus(<?php echo ($item["id"]); ?>)">上架</a>
                                <?php else: ?>
                                <a href="javascript:;" style="color:red;" onclick="changeStatus(<?php echo ($item["id"]); ?>)">下架</a><?php endif; ?>
                        </td>
                        <td align="center">
                            <a href="javascript:;" style="color:blue;" onclick="show(<?php echo ($item["commodity"]["type"]); ?>,'<?php echo ($item["source"]); ?>','<?php echo ($item["title"]); ?>',<?php echo ($item["id"]); ?>)">使用记录</a>&nbsp;
                            <a href="javascript:;" style="color:red;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;
                            <a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="10" class="bottomTd"></td>
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

    var _layerIndex = 0;
    var _page = "<?php echo ($params["page"]); ?>";
    var _type = "<?php echo ($params["type"]); ?>";

    function add(){
        window.location.href = ROOT + "/member/birthday_add";
    }

    function show(type,source,name,id){
        if(type == 2){
            window.open(ROOT + "/member/commodities_use_show/title/"+name+"/source/"+source+"/model/UserInterestCoupon");
        }else if(type == 1){
            window.open(ROOT + "/member/commodities_use_show/title/"+name+"/source/"+source+"/model/UserRedenvelope");
        }else if(type == 3){
            window.open(ROOT + "/member/commodities_use_show/title/"+name+"/source/"+source+"/model/UserCashCoupon");
        } else {
            window.open(ROOT + "/member/commodities_use_show/title/"+name+"/source/"+id+"/model/MemberBirthdayUser");
        }
    }
    function edit(_id){
        window.location.href = ROOT + "/member/birthday_edit/id/" + _id + '/p/' + _page + '/type/' + _type;
    }
    function del(_id){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('数据删除中...');
            var model = 'MemberBirthday';
            $.post(ROOT + "/member/delete", {model:model,id: _id}, function(msg){
                layer.close(_layerIndex);
                if(!msg.status){
                    layer.alert(msg.info, -1,function (){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        });
    }
    function changeStatus(_id){
        layer.confirm('确定改变状态吗?', function(){
            _layerIndex = layer.load('状态改变中...');
            var model = "MemberBirthday";
            $.post(ROOT + "/member/update", {model : model,id: _id}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('改变状态成功~', -1, function(){
                        window.location.reload();
                    })
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        });
    }
</script>