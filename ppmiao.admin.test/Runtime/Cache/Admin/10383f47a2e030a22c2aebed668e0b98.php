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

<style>
    .hButton{
    }

    .btnSelect {
        background-color: #999999!important;
    }

    .imgInput{
        width: 90px;
        height: 28px;
        margin-top: 10px;
        margin-right: 50px;
        margin-bottom: 10px;
        border: 0;
        font-size: 15px;
        padding-top: 1px !important;
        padding-top: 5px;
        letter-spacing: 4px;
        font-weight: bold;
        border: 1px solid #393939;
        background-color: #F0F0FF;
        background-position: 5px 40%;
        background-repeat: no-repeat;
        cursor: pointer;
        text-align: center;
    }
</style>
<!-- 菜单区域  -->
<script type="text/javascript" src="/Public/admin/js/Validform_v5.3.2_min.js"></script>
<script type="text/javascript" src="/Public/admin/js/jquery.form.js"></script>
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">渠道统计</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- tab 切换 -->
                <div class="impBtn hButton shadow" >
                    <input type="button"  class='search imgInput <?php if(($cons_type) == "2"): ?>btnSelect<?php endif; ?>' value="合作推广">
                    <input type="button"  class='search imgInput <?php if(($cons_type) == "1"): ?>btnSelect<?php endif; ?>'  value="自然统计">
                </div>
            <!-- tab 切换 end -->
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
                        <?php $start_time = $params['start_time']?$params['start_time']:0; $end_time = $params['end_time']?$params['end_time']:0; ?>
                    <div class="impBtn hMargin fLeft shadow"><a href="<?php echo C('ADMIN_ROOT');?>/statistics/channel_statistics_excel/dosearch/<?php echo ($params["dosearch"]); ?>/chn/<?php echo ($params["chn"]); ?>/st/<?php echo ($start_time); ?>/et/<?php echo ($end_time); ?>"> <input type="button" value="导出" class="search imgButton"> </a> </div>

                </form>

                <form  id="frmMain"  method='post' action="" enctype="multipart/form-data">
                    <div style="margin-top: 10px" class="impBtn hMargin fLeft shadow">
                        <input type="file" value="批量导入" name="xls" class="bleftrequire" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" >
                        <input type="submit" value="批量导入" class="search imgButton">
                    </div>
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
                    <td height="5" colspan="15" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="8px"><input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"></th>
                    <th width="200px">渠道名称</th>
                    <th width="150px" >日期</th>

                    <th width="80px" align="center">注册用户数</th>
                    <th width="80px" >总投资次数</th>

                    <th width="80px" align="center">一投用户数</th>
                    <th width="100px" align="center">一投总额</th>

                    <th width="80px" align="center">二投用户数</th>
                    <th width="100px" align="center">二投总额</th>

                    <th width="80px" align="center">总投用户数</th>
                    <th width="150px" align="center">累计投资总额</th>

                    <th width="80px" align="center">还款用户数</th>
                    <th width="100px" align="center">还款总额</th>

                    <!--<th width="150px" align="center">钱包余额</th>-->
                    <th width="80px">下载用户数</th>
                </tr>

                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                        <td><?php echo ($item["cons_value"]); ?></td>
                        <td><?php echo (date('Y-m-d',strtotime($item["add_time"]))); ?></td>
                        <!--<td><?php echo (date('Y-m-d H:i:s',strtotime($item["start_time"]))); ?>~<?php echo (date('Y-m-d H:i:s',strtotime($item["end_time"]))); ?></td>-->
                        <td align="right"><?php echo ($item["user_count"]); ?></td>
                        <td align="right"><?php echo ($item["total_pay_count_sum"]); ?></td>

                        <td align="right"><?php echo ($item["first_pay_person_sum"]); ?></td>
                        <td align="right"><?php echo (number_format($item["first_pay_sum"],2)); ?></td>

                        <td align="right" ><?php echo ($item["second_pay_person_sum"]); ?></td>
                        <td align="right"><?php echo (number_format($item["second_pay_sum"],2)); ?></td>

                        <td align="right" ><?php echo ($item["total_pay_person_sum"]); ?></td>
                        <td align="right"><?php echo (number_format($item["total_pay_sum"],2)); ?></td>

                        <td align="right" ><?php echo ($item["total_payment_user"]); ?></td>
                        <td align="right"><?php echo (number_format($item["total_payment_user_pay_sum"],2)); ?></td>

                        <!--<td align="right"><?php echo (number_format($item["wallet_sum"],4)); ?></td>-->
                        <td align="right"><?php echo ($item["user_download"]); ?></td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="16" class="bottomTd"></td>
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
<script>
    var _page = "<?php echo ($params["page"]); ?>";
    var _chn = "<?php echo ($params["chn"]); ?>";
    var _st = "<?php echo ($params["start_time"]); ?>";
    var _et = "<?php echo ($params["end_time"]); ?>";

    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: laydate.now(0, 'YYYY-MM-DD 23:59:59'), //最大日期
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
        min: _st,
        max: laydate.now(0, 'YYYY-MM-DD 23:59:59'),
        istime: false,
        istoday: true,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);


    $(document).ready(function(){

        $("#frmMain").Validform({ // 表单验证
            tiptype: 3,
            callback: function(form){
                _layerIndex = layer.load('数据提交中...');
                $("#frmMain").ajaxSubmit({
                    url: ROOT + '/statistics/channel_file_excel',
                    type: "post",
                    dataType: "json",
                    success: function(msg){
                        layer.close(_layerIndex);
                        if(msg.status){
                            layer.alert('添加成功~!', -1, function(){

                                var href_url = "/dosearch/<?php echo ($params["dosearch"]); ?>/chn/<?php echo ($params["chn"]); ?>"
                                if(_st){
                                    href_url +='/st/<?php echo ($start_time); ?>';
                                }
                                if(_et){
                                    href_url +='/et/<?php echo ($end_time); ?>';
                                }

                                window.location.href = ROOT + "/statistics/channel_statistics"+ href_url;
                               // window.location.href = ROOT + "/statistics/channel_statistics/dosearch/<?php echo ($params["dosearch"]); ?>/chn/<?php echo ($params["chn"]); ?>/st/<?php echo ($start_time); ?>/et/<?php echo ($end_time); ?>";
                            });
                        }else{
                            layer.alert(msg.info);
                        }
                    }
                });
                return false;
            }
        });
    });

</script>