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
        <div class="title">付息列表<label style="color:blue;"><input type="checkbox" value="1" onchange="changeLL(this)" <?php if(($force) == "1t"): ?>checked<?php endif; ?> />强制1%手续费</label></div>

        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->

            <div class="fLeft">
                付息时间
                <input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($start_time); ?>" readonly />
            </div>
            <div class="impBtn hMargin fLeft shadow"><input type="button" onclick="loadDataByDate()" value="搜索" class="search imgButton">
            	<?php if(checkAuth('Admin/project/project_batchto_wallet') == true): ?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="toWallet()">批量转入</a><?php endif; ?>
                
                <!-- 
                &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="toBank()">批量转入银行卡</a>
                 -->
            </div>


            <div class="fRig">

            </div>
        </div>
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <td height="5" colspan="11" class="topTd"></td>
                    </tr>
                    <tr class="row">
                        <th width="20px" align="center"><input id="checkAll" onclick="checkOrUnCheck()" type="checkbox"></th>
                        <th width="200px" align="center">产品名称</th>
                        <th width="100px" align="center">付息时间</th>
                        <th width="100px" align="center">支付利息</th>
                        <th width="100px" align="center">超出利息</th>
                        <th width="150px" align="center">(实际)支付本金</th>
                        <th width="100px" align="center" style="color:green;">超出部分</th>
                        <th width="100px" align="center">幽灵账户</th>
                        <th width="100px" align="center">标的状态</th>
                        <th width="100px" align="center">还款状态</th>
                        <th width="*" align="center">操作</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <tr>
                        <td height="5" colspan="11" class="bottomTd"></td>
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
        $.post(ROOT + "/interest/lastestpay", {force: _force,date:date}, function(msg){
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
        _layerIndex = layer.load('正在加载数据...');
        $.post(ROOT + "/interest/lastestpay", {force: _force,date:date}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                $('#checkAll').attr("checked", false);
                $("#checkList tbody").html(msg.info);
            }else{
                layer.alert(msg.info, -1);
            }
        });
    }

    function batchto_wallet(_id,_rid){
    	
    	var flag = false;
    	<?php if(checkAuth('Admin/project/project_batchto_wallet') == true): ?>flag = true;<?php endif; ?>
    	
    	if(flag){
	       	if(confirm('确认批量还款吗?')){
	           _layerIndex = layer.load('操作中...');
	           $.post('<?php echo C("ADMIN_ROOT");?>/project/project_batchto_wallet', {id: _id,rid:_rid}, function(msg){
	               layer.close(_layerIndex);
	               if(msg.status){
	                   layer.alert(msg.info);
	                   loadData();
	               }else{
	                   layer.alert(msg.info);
	               }
	           });
	       }
    	}
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
    //Project
    function pay(_pid, _rid, _status2, _action){
        layer.confirm('确定执行『' + _action + '』操作吗?', function(){
            _layerIndex = layer.load('数据提交中...');
            $.post('<?php echo C("ADMIN_ROOT");?>/project/repay', {id: _pid}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                	/*
                    if(msg.info){
                        layer.alert("『" + _action + "』操作成功~!<br>" + msg.info, {
                            skin: 'layui-layer-molv' //样式类名
                            ,closeBtn: 0
                        }, function(){
                            loadData();
                        });

                    }else{
                        layer.alert('『' + _action + '』操作成功!~');
                        loadData();
                    }*/
                    
                	layer.alert('『' + _action + '』操作成功!~');
                    loadData();
                    
                }else{
                    layer.alert(msg.info);
                }
            });
        });
    }
    function towalletlist(_pid, _rid){
        window.open(ROOT + "/project/paylisttowallet/id/" + _pid + '/rid/' + _rid, 'paylisttowallet', 'height=600,width=850,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function buylist(_pid, _rid){
        window.open(ROOT + "/project/paylist/id/" + _pid + '/rid/' + _rid, 'buylist', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function changeLL(_obj){
        if($(_obj).is(':checked')){
            window.location.href = '<?php echo C("ADMIN_ROOT");?>/project/lastestpay/force/1';
        }else{
            window.location.href = '<?php echo C("ADMIN_ROOT");?>/project/lastestpay/force/0';
        }
    }
    function paysms(_id, _obj){
        layer.confirm('确定执行『短信通知』操作吗?', function(){
            _layerIndex = layer.load('数据提交中...');
            $.post('<?php echo C("ADMIN_ROOT");?>/project/paysms', {id: _id}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    $(_obj).remove();
                    if(msg.info){
                        layer.msg(msg.info, 2, -1);
                    }else{
                        layer.msg('『短信通知』操作成功~!', 2, -1);
                    }
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        });
    }
    function checksms(_id){
        window.open(ROOT + "/project/checksms/id/" + _id, 'checksms', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }

    function closeLayer(){
//        alert(_layerIndex2);
        $('#checkAll').attr("checked", false);
        loadDataByDate();
        layer.close(_layerIndex2);
    }

    function startPay(){
        var _success = 0;
        var _fail = 0;
        $(".payStatus").each(function(){
            var obj = $(this);
            var _pid= obj.data('projectid');
            var _rid= obj.data('id');
            obj.html('支付中......');
            $.post('<?php echo C("ADMIN_ROOT");?>/interest/repay',{id: _pid, rid: _rid, status: 2},function(msg){

                if(msg.status){
                    obj.removeClass('payStatus');
                    obj.html('支付完成');
                    _success = _success+1;
                    $('#pay_bank_s').html(_success);
                }else{
                    obj.html(msg.info);
                    _fail = _fail+1;
                    $('#pay_bank_f').html(_fail);
                    $('#startPay').html('批量支付失败项目')
                }
            });
        });
    }

    function startPayWallet(){
        var _success = 0;
        var _fail = 0;
        $(".payStatus").each(function(){
            var obj = $(this);
            var _pid= obj.data('projectid');
            var _rid= obj.data('id');
            obj.html('转入平台账户中......');
            
            $.post('<?php echo C("ADMIN_ROOT");?>/project/project_batchto_wallet', {id: _pid,rid:_rid}, function(msg){
            //$.post('<?php echo C("ADMIN_ROOT");?>/interest/project_batchto_wallet',{id: _pid, rid: _rid, status: 2},function(msg){

                if(msg.status){
                    obj.removeClass('payStatus');
                    obj.html('转入平台账户完成');
                    _success = _success+1;
                    $('#pay_s').html(_success);
                }else{
                    obj.html(msg.info);
                    _fail = _fail+1;
                    $('#pay_f').html(_fail);
                    $('#startPayWallet').html('批量操作失败项目')
                }
            });
        });

    }

    function toBank(){
        var aa=0;

        var html = '<table width="100%"><thread><tr>' +
                '<th width="30%">产品名称</th>' +
                '<th width="20%">付息时间</th>' +
                '<th width="20%">状态</th>' +
                '<th>操作</th>' +
                '</tr></thread>';
        $(".to_bank:checkbox:checked").each(function(){
            aa+=Number($(this).data('amount'));
            html+= "<tr>" +
                    "<td align='center'>"+$(this).data('title')+"</td>" +
                    "<td align='center'>"+$(this).data('time')+"</td>" +
                    "<td align='center' class='payStatus' data-projectId='"+$(this).data('projectid')+"' data-id='"+$(this).data('id')+"'>等待支付</td>" +
                    "<td align='center'></td></tr>"
        });
        html+="</table>";



        html+="<div style='border-bottom:1px solid #ccc;text-align: center;'>" +
                "<div style='width:50%;float: left;line-height:25px;'>" +
                "<p><img style='width: 19px;height: 19px;' src='/Public/admin/images/u171.png'>支付成功: <i id='pay_bank_s'>0</i>个产品</p>" +
                "</div>"+
                "<div style='width:50%;float: left;line-height:25px;'>" +
                "<p><img style='width: 19px;height: 19px;' src='/Public/admin/images/u173.png'>支付失败: <i id='pay_bank_f'>0</i>个产品</p>" +
                "</div>"+
                "</div>"

        html+="<div style='line-height:25px;text-align: center;border-radius: 5px;'>" +
                "<div style='width:50%;float: left;'>"+
                "<button onclick='startPay()' id='startPay' style='width:50%;'>开始批量支付</button></div>"+
                "<div style='width:50%;float: left;'>"+
                "<button style='width:50%;' onclick='closeLayer()'>完成</button></div>"+
                "</div>"





        _layerIndex2 =  layer.open({
            type: 1,
            title:'批量转入银行卡',
            skin: 'layui-layer-rim', //加上边框
            area: ['550px', '400px'], //宽高
            content: html
        });


    }

    function toWallet(){
        var aa=0;

        var html = '<table width="100%"><thread><tr>' +
                '<th width="30%">产品名称</th>' +
                '<th width="20%">付息时间</th>' +
                '<th width="20%">状态</th>' +
                '<th>操作</th>' +
                '</tr></thread>';
        $(".to_wallet:checkbox:checked").each(function(){
            aa+=Number($(this).data('amount'));
            html+= "<tr>" +
                    "<td align='center'>"+$(this).data('title')+"</td>" +
                    "<td align='center'>"+$(this).data('time')+"</td>" +
                    "<td align='center' class='payStatus' data-projectId='"+$(this).data('projectid')+"' data-id='"+$(this).data('id')+"'>等待转入</td>" +
                    "<td align='center'></td></tr>"
        });
        html+="</table>";



        html+="<div style='border-bottom:1px solid #ccc;text-align: center;'>" +
                "<div style='width:50%;float: left;line-height:25px;'>" +
                "<p><img style='width: 19px;height: 19px;' src='/Public/admin/images/u171.png'>转入成功: <i id='pay_s'>0</i>个产品</p>" +
                "</div>"+
                "<div style='width:50%;float: left;line-height:25px;'>" +
                "<p><img style='width: 19px;height: 19px;' src='/Public/admin/images/u173.png'>转入失败: <i id='pay_f'>0</i>个产品</p>" +
                "</div>"+
                "</div>"

        html+="<div style='line-height:25px;text-align: center;border-radius: 5px;'>" +
                "<div style='width:50%;float: left;'>"+
                "<button onclick='startPayWallet()' id='startPayWallet' style='width:50%;'>开始批量转入</button></div>"+
                "<div style='width:50%;float: left;'>"+
                "<button style='width:50%;' onclick='closeLayer()'>完成</button></div>"+
                "</div>"





        _layerIndex2 =  layer.open({
            type: 1,
            title:'批量转入平台账户',
            skin: 'layui-layer-rim', //加上边框
            area: ['550px', '400px'], //宽高
            content: html
        });


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
    
    
</script>