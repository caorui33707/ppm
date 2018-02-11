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
        <div class="title">还款数据</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
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
    var _plusDescr = '<?php echo (json_encode($plus_descr)); ?>';
    _plusDescr = $.parseJSON(_plusDescr);
    $(function () {
        $('#container').highcharts({
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: '还款数据(<?php echo ($dt); ?>)'
            },
            subtitle: {
                text: ''
            },
            xAxis: [{
                categories: [<?php echo ($categories); ?>]
            }],
            yAxis: [{ // Primary yAxis
            	min:0, // 定义最小值    
                labels: {
                    format: '{value}元',
                    style: {
                        color: '#89A54E'
                    }
                },
                title: {
                    text: '还款金额',
                    style: {
                        color: '#89A54E'
                    }
                }
            }, { // Secondary yAxis
                title: {
                    text: '还款利息',
                    style: {
                        color: '#4572A7'
                    }
                },
                labels: {
                    format: '{value}元',
                    style: {
                        color: '#4572A7'
                    }
                },
                opposite: true
            }],
            tooltip: {
                formatter: function(){
                    var year = this.x;
                    var _plus = '';
                    $.each(_plusDescr, function(i, n){
                        if(_plusDescr[i]['datetime'] == year){
                            _plus = _plusDescr[i]['descr'];
                        }
                    });

                    var s = '<b>' + this.x + '</b>';
                    $.each(this.points, function () {
                        s += '<br/>' + this.series.name + ': ' +
                             this.y + ' 元';
                    });
                    s += '<br/>' + '--------------------';
                    s += '<br/>' + _plus;
                    return s;
                },
                shared: true
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 120,
                verticalAlign: 'top',
                y: 50,
                floating: true,
                backgroundColor: '#FFFFFF'
            },
            series: [{
                name: '还款利息',
                color: '#4572A7',
                type: 'column',
                yAxis: 1,
                data: [<?php echo ($totle_interest); ?>]
//                tooltip: {
//                    valueSuffix: '元'
//                }
            }, {
                name: '还款金额',
                color: '#89A54E',
                type: 'spline',
                data: [<?php echo ($totle_price); ?>]
//                tooltip: {
//                    valueSuffix: '元'
//                }
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