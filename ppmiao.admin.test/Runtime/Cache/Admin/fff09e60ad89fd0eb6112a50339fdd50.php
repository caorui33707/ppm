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
var URL = '/admin.php/Message';
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
        <div class="title">推送消息管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
			<div class="impBtn hMargin fLeft shadow"><input type="button" name="add" value="新增" onclick="add()" class="add imgButton"></div>
            <!-- 高级查询区域 -->
        </div>
        <?php $btnSelect1 = $btnSelect2 = $btnSelect3 = $btnSelect4 = ''; if($status==-1){ $btnSelect1 = 'btnSelect'; }elseif($status==0){ $btnSelect2 = 'btnSelect'; }elseif($status==1){ $btnSelect3 = 'btnSelect'; }elseif($status==2){ $btnSelect4 = 'btnSelect'; } ?>

        <div class="operate" >
            <!-- 选择区域 -->
            <div class="impBtn hMargin fLeft shadow"  ><input type="button" name="select" value="全部"   onclick="selectUrl($(this))" data-status ="-1"  class=" bb add imgButton <?php echo ($btnSelect1); ?>"></div>
            <div class="impBtn hMargin fLeft shadow"  ><input type="button" name="select" value="待推送" onclick="selectUrl($(this))" data-status ="0" class="add imgButton <?php echo ($btnSelect2); ?>"></div>
            <div class="impBtn hMargin fLeft shadow"><input type="button" name="select" value="推送中" onclick="selectUrl($(this))" data-status ="1" class="add imgButton <?php echo ($btnSelect3); ?>"></div>
            <div class="impBtn hMargin fLeft shadow"><input type="button" name="select" value="已推送" onclick="selectUrl($(this))" data-status ="2" class="add imgButton <?php echo ($btnSelect4); ?>"></div>
            <!-- 选择区域 -->
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
                    <th width="3%">编号</th>
                    <th width="20%">内容</th>
                    <th width="10%">定时推送时间</th>
                    <th width="10%">推送成功时间</th>
                    <th width="10%">推送对象</th>
                    <th width="10%">极光Id</th>
                    <th width="10%">平台</th>
                    <th width="10%">状态</th>
                    <th width="10%">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): if($item['done_time']){ $done_time = date('Y-m-d H:i:s',$item['done_time']); }else{ $done_time = ''; } ?>
                    <tr>
                        <td><?php echo ($item["id"]); ?></td>
                        <td>
                            <?php
 $text = json_encode($item['content']); $text = preg_replace_callback('/\\\\\\\\/i',function($str){ return '\\'; },$text); echo json_decode($text); ?>
                        </td>
                        <td><?php if($item['push_time'] && $item['push_type']==1){echo date('Y-m-d H:i:s',$item['push_time']);}else{echo '';} ?></td>
                        <td><?php if($item['done_time']){echo date('Y-m-d H:i:s',$item['done_time']);}else{echo '';} ?></td>
                        <td>                        
                        	<?php switch($item["target"]): case "0": ?>版本推送<?php break;?>
                        		<?php case "1": ?>个人推送<?php break;?>
                        		<?php case "2": ?>手机号推送<?php break; endswitch;?>
                        </td>
                        <td><?php echo ($item["registration_id"]); ?></td>
                        <td>

                            <?php if(($item["target"]) == "0"): if($item["android_ver"] != null AND $item["android_ver"] >= 0 ): if($item["android_ver"] == 0): ?>android: 全部/
                                    <?php else: ?>
                                        android:<?php echo ($item["android_ver"]); ?>/<?php endif; endif; ?>

                                <?php if($item["ios_ver"] != null AND $item["ios_ver"] >= 0 ): if($item["ios_ver"] == 0): ?>Ios: 全部
                                    <?php else: ?>
                                        Ios:<?php echo ($item["ios_ver"]); endif; endif; endif; ?>



                        </td>

                        <td>
                        	<?php switch($item["status"]): case "0": ?>待推送<?php break;?>
                        		<?php case "1": ?>推送中<?php break;?>
                        		<?php case "2": ?>已推送<?php break; endswitch;?>
                        </td>
                        <td>
                            <?php if($item["status"] == 0): ?><a href="javascript:;"  onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>
                                <a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>
                            <?php elseif($item["status"] == 1): ?>
                                <!--<if condition="checkAuth('Admin/message/comment_delete') eq true"><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>-->
                            <?php elseif($item["status"] == 2): ?>
                                <a href="javascript:;"  onclick="content(<?php echo ($item["id"]); ?>)">详情</a>
                            <?php else: endif; ?>
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

    function selectUrl(obj){
        var status = obj.attr('data-status');
        window.location.href = ROOT + "/Message/msg_push_index?status=" + status;
    }

    function add(){
        window.location.href = ROOT + "/Message/msg_push_add";
    }

    function edit(_id){
        window.location.href = ROOT + "/Message/msg_push_edit/id/"+ _id;
    }
    
    function content(_id) {
        window.location.href = ROOT + "/Message/msg_push_content/id/"+ _id;
    }
    
    function del(_id){
        layer.confirm('确认删除吗?', function(){
            _layerIndex = layer.load('正在删除中...');
            $.post(ROOT + "/message/msg_push_delete", {id: _id}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    window.location.reload();
                }else{
                    layer.msg(msg.info, 1, -1);
                }
            });
        });
    }

</script>