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
var URL = '/admin.php/Exchange';
var APP	 =	 '/admin.php';
var PUBLIC = '/Public';
//-->
</script>
</head>

<body>
<!-- 菜单区域  -->
<script type="text/javascript" src="/Public/admin/layer-v2.4/layer.js"></script>
<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
    	<div class="title">积分兑换列表</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='get' action="<?php echo C("ADMIN_ROOT");?>/exchange/index">
                    <div class="fLeft">
                        <label for="start_time">vip等级：</label>
                        <select name="level_id">
                            <option value="-1" <?php if(($params["level_id"]) == "-1"): ?>selected<?php endif; ?>>全部</option> 
                            <?php if(is_array($params["level_list"])): foreach($params["level_list"] as $key=>$item): ?><option value="<?php echo ($item["level"]); ?>" <?php if(($params["level_id"]) == $item['level']): ?>selected<?php endif; ?>><?php echo ($item["name"]); ?></option><?php endforeach; endif; ?>  
                        </select>    
                        <span>
                        
                        <label for="start_time">奖品类型：</label>
                        <select name="type">
                            <option value="0" <?php if(($params["type"]) == "0"): ?>selected<?php endif; ?>>全部</option> 
                            <option value="1" <?php if(($params["type"]) == "1"): ?>selected<?php endif; ?>>红包</option>     
                            <option value="2" <?php if(($params["type"]) == "2"): ?>selected<?php endif; ?>>现金券</option>     
                            <option value="3" <?php if(($params["type"]) == "3"): ?>selected<?php endif; ?>>加息券</option> 
                            <option value="4" <?php if(($params["type"]) == "4"): ?>selected<?php endif; ?>>第三方券</option> 
                            <option value="5" <?php if(($params["type"]) == "5"): ?>selected<?php endif; ?>>实物奖励</option>   
                        </select>   
                                      
                    </div>
                    <div class="impBtn hMargin fLeft shadow">
                    	<input type="submit" id="" name="search" value="查询" onclick="" class="search imgButton">
                    	
                    	<?php if(checkAuth('Admin/exchange/add') == true): ?>&nbsp;&nbsp;&nbsp;<input type="button" name="add" value="新增" onclick="add3()" class="add imgButton"><?php endif; ?>
                    	
                    </div>
                </form>
            </div>
        </div>
        
        
        <!-- 功能操作区域结束 -->

        <!-- 列表显示区域  -->
        <div class="list" >
            <table id="checkList" class="list" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="5" colspan="9" class="topTd"></td>
                </tr>
                <tr class="row">
                    <th width="5%" align="center">编号</th>
                    <th width="12%" align="center">名称</th>
                    <th width="10%" align="center">类型</th> 
                    <th width="10%" align="center">vip等级</th>
                    <th width="8%" align="center">所需积分</th> 
                    <th width="8%" align="center">兑换个数</th>
                    <th width="8%" align="center">使用个数</th>
                    <th width="8%" align="center">状态</th>
                    <th width="10%" align="center">操作</th>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr>
                        <td><?php echo ($item["id"]); ?></td>
                        <td><?php echo ($item["name"]); ?></td>
                        <td>
                        	<?php switch($item["type"]): case "1": ?>红包<?php break;?>
                        		<?php case "2": ?>现金券<?php break;?>
                        		<?php case "3": ?>加息券<?php break;?>
                        		<?php case "4": ?>第三方券<?php break;?>
                        		<?php case "5": ?>实物奖励<?php break; endswitch;?>
                        </td>
                        <td align="center"><?php echo ($item["level_name"]); ?></td>
                        <td align="center"><?php echo ($item["jf_val"]); ?></td>
                        <td align="center"><?php echo ($item["total"]); ?></td>
                        <td align="center"><?php echo ($item["use_cnt"]); ?></td>
                        <td align="center">
							<?php switch($item["status"]): case "0": ?>正常<?php break;?>
                        		<?php case "1": ?><font color="red">下架</font><?php break; endswitch;?>
						</td>
                        <td align="center">
                        	<?php if(checkAuth('Admin/exchange/exchange_log') == true): ?><a href="javascript:;" onclick="detail(<?php echo ($item["id"]); ?>,'<?php echo ($item["name"]); ?>')">兑换明细</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/exchange/edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                            <?php if(checkAuth('Admin/exchange/delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; ?>
                <tr>
                    <td height="5" colspan="9" class="bottomTd"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
       
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
    
    function add3(){
    	window.location.href = ROOT + '/exchange/add';
    }
        
    function edit(_id){
    	window.location.href = ROOT + '/exchange/edit/id/' + _id ;
    }

    function del(_id){
        layer.confirm('确定删除吗?', function(){
            _layerIndex = layer.load('正在删除中...');
            $.post(ROOT + "/exchange/delete", {id: _id}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('删除成功~!', -1, function(){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info);
                }
            });
        });
    }
    
    function detail(_id,_title){
		layer.open({
  			type: 2,
  		  	skin: 'layui-layer-rim', //加上边框
  		  	area: ['960px', '480px'], //宽高
  		  	title: ''+_title,
  		  	maxmin: true,
  		  	content: '<?php echo C('ADMIN_ROOT');?>/exchange/exchange_log/exchange_id/'+_id,
  		  	cancel: function(index){
  		  		//layer.close(index);
  		  		//window.location.reload();
  		  	}
  		});
    }
</script>