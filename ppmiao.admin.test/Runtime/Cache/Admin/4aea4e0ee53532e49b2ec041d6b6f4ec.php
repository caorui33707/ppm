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
<!-- 主页面开始 -->
<div id="main" class="main" >
    <!-- 主体内容  -->
    <div class="content" >
        <div class="title">用户查询</div>
        <!--  功能操作区域  -->
        <div class="operate" >
            <!-- 查询区域 -->
            <div class="fRig">
                <form method='post' action="">
                    <div class="fLeft">
                        <span><input type="text" name="key" placeholder="输入用户名字/手机号码/身份证" class="medium" value="<?php echo ($key); ?>"></span>
                    </div>
                    <div class="impBtn hMargin fLeft shadow"><input type="submit" id="" name="search" value="查询" onclick="" class="search imgButton"></div>
                </form>
            </div>
            <!-- 高级查询区域 -->
            <div id="addPlus" class="none search cBoth" style="display: none;width:auto;">
            </div>
        </div>

        <div class="list" >
            <?php if(!empty($user_info)): if(is_array($user_info)): foreach($user_info as $key=>$item): ?><TABLE id="checkList" class="list" cellpadding=0 cellspacing=0 >
                        <tr><td height="3" colspan="4" class="topTd" ></td></tr>
                        <tr class="row" ><th colspan="4" class="space">用户基本信息</th></tr>
                        <tr class="row">
                            <td width="20%" align="right">UID\存管账号</td><td width="25%"><?php echo ($item["id"]); ?> \ <?php echo ($item["platcust"]); ?></td>
                            <td width="15%" align="right">RegistrationID</td><td><?php echo ($item["registration_id"]); ?></td>
                        </tr>
                        <tr class="row">
                            <td width="15%" align="right">真实姓名</td><td width="15%"><?php echo ($item["real_name"]); ?>&nbsp;<?php if(($item["real_name_auth"]) == "1"): ?><span style="color:green;">[已实名]</span><?php else: ?><span style="color:red;">[未实名]</span><?php endif; ?></td>
                            <td width="15%" align="right">账户状态</td>
                            <td>
                                <?php if($item['status'] == 0 ): ?><span style="color:red">已锁定</span>
                                    <?php elseif($item['status'] == 1): ?>
                                        <span style="color:red">未激活</span>
                                    <?php elseif($item['status'] == 2): ?>
                                        正常
                                    <?php else: ?>
                                        <span style="color:red">异常</span><?php endif; ?>
                            </td>
                        </tr>
                        <tr class="row">
                            <td width="15%" align="right">手机号码</td><td width="15%"><?php echo ($item["username"]); if(checkAuth('Admin/statistics/user_update') == true): ?>&nbsp;[<a href="javascript:;" onclick="alertPhone(<?php echo ($item["id"]); ?>, '<?php echo ($item["username"]); ?>')">修改</a>]<?php endif; ?></td>
                            <td width="15%" align="right">来源渠道</td><td><?php if($item["channelStr"] == '点入'): echo ($item["channelStr"]); ?>(注:AppStore)<?php else: echo ($item["channelStr"]); endif; ?></td>
                        </tr>
                        <tr class="row">
                            <td width="15%" align="right">身份证</td><td width="15%"><?php echo ($item["card_no"]); ?></td>
                            <td width="15%" align="right">系统版本</td><td><?php echo ($item["phone_system_version"]); ?></td>
                        </tr>
                        <tr class="row">
                            <td width="15%" align="right">年龄</td><td width="15%"><?php echo ($item["year"]); ?>&nbsp(<?php echo ($item["sex"]); ?>)</td>
                            <td width="15%" align="right">序列号</td><td><?php echo ($item["device_serial_id"]); ?></td>
                        </tr>
                        <tr class="row">
                            <td width="15%" align="right">注册时间</td><td width="15%"><?php echo ($item["register_time"]); ?></td>
                            <td width="15%" align="right">票票币</td><td><?php echo (number_format($item["money_stone"])); if(checkAuth('Admin/statistics/user_update') == true): ?>&nbsp;[<a href="javascript:;" onclick="giveStoneMoney(<?php echo ($item["id"]); ?>)">赠送</a>]<?php endif; ?></td>
                        </tr>
                        <tr class="row">
                            <td width="15%" align="right">账户金额</td>
                            <td width="15%"><?php echo (number_format($item["wallet_totle"],4)); ?>&nbsp;
                            	<a href="javascript:;" onclick="withdrawals(<?php echo ($item["id"]); ?>)">提现记录</a>&nbsp;
                            	<a href="javascript:;" onclick="recharge(<?php echo ($item["id"]); ?>)">充值记录</a>&nbsp;
                            	<!-- 
                            	<a href="javascript:;" onclick="userInterestList(<?php echo ($item["id"]); ?>)">用户利息列表</a>&nbsp;
                            	<a href="javascript:;" onclick="supplyWalletRecords(<?php echo ($item["id"]); ?>,'<?php echo ($item["key"]); ?>')">钱包补记录</a>
                            	 -->
                            </td>

                            <td width="15%" align="right">APP版本号</td>
                            <td><?php echo ($item["app_version"]); ?></td>
                        </tr>
                        <tr class="row" >
                            <th colspan="4" class="space">购买记录
                            	<?php if(checkAuth('Admin/statistics/history_order') == true): ?>&nbsp;&nbsp;
                            		<a href="javascript:;" onclick="history(<?php echo ($item["id"]); ?>)">历史下单记录</a><?php endif; ?> 
                            	<?php if(checkAuth('Admin/statistics/bank_list') == true): ?>&nbsp;&nbsp;
                            		<a href="javascript:;" onclick="bank_list(<?php echo ($item["id"]); ?>)">用户银行卡信息</a><?php endif; ?>
                            	<?php if(checkAuth('Admin/statistics/due_search') == true): ?>&nbsp;&nbsp;
                            		<a href="javascript:;" onclick="due_search(<?php echo ($item["id"]); ?>,'going')">用户在投项目</a><?php endif; ?>
                            	<?php if(checkAuth('Admin/statistics/due_search') == true): ?>&nbsp;&nbsp;
                            		<a href="javascript:;" onclick="due_search(<?php echo ($item["id"]); ?>,'history')">用户累计投资项目</a><?php endif; ?> 
                            	<?php if(checkAuth('Admin/statistics/user_track') == true): ?>&nbsp;&nbsp;
                            		<a href="javascript:;" onclick="user_address(<?php echo ($item["id"]); ?>)">收货地址</a><?php endif; ?>
                            </th>
                        </tr>
                        
                        
                        <tr class="row">
                            <td colspan="4">
                                <table cellpadding=0 cellspacing=0 width="100%">
                                    <thead>
                                    <tr>
                                        <th>产品名称</th>
                                        <th>投资金额(元)</th>
                                        <th>充值编码</th>
                                        <th>产品期限(天)</th>
                                        <th>产品利率(%)</th>
                                        <th>加息券(%)</th>
                                        <th>产品利息</th>
                                        <th>投资卡号</th>
                                        <th>所属银行</th>
                                        <th>支行</th>
                                        <th>持有人名称</th>
                                        <th>投资时间</th>
                                        <th>到期时间</th>
										<th>后台补单操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(is_array($item["recharge_log_list"])): foreach($item["recharge_log_list"] as $key=>$sub): ?><tr class="row">
                                            <td><?php echo ($sub["project_info"]["title"]); ?>(编号:<?php echo ($sub["project_id"]); ?>)</td>
                                            <td ><?php echo (number_format($sub["amount"],2)); ?></td>
                                            <td><?php echo ($sub["recharge_no"]); ?></td>
                                            <td><?php echo ($sub["duration_day"]); ?></td>
                                            <td><?php echo ($sub["project_info"]["user_interest"]); ?></td>
                                            <td><?php echo ($sub["interest_coupon"]); ?></td>
                                            <td><?php echo ($sub["due_interest"]); ?></td>
                                            <?php if(($sub["type"]) == "3"): ?><td>零钱包</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                            <?php else: ?>
                                                <td><?php echo ($sub["card_no"]); ?></td>
                                                <td><?php echo ($sub["card_from"]); ?></td>
                                                <td><?php echo ($sub["card_area"]); ?>&nbsp;<?php echo ($sub["card_address"]); if(checkAuth('Admin/statistics/user_bank_update') == true): ?><a href="javascript:;" onclick="update_bank(<?php echo ($sub["card_id"]); ?>, '<?php echo ($sub["card_address"]); ?>')">[更新]</a><?php endif; ?></td>
                                                <td><?php echo ($sub["card_uname"]); ?></td><?php endif; ?>
                                            <td width="180px"><?php echo (date('Y-m-d H:i:s',strtotime($sub["add_time"]))); ?></td>
                                            <td><?php echo (date('Y-m-d H:i:s',strtotime($sub["project_info"]["end_time"]))); ?></td>
											<?php if(($sub["type"]) == "3"): ?><td>禁止操作</td>
											<?php else: ?>
											<td><a href="javascript:;" onclick="checkSingleOut('<?php echo ($sub["recharge_id"]); ?>')">[核实是否掉单]</a> | <a href="javascript:;" onclick="supplySingle('<?php echo ($sub["recharge_id"]); ?>')">[补单]</a></td><?php endif; ?>
                                        </tr><?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr><td height="3" colspan="4" class="bottomTd"></td></tr>
                    </TABLE><?php endforeach; endif; endif; ?>
        </div>
    </div>
