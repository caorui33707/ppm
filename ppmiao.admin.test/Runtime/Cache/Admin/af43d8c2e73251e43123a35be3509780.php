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

<!-- 主页面开始 -->
<div id="main" class="main" >

    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">产品管理</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <?php if(checkAuth('Admin/project/add') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" id="" name="add" value="新增" style="color:green;" onclick="add()" class="add imgButton"></div><?php endif; ?>
            <?php if(checkAuth('Admin/project/delete') == true): ?><div class="impBtn hMargin fLeft shadow"><input type="button" id="" name="delete" value="批量删除" onclick="foreverdel()" style="width:100px;color:red;" class="delete imgButton"></div><?php endif; ?>
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <select name="status">
                            <option value="0">所有状态</option>
                            <option value="1" <?php if(($params["status"]) == "1"): ?>selected<?php endif; ?>>平台未审核</option>
                            <option value="2" <?php if(($params["status"]) == "2"): ?>selected<?php endif; ?>>销(待)售中</option>
                            <option value="3" <?php if(($params["status"]) == "3"): ?>selected<?php endif; ?>>已售罄</option>
                            <option value="4" <?php if(($params["status"]) == "4"): ?>selected<?php endif; ?>>还款中</option>
                            <option value="5" <?php if(($params["status"]) == "5"): ?>selected<?php endif; ?>>已还款</option>
                            <option value="6" <?php if(($params["status"]) == "6"): ?>selected<?php endif; ?>>未提交银行审核</option>
                            <option value="7" <?php if(($params["status"]) == "7"): ?>selected<?php endif; ?>>银行审核失败</option>
                            <option value="8" <?php if(($params["status"]) == "8"): ?>selected<?php endif; ?>>废标</option>
                        </select>

                        <span style="display: none">
                            <option value="0" <?php if(($params["tag_id"]) == "0"): ?>selected<?php endif; ?>>普通</option>
                            <option value="1" <?php if(($params["tag_id"]) == "1"): ?>selected<?php endif; ?>>新人特惠</option>
                            <option value="2" <?php if(($params["tag_id"]) == "2"): ?>selected<?php endif; ?>>爆款</option>
                            <option value="3" <?php if(($params["tag_id"]) == "3"): ?>selected<?php endif; ?>>HOT</option>
                            <option value="6" <?php if(($params["tag_id"]) == "6"): ?>selected<?php endif; ?>>活动</option>
                            <option value="8" <?php if(($params["tag_id"]) == "8"): ?>selected<?php endif; ?>>私人专享</option>
                            <option value="9" <?php if(($params["tag_id"]) == "9"): ?>selected<?php endif; ?>>月月加薪标</option>
                        </span>
                        
                        <select name="tag_id" id="tag_id" onchange="changeTag()">
                        	<option value="-1">所有标签</option>
                            <?php if(is_array($notice_tags)): foreach($notice_tags as $key=>$tag): ?><option value="<?php echo ($tag["id"]); ?>" <?php if(($params["tag_id"]) == $tag["id"]): ?>selected<?php endif; ?>><?php echo ($tag["tag_title"]); ?></option><?php endforeach; endif; ?>
                        </select>

                        <select name="draft_type_id" id="draft_typ_id" >
                            <option value="-1">所有类型</option>
                            <option value="0" <?php if(($params["draft_typ_id"]) == "0"): ?>selected<?php endif; ?>>银行承兑汇票</option>
                            <option value="1" <?php if(($params["draft_typ_id"]) == "1"): ?>selected<?php endif; ?>>电子银行承兑汇票</option>
                            <option value="2" <?php if(($params["draft_typ_id"]) == "2"): ?>selected<?php endif; ?>>商业承兑汇票</option>
                            <option value="3" <?php if(($params["draft_typ_id"]) == "3"): ?>selected<?php endif; ?>>电子商业承兑汇票</option>
                        </select>
                        
                        <select name="project_group" id="project_group">
                            
                        </select>
                        
                        <span><input type="text" name="key" placeholder="输入关键字" class="medium" value="<?php echo ($params["search"]); ?>"></span>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" value="搜索" class="search imgButton"></div>
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
                        <th width="8px"><input type="checkbox" id="check" onclick="CheckAll(this, 'checkList')"></th>
                        <th width="50px" align="center">编号</th>
                        <th width="100px" align="center">标题</th>
                        <th width="80px" align="center">项目总额(元)</th>
                        <th width="80px" align="center">汇票类型</th>
                        <th width="100px" align="center">剩余金额(元)</th>
                        <th width="60px" align="center">利率</th>
                        <th width="70px" align="center">还款审核</th>
                        <th width="100px" align="center">融资方</th>
                        <th width="100px" align="center">特殊标签</th>
                        <th width="100px" align="center">分组标签</th>
                        <th width="70px" align="center">状态</th>
                        <th width="60px" align="center">期限(天)</th>
                        <th width="80px" align="center">上线时间</th>
                        <th width="80px" align="center">到期时间</th>
                        <th width="*">操作</th>
                    </tr>
                    <?php if(is_array($list)): foreach($list as $key=>$item): ?><tr class="row" 
                        	<?php switch($item["bgcolor"]): case "1": ?>style="background-color:LightPink;"<?php break;?>
								<?php case "2": ?>style="background-color:aliceblue;"<?php break; endswitch;?>
                        >
                            <td><input type="checkbox" id="check_<?php echo ($item["id"]); ?>" alt="<?php echo ($item["id"]); ?>"></td>
                            <td align="center"><?php echo ($item["id"]); ?></td>
                            <td><?php echo ($item["title"]); ?>
                                <?php if(($item["is_countdown"]) == "1"): ?><img src="<?php echo C('STATIC_ADMIN');?>/images/icon_countdown.png" style="width:20px;" title="已开启倒计时" /><?php endif; ?>
                                <?php if(!empty($item["contract_no"])): ?><img src="<?php echo C('STATIC_ADMIN');?>/images/icon_contract.png" style="width:20px;" title="合同编号：<?php echo ($item["contract_no"]); ?>" /><?php endif; ?>
                                <?php switch($item["new_preferential"]): case "1": ?><span style="color:red;">[新人特惠]</span><?php break; case "2": ?><span style="color:red;">[爆款]</span><?php break; endswitch;?>
                            </td>
                            <td align="right"><?php echo (number_format($item["amount"])); if(($item["overflow"]) > "0"): ?><img src="<?php echo C('STATIC_ADMIN');?>/images/icon_jinggao.png" title="超出<?php echo (number_format($item["overflow"])); ?>元" /><?php endif; ?></td>

                            <td align="center">
                                <?php switch($item["draft_type"]): case "0": ?>银行承兑汇票<?php break;?>
                                    <?php case "1": ?>电子银行承兑汇票<?php break;?>
                                    <?php case "2": ?>商业承兑汇票<?php break;?>
                                    <?php case "3": ?>电子商业承兑汇票<?php break; endswitch;?>
                            </td>

                            <td align="right"><?php echo (number_format($item["able"])); ?></td>
                            <td align="right"><?php echo ($item["user_interest"]); ?>%</td>
                            <td align="center">
								<?php switch($item["repay_review"]): case "1": ?>还款金额已审核<?php break;?>
									<?php case "2": ?>融资方已经还款<?php break;?>
									<default></default><?php endswitch;?>
							</td>
                            <td align="center"><?php echo ($item["financing"]); ?></td>

                        <?php  ?>

                        <td align="center">
                            <?php switch($item["new_preferential"]): case "0": ?>普通<?php break;?>
                                <?php case "1": ?>新人特惠<?php break;?>
                                <?php case "2": ?>爆款<?php break;?>
                                <?php case "3": ?>HOT<?php break;?>
                                <?php case "6": ?>活动<?php break;?>
                                <?php case "7": ?>会员专享<?php break;?>
                                <?php case "8": ?>私人专享<?php break;?>
                                <?php case "9": ?>月月加薪标<?php break; endswitch;?>
                        </td>

                            
                            <td align="center">
                            	<?php echo ($item["group_tag_name"]); ?>
                            </td>
                            
                            <td id="status_<?php echo ($item["id"]); ?>" align="center"><?php echo ($item["status_str"]); ?></td>
                            <td align="center"><?php echo ($item["days"]); ?></td>
                            <td align="center"><?php echo (date('Y-m-d H:i:s',strtotime($item["start_time"]))); ?></td>
                            <td align="center"><?php echo (date('Y-m-d H:i:s',strtotime($item["end_time"]))); ?></td>
                            <td>
                                <?php if(checkAuth('Admin/project/detail') == true): ?><a href="javascript:;" onclick="pdetail(<?php echo ($item["id"]); ?>)">详细</a>&nbsp;<?php endif; ?>
                                <?php if(checkAuth('Admin/project/edit') == true): ?><a href="javascript:;" onclick="edit(<?php echo ($item["id"]); ?>)">编辑</a>&nbsp;<?php endif; ?>
                                <?php if(checkAuth('Admin/project/preview') == true): ?><a href="javascript:;" onclick="preview(<?php echo ($item["id"]); ?>)">预览</a>&nbsp;<?php endif; ?>
                                <!-- 
                                <?php if(checkAuth('Admin/project/verify') == true): if(($item["status"]) == "1"): ?><a href="javascript:;" style="color:green;" onclick="pass(this, <?php echo ($item["id"]); ?>)">审核</a>&nbsp;<?php endif; endif; ?>
                                -->
                                <!-- 
                                <?php if(($item["status"]) == "2"): ?><a href="javascript:;" onclick="establish(<?php echo ($item["id"]); ?>)">成标</a>&nbsp;<?php endif; ?>
                                 -->
                                <?php if(checkAuth('Admin/project/verify') == true): if(($item["status"] == 1) OR ($item["status"] == 6) ): ?><a href="javascript:;" onclick="pass(<?php echo ($item["id"]); ?>)">平台审核</a><?php endif; endif; ?>
								
								<?php if(checkAuth('Admin/project/bankVerify') == true): if($item["status"] == 6): ?><a href="javascript:;"  onclick="bankPass(<?php echo ($item["id"]); ?>)">银行审核</a><?php endif; endif; ?>
                                 
                                
                         		<?php if(($item["status"] == 2) OR ($item["status"] == 3) ): ?><a href="javascript:;" onclick="chargeoff(<?php echo ($item["id"]); ?>)">出账</a>&nbsp;<?php endif; ?>
                                
                                <?php if(checkAuth('Admin/project/delete') == true): ?><a href="javascript:;" style="color:red;" onclick="del(<?php echo ($item["id"]); ?>)">删除</a>&nbsp;<?php endif; ?>
                                <?php if(checkAuth('Admin/project/push') == true): if(($item["can_subscribe"]) == "1"): ?><a href="javascript:;" onclick="push(<?php echo ($item["id"]); ?>)" title="已订阅人数:<?php echo ($item["dy_count"]); ?>">开售预告</a>&nbsp;<?php endif; endif; ?>
                                <?php if(checkAuth('Admin/project/buylist') == true): ?><a href="javascript:;" onclick="buylist(<?php echo ($item["id"]); ?>)">购买列表</a>&nbsp;<?php endif; ?>
                                
                            </td>
                        </tr><?php endforeach; endif; ?>
                    <tr>
                        <td height="5" colspan="20" class="bottomTd"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--  分页显示区域 -->
        <div class="page"><?php echo ($show); ?></div>
        <!-- 列表显示区域结束 -->
        <div style="clear: both;"></div>
        <div><?php if(checkAuth('Admin/project/recycle') == true): ?><input type="button" value="回收站" onclick="recycle()" style="color:red;" class="add imgButton"><?php endif; ?></div>
    </div>
    <!-- 主体内容结束 -->
</div>
<!-- 主页面结束 -->
<script>
    var _layerIndex = 0;
    var _page = "<?php echo ($params["page"]); ?>";
    var _s = "<?php echo ($params["search"]); ?>";
    var _type = "<?php echo ($params["type"]); ?>";
    var _status = "<?php echo ($params["status"]); ?>";

    setTimeout('reloadPage()', 60000);
    function reloadPage(){
        window.location.reload();
    }
    function buylist(_id){
        window.open(ROOT + "/project/buylist/id/" + _id, 'buylist', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function push(_id){
        layer.load('正在推送中...');
        $.post(ROOT + "/project/push", {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.msg(msg.info, 2, -1);
            }else{
                layer.alert(msg.info, -1);
            }
        });
    }

    function advance(_id, _advance_time){ // 提前还款
        if(_advance_time != ''){ // 已设置截止日期,执行还款流程
            layer.confirm('确定要提前还款吗?', function(){
                _layerIndex = layer.load('正在处理中...');
                $.post(ROOT + "/project/advance", {id: _id, advance_time: _advance_time}, function(msg){
                    layer.close(_layerIndex);
                    if(msg.status){
                        layer.alert('操作成功~!', -1, function(){
                            window.location.reload();
                        });
                    }else{
                        layer.alert(msg.info, -1);
                    }
                });
            });
        }else{ // 设置截止日期
            var _pEndTime = prompt('请输入产品截止日期（格式：xxxx-xx-xx）：');
            if(_pEndTime != '' && _pEndTime != null){
                $.post(ROOT + "/project/advance", {id: _id, pEndTime: _pEndTime}, function(msg){
                    layer.close(_layerIndex);
                    if(msg.status){
                        layer.alert('操作成功~!', -1, function(){
                            window.location.reload();
                        });
                    }else{
                        layer.alert(msg.info, -1);
                    }
                });
            }
        }
    }
    
	function onenotice(_obj, _id){ // 一键推送
	    layer.confirm('确定一键推送标预告通知吗?', function(){
	        _layerIndex = layer.load('正在推送中...');
	        $.post(ROOT + "/project/onenotice", {id: _id}, function(msg){
	            layer.close(_layerIndex);
	            if(msg.status){
	                layer.alert('推送成功~!', -1);
	            }else{
	                layer.alert(msg.info);
	            }
	        });
	    });
	}
	
	var p_group = "<?php echo ($params["p_group"]); ?>"
	
	function changeTag(){
		var v = $("#tag_id").val();
		$.post(ROOT + "/Common2/getProjectGroupInfo", {tga_id: v}, function(msg){
    		if(msg.status){
                var s = "";
                //if(v <0){
                	
                	if(p_group == '-1'){
                		s += "<option value='-1' selected>所有分组</option>";
                	} else {
                		s += "<option value='-1'>所有分组</option>";
                	}
                	
                	if(p_group == '0'){
                		s += "<option value='0' selected>无</option>";
                	} else{
                		s += "<option value='0'>无</option>";
                	}
                	
                //}
                for (item in msg.info) {
                	if(p_group == msg.info[item].id) {
                		s += "<option value='"+msg.info[item].id+"' selected>"+msg.info[item].name+"</option>";
                	} else {
                		s += "<option value='"+msg.info[item].id+"'>"+msg.info[item].name+"</option>";
                	}
                	
                }
                $("#project_group").empty().html(s);
            }
        });
	}	
	
	changeTag();

</script>

<script type="application/javascript" src="<?php echo C('STATIC_ADMIN');?>/js/project_list.js?v=0.12"></script>