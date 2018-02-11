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
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">日销售额(明细)</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
            
                <form method='get' action="">
                    <div class="fLeft">
                        开始日期
                        <input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($params["start_time"]); ?>" readonly />
                        <input type="text" class="laydate-icon" name="end_time" id="end_time" value="<?php echo ($params["end_time"]); ?>" readonly />
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" value="搜索" class="search imgButton">
                    	<span>&nbsp;&nbsp;<a href="###" id="exportExcel">导出Excel</a></span>
                    </div>
                </form>
                
            </div>
        </div>
        
        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="15" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="3%" align="center">编号</th>
                    <th width="3%" align="center">账号</th>
                    <th width="4%" align="center">姓名</th>
                    <th width="3%" align="center">性别</th>
                    <th width="3%" align="center">年龄</th>
                    <th width="7%" align="center">期数</th>
                    <th width="6%" align="center">金额</th>
                    <!-- <th width="4%" align="center">红包金额</th> -->
                    <th width="4%" align="center">收益</th>
                    <th width="5%" align="center">购买日期</th>
                    <th width="3%" align="center">购买时刻</th>
                    <th width="6%" align="center">回款日期</th>
                    <th width="5%" align="center">购买渠道</th>
                    <th width="6%" align="center">产品标签</th>
                    <th width="8%" align="center">融资方</th>
                    <th width="6%" align="center">分组名称</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                    	<th><?php echo ($item["id"]); ?></th>
                        <td align="left"><?php echo ($item["username"]); ?></td>
                        <td align="left"><?php echo ($item["real_name"]); ?></td>
                        <td align="center"><?php echo ($item["sex"]); ?></td>
                        <td align="center"><?php echo ($item["age"]); ?></td>
                        <td align="left"><?php echo ($item["project"]["title"]); ?></td>
                        <td align="center"><?php echo ($item["amount"]); ?></td>
                        <!-- <td align="center"><?php echo ($item["red_amount"]); ?></td> -->
                        <td align="center"><?php echo ($item["due_interest"]); ?></td>
                        <td align="center"><?php echo ($item["date"]); ?></td>
                        <td align="center"><?php echo ($item["time"]); ?></td>
                        <td align="center"><?php echo ($item["due_time"]); ?></td>
                        <td align="left"><?php echo ($item["des"]); ?></td>
                        
                        
                        <td align="center">
                        	<?php switch($item["project"]["new_preferential"]): case "0": ?>普通标<?php break;?> 
	                            <?php case "1": ?>新人特惠<?php break;?> 
	                            <?php case "2": ?>爆款<?php break;?> 
	                            <?php case "3": ?>HOT<?php break;?>
	                            <?php case "6": ?>活动<?php break;?> 
	                            <?php case "8": ?>私人专享<?php break;?> 
	                            <?php case "9": ?>月月加薪<?php break; endswitch;?>
                        </td>
                        <td align="center"><?php echo ($item["project"]["financing"]); ?></td>
                        <td align="left"><?php echo ($item["group_name"]); ?></td>
                        
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="15" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page">
            <?php echo ($show); ?>
        </div>
        <!-- 列表显示区域结束 -->
        <div style="clear: both;"></div>
    </div>
    <label><?php if($params["totalCnt"] > 0 ): ?>总金额:<b><?php echo ($params["total_money"]); ?></b>元;&nbsp;&nbsp;总收益：<b><?php echo ($params["total_due_interest"]); ?></b>元;&nbsp;&nbsp;- 共:<?php echo ($params["totalCnt"]); ?>条记录<?php endif; ?></label>
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

	var _start_time = "<?php echo ($params["start_time"]); ?>";
	var _end_time = "<?php echo ($params["end_time"]); ?>";
	
	var _cnt = <?php echo ($params["totalCnt"]); ?>;

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
    
    function user_list(_pid){
        window.open(ROOT + "/redenvelope/user_packet_list/pid/" + _pid, 'buylist', 'height=600,width=900,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    
    $("#exportExcel").click(function(){
    	
    	if(_cnt <=0) {
    		layer.alert('没有记录，没无导出！');
    		return; 
    	}
    	location.href = "<?php echo C('ADMIN_ROOT');?>/project/saledetailsexportExcel/start_time/"+_start_time+"/end_time/"+_end_time;  
    })
    
</script>