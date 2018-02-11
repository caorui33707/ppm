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
var URL = '/admin.php/Contract';
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
        <div class="title">合同管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <?php if(checkAuth('Admin/contract/add') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" id="" name="add" value="新增合同" style="color:green;width:100px;" onclick="add()" class="add imgButton"></div><?php endif; ?>
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <span><input type="text" name="name1" placeholder="输入合同号开始" width="200px" value="<?php echo ($params["name1"]); ?>"></span>
                        <span><input type="text" name="name2" placeholder="输入合同号结束" width="200px" value="<?php echo ($params["name2"]); ?>"></span>
                        <span><input type="text" name="start_time" placeholder="到期日期" class="laydate-icon"  id="start_time" value="<?php echo ($params["tt"]); ?>" readonly /></span>
                    </div>
                    
                    
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" value="搜索" class="search imgButton">
                    	&nbsp;&nbsp;&nbsp;&nbsp;<a href="###" id ="export">导出excel</a>
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
                    <td height="5" colspan="20" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="2%"><!-- <input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"> --></th>
                    <th width="3%" align="center">编号</th>
                    <th width="8%" align="center">合同编号</th>
                    <th width="10%" align="center">汇票类型</th>
                    <th width="10%" align="center">票号</th>
                    <th width="8%" align="center">票面金额(元)</th>
                    <th width="8%" align="center">出票日期</th>
                    <th width="7%" align="center">到期日期</th>
                    <th width="5%" align="center">投资期限</th>
                    <th width="5%" align="center">线下利率(%)</th>
                    <!-- <th width="10%" align="center">手续费率(%)</th> -->
                    <th width="8%" align="center">剩余金额(元)</th>
                    <th width="8%" align="center">出票人</th>
                    <th width="12%" align="center">承兑人信息</th>
                    <th width="13%">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row">
                        <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" value="<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                        <td align="center"><?php echo ($item["id"]); ?></td>
                        <td><a href="javascript:;" class="icon_add" onclick="showSub(this, <?php echo ($item["id"]); ?>)"><?php echo ($item["name"]); ?></a></td>

                        <td align="center">
                            <?php switch($item["draft_type"]): case "0": ?>银行承兑汇票<?php break;?>
                                <?php case "1": ?>电子银行承兑汇票<?php break;?>
                                <?php case "2": ?>商业承兑汇票<?php break;?>
                                <?php case "3": ?>电子商业承兑汇票<?php break; endswitch;?>
                        </td>

                        <td align="right"><?php echo ($item["ticket_number"]); ?></td>
                        <td align="right"><?php echo (number_format($item["price"])); ?></td>
                        <td align="center"><?php echo (date('Y-m-d',$item["start_time"])); ?></td>
                        <td align="center"><?php echo (date('Y-m-d',$item["end_time"])); ?></td>
                        <td align="center"><?php echo ($item["days"]); ?></td>
                        <td align="center"><?php echo (number_format($item["interest"],2)); ?></td>
                        <!-- <td align="center"><?php echo (number_format($item["fee"],2)); ?></td> -->
                        <td align="right"><?php echo (number_format($item["lastprice"])); ?></td>                        
                        <td align="right"><?php echo ($item["drawer"]); ?></td>
                        <td align="right"><?php echo ($item["acceptor"]); ?></td>                        
                        <td>
                            <?php if(checkAuth('Admin/contract/add_project') == true): ?><a href="javascript:;" onclick="addProject(<?php echo ($item["id"]); ?>)">添加产品</a>&nbsp;&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/contract/edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑合同</a>&nbsp;&nbsp;<?php endif; ?>
                            <!-- 
                            <?php if(checkAuth('Admin/contract/upload') == true): ?><a href="javascript:;" onclick="filemanage(<?php echo ($item["id"]); ?>)">文件管理</a><?php endif; ?>
                             -->
                        </td>
                    </tr>
                    <?php if(is_array($item["projects"])): foreach($item["projects"] as $key=>$sub): ?><tr class="sub_<?php echo ($item["id"]); ?>" class="row" style="display:none;background-color:#F3F3F3;">
                            <td><input type="checkbox" id="check_<?php echo ($sub["id"]); ?>" alt="<?php echo ($sub["id"]); ?>"></td>
                            <td align="center">&nbsp;</td>
                            <td align="right"><?php echo ($sub["project_name"]); ?></td>
                            <td align="right"><?php echo ($sub["project_name"]); ?></td>
                            <td align="right"><?php echo (number_format($sub["price"])); ?></td>
                            <td align="center">-</td>
                            <td align="center">-</td>
                            <td align="center">-</td>
                            <td align="center">-</td>
                            <td align="center">-</td>
                            <td align="center">-</td>
                            <td>
                                <?php if(checkAuth('Admin/contract/edit_project') == true): ?><a href="javascript:;" onclick="editProject(<?php echo ($sub["id"]); ?>)">编辑产品</a><?php endif; ?>
                            </td>
                        </tr><?php endforeach; endif; endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="20" class="bottomTd"></td>
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
    var _layerIndex = 0;
    var _page = "<?php echo ($params["page"]); ?>";
    var _s = "<?php echo ($params["search"]); ?>";
    var _s2 = "<?php echo ($params["search2"]); ?>";

    function add(){
        window.location.href = ROOT + '/contract/add';
    }
    function edit(_id){
        window.location.href = ROOT + '/contract/edit/id/' + _id + '/p/' + _page + (_s ? '/s/'+_s : '') + (_s ? '/s2/'+_s2 : '');
    }
    function showSub(_obj, _id){
        if($(".sub_" + _id).css('display') == 'none'){
            $(_obj).addClass('icon_close');
            $(".sub_" + _id).css('display', 'table-row');
        }else{
            $(_obj).removeClass('icon_close');
            $(".sub_" + _id).css('display', 'none');
        }
    }
    function addProject(_cid){
        window.open(ROOT + "/contract/add_project/cid/" + _cid, 'addproject', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function editProject(_id){
        window.open(ROOT + "/contract/edit_project/id/" + _id, 'editproject', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function filemanage(_id){
        window.open(ROOT + "/contract/upload/id/" + _id, 'filemanage', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    
    var start = {
        elem: '#start_time',
        format: 'YYYY-MM-DD',
        min: '1970-00-00 00:00:00', //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: false,
        istoday: true,
    };
    
    laydate(start);
    
    
    
    var name1 = '<?php echo ($params["name1"]); ?>';
    var name2 = '<?php echo ($params["name2"]); ?>';
    var time = '<?php echo ($params["tt"]); ?>';
    
    $('#export').click(function(){    	
    	var url = "<?php echo C('ADMIN_ROOT');?>/contract/export/" +(name1 ? '/s/'+name1 : '') + (name2 ? '/s2/'+name2 : '')  + (time ? '/s3/'+time : '') ;  
    	location.href = url;
    })
    
    
    
    
</script>