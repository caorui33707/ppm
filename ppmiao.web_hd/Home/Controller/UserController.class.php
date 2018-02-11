<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 用户中心控制器
 * @package Home\Controller
 */
class UserController extends BaseController{

    /**
     * 用户中心首页
     */
    public function index(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }
        if(!IS_POST){

            // 获取用户钱包总额
            $userAccountObj = M("UserAccount");
            $walletTotle = $userAccountObj->where(array('user_id'=>$userInfo['id']))->getField('wallet_totle');
            $this->assign('wallet_totle', $walletTotle);

            // 在投本金和当前盈亏
            $projectModelFundObj = M("ProjectModelFund");
            $fundDetailObj = M("FundDetail");
            $userDueDetailObj = M("UserDueDetail");
            $projectObj = M("Project");
            $dueList = $userDueDetailObj->where('user_id='.$userInfo['id'].' and (status_new=1 or status_new=3)')->select();
            $dueTotle = 0; // 在投本金
            $dueInterest = 0; // 当前盈亏
            foreach($dueList as $key => $val){
                $dueTotle += $val['due_capital'];
                $pinfo = $projectObj->field('id,title,type,user_interest,start_time,end_time,advance_end_time')->where(array('id'=>$val['project_id']))->find(); // 产品类型
                switch($pinfo['type']){
                    case 104:
                        $percent = 0;
                        $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$pinfo['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'DXG'); // 结束净值
                            $percent = ($endFundValue - $startFundValue)/$startFundValue;
                        }
                        $dueInterest += $val['due_capital']*$percent;
                        break;
                    case 109:
                        $percent = 0;
                        $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$pinfo['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'B'); // 结束净值
                            $percent = ($endFundValue - $startFundValue)/$startFundValue;
                        }
                        $dueInterest += $val['due_capital']*$percent;
                        break;
                    case 110:
                        $percent = 0;
                        $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$pinfo['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'A'); // 结束净值
                            $percent = ($endFundValue - $startFundValue)/$startFundValue;
                        }
                        $dueInterest += $val['due_capital']*$percent;
                        break;
                    default:
                        $dueInterest += $val['due_interest'];
                        break;
                }
            }
            $this->assign('due_totle', $dueTotle);
            $this->assign('due_interest', $dueInterest);

            // 我的理财(取最近3条待还款记录)
            $uDueList = $userDueDetailObj->field(true)->where(array('user_id'=>$userInfo['id'],'status_new'=>1))->order('due_time asc')->limit(3)->select();
            foreach($uDueList as $key => $val){
                $uDueList[$key]['pinfo'] = $projectObj->field('id,title,type,user_interest,start_time,end_time,advance_end_time')->where(array('id'=>$val['project_id']))->find(); // 产品信息
                switch($uDueList[$key]['pinfo']['type']){
                    case 104:
                        $percent = 0;
                        $per = 0;
                        $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$uDueList[$key]['pinfo']['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($uDueList[$key]['pinfo']['advance_end_time'] ? $uDueList[$key]['pinfo']['advance_end_time'] : $uDueList[$key]['pinfo']['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 0 && $fundList[0]['datetime'] != $startTime){ // 补全由于遇到节假日而不存在的净值数据
                            // 遇到节假日期间净值则取节假日之前的第一个有效净值
                            $fundStart = $fundDetailObj->where("fund_id=" . $proFundInfo['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->order("datetime desc")->getField('val');
                            // 当前取出净值数据往前推至真实进入点日期
                            $valRows = array(
                                'val' => $fundStart,
                                'datetime' => $startTime,
                            );
                            array_unshift($fundList, $valRows);
                        }
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $yestodayFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-2]['val'], 'DXG'); // 结束前一日净值
                            $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'DXG'); // 结束净值
                            $percent = ($endFundValue - $startFundValue)/$startFundValue;
                            $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                        }
                        $uDueList[$key]['due_interest'] = $val['due_capital']*$percent;
                        $uDueList[$key]['pinfo']['user_interest'] = $per;
                        break;
                    case 109:
                        $percent = 0;
                        $per = 0;
                        $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$uDueList[$key]['pinfo']['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($uDueList[$key]['pinfo']['advance_end_time'] ? $uDueList[$key]['pinfo']['advance_end_time'] : $uDueList[$key]['pinfo']['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 0 && $fundList[0]['datetime'] != $startTime){ // 补全由于遇到节假日而不存在的净值数据
                            // 遇到节假日期间净值则取节假日之前的第一个有效净值
                            $fundStart = $fundDetailObj->where("fund_id=" . $proFundInfo['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->order("datetime desc")->getField('val');
                            // 当前取出净值数据往前推至真实进入点日期
                            $valRows = array(
                                'val' => $fundStart,
                                'datetime' => $startTime,
                            );
                            array_unshift($fundList, $valRows);
                        }
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $yestodayFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-2]['val'], 'B'); // 结束前一日净值
                            $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'B'); // 结束净值
                            $percent = ($endFundValue - $startFundValue)/$startFundValue;
                            $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                        }
                        $uDueList[$key]['due_interest'] = $val['due_capital']*$percent;
                        $uDueList[$key]['pinfo']['user_interest'] = $per;
                        break;
                    case 110:
                        $percent = 0;
                        $per = 0;
                        $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$uDueList[$key]['pinfo']['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($uDueList[$key]['pinfo']['advance_end_time'] ? $uDueList[$key]['pinfo']['advance_end_time'] : $uDueList[$key]['pinfo']['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 0 && $fundList[0]['datetime'] != $startTime){ // 补全由于遇到节假日而不存在的净值数据
                            // 遇到节假日期间净值则取节假日之前的第一个有效净值
                            $fundStart = $fundDetailObj->where("fund_id=" . $proFundInfo['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->order("datetime desc")->getField('val');
                            // 当前取出净值数据往前推至真实进入点日期
                            $valRows = array(
                                'val' => $fundStart,
                                'datetime' => $startTime,
                            );
                            array_unshift($fundList, $valRows);
                        }
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $yestodayFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-2]['val'], 'A'); // 结束前一日净值
                            $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'A'); // 结束净值
                            $percent = ($endFundValue - $startFundValue)/$startFundValue;
                            $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                        }
                        $uDueList[$key]['due_interest'] = $val['due_capital']*$percent;
                        $uDueList[$key]['pinfo']['user_interest'] = $per;
                        break;
                    case 148: // 博息宝
                        $projectModelSectionObj = M("ProjectModelSection");
                        $percent = 0;
                        $proSectionInfo = $projectModelSectionObj->where(array('project_id'=>$uDueList[$key]['pinfo']['id']))->find();
                        $startTime = date('Y-m-d', strtotime($proSectionInfo['enter_time']));
                        $endTime = date('Y-m-d', strtotime($uDueList[$key]['pinfo']['advance_end_time'] ? $uDueList[$key]['pinfo']['advance_end_time'] : $uDueList[$key]['pinfo']['end_time']));
                        $fundList = $fundDetailObj->where('fund_id='.$proSectionInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                        if(count($fundList) > 0 && $fundList[0]['datetime'] != $startTime){ // 补全由于遇到节假日而不存在的净值数据
                            // 遇到节假日期间净值则取节假日之前的第一个有效净值
                            $fundStart = $fundDetailObj->where("fund_id=" . $proSectionInfo['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->order("datetime desc")->getField('val');
                            // 当前取出净值数据往前推至真实进入点日期
                            $valRows = array(
                                'val' => $fundStart,
                                'datetime' => $startTime,
                            );
                            array_unshift($fundList, $valRows);
                        }
                        if(count($fundList) > 1){
                            $startFundValue = $fundList[0]['val']; // 起始净值
                            $yestodayFundValue = $fundList[count($fundList)-2]['val']; // 前日净值
                            $endFundValue = $fundList[count($fundList)-1]['val']; // 结束净值
                            $percent = (($endFundValue - $startFundValue)/$startFundValue)*100;
                            if($percent <  $uDueList[$key]['pinfo']['user_interest']){
                                $percent =  $uDueList[$key]['pinfo']['user_interest'];
                            }else if($percent > $proSectionInfo['max_interest']){
                                $percent = $proSectionInfo['max_interest'];
                            }
                            $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                        }else{
                            $percent = $uDueList[$key]['pinfo']['user_interest'];
                        }
                        $currentDatetime = date('Y-m-d H:i:s', time());
                        if($uDueList[$key]['due_time'] < $currentDatetime) $currentDatetime = $uDueList[$key]['due_time'];
                        $uDueList[$key]['due_interest'] = $val['due_capital']*$percent*count_days($currentDatetime, date('Y-m-d', strtotime($uDueList[$key]['add_time'])))/100/365;
                        $uDueList[$key]['pinfo']['user_interest'] = $per; // ETF昨日涨跌幅
                        break;
                }
            }
            $this->assign('u_due_list', $uDueList);

            // 获取用户绑定银行卡
            $userBankObj = M("UserBank");
            $bankList = $userBankObj->field('id,bank_name,bank_code,bank_card_no')->where(array('user_id'=>$userInfo['id'],'has_pay_success'=>2))->order('latest_payment_time desc,add_time desc')->limit(3)->select();
            $this->assign('bank_list', $bankList);

            $this->display();
        }else{
            // 钱包充值
            $money = I('post.money', 0, 'int');
            if($money > 0){
                $rows = array(
                    'id' => 0,
                    'money' => $money,
                );
                redirect(C('WEB_ROOT').'/product/pay/'.st_encrypt(json_encode($rows), C('PRODUCT_KEY')));
            }else{
                $this->error('充值金额不正确');exit;
            }
        }
    }

    /**
     * 我的钱包
     */
    public function wallet(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }
        if(!IS_POST){
            $timeNow = time();
            $yestoday = strtotime('-1 days', $timeNow);
            $yestoday = $timeNow;
            $sevenStart = strtotime('-6 days', $yestoday); // 七日年化开始时间
            $userAccountObj = M("UserAccount");
            $userWalletInterestObj = M("UserWalletInterest");
            $userWalletAnnualizedRateObj = M("UserWalletAnnualizedRate");

            $accountInfo = $userAccountObj->field('id,wallet_totle,to_wallet,wallet_interest_totle')->where(array('user_id'=>$userInfo['id']))->find();
            // 昨日收益
            $yestodayInterest = $userWalletInterestObj->where(array('user_id'=>$userInfo['id']))->order('add_time desc')->getField('interest');
            // 今日年化利率
            $today_rate = $userWalletAnnualizedRateObj->where("add_time='".date('Y-m-d',$timeNow)."'")->getField('rate');
            if(!$today_rate) $today_rate = 7.13;
            // 近一月收益
            $oneMonthStart = strtotime('-1 month', $timeNow);
            $monthSum = $userWalletInterestObj->where(array('user_id'=>$userInfo['id']))->sum('interest');
            $this->assign('month_sum', $monthSum);

            // 七日年化收益率
            $sevenYearList = $userWalletAnnualizedRateObj->where("add_time>='".date('Y-m-d',$sevenStart)."' and add_time<='".date('Y-m-d',$yestoday)."'")->limit(7)->order('add_time asc')->select();
            if($sevenYearList) $this->assign('yestoday_percent', $sevenYearList[count($sevenYearList)-1]['rate']);
            $xLabel = ''; $yLabel = '';
            $year = date('Y', time());
            foreach($sevenYearList as $key => $val){
                $xLabel .= ",'".date('Y-m-d',strtotime($val['add_time']))."'";
                $yLabel .= ",".$val['rate'];
                $sevenYearList[$key]['add_time'] = strtotime($val['add_time'])*1000;
            }

            if($xLabel) {
                $xLabel = str_replace($year.'-', '', substr($xLabel, 1));
            }
            if($yLabel) $yLabel = substr($yLabel, 1);
            $this->assign('xLabel', $xLabel);
            $this->assign('yLabel', $yLabel);
            $this->assign('seven_year_list', $sevenYearList);
            $this->assign('yestoday_interest', $yestodayInterest);
            $this->assign('today_rate', $today_rate);
            $this->assign('user_account', $accountInfo);
            $this->display();
        }else{
            if(IS_AJAX){
                $page = I('post.page', 1, 'int');

                $userWalletRecordsObj = M("UserWalletRecords");
                $userBankObj = M("UserBank");

                // 分页
                $conditions = 'user_id='.$userInfo['id'].' and ((type=1 and pay_status=2 and pay_type>0) or type=2)';
                $count = 8;
                $counts = $userWalletRecordsObj->where($conditions)->count();
                $Page = new \Think\PageAjax($counts, $count, array('event'=>'loadWalletRecord','page'=>$page)); // 自定义分页类
                $show = $Page->show();
                // 列表数据
                $list = $userWalletRecordsObj->field('id,type,value,status,pay_status,user_bank_id,add_time')->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $html = '';
                foreach($list as $key => $val){
                    if($val['user_bank_id'] > 0) $bankInfo = $userBankObj->field('bank_name,bank_card_no')->where(array('id'=>$val['user_bank_id']))->find();
                    $html .= '<dd class="item">';
                    $html .= '<span class="span01">'.date('Y-m-d H:i:s', strtotime($val['add_time'])).'</span>';
                    if($val['value'] > 0){
                        $html .= '<span class="span02" style="color:green;">+'.number_format($val['value'], 2).'</span>';
                    }else{
                        $html .= '<span class="span02" style="color:red;">'.number_format($val['value'], 2).'</span>';
                    }
                    if($val['user_bank_id'] == 0){
                        $html .= '<span class="span03">钱包</span>';
                    }else{
                        $html .= '<span class="span03">'.$bankInfo['bank_name'].'('.substr($bankInfo['bank_card_no'], strlen($bankInfo['bank_card_no'])-4).')</span>';
                    }
                    $html .= '<span class="span04">';
                    if($val['type'] == 1){ // 转入
                        switch($val['pay_status']){
                            case 1:
                                $html .= '交易中';
                                break;
                            case 2:
                                $html .= '交易完成';
                                break;
                            case 3:
                                $html .= '交易失败';
                                break;
                        }
                    }else if($val['type'] == 2){ // 转出
                        switch($val['status']){
                            case 1:
                                $html .= '交易完成';
                                break;
                            case 2:
                                $html .= '交易中';
                                break;
                            case 3:
                                $html .= '等待处理';
                                break;
                        }
                    }else if($val['type'] == 3){ // 钱包购买产品
                        $html .= '交易完成';
                    }
                    $html .= '</span>';
                    $html .= '</dd>';
                }
                $rows = array(
                    'show' => $show,
                    'list' => $html,
                );
                $this->ajaxReturn(array('status'=>1,'data'=>$rows));
            }else{
                // 钱包充值
                $money = I('post.money', 0, 'int');
                if($money > 0){
                    $rows = array(
                        'id' => 0,
                        'money' => $money,
                    );
                    redirect(C('WEB_ROOT').'/product/pay/'.st_encrypt(json_encode($rows), C('PRODUCT_KEY')));
                }else{
                    $this->error('充值金额不正确');exit;
                }
            }
        }
    }

    /**
     * 我的理财
     */
    public function product(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }
        if(!IS_POST){
            $this->display();
        }else{
            $target = I('post.target', 'norepay', 'strip_tags');
            $page = I('post.page', 1, 'int');

            $userDueDetailObj = M("UserDueDetail");
            $projectObj = M("Project");
            $fundDetailObj = M("FundDetail");
            $projectModelFundObj = M("ProjectModelFund");

            if($target == 'norepay') { // 未还款
                // 分页
                $count = 6;
                $counts = $userDueDetailObj->where('user_id='.$userInfo['id'].' and status_new in (1,3)')->count();
                $Page = new \Think\PageAjax($counts, $count, array('event'=>'loadNRepayProRecord','target'=>'norepay','page'=>$page)); // 自定义分页类
                $show = $Page->show();
                // 列表数据
                $list = $userDueDetailObj->where('user_id='.$userInfo['id'].' and status_new in (1,3)')->order('status_new desc,due_time asc,add_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $html = '';
                foreach($list as $key => $val){
                    $pinfo = $projectObj->field('id,title,type,user_interest,start_time,end_time,advance_end_time')->where(array('id'=>$val['project_id']))->find();
                    $syl = '年化收益';
                    $dslx = '待收利息';
                    $per = $pinfo['user_interest'];
                    switch($pinfo['type']){
                        case 104:
                            $percent = 0;
                            $per = 0;
                            $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$pinfo['id']))->find();
                            $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                            $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                            $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                            if(count($fundList) > 1){
                                $startFundValue = $fundList[0]['val']; // 起始净值
                                $yestodayFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-2]['val'], 'DXG'); // 结束前一日净值
                                $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'DXG'); // 结束净值
                                $percent = ($endFundValue - $startFundValue)/$startFundValue;
                                $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                            }
                            $list[$key]['due_interest'] = $val['due_capital']*$percent;
                            $syl = '昨日涨跌幅';
                            $dslx = '当前盈亏';
                            break;
                        case 109:
                            $percent = 0;
                            $per = 0;
                            $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$pinfo['id']))->find();
                            $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                            $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                            $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                            if(count($fundList) > 1){
                                $startFundValue = $fundList[0]['val']; // 起始净值
                                $yestodayFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-2]['val'], 'B'); // 结束前一日净值
                                $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'B'); // 结束净值
                                $percent = ($endFundValue - $startFundValue)/$startFundValue;
                                $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                            }
                            $list[$key]['due_interest'] = $val['due_capital']*$percent;
                            $syl = '昨日涨跌幅';
                            $dslx = '当前盈亏';
                            break;
                        case 110:
                            $percent = 0;
                            $per = 0;
                            $proFundInfo = $projectModelFundObj->field('fund_id,enter_time')->where(array('project_id'=>$pinfo['id']))->find();
                            $startTime = date('Y-m-d', strtotime($proFundInfo['enter_time']));
                            $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                            $fundList = $fundDetailObj->where('fund_id='.$proFundInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                            if(count($fundList) > 1){
                                $startFundValue = $fundList[0]['val']; // 起始净值
                                $endFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-1]['val'], 'A'); // 结束净值
                                $yestodayFundValue = formula_fund_net($startFundValue, $fundList[count($fundList)-2]['val'], 'A'); // 结束前一日净值
                                $percent = ($endFundValue - $startFundValue)/$startFundValue;
                                $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                            }
                            $list[$key]['due_interest'] = $val['due_capital']*$percent;
                            $syl = '昨日涨跌幅';
                            $dslx = '当前盈亏';
                            break;
                        case 139: // 股权产品
                            $syl = '';
                            $dslx = '对应股份(股)';
                            $projectModelEquityObj = M("ProjectModelEquity");
                            $list[$key]['ext'] = $projectModelEquityObj->field('evaluation_source,evaluation')->where(array('project_id'=>$pinfo['id']))->find();
                            break;
                        case 148: // 博息宝
                            $projectModelSectionObj = M("ProjectModelSection");
                            $percent = 0;
                            $per = 0;
                            $proSectionInfo = $projectModelSectionObj->where(array('project_id'=>$pinfo['id']))->find();
                            $startTime = date('Y-m-d', strtotime($proSectionInfo['enter_time']));
                            $endTime = date('Y-m-d', strtotime($pinfo['advance_end_time'] ? $pinfo['advance_end_time'] : $pinfo['end_time']));
                            $fundList = $fundDetailObj->where('fund_id='.$proSectionInfo['fund_id'].' and datetime>=\''.$startTime.'\' and datetime<=\''.$endTime.'\'')->order('datetime asc')->select();
                            if(count($fundList) > 0 && $fundList[0]['datetime'] != $startTime){ // 补全由于遇到节假日而不存在的净值数据
                                // 遇到节假日期间净值则取节假日之前的第一个有效净值
                                $fundStart = $fundDetailObj->where("fund_id=" . $proSectionInfo['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->getField('val');
                                // 当前取出净值数据往前推至真实进入点日期
                                $valRows = array(
                                    'val' => $fundStart,
                                    'datetime' => $startTime,
                                );
                                array_unshift($fundList, $valRows);
                            }
                            if(count($fundList) > 1){
                                $startFundValue = $fundList[0]['val']; // 起始净值
                                $endFundValue = $fundList[count($fundList)-1]['val']; // 结束净值
                                $yestodayFundValue = $fundList[count($fundList)-2]['val']; // 结束前一日净值
                                $percent = (($endFundValue - $startFundValue)/$startFundValue)*100;
                                if($percent <  $pinfo['user_interest']){
                                    $percent =  $pinfo['user_interest'];
                                }else if($percent > $proSectionInfo['max_interest']){
                                    $percent = $proSectionInfo['max_interest'];
                                }
                                $per = (($endFundValue - $yestodayFundValue)/$yestodayFundValue)*100;
                            }
                            $currentDatetime = date('Y-m-d H:i:s', time());
                            if($val['due_time'] < $currentDatetime) $currentDatetime = $val['due_time'];
                            $list[$key]['due_interest'] = $val['due_capital']*$percent*count_days($currentDatetime, date('Y-m-d', strtotime($val['add_time'])))/100/365;
                            $syl = 'ETF昨日趋势';
                            $dslx = '待收利息';
                            break;
                    }
                    $html .= '<li><dl>';
                    if($pinfo['type'] == 139){
                        $html .= '<dd class="dd01"><a href="'.C('WEB_ROOT').'/user/product/detail/'.$val['id'].'"><h5>'.$pinfo['title'].'</h5></a><p><em>&nbsp;</em> &nbsp;</p></dd>';
                    }else{
                        if($per > 0){
                            $html .= '<dd class="dd01"><a href="'.C('WEB_ROOT').'/user/product/detail/'.$val['id'].'"><h5>'.$pinfo['title'].'</h5></a><p><em>'.number_format($per, 2).'%</em> '.$syl.'</p></dd>';
                        }else if($per < 0){
                            $html .= '<dd class="dd01"><a href="'.C('WEB_ROOT').'/user/product/detail/'.$val['id'].'"><h5>'.$pinfo['title'].'</h5></a><p><em style="color:green;">'.number_format($per, 2).'%</em> '.$syl.'</p></dd>';
                        }else{
                            $html .= '<dd class="dd01"><a href="'.C('WEB_ROOT').'/user/product/detail/'.$val['id'].'"><h5>'.$pinfo['title'].'</h5></a><p><em style="color:black;">'.number_format($per, 2).'%</em> '.$syl.'</p></dd>';
                        }
                    }
                    $html .= '<dd class="dd02"><h5>'.number_format($val['due_capital'], 2).'</h5><p>投资金额</p></dd>';
                    if($pinfo['type'] == 139){
                        $html .= '<dd class="dd03"><h5 class="red">'.($list[$key]['due_capital']/$list[$key]['ext']['evaluation_source']).'</h5><p>'.$dslx.'</p></dd>';
                    }else{
                        if($list[$key]['due_interest'] > 0){
                            $html .= '<dd class="dd03"><h5 class="red">'.number_format($list[$key]['due_interest'], 2).'</h5><p>'.$dslx.'</p></dd>';
                        }else if($list[$key]['due_interest'] < 0){
                            $html .= '<dd class="dd03"><h5 class="green">'.number_format($list[$key]['due_interest'], 2).'</h5><p>'.$dslx.'</p></dd>';
                        }else{
                            $html .= '<dd class="dd03"><h5>'.number_format($list[$key]['due_interest'], 2).'</h5><p>'.$dslx.'</p></dd>';
                        }
                    }
                    $html .= '</dl></li>';
                }
                $rows = array(
                    'show' => $show,
                    'list' => $html,
                );
                $this->ajaxReturn(array('status'=>1,'data'=>$rows));
            }else if($target == 'repay'){ // 已还款
                // 分页
                $count = 6;
                $counts = $userDueDetailObj->where('user_id='.$userInfo['id'].' and status_new=2')->count();
                $Page = new \Think\PageAjax($counts, $count, array('event'=>'loadRepayProRecord','target'=>'repay','page'=>$page)); // 自定义分页类
                $show = $Page->show();
                // 列表数据
                $list = $userDueDetailObj->where('user_id='.$userInfo['id'].' and status_new=2')->order('real_time desc,due_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $html = '';
                foreach($list as $key => $val){
                    $pinfo = $projectObj->field('id,title,type,user_interest,start_time,end_time,advance_end_time')->where(array('id'=>$val['project_id']))->find();
                    $detail = $projectObj->where(array('id' =>$val['project_id'], 'is_delete' => 0))->find();
                    $percent = 0; // 基金类收益率
                    if($detail['type'] == 104 || $detail['type'] == 109 || $detail['type'] == 110) { // 基金类产品
                        $isFund = true;
                        $detailExt = $projectModelFundObj->where(array('project_id' => $detail['id']))->find();
                        $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                        if (!$detail['advance_end_time']) $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                        else $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time']));
                        $today = date('Y-m-d', time()); // 当前时间点
                        if ($today < $timeEnd) $timeEnd = $today;
                        $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
                        if (count($fundList) > 1) { // 两个净值点以上
                            $fundStart = $fundList[0]['val']; // 起始净值
                            $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                            switch ($detail['type']) {
                                case 104: // 打新股,收益超过18%分成
                                    if ($fundEnd - $fundStart > 0) {
                                        if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
                                            $percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
                                        } else {
                                            $percent = ($fundEnd - $fundStart) / $fundStart;
                                        }
                                    }
                                    break;
                                case 109: // B类基金,杠杆0.2
                                    if ($fundEnd - $fundStart > 0) {
                                        $fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
                                        $percent = ($fundEndB - $fundStart) / $fundStart;
                                    }
                                    break;
                                case 110: // A类基金,杠杆2.6
                                    if ($fundEnd - $fundStart > 0) {
                                        $fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
                                        $percent = ($fundEndA - $fundStart) / $fundStart;
                                    } else if ($fundEnd - $fundStart < 0) {
                                        $fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
                                        $percent = ($fundEndA - $fundStart) / $fundStart;
                                    }
                                    break;
                            }
                        }
                    }else if($detail['type'] == 148){ // 搏息宝
                        $isFund = true;
                        $percent = cal_fund_percent($val['project_id']);
                    }else{
                        $isFund = false;
                    }
                    if($isFund) {
                        if($detail['type'] == 148){
                            $dueTime = date('Y-m-d', time());
                            if(date('Y-m-d', strtotime($val['due_time'])) < $dueTime) $dueTime = date('Y-m-d', strtotime($val['due_time']));
                            $val['due_interest'] = round($val['due_capital']*$percent*(count_days($dueTime.' 08:00:00', date('Y-m-d', strtotime($val['start_time'])).' 08:00:00')+1)/365, 2);

                        }else{
                            $val['due_interest'] = $val['due_capital']*$percent;
                        }
                    }
                    $syl = '年化收益';
                    $dslx = '利息';
                    switch($pinfo['type']){
                        case 104:
                            $syl = '近1个月年化收益率';
                            $dslx = '盈亏';
                            break;
                        case 109:
                            $syl = '近3个月年化收益率';
                            $dslx = '盈亏';
                            break;
                        case 110:
                            $syl = '近3个月年化收益率';
                            $dslx = '盈亏';
                            break;
                    }
                    $html .= '<li><dl>';
                    $html .= '<dd class="dd01"><a href="'.C('WEB_ROOT').'/user/product/detail/'.$val['id'].'"><h5>'.$pinfo['title'].'</h5></a><p><em>'.number_format($pinfo['user_interest'], 2).'%</em> '.$syl.'</p></dd>';
                    $html .= '<dd class="dd02"><h5>'.number_format($val['due_capital'], 2).'</h5><p>投资金额</p></dd>';
                    if($val['due_interest'] > 0){
                        $html .= '<dd class="dd03"><h5 class="red">'.number_format($val['due_interest'], 2).'</h5><p>'.$dslx.'</p></dd>';
                    }else if($val['due_interest'] < 0){
                        $html .= '<dd class="dd03"><h5 class="green">'.number_format($val['due_interest'], 2).'</h5><p>'.$dslx.'</p></dd>';
                    }else{
                        $html .= '<dd class="dd03"><h5>'.number_format($val['due_interest'], 2).'</h5><p>'.$dslx.'</p></dd>';
                    }
                    $html .= '</dl></li>';
                }
                $rows = array(
                    'show' => $show,
                    'list' => $html,
                );
                $this->ajaxReturn(array('status'=>1,'data'=>$rows));
            }
        }
    }

    /**
     * 已购产品详细页
     */
    public function product_detail(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }

        $userDueDetailId = I('get.id', 0, 'int');

        $userDueDetailObj = M("UserDueDetail");
        $projectObj = M("Project");
        $userBankObj = M("UserBank");
        $userObj = M("User");

        $dueDetail = $userDueDetailObj->where(array('id'=>$userDueDetailId,'user_id'=>$userInfo['id']))->find();
        if (!$dueDetail) {
            header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
            $this->display("PublicNew:404");
            exit;
        }
        $dueDetail['uinfo'] = $userObj->field('username,real_name,card_no')->where(array('id'=>$userInfo['id']))->find();
        $detail = $projectObj->where(array('id'=>$dueDetail['project_id']))->find();
        $bankInfo = $userBankObj->where(array('bank_card_no'=>$dueDetail['card_no'],'has_pay_success'=>2))->find();
        $this->assign('bank_info', $bankInfo);
        if($detail['type'] == 109 || $detail['type'] == 110) { // 基金类产品
            $fundObj = M('Fund');
            // 获取基金净值数据
            $projectModelFundObj = M("ProjectModelFund");
            $fundDetailObj = M("FundDetail");
            $detailExt = $projectModelFundObj->where(array('project_id' => $detail['id']))->find();
            $detail['cyDays'] = $detailExt['days'];
            $fundInfo = $fundObj->where(array('id'=>$detailExt['fund_id']))->find();
            // 根据其中一个基金计算两支基金的总额
            $totleMoney = 0;
            if($detail['type'] == 109){ // B类
                $totleMoney = $detail['amount']/2 + $detail['amount'];
            }else if($detail['type'] == 110){ // A类
                $totleMoney = $detail['amount']*3;
            }
            $percent = 0; // 基金类收益率
            if ($detailExt) {
                if (!$detailExt['enter_time']) {
                    $timeStart = date('Y-m-d', strtotime($detail['start_time'])); // 产品净值进入时间点
                } else {
                    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                }
                if (!$detail['advance_end_time']) {
                    $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                } else {
                    $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }
                $today = date('Y-m-d', time()); // 当前时间点
                if ($today < $timeEnd) $timeEnd = $today;
                $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
                $fundStart = $fundList[0]['val']; // 起始净值
                if (count($fundList) > 1) { // 两个净值点以上
                    $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                    switch ($detail['type']) {
                        case 109: // B类基金,杠杆0.2
                            if ($fundEnd - $fundStart > 0) {
                                $fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
                                $percent = ($fundEndB - $fundStart) / $fundStart;
                            }
                            break;
                        case 110: // A类基金,杠杆2.6
                            if ($fundEnd - $fundStart > 0) {
                                $fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
                                $percent = ($fundEndA - $fundStart) / $fundStart;
                            } else if ($fundEnd - $fundStart < 0) {
                                $fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
                                $percent = ($fundEndA - $fundStart) / $fundStart;
                            }
                            break;
                    }
                }
                $syData = array();
                foreach($fundList as $key => $val){
                    $fundList[$key]['datetime'] = strtotime($val['datetime'])*1000;
                    $syData[$key]['datetime'] = strtotime($val['datetime'])*1000;
                    if($key > 0){
                        if($detail['type'] == 109){ // B类基金
                            $syData[$key]['val'] = formula_fund_net($fundStart, $val['val'], 'B');
                        }else if($detail['type'] == 110){ // A类基金
                            $syData[$key]['val'] = formula_fund_net($fundStart, $val['val'], 'A');
                        }
                    }else{
                        $syData[$key]['val'] = $fundStart;
                    }
                }
                $this->assign('fund_data', $fundList);
                $this->assign('sy_data', $syData);
            }

            $dueDetail['due_interest'] = $dueDetail['due_capital'] * number_format($percent, 4);
            $this->assign('totle_money', $totleMoney);
            $this->assign('fund_info', $fundInfo);
            $this->assign('current_profit', $percent);
            $this->assign('ex_detail', $detailExt);
            $this->assign('detail', $detail);
            $this->assign('due_detail', $dueDetail);
            $this->display('product_detail_fund');
        }else if($detail['type'] == 104){ // 打新股产品
            $fundObj = M('Fund');
            $projectModelFundObj = M('ProjectModelFund');
            $fundDetailObj = M("FundDetail");
            $detailExt = $projectModelFundObj->field('id,fund_id,enter_time,days')->where(array('project_id' => $detail['id']))->find();
            $detail['cyDays'] = $detailExt['days'];
            $fundInfo = $fundObj->where(array('id'=>$detailExt['fund_id']))->find();

            $percent = 0; // 基金类收益率
            if ($detailExt) {
                if (!$detailExt['enter_time']) {
                    $timeStart = date('Y-m-d', strtotime($detail['start_time'])); // 产品净值进入时间点
                } else {
                    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                }
                if (!$detail['advance_end_time']) {
                    $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                } else {
                    $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }
                $today = date('Y-m-d', time()); // 当前时间点
                if ($today < $timeEnd) $timeEnd = $today;
                $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
                $fundStart = $fundList[0]['val']; // 起始净值
                if (count($fundList) > 1) { // 两个净值点以上
                    $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                    if ($fundEnd - $fundStart > 0) {
                        if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
                            $percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
                        } else {
                            $percent = ($fundEnd - $fundStart) / $fundStart;
                        }
                    }
                }
                foreach($fundList as $key => $val){
                    $fundList[$key]['datetime'] = strtotime($val['datetime'])*1000;
                }
                $this->assign('fund_data', $fundList);
            }

            $dueDetail['due_interest'] = $dueDetail['due_capital'] * number_format($percent, 4);
            $this->assign('fund_info', $fundInfo);
            $this->assign('current_profit', $percent);
            $this->assign('detail_ext', $detailExt);
            $this->assign('detail', $detail);
            $this->assign('due_detail', $dueDetail);
            $this->display('product_detail_dxg');
        }else if($detail['type'] == 139) { // 股权众筹
            $projectModelEquityObj = M("ProjectModelEquity");
            $projectModelEquityDynamicObj = M("ProjectModelEquityDynamic");
            $detailExt = $projectModelEquityObj->field('id,project_id,evaluation_source,evaluation,shares')->where(array('project_id' => $detail['id']))->find();

            // 项目动态
            $projectDynamic = $projectModelEquityDynamicObj->field('id,title,add_time')->where(array('project_id' => $detail['id'], 'is_show' => 1))->order('add_time desc')->select();

            $this->assign('dynamic', $projectDynamic);
            $this->assign('ex_detail', $detailExt);
            $this->assign('detail', $detail);
            $this->assign('due_detail', $dueDetail);
            $this->display('product_detail_equity');
        }else if($detail['type'] == 148){ // 博息宝
            $fundObj = M('Fund');
            $projectModelSectionObj = M("ProjectModelSection");
            $fundDetailObj = M("FundDetail");

            $detailExt = $projectModelSectionObj->where(array('project_id' => $detail['id']))->find();
            $detail['cyDays'] = $detailExt['days'];
            $fundInfo = $fundObj->where(array('id'=>$detailExt['fund_id']))->find();

            $minInterest = $detail['user_interest']; // 最小年化
            $maxInterest = $detailExt['max_interest']; // 最大年化
            $changeType = $detailExt['change_type']; // 看涨看跌类型(1:看涨/2:看跌)
            $nhData = array(); // 年化率数据
            $zfData = array(); // ETF涨幅

            $percent = 0; // 基金类收益率
            if ($detailExt) {
                if (!$detailExt['enter_time']) {
                    $timeStart = date('Y-m-d', strtotime($detail['start_time'])); // 产品净值进入时间点
                } else {
                    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                }
                if (!$detail['advance_end_time']) {
                    $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                } else {
                    $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }
                $today = date('Y-m-d', time()); // 当前时间点
                if ($today < $timeEnd) $timeEnd = $today;
                $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
                if(count($fundList) > 0 && $fundList[0]['datetime'] != $timeStart){ // 补全由于遇到节假日而不存在的净值数据
                    // 遇到节假日期间净值则取节假日之前的第一个有效净值
                    $fundStart = $fundDetailObj->where("fund_id=" . $detailExt['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->getField('val');
                    // 当前取出净值数据往前推至真实进入点日期
                    $valRows = array(
                        'val' => $fundStart,
                        'datetime' => $timeStart,
                    );
                    array_unshift($fundList, $valRows);
                }

                $fundStart = $fundList[0]['val']; // 起始净值
                if (count($fundList) > 1) { // 两个净值点以上
                    $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值
                    $fundBeforeEnd = $fundList[count($fundList) - 2]['val']; // 结束前一个净值
                    $percent = (($fundEnd - $fundStart)/$fundStart)*100;
                    if($percent <  $detail['user_interest']){
                        $percent =  $detail['user_interest'];
                    }else if($percent > $detailExt['max_interest']){
                        $percent = $detailExt['max_interest'];
                    }

                    $per = number_format((($fundEnd - $fundBeforeEnd)/$fundBeforeEnd)*100, 2); // ETF昨日涨跌幅
                }else{
                    $percent = $detail['user_interest'];
                    $per = 0;
                }
                $this->assign('per', $per); // 昨日涨跌幅
                // 走势图数据
                foreach($fundList as $key => $val){
                    $nhData[$key]['datetime'] = strtotime($val['datetime'])*1000;
                    $zfData[$key]['datetime'] = strtotime($val['datetime'])*1000;
                    $fundList[$key]['datetime'] = strtotime($val['datetime'])*1000;
                    if($key > 0){
                        $zfData[$key]['val'] = round(($val['val']-$fundStart)/$fundStart, 4)*100;
                        if($changeType == 1){ // 看涨
                            $currentInterest = round(($val['val']-$fundStart)/$fundStart, 4)*100;
                            if($currentInterest > $minInterest && $currentInterest < $maxInterest) {
                                $nhData[$key]['val'] = $currentInterest;
                            }else if($currentInterest >= $maxInterest){
                                $nhData[$key]['val'] = $maxInterest;
                            }else{
                                $nhData[$key]['val'] = $minInterest;
                            }
                        }else{ // 看跌
                            $currentInterest = ($val['val']-$fundStart)/$fundStart;
                            if($currentInterest < 0){
                                $currentInterest = round($currentInterest, 4)*100*(-1);
                                if($currentInterest > $minInterest && $currentInterest < $maxInterest){
                                    $nhData[$key]['val'] = $currentInterest;
                                }else{
                                    $nhData[$key]['val'] = $minInterest;
                                }
                            }else{
                                $nhData[$key]['val'] = $minInterest;
                            }
                        }
                    }else{
                        $zfData[$key]['val'] = 0;
                        $nhData[$key]['val'] = $minInterest;
                    }
                }
                $this->assign('fund_data', $fundList);
                $this->assign('nh_data', $nhData);
                $this->assign('zf_data', $zfData);
            }
            $dueDetail['due_interest'] = round($dueDetail['due_capital']*count_days($timeEnd, date('Y-m-d', strtotime($dueDetail['add_time'])))*$percent/100/365, 2);
            $this->assign('fund_info', $fundInfo);
            $this->assign('current_profit', $percent);
            $this->assign('ex_detail', $detailExt);
            $this->assign('endtime', strtotime($timeEnd)*1000); // 走势图需要用到的结束时间点
            $this->assign('days', $detailExt['days']);
            $this->assign('detail', $detail);
            $this->assign('due_detail', $dueDetail);
            $this->display('product_detail_section');
        }else if($detail['type'] == 149){ // 增值产品
            $this->error('该产品请从APP查看详情');exit;
        }else{
            $this->assign('detail', $detail);
            $this->assign('due_detail', $dueDetail);
            $this->display();
        }
    }

    /**
     * 银行卡管理
     */
    public function bank(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }
        // 获取用户绑定银行卡
        $userBankObj = M("UserBank");
        $bankList = $userBankObj->field('id,bank_name,bank_code,bank_card_no')->where(array('user_id'=>$userInfo['id'],'has_pay_success'=>2))->order('latest_payment_time desc,add_time desc')->select();
        $this->assign('bank_list', $bankList);
        $this->display();
    }

    /**
     * 个人中心
     */
    public function profile(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }
        $this->display();
    }

    /**
     * 消息中心
     */
    public function message(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }

        if(!IS_POST){
            $this->display();
        }else{
            $target = I('post.target', 'system', 'strip_tags');
            $page = I('post.page', 1, 'int');

            if($target == 'system'){ // 系统消息
                $messageGroupObj = M("MessageGroup");
                // 分页
                $count = 6;
                $counts = $messageGroupObj->where(array('type'=>1,'status'=>1,'is_delete'=>0))->count();
                $Page = new \Think\PageAjax($counts, $count, array('event'=>'loadSysMsg','target'=>'system','page'=>$page)); // 自定义分页类
                $show = $Page->show();
                // 列表数据
                $sql = "select a.id,b.title,b.summary,b.description from s_message_group a left join s_message b on b.group_id=a.id where a.type=1 and a.status=1 and a.is_delete=0 order by a.add_time desc limit ".$Page->firstRow . ',' . $Page->listRows;
                $list = $messageGroupObj->query($sql);
                $html = '';
                foreach($list as $key => $val){
                    $html .= '<li>';
                    $html .= '<h5>'.$val['title'].'</h5>';
                    $html .= '<p class="nrjy pd'.($key+1).'">'.$val['summary'].'<a href="javascript:showMore(this,'.($key+1).');" class="more">查看详细</a></p>';
                    $html .= '<p class="p'.($key+1).' dis" style="display: none;">'.$val['description'].'</p>';
                    $html .= '</li>';
                }
                $rows = array(
                    'show' => $show,
                    'list' => $html,
                );
                $this->ajaxReturn(array('status'=>1,'data'=>$rows));
            }else if($target == 'personal'){ // 个人消息
                $messagePersonalObj = M("MessagePersonal");
                // 分页
                $count = 6;
                $counts = $messagePersonalObj->where(array('recipient_uid'=>$userInfo['id'],'is_delete'=>0))->count();
                $Page = new \Think\PageAjax($counts, $count, array('event'=>'loadPerMsg','target'=>'personal','page'=>$page)); // 自定义分页类
                $show = $Page->show();
                // 列表数据
                $sql = "select a.id,a.is_read,a.add_time,b.content from s_message_personal a left join s_message_personal_content b on b.id=a.message_content_id where recipient_uid=".$userInfo['id']." and is_delete=0 order by a.add_time desc limit ".$Page->firstRow . ',' . $Page->listRows;
                $list = $messagePersonalObj->query($sql);
                $html = '';
                foreach($list as $key => $val){
                    $html .= '<li>';
                    $html .= '<h4>'.date('Y-m-d H:i:s',strtotime($val['add_time'])).'</h4>';
                    $html .= '<p class="nrjy pd4">'.$val['content'].'</a></p>';
                    $html .= '</li>';
                }
                $rows = array(
                    'show' => $show,
                    'list' => $html,
                );
                $this->ajaxReturn(array('status'=>1,'data'=>$rows));
            }

        }
    }

    /**
     * 钱包提现
     */
    public function withdrawals(){
        $userInfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!$userInfo['token']){
            $this->error('登录超时,请重新登录~', C('WEB_ROOT').'/login/');
            exit;
        }

        if(!IS_POST){
            $ret = post(C('API').C('interface.user_bank'),array('token'=>$userInfo['token'])); // 请求用户银行卡列表
            if($ret->code == 0){
                $now = date('Y-m-d', time());
                $userWalletRecordsObj = M("UserWalletRecords");
                $userBankObj = M("UserBank");
                $userAccountObj = M("UserAccount");
                $accountInfo = $userAccountObj->where(array('user_id'=>$userInfo['id']))->find();
                $bankList = objectToArray($ret->result);

                // 获取今日剩余提现次数
                $times = $userWalletRecordsObj->where("user_id=".$userInfo['id']." and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$now." 00:00:00.000000' and add_time<='".$now." 23:59:59.999000'")->count();
                $this->assign('times', 3-$times);

                foreach($bankList as $key => $val){
                    $bankList[$key]['shortBankCardNo'] = substr($val['bankCardNo'], strlen($val['bankCardNo']) - 4);
                    $bankList[$key]['limit'] = $userBankObj->where(array('id'=>$val['id']))->getField('wallet_money') + $accountInfo['wallet_interest'] + $accountInfo['wallet_product_interest']; // 可转出最大金额
                }
                $this->assign('account_info', $accountInfo);
                $this->assign('bank_list', $bankList);
            }
            $this->display();
        }else{
            $bankId = I('post.bank_item', 0, 'int'); // 银行卡ID
            $money = I('post.money', 0, 'float'); // 提现金额
            $code = I('post.vcode', '', 'strip_tags'); // 验证码
            $config = array(
                'seKey' => C('VCODE_KEY'),
            );
            $verify = new \Think\Verify($config);
            $vResult = $verify->check($code);
            if(!$vResult){
                if(IS_AJAX) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'验证码错误'));
                }else{
                    $this->error('验证码错误');exit;
                }
            }

            $uid = $userInfo['id'];
            $userAccountObj = M("UserAccount");
            $accountInfo = $userAccountObj->where(array('user_id'=>$uid))->find();

            // 获取今日剩余提现次数
            $userWalletRecordsObj = M("UserWalletRecords");
            $now = date('Y-m-d', time());
            if($userWalletRecordsObj->where("user_id=".$uid." and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$now." 00:00:00.000000' and add_time<='".$now." 23:59:59.999000'")->count() >= 3) {
                if(IS_AJAX){
                    $this->ajaxReturn(array('status'=>0,'info'=>'今日提现次数不足'));
                }else{
                    $this->error('今日提现次数不足');exit;
                }
            }

            // 判断可提现金额是否满足条件
            $userBankObj = M("UserBank");
            $maxMoney = $userBankObj->where(array('id'=>$bankId))->getField('wallet_money') + $accountInfo['wallet_interest'] + $accountInfo['wallet_product_interest']; // 可转出最大金额
            if($money > $maxMoney){
                if(IS_AJAX){
                    $this->ajaxReturn(array('status'=>0,'info'=>'本卡最多只能转出'.$maxMoney.'元'));
                }else{
                    $this->error('本卡最多只能转出'.$maxMoney.'元');exit;
                }
            }

            $ret = post(C('API').C('interface.user_wallet_withdrawal'), array('token'=>$userInfo['token'],'amount'=>$money,'bankId'=>$bankId));

            if($ret->code == 0){
                $this->success('提现成功~!', C('WEB_ROOT').'/user/wallet');
            }else{
                if(IS_AJAX){
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret->errorMsg));
                }else{
                    $this->error($ret->errorMsg);exit;
                }
            }
        }
    }

}