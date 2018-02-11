<?php
namespace Admin\Controller;

/**
 * 后台主页 需要认证  通过 才可访问
 *
 */
class IndexController extends AdminController {

    public function index(){
		$this->display();
	}

    /**
     * 修改管理员密码
     */
    public function password(){
        if(!IS_POST){
            $this->display();
        }else{
            $uid = $_SESSION['user_auth']['uid'];
            $oldpassword = I('post.oldpassword', '', 'htmlspecialchars');
            $password = I('post.password', '', 'htmlspecialchars');
            $repassword = I('post.repassword', '', 'htmlspecialchars');
            $verify = I('post.verify', '', '');
            if(empty($oldpassword)) {
                $this->error('旧密码不能为空！');exit;
            }elseif (empty($password)){
                $this->error('新密码不能为空！');exit;
            }elseif ($password != $repassword){
                $this->error('两次密码不一致！');exit;
            }elseif (empty($verify)){
                $this->error('验证码不能为空！');exit;
            }
            if(!check_verify($verify, '')){
                $this->error('验证码错误!', U('Index/password'));exit;
            }

            $member = M('Member');
            if(!$member->find($uid)){
                $this->error('用户信息不存在或已被删除!');exit;
            }
            $member->password = md5($password);
            $member->modify_time = toDate(time()).'.'.getMillisecond().'000';
            if(!$member->save()){
                echo $member->getLastSql();exit;
                $this->error('修改密码失败,请重试!');exit;
            }
            $this->success('修改密码成功~!', U('Public/main'));
        }
    }

    /**
     * 后台首页数据异步获取
     */
    public function main_ajax(){
        if(!IS_POST || !IS_AJAX) exit;
        $act = I('post.act', '', 'strip_tags');
        switch($act){
            case 'data_statistics': // 数据统计
                if(checkAuth('Admin/main/datastatistics')){ // 是否有查看最新购买记录权限
                    $rechargeLogObj = M('RechargeLog');
                    $userObj = M('User');
                    $time = time();

                    // 今日销售额
                    $conditions = "user_id>0 and status=2 and add_time>='".date('Y-m-d', $time)." 00:00:00.000000' and add_time<='".date('Y-m-d', $time)." 23:59:59.999000'";
                    $todayTotleMoney = $rechargeLogObj->where($conditions)->sum('amount');
                    //定期总销售额
                    $conditions = "user_id>0 and status=2";
                    $projectTotleMoney = $rechargeLogObj->where($conditions)->sum('amount');
                    
                    //产品销售总额 +活动
                    $walletTotleMoney =  M('UserWalletRecords')->where('user_id > 0 and type in(1,4,5) and pay_status=2')->sum('value');
                    
                    // 当前用户数量
                    $userCountCache = S('CacheUserCount');
                    if(!$userCountCache){
                        $times = 3600;
                        $userCountArr['ios_count'] = $userObj->where(array('device_type'=>1,'real_name_auth'=>1))->count(); // IOS已购买
                        $userCountArr['android_count'] = $userObj->where(array('device_type'=>2,'real_name_auth'=>1))->count(); // 安卓已购买
                        $userCountArr['not_count'] = $userObj->where(array('real_name_auth'=>0))->count(); // 未购买的
                        $userCountArr['totle'] = $userCountArr['ios_count'] + $userCountArr['android_count'] + $userCountArr['not_count'];
                        $userCountArr['update_time'] = time() + $times;
                        S('CacheUserCount', $userCountArr, $times); // 缓存一个小时
                        $userCountCache = $userCountArr;
                    }
                    $userCountCache['update_time'] = date('Y-m-d H:i:s', $userCountCache['update_time']);


                    $fund = D('FundAccount')->getResult();

                    $html = '';
                    if($fund[5] < 30000){
                        $html .='<div class="moneynotice">通知：抵用金子账户余额不足3万元，请尽快充值。</div>';
                    }
//                    if($fund[6] < 30000){
//                        $html .='<div class="moneynotice">通知：加息金子账户余额不足3万元，请尽快充值。</div>';
//                    }
                    if($fund[9] < 20000){
                        $html .='<div class="moneynotice">通知：奖励金子账户余额不足2万元，请尽快充值。</div>';
                    }


                    $data = array(
                        'todayTotleMoney' => number_format($todayTotleMoney, 2),
                        'projectTotleMoney' => number_format($projectTotleMoney, 2),
                        'allTotleMoney' => number_format(($projectTotleMoney + $walletTotleMoney), 2),
                        'userCountCache' => $userCountCache,
                        'fundData' => $html,
                    );
                    $this->ajaxReturn(array('status'=>1, 'data'=>$data));
                }
                break;
            case 'latest_buy_record': // 最新购买记录
                if(checkAuth('Admin/main/latestbuyrecord')) { // 是否有查看最新购买记录权限
                    
                    $rechargeLogObj = M('RechargeLog');
                    
                    $userObj = M('User');
                    
                    $projectObj = M('Project');
                    
                    $top = 10; // 显示最新购买的数量
                    
                    $html = '';
                    
                    $cond = 'status = 2';
                    if(C('GHOST_ACCOUNT')==false){
                        $cond .=' and user_id>0';
                    }                    
                    
                    $topList = $rechargeLogObj->where($cond)->order('add_time desc')->limit($top)->select();
                    
                    foreach($topList as $key => $val){
                        
                        if($val['user_id'] > 0) $topList[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                        
                        $topList[$key]['project_title'] = $projectObj->where(array('id'=>$val['project_id']))->getField('title');
                        
                        $bagAmount = M("UserRedenvelope")->where(array("recharge_no"=>$val["recharge_no"]))->getField("amount");
                        
                        $html .= '<tr class="row lbr">';
                        $html .= '<td width="15%" align="right">'.date('Y-m-d H:i:s',strtotime($val['add_time'])).'</td>';
                        $html .= '<td>';
                        if($val['user_id'] > 0){
                            
                            $html .= '用户 <a href="'.C('ADMIN_ROOT').'/statistics/user_search/key/'.$topList[$key]['uinfo']['real_name'].'" style="color:#B34EE9;">'.$topList[$key]['uinfo']['real_name'].'</a>(<a href="'.C('ADMIN_ROOT').'/statistics/user_search/key/'.$topList[$key]['uinfo']['username'].'" style="color:#B34EE9;">'.$topList[$key]['uinfo']['username'].'</a>) 申购 <span style="color:blue;">['.$topList[$key]['project_title'].']</span> <span style="color:red;">'.$val['amount'].'</span>元 成功~!';
                            
                            if($bagAmount) {
                                
                                $html .='  ;该笔订单使用红包：<span style="color:red;">'.$bagAmount.'</span>元';
                            }
                        
                        }else{
                            if($val['user_id']<0){
                                $u = GhostUser(abs($val['user_id']));
                                if($u) {
                                    $html .= '用户 <em style="color:gray;">'.$u[1].'('.$u[0].')'.'</em> 申购 <span style="color:blue;">['.$topList[$key]['project_title'].']</span> <span style="color:red;">'.$val['amount'].'</span>元 成功~!';
                                } else{
                                    $html .= '用户 <em style="color:gray;">幽灵账户</em> 申购 <span style="color:blue;">['.$topList[$key]['project_title'].']</span> <span style="color:red;">'.$val['amount'].'</span>元 成功~!';
                                }
                            } else{
                                $html .= '用户 <em style="color:gray;">幽灵账户</em> 申购 <span style="color:blue;">['.$topList[$key]['project_title'].']</span> <span style="color:red;">'.$val['amount'].'</span>元 成功~!';
                            }
                        }
                        $html .= '</td></tr>';
                    }
                    $this->ajaxReturn(array('status'=>1,'info'=>$html));
                }
                break;
            case 'wallet_recharge_record': // 钱包充值记录
                if(checkAuth('Admin/main/walletrechargerecord')) { // 是否有查看最新购买记录权限
                    $userWalletRecordsObj = M('UserWalletRecords');
                    $userObj = M('User');
                    $top = 10; // 显示最新购买的数量
                    $html = '';
                    $topList = $userWalletRecordsObj->where(array('type'=>1,'pay_status'=>2))->order('add_time desc')->limit($top)->select();
                    foreach($topList as $key => $val){
                        $uinfo = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                        $html .= '<tr class="row wrr">';
                        $html .= '<td width="15%" align="right">'.date('Y-m-d H:i:s',strtotime($val['add_time'])).'</td>';
                        $html .= '<td>';
                        $html .= '用户 <a href="'.C('ADMIN_ROOT').'/statistics/user_search/key/'.$uinfo['real_name'].'" style="color:#B34EE9;">'.$uinfo['real_name'].'</a>(<a href="'.C('ADMIN_ROOT').'/statistics/user_search/key/'.$uinfo['username'].'" style="color:#B34EE9;">'.$uinfo['username'].'</a>) 向钱包充值 <span style="color:red;">'.$val['value'].'</span>元 成功~!';
                        $html .= '</td></tr>';
                    }
                    $this->ajaxReturn(array('status'=>1,'info'=>$html));
                }
                break;
            case 'lastest_error_payment': // 最新购买失败记录
                if(checkAuth('Admin/main/lastesterrorpayment')) { // 是否有查看最新购买记录权限
                    $top = 10; // 显示最新购买失败的数量
                    $behaviorTrackingObj = M("BehaviorTracking");
                    $behaviorTrackingExtendObj = M("BehaviorTrackingExtend");
                    $topErrorList = $behaviorTrackingObj->field('id,device_serial_id,username,add_time')->where(array('ident_id'=>17))->order("add_time desc")->limit($top)->select();
                    $html = '';
                    foreach($topErrorList as $key => $val){
                        $topErrorList[$key]['ext'] = $behaviorTrackingExtendObj->where(array('track_id'=>$val['id']))->getField('content');
                        $html .= '<tr class="row lep">';
                        $html .= '<td width="15%" align="right">'.date('Y-m-d H:i:s',$val['add_time']).'</td>';
                        $html .= '<td>';
                        if(!$val['username']){
                            $html .= '设备 '.$val['device_serial_id'].' :';
                        }else{
                            $html .= '用户 '.$val['username'].' :';
                        }
                        $html .= $topErrorList[$key]['ext'];
                        $html .= '</td></tr>';
                    }
                    $this->ajaxReturn(array('status'=>1,'info'=>$html));
                }
                break;
            case 'system_profit': // 净值
                if(checkAuth('Admin/main/datastatistics')) { // 是否有查看最新购买记录权限
                    
                    $profitAmountCache = S('cacheProfitAmount');
        
                    if(!$profitAmountCache){
                        /*
                        $sql = "SELECT SUM(amount) as amount from s_recharge_log WHERE user_id >0 and status=2 and project_id in(SELECT id FROM s_project WHERE status in(2,3,4) and is_delete=0)";
                        $ret = M()->query($sql);
                        $totalMoney = 0;
                        if($ret) {
                            $totalMoney = $ret[0]['amount'];
                        }
                        */
                        
                        //定期存量算上红包                        
                        $totalMoney = M('userDueDetail')->where('user_id>0 and status = 1')->sum('due_capital');
                        $times = 3600;
                        $cacheData['total_money'] = $totalMoney;
                        $cacheData['update_time'] = time() + $times;
                        
                        $_t = $this->getInventoryInfo(); 
                        $cacheData['buy_user'] =  $_t['buy_user'];                  
                        $cacheData['buy_count'] = $_t['buy_count'];           
                        
                        S('cacheProfitAmount', $cacheData, $times); // 缓存一个小时
                        $profitAmountCache = $cacheData;
                    } 
                    $profitAmountCache['update_time'] = date('Y-m-d H:i:s', $profitAmountCache['update_time']);
                    $profitAmountCache['total_money'] = number_format($profitAmountCache['total_money'],2);
                    $data = array('system_profit' => $profitAmountCache);
                    $this->ajaxReturn(array('status'=>1, 'data'=>$data));
                }
            break;
        }
    }
    
    /**
    * 返回存量人数，笔数
    * @date: 2017-2-8 下午12:03:12
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    private function getInventoryInfo() {    
        $r=array();
        $sql = "SELECT COUNT(user_id) AS tp_count from s_recharge_log WHERE user_id >0 and status=2 and project_id in(SELECT id FROM s_project WHERE status in(2,3,4) and is_delete=0)";
        $ret = M()->query($sql);
        $r['buy_count'] = 0;
        if($ret) {
            $r['buy_count'] = $ret[0]['tp_count'];
        }
        $sql = "SELECT COUNT(DISTINCT user_id) AS tp_count from s_recharge_log WHERE user_id >0 and status=2 and project_id in(SELECT id FROM s_project WHERE status in(2,3,4) and is_delete=0)";
        $ret = M()->query($sql);
        $r['buy_user'] = 0;
        if($ret) {
            $r['buy_user'] = $ret[0]['tp_count'];
        }
        return $r;
    }

}