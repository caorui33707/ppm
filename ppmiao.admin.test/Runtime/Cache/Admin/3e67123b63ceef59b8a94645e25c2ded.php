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
var URL = '/admin.php/Order';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>

<style>
    a {
        text-decoration: none;
        color: #174B73;
        border-bottom: 0px dashed gray;
    }

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
<script type="text/javascript" src="/Public/admin/auth/js/common.js"></script>
<script type="text/javascript" src="/Public/admin/laydate/laydate.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">掉单管理列表</div>
        <!--<div class="title">日销售额 <a href="<?php echo C('ADMIN_ROOT');?>/project/daysales_export/dt/<?php echo ($datetime); ?>" target="_blank">导出Excel</a>&nbsp;&nbsp;日销售额宝付对账 <a href="<?php echo C('ADMIN_ROOT');?>/project/daysales_lianlian_export/dt/<?php echo ($datetime); ?>" target="_blank">导出Excel</a></div>-->
        <!--&lt;!&ndash;  功能操作区域  &ndash;&gt;-->
        <div class="operate" >

            <!-- tab 切换 -->
            <div class="impBtn hButton shadow" >
                <input type="button"  class='search imgInput <?php if(($type) == "1"): ?>btnSelect<?php endif; ?>' value="待处理">
               <a href="<?php echo C('ADMIN_ROOT');?>/order/depository_succ" >
                    <input type="button"  class='search imgInput <?php if(($type) == "2"): ?>btnSelect<?php endif; ?>'  value="已处理">
               </a>
            </div>
            <!-- tab 切换 end -->

            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <!--<label for="st">开始时间：<input type="text" class="laydate-icon" name="st" id="st" value="<?php echo ($startTime); ?>" readonly /></label>-->
                        <label for="dt">查询时间：<input type="text" class="laydate-icon" name="dt" id="dt" value="<?php echo ($datetime); ?>" readonly /></label>
                        <!--<label for="flushcache"><input type="checkbox" id="flushcache" name="flushcache" value="1" />更新缓存</label>-->
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
                    <div class="impBtn hMargin fLeft shadow" onclick="return save_all()" ><input type="button" value="批量修复" class="search imgButton"></div>
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
                    <th width="50px" align="center">编号</th>
                    <th width="100px" align="center">订单号</th>
                    <th width="50px" align="center">订单类型</th>
                    <th width="60px" align="center">本地状态</th>
                    <th width="80px" align="center">银行状态</th>
                    <th width="120px" align="center">客户编号</th>
                    <th width="110px" align="center">手机号码</th>
                    <th width="180px" align="center">错误信息</th>
                    <th width="150px" align="center">添加时间</th>
                    <th width="100px" align="center">备注</th>
                    <th width="*" align="center">操作</th>
                </tr>


                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                        <td><?php echo ($item["id"]); ?></td>
                        <td align="left"><?php echo ($item["order_no"]); ?></td>
                        <td align="left">
                            <?php switch($item["order_type"]): case "6": ?>充值<?php break;?>
                                <?php case "7": ?>提现<?php break; endswitch;?>
                        </td>
                        <td align="left">
                            <?php switch($item["order_status"]): case "2": ?>失败<?php break;?>
                                <?php case "3": ?>掉单失败<?php break; endswitch;?>
                        </td>
                        <td align="center">
                            <?php switch($item["order_bank_status"]): case "0": ?>处理中<?php break;?>
                                <?php case "21": ?>确认成功<?php break;?>
                                <?php case "22": ?>确认失败<?php break; endswitch;?>
                        </td>

                        <td align="center"><?php echo ($item["add_platcust"]); ?> </td>

                        </td>
                        <td align="center">
                            <?php echo ($item["mobile"]); ?>
                        </td>

                        <td align="left">
                            本地错误信息：<?php echo ($item["error_msg"]); ?> <br/>
                            银行错误信息：<?php echo ($item["error_bank_msg"]); ?>
                        </td>
                        <td align="center">
                            <?php echo (date('Y-m-d H:i:s',strtotime($item["add_time"]))); ?>
                        </td>

                        <td><?php echo ($item["remark"]); ?></td>

                        <td>
                            <?php if(checkAuth('Admin/auth/member_edit') == true): ?><a href="javascript:;" data-id="<?php echo ($item["id"]); ?>" onclick="save($(this))">修复</a>&nbsp;<?php endif; ?>
                        </td>

                    </tr><?php endforeach; endif; ?>

                <tr>
                    <td height="5" colspan="14" class="bottomTd"></td>
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
    var _layerIndex = 0;
    var _st = "<?php echo ($startTime); ?>";
    var _dt = "<?php echo ($datetime); ?>";

    var starttime = {
        elem: '#st',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true,
        choose: function(datas){
            datetime.min = datas; //开始日选好后，重置结束日的最小日期
            datetime.start = datas //将结束日的初始值设定为开始日
        }
    };

    var datetime = {
        elem: '#dt',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true
    };

   // laydate(starttime);
    laydate(datetime);

    function update_remark(_id){
        var _remark = prompt('请输入备注信息:');
        if(_remark != '' && _remark != null){
            _layerIndex = layer.load('操作中...');
            $.post(ROOT + "/project/update_project_remark", {id: _id, dt: _dt, remark: _remark}, function(msg){
                if(msg.status){
                    $("#remark_" + _id).text(_remark);
                    layer.msg(msg.info, 1, -1); //  '更新成功~'

                }else{
                    layer.alert(msg.info, -1);
                }
            });
        }
    }

    function save(_obj){ // 单个修复
        var id = _obj.attr('data-id');
        var idArr = new Array(id);
        _layerIndex = layer.load('操作中...');
        $.post(ROOT + '/order/depository_save',{idArr:idArr},function(msg){
            if(msg.status){
                //$("#remark_" + _id).text(_remark);
                layer.msg(msg.info, 1, -1); // '更新成功~'

                window.location.href=location.href;
            }else{
                layer.alert(msg.info, -1);
                window.location.reload();
                return false;
            }
        });
    }

    function save_all(){ // 批量修复
        var items = $("#checkList tbody").find("input[type=checkbox]:checked").not("#check");
        if(items.length > 0){
            layer.confirm('确定要修复选择的数据吗?', function(){
                var _ids = '';
                var _idsArr = new Array();

                $.each(items, function(i, n){
                    //_ids += ',' + $(n).attr('alt');
                    _idsArr[i] = $(n).attr('alt');
                });
                //if(_ids) _ids = _ids.substr(1);

                //alert(_ids);

                _layerIndex = layer.load('操作中...');
                $.post(ROOT + '/order/depository_save', {idArr: _idsArr}, function(msg){
                    layer.close(_layerIndex);
                    if(msg.status){
                        layer.alert(msg.info, -1, function(){ // '成功修复'+ items.length + '条数据~!'
                            window.location.reload();
                        });
                    }else{
                        layer.alert(msg.info);
                        window.location.reload();
                    }
                });
            });
        }else{
            layer.alert('请选择要修复的订单');
        }
    }

</script>