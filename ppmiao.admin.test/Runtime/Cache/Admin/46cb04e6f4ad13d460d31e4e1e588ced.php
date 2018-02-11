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
var URL = '/admin.php/Statistics';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<div id="main" class="main" >
    <script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
    <script type="text/javascript" src="/Public/admin/highcharts/js/highcharts.js"></script>
    <script type="text/javascript" src="/Public/admin/highcharts/modules/exporting.js"></script>
    <div class="content">
        <div class="title">平台存量</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='get' action="">
                    <div class="fLeft">
                    	<select name="channel" style="width:200px;">
                            <option value="0">全部渠道</option>
                            <?php if(is_array($channel_list)): foreach($channel_list as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($item["id"]) == $channel): ?>selected<?php endif; ?>><?php echo ($item["cons_value"]); ?>(<?php echo ($item["cons_key"]); ?>)</option><?php endforeach; endif; ?>
                        </select>
                        
                        <span><input type="text" name="dt" id="dt" class="laydate-icon" placeholder="选择日期" class="medium" value="<?php echo ($dt); ?>" readonly></span>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="筛选" class="search imgButton"></div>
                </form>
            </div>
        </div>
        <div id="result" class="result none"></div>
        <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    </div>
</div>
<script>
    $(function () {
        $('#container').highcharts({
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: '存量月数据(<?php echo ($dt); ?>)-平均日均存量余额:'+<?php echo ($avgMoney); ?>+'元'
            },
            
            xAxis: [{
                categories: [<?php echo ($categories); ?>]
            }],
            yAxis: [{ // Primary yAxis
                min:0,
                labels: {
                    formatter: function(){
                        return this.value/10000 + '万';
                    },
                    style: {
                        color: '#89A54E'
                    }
                },
                title: {
                    text: '存量金额',
                    style: {
                        color: '#89A54E'
                    }
                }
            }],
            tooltip: {
                shared: true
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 120,
                verticalAlign: 'top',
                y: 100,
                floating: true,
                backgroundColor: '#FFFFFF',
                enabled: false
            },
            series: [ {
                name: ' ',
                color: '#89A54E',
                type: 'spline',
                data: [<?php echo ($totlemoney); ?>],
                tooltip: {
                    valueSuffix: '元'
                }
            }]
        });
    });

    				

    var dt = {
        elem: '#dt',
        format: 'YYYY-MM',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true,
        choose: function(datas){

        }
    };
    laydate(dt);
</script>