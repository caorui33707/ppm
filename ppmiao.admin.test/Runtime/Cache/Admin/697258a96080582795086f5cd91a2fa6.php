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
var URL = '/admin.php/Statistics';
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
        <div class="title">支付渠道统计</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <select name="chn" style="width:200px;">
                            <option value="0">全部渠道</option>
                            <option value="1">宝付支付</option>
                            <option value="4">融宝支付</option>
                            <option value="5">宝付API支付</option> 
                            <option value="6">连连支付</option>                         
                        </select>
                        <label for="start_time">开始时间：<input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($params["st"]); ?>" readonly /></label>
                        <label for="end_time">结束时间：<input type="text" class="laydate-icon" name="end_time" id="end_time" value="<?php echo ($params["et"]); ?>" readonly /></label>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="button" value="搜索" class="search imgButton" id="getdata"></div>
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
                <tbody id="rowlist">
                <tr>
                    <td height="5" colspan="4" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="25%" align="center">渠道名称</th>
                    <th width="25%" align="center">购买产品总额</th>
                    <th width="25%" align="center">充值钱包总额</th>
                    <th width="25%" align="center">充值总额</th>
                </tr>
                
                
                </tbody>
            </table>
        </div>
        
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<script>
    var _page = "<?php echo ($params["page"]); ?>";
    var _chn = "<?php echo ((isset($params["chn"]) && ($params["chn"] !== ""))?($params["chn"]):0); ?>";
    var _st = "<?php echo ($params["start_time"]); ?>";
    var _et = "<?php echo ($params["end_time"]); ?>";

    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true,
        choose: function(datas){
            end.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas //将结束日的初始值设定为开始日
        }
    };
    var end = {
        elem: '#end_time',
        format: 'YYYY-MM-DD',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: false,
        istoday: true,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);
    
    
    $("#getdata").click(function(){
		_layerIndex = layer.load('正在查询...');
		var _st = $('#start_time').val();
		var _et = $("#end_time").val();
		var _channel = $("#chn").val();
		
		$.post(ROOT + "/statistics/statistics_payment_channel", {st: _st, et: _et,channel:_channel}, function(msg){
	    	layer.close(_layerIndex);
		    if(msg.status){
		    	var dd = msg.info;
		    	var s = '<tr><td height="5" colspan="4" class="topTd"></td></tr>';
                s +='<tr class="row">';	
                s +='<th width="25%" align="center">渠道名称</th>'
                s +='<th width="25%" align="center">购买产品总额</th>'
                s +='<th width="25%" align="center">充值钱包总额</th>'
                s +='<th width="25%" align="center">充值总额</th>'
		        s +='</tr>';
		    	
		    	for(var key in dd){  		    				    		
		    		s += '<tr class="row"><td align="center">'+dd[key]['channel_name']+'</td>';
		    		s += "<td align='right'><a href='javascript:;' onclick='showDetail(\""+dd[key]['type']+"\",\""+dd[key]['start_time']+"\",\""+dd[key]['end_time']+"\",\"1\")'>"+dd[key]['amount']+"元</a></td>";    
		    		s += "<td align='right'><a href='javascript:;' onclick='showDetail(\""+dd[key]['type']+"\",\""+dd[key]['start_time']+"\",\""+dd[key]['end_time']+"\",\"2\")'>"+dd[key]['wallet_amount']+'元</a></td>';
		    		s += '<td align="right">'+dd[key]['total']+'元</td>';
		    		s += '</tr>';
		    	}
		    	$("#rowlist").empty().html(s);
		    }else{
		        layer.alert(msg.info, -1);
		    }
		});
    });
    
    
    function showDetail(t,s,e,o){
        window.open(ROOT + "/statistics/statistics_payment_channel_detail/paytype/" + t + "/st/" + s+"/et/"+e+"/o/"+o, 'showperson', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    
</script>