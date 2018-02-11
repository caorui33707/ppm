<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<script type="text/javascript" src="/Public/admin/auth/js/common.js"></script>
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">退汇管理列表</div>
        <!--<div class="title">日销售额 <a href="<?php echo C('ADMIN_ROOT');?>/project/daysales_export/dt/<?php echo ($datetime); ?>" target="_blank">导出Excel</a>&nbsp;&nbsp;日销售额宝付对账 <a href="<?php echo C('ADMIN_ROOT');?>/project/daysales_lianlian_export/dt/<?php echo ($datetime); ?>" target="_blank">导出Excel</a></div>-->
        <!--&lt;!&ndash;  功能操作区域  &ndash;&gt;-->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <label for="dt">开始时间：<input type="text" class="laydate-icon" name="dt" id="dt" value="<?php echo ($datetime); ?>" readonly /></label>
                        <!--<label for="flushcache"><input type="checkbox" id="flushcache" name="flushcache" value="1" />更新缓存</label>-->
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
                    <div class="impBtn hMargin fLeft shadow" onclick="return refund_notify_All()" ><input type="button" value="批量退汇" class="search imgButton"></div>
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
                    <td height="5" colspan="14" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="8px"><input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"></th>
                    <th width="100px" align="center">用户姓名</th>
                    <th width="150px" align="center">手机号</th>
                    <th width="150px" align="center">订单号</th>
                    <th width="200px" align="center">平台客户编号</th>
                    <th width="150px" align="center">退汇金额（元）</th>
                    <th width="100px" align="center">状态</th>
                    <th width="150px" align="center">最近操作时间</th>
                    <th width="*" align="center">操作</th>
                </tr>


                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                        <td><?php echo ($item["real_name"]); ?></td>
                        <td><?php echo ($item["mobile"]); ?></td>
                        <td align="right"><?php echo ($item["order_no"]); ?></td>
                        <td align="left">
                            <?php echo ($item["plat_no"]); ?>
                        </td>
                        <td align="left">
                            <?php echo ($item["amt"]); ?>
                        </td>
                        <td align="center">
                            <?php switch($item["refund_status"]): case "1": ?>等待退汇<?php break;?>
                                <?php case "2": ?>已退汇<?php break; endswitch;?>
                        </td>
                        <td align="center">
                            <?php if(($item["update_time"]) == ""): endif; ?>
                            <!--<?php if(item.update_time < 0): echo (strtotime($item["update_time"])); endif; ?>-->
                            <?php if(($item["update_time"]) > "0"): echo (date('Y-m-d H:i:s',$item["update_time"])); endif; ?>
                        </td>
                        <td align="center">
                            <?php if(($item["refund_status"]) == "1"): ?><a href="javascript:;" data-id="<?php echo ($item["id"]); ?>" onclick="return refund_notify_save($(this))">退汇</a><?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>

                <tr>
                    <td height="5" colspan="14" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<script>
    var _layerIndex = 0;
    var _dt = "<?php echo ($datetime); ?>";

    var datetime = {
        elem: '#dt',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true
    };
    laydate(datetime);

    function update_remark(_id){
        var _remark = prompt('请输入备注信息:');
        if(_remark != '' && _remark != null){
            _layerIndex = layer.load('操作中...');
            $.post(ROOT + "/project/update_project_remark", {id: _id, dt: _dt, remark: _remark}, function(msg){
                if(msg.status){
                    $("#remark_" + _id).text(_remark);
                    layer.msg('更新成功~', 1, -1);

                }else{
                    layer.alert(msg.info, -1);
                }
            });
        }
    }

    function refund_notify_save(_obj){
        var id = _obj.attr('data-id');
        var idArr = new Array(id);
        _layerIndex = layer.load('操作中...');
        $.post(ROOT + '/project/refund_notify_save',{idArr:idArr},function(msg){
            if(msg.status){
                //$("#remark_" + _id).text(_remark);
                layer.msg('更新成功~', 1, -1);

                window.location.href=location.href;
            }else{
                layer.alert(msg.info, -1);
                return false;
            }
        });
    }

    function refund_notify_All(){ // 所有退汇
        var items = $("#checkList tbody").find("input[type=checkbox]:checked").not("#check");
        if(items.length > 0){
            layer.confirm('确定要退汇选择的数据吗?', function(){
                var _ids = '';
                var _idsArr = new Array();

                $.each(items, function(i, n){
                    //_ids += ',' + $(n).attr('alt');
                    _idsArr[i] = $(n).attr('alt');
                });
                //if(_ids) _ids = _ids.substr(1);

                //alert(_ids);

                _layerIndex = layer.load('操作中...');
                $.post(ROOT + '/project/refund_notify_save', {idArr: _idsArr}, function(msg){
                    layer.close(_layerIndex);
                    if(msg.status){
                        layer.alert('更新成功~!', -1, function(){
                            window.location.reload();
                        });
                    }else{
                        layer.alert(msg.info);
                    }
                });
            });
        }else{
            layer.alert('请选择要退汇的项');
        }
    }

</script>