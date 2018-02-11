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
var URL = '/admin.php/Interest';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<script type="text/javascript" src="/Public/admin/layer3/layer.js"></script>

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">财务审核</div>

        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->

            <div class="fLeft">
                付息时间
                <input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($start_time); ?>" readonly />
            </div>
            <div class="fLeft">
                <select name="fid" id="fid">
                    <option name="0">全部融资方</option>
                    <?php if(is_array($flist)): foreach($flist as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>"><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>
                </select>
            </div>
            <div class="impBtn hMargin fLeft shadow"><input type="button" onclick="loadDataByDate()" value="搜索" class="search imgButton">
                <!--
                &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="toWallet()">批量转入钱包</a>
                 -->
                &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="rangeCheck()">批量审核</a>
                <?php if(checkAuth('Admin/interest/repay_review_export') == true): ?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" id="exportExcel">导出Excel</a><?php endif; ?>
            </div>


            <div class="fRig">

            </div>
        </div>
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <td height="5" colspan="10" class="topTd"></td>
                    </tr>
                    <tr class="row">
                        <th width="20px" align="center"><input id="checkAll" onclick="checkOrUnCheck()" type="checkbox"></th>
                        <th width="200px" align="center">产品名称</th>
                        <th width="100px" align="center">付息时间</th>
                        <th width="100px" align="center">总额</th>
                        <th width="100px" align="center">支付利息</th>
                        <th width="150px" align="center">支付本金</th>
                        <th width="100px" align="center">融资方</th>
                        <th width="100px" align="center">标的状态</th>
                        <th width="*" align="center">操作</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <tr>
                        <td height="5" colspan="10" class="bottomTd"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<script>
    var _layerIndex = 0;
    var _layerIndex2 = 0;
    var _force = "<?php echo ($force); ?>";
    $(document).ready(function(){
        loadData();
    });
    function loadData(){
        var date = $('#start_time').val();
        _layerIndex = layer.load('正在加载数据...');
        $.post(ROOT + "/interest/repay_review", {force: _force,date:date}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                $("#checkList tbody").html(msg.info);
            }else{
                layer.alert(msg.info, -1);
            }
        });
    }
    function loadDataByDate(){
        var date = $('#start_time').val();
        var fid = $('#fid').val();
        _layerIndex = layer.load('正在加载数据...');
        $.post(ROOT + "/interest/repay_review", {force: _force,date:date,fid:fid}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                $('#checkAll').attr("checked", false);
                $("#checkList tbody").html(msg.info);
            }else{
                layer.alert(msg.info, -1);
            }
        });
    }



    function checkOrUnCheck(){
        $('.input_users').each(function(){
//            console.log(1);
            console.log($(this).attr("checked"));

            if($(this).attr("checked") == 'checked'){
                $(this).attr("checked", false);
            } else {
                $(this).attr("checked", true);
            }

        })
    }


    function rangeCheck(){
        layer.confirm('确定批量审核操作吗?', function(){

            var chk_value =[];
            $(".to_bank:checkbox:checked").each(function(){
                chk_value.push($(this).data('projectid'));
            });
            if(chk_value.length==0){
                alert('你还没有选择任何内容！')
                return
            }

            var check_string = chk_value.join(":");
            _layerIndex = layer.load('数据提交中...');
            $.post('<?php echo C("ADMIN_ROOT");?>/interest/range_review', {check_string:check_string}, function(msg){
                layer.close(_layerIndex);
                if(msg.status == 1){
                    layer.alert(msg.info, -1,function (){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info, -1);
                }
//                layer.close(_layerIndex);
//                if(msg.status){
//                    if(msg.info){
//                        layer.alert("『" + _action + "』操作成功~!<br>" + msg.info, {
//                            skin: 'layui-layer-molv' //样式类名
//                            ,closeBtn: 0
//                        }, function(){
//                            loadData();
//                        });
//
//                    }else{
//                        layer.alert('『' + _action + '』操作成功!~');
//                        loadData();
//                    }
//                }else{
//                    layer.alert(msg.info);
//                }
            });
        });
    }

    function review(_pid, _rid, _status2, _action){
        layer.confirm('确定执行『' + _action + '』操作吗?', function(){
            _layerIndex = layer.load('数据提交中...');
            $.post('<?php echo C("ADMIN_ROOT");?>/interest/review', {id: _pid, rid: _rid, status: _status2}, function(msg){
                layer.close(_layerIndex);
                if(msg.status == 1){
                    layer.alert(msg.info, -1,function (){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info, -1);
                }
//                layer.close(_layerIndex);
//                if(msg.status){
//                    if(msg.info){
//                        layer.alert("『" + _action + "』操作成功~!<br>" + msg.info, {
//                            skin: 'layui-layer-molv' //样式类名
//                            ,closeBtn: 0
//                        }, function(){
//                            loadData();
//                        });
//
//                    }else{
//                        layer.alert('『' + _action + '』操作成功!~');
//                        loadData();
//                    }
//                }else{
//                    layer.alert(msg.info);
//                }
            });
        });
    }
    function buylist(_pid, _rid){
        window.open(ROOT + "/interest/review_buylist/id/" + _pid + '/rid/' + _rid, 'buylist', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }


    var _start_time = "<?php echo ($start_time); ?>";
    //var _end_time = "<?php echo ($params["end_time"]); ?>";



    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00', //设定最小日期为当前日期
        max: '2099-06-16', //最大日期
        istime: false,
        istoday: true,
    };

    laydate(start);
    
    $("#exportExcel").click(function(){
    	var fid = $("#fid").val();
    	var date = $("#start_time").val();
    	location.href = ROOT + "/interest/repay_review_export/fid/"+fid+"/date/"+date;  
    })
</script>