<script>
    var _layerIndex = 0;
    function alertPhone(_uid, _phone){
        var _newPhone = prompt('请输入正确的手机号码:', _phone);
        if(_newPhone != '' && _newPhone != null){
            _layerIndex = layer.load('正在修改中...');
            $.post("<?php echo C('ADMIN_ROOT');?>/statistics/user_update", {uid: _uid, phone: _newPhone, act: 'alert_phone'}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('修改成功~!', -1, function(){
                       window.location.reload();
                    });
                }else{
                    layer.msg(msg.info, 1, -1);
                }
            });
        }
    }
    function giveStoneMoney(_uid){ // 赠送票票币
        var _stoneMoney = prompt('请输入要赠送的票票币数量:', 0);
        if(_stoneMoney != null && _stoneMoney != 0){
            _layerIndex = layer.load('正在赠送中...');
            $.post("<?php echo C('ADMIN_ROOT');?>/statistics/user_update", {uid: _uid, money: _stoneMoney, act: 'stone_money'}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('赠送成功~!', -1, function(){
                        window.location.reload();
                    });
                }else{
                    layer.msg(msg.info, 1, -1);
                }
            });
        }
    }
    function update_bank(_bank_id, _default){
        var _bank_info = prompt('请输入支行信息:', _default);
        if(_bank_info != '' && _bank_info != null){
            _layerIndex = layer.load('修改中...');
            $.post(ROOT + "/statistics/user_bank_update", {bid: _bank_id, binfo: _bank_info}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('更新银行支行信息成功~', -1, function(){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info, -1);
                }
            });
        }
    }
    function history(_uid){
        window.open(ROOT + "/statistics/history_order/uid/" + _uid, 'historyOrder', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function withdrawals(_uid){
        window.open(ROOT + "/statistics/withdrawals_order/uid/" + _uid, 'withdrawalsOrder', 'height=500,width=950,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function recharge(_uid){
        window.open(ROOT + "/statistics/recharge_order/uid/" + _uid, 'rechargeOrder', 'height=500,width=950,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    function bank_list(_uid){
        window.open(ROOT + "/statistics/bank_list/uid/" + _uid, 'bankList', 'height=500,width=960,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
	 function due_search(_uid,_type){
        window.open(ROOT + "/statistics/due_search/user_id/" + _uid+"/type/"+_type, 'dueSearch', 'height=500,width=960,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
	//核实是否掉单
	function checkSingleOut(_id){
        _layerIndex = layer.load('正在核查中...');
		$.post(ROOT + "/statistics/checkSingleOut", {id: _id}, function(msg){
			layer.close(_layerIndex);
			if(msg.status){
				layer.alert(msg.info, -1, function(){
					window.location.reload();
				});
			}else{
				layer.alert(msg.info, -1);
			}
		});
    }
	//核实了，确实需要补单
	function supplySingle(_id){
        layer.confirm('确定要补单吗?', function(){
                _layerIndex = layer.load('正在补单中...');
                $.post(ROOT + "/statistics/supplySingle", {id: _id}, function(msg){
                    layer.close(_layerIndex);
                    if(msg.status){
                        layer.alert(msg.info, -1, function(){
                            window.location.reload();
                        });
                    }else{
                        layer.alert(msg.info, -1);
                    }
                });
		})
    }
    //用户利息列表
    function userInterestList(_uid){
        window.open(ROOT + "/statistics/userInterestList/uid/" + _uid, 'rechargeOrder', 'height=500,width=950,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }
    //补记录
    function  supplyWalletRecords(_uid,_key){
        window.location.href = ROOT + "/statistics/userWalletAddRecords/user_id/"+_uid+"/key/"+_key;
    }
    
    
  	//用户收货地址
    function user_address(_uid){
        window.open(ROOT + "/statistics/get_user_address/uid/" + _uid, 'rechargeOrder', 'height=500,width=950,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
    }

</script>
<!-- 主页面结束 -->