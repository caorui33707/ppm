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
        <div class="title">用户充值统计</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <select name="chn" style="width:200px;">
                            <option value="0">全部渠道</option>
                            <?php if(is_array($channel_list)): foreach($channel_list as $key=>$item): ?><option value="<?php echo ($item["id"]); ?>" <?php if(($item["id"]) == $params['chn']): ?>selected<?php endif; ?>><?php echo ($item["cons_value"]); ?>(<?php echo ($item["cons_key"]); ?>)</option><?php endforeach; endif; ?>
                        </select>
                        <label for="start_time">开始时间：<input type="text" class="laydate-icon" name="start_time" id="start_time" value="<?php echo ($params["start_time"]); ?>" readonly /></label>
                        <label for="end_time">结束时间：<input type="text" class="laydate-icon" name="end_time" id="end_time" value="<?php echo ($params["end_time"]); ?>" readonly /></label>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
                </form>
            </div>
            <!-- 高级查询区域 -->
            <div  id="searchM" class=" none search cBoth" >
            </div>
            </form>
        </div>
        <!-- 功能操作区域结束 -->

		<div id="searchM" class=" search cBoth">
			<table>
				<tbody>
					<tr>
						<td>人数统计  （男：<?php echo ($params['countInfo']['man']); ?>;&nbsp;&nbsp;女：<?php echo ($params['countInfo']['woman']); ?>）</td>
					</tr>
					<tr>
						<td>渠道  （安卓：<?php echo ($params['countInfo']['android']); ?>;&nbsp;&nbsp;苹果：<?php echo ($params['countInfo']['ios']); ?>;&nbsp;&nbsp;平台：<?php echo ($params['countInfo']['platform']); ?>）</td>
					</tr>
					
					<tr>
						
						<!--<a href="/admin.php/statistics/export_reg_user">导出<?php echo date("Ym",strtotime("-1 month"));?>月份 - 只充值零钱包用户清单</a> &nbsp;&nbsp;
							<a href="/admin.php/statistics/export_blank_user">导出<?php echo date("Ym",strtotime("-1 month"));?>月份  注册的没交易过用户列表</a>
						-->
						<td>	
							<?php echo date("y-m",strtotime('-1 month'));?>月注册的用户数：<span id="reg_number"><?php echo ($params["reg_info"]["reg_number"]); ?> 人</span>
						<br/>
							<?php echo date("y-m",strtotime('-1 month'));?>月交易用户数：<span id="trade_number"><?php echo ($params["reg_info"]["trade_number"]); ?> 人</span>
						</td>
					</tr>
					
				</tbody>
			</table>
		</div>

		<!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td height="5" colspan="13" class="topTd"></td>
                    </tr>
                    <tr class="row">
                        <th width="8px"><input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"></th>
                        <th align="center">编号</th>
                        <th align="center">渠道</th>
                        <th align="center">手机号</th>
                        <th align="center">年龄</th>
                        <th align="center">性别</th>
                        <th align="center">注册时间</th>
                        <th align="center">最近投资时间</th>
                        <th align="center">首投理财金额(元)</th>
                        <th align="center">指定时段内后续投理财金额(元)</th>
                        <th align="center">钱包实际余额(元)</th>
                        <th align="center">购买次数/下单次数</th>
                        <th width="*">收益(元)</th>
                    </tr>
                    <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                            <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                            <td><?php echo ($item["id"]); ?></td>
                            <td><?php echo ($item["channelStr"]); ?></td>
                            <td align="center"><a href="<?php echo C('ADMIN_ROOT');?>/statistics/user_search/key/<?php echo ($item["username"]); ?>"><?php echo ($item["username"]); ?></a></td>
                            <td align="center"><?php echo ($item["year"]); ?></td>
                            <td align="center"><?php echo ($item["sex"]); ?></td>
                            <td align="center"><?php echo (date('Y-m-d H:i',strtotime($item["add_time"]))); ?></td>
                            <td align="center"><?php echo ($item["lastest_buy_time"]); ?></td>
                            <td align="right"><?php echo (number_format($item["first_recharge"])); ?></td>
                            <td align="right"><?php echo (number_format($item["second_recharge"])); ?></td>
                            <td align="right"><?php echo (number_format($item["wallet"],2)); ?></td>
                            <td align="right"><?php echo ((isset($item["buy_count"]) && ($item["buy_count"] !== ""))?($item["buy_count"]):0); ?> / <?php echo ((isset($item["order_count"]) && ($item["order_count"] !== ""))?($item["order_count"]):0); ?></td>
                            <td><?php echo ($item["interest"]); ?></td>
                        </tr><?php endforeach; endif; ?>
                    <tr>
                        <td height="5" colspan="13" class="bottomTd"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page"><?php echo ($show); ?></div>
        <!-- 列表显示区域结束 -->
    </div>
    <!-- 主体内容结束 -->
    <?php if(checkAuth('Admin/statistics/recharge_export') == true): ?><label>每页条数:<input type="text" id="exp_count" value="20" style="width:50px;text-align:center;" /></label>
        <label>页码:<input type="text" id="exp_page" value="1" style="width:50px;text-align:center;" /></label>
        <input type="button" value="导出excel" style="width:120px;" class="search imgButton" onclick="export_excel()"><?php endif; ?>
</div>
<!-- 主页面结束 -->
<script>
    var _page = "<?php echo ($params["page"]); ?>";
    var _chn = "<?php echo ($params["chn"]); ?>";
    var _start_time = "<?php echo ($params["start_time"]); ?>";
    var _end_time = "<?php echo ($params["end_time"]); ?>";

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
    function export_excel(){
        var _query = '';
        var _ex_count = $("#exp_count").val();
        var _ex_page = $("#exp_page").val();
        if(_chn) _query += '/chn/' + _chn;
        if(_start_time) _query += '/st/' + _start_time;
        if(_end_time) _query += '/et/' + _end_time;
        if(_ex_count) _query += '/excount/' + _ex_count;
        if(_ex_page) _query += '/expage/' + _ex_page;
        window.location.href = "<?php echo C('ADMIN_ROOT');?>/statistics/recharge_export" + _query;
    }
    
</script>