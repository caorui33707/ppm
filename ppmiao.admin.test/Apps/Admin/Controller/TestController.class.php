<?php
namespace Admin\Controller;

class TestController extends \Think\Controller{

    function index(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $ret = check_order_pay_status_by_sft('20150810001', 'B1508101721216300052');
//        if($ret['return']['resultCode'] != 0){
//            echo $ret['return']['resultMessage'];
//        }
    }

    /**
     * 处理邀请好友数据
     */
    function invit(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        echo '上次处理时间:2015-07-07 09:15:07';exit;
        $actEndTime = '2015-07-06 23:59:59.999000'; // 爆款活动结束时间
        if(date('Y-m-d H:i:s',time()) > $actEndTime){ // 活动结束
            echo '活动已结束';exit;
        }
        $dueCount = 0; // 处理条数
        $userInvitationObj = M("UserInvitation");
        $userObj = M("User");
        $userDueDetail = M("UserDueDetail");
        $userAccountObj = M("UserAccount");
        $list = $userInvitationObj->where(array('invited_success'=>0))->order('id desc')->select();
        foreach($list as $key => $val){
            $uinfo = $userObj->field('id,card_no')->where(array('username'=>$val['invited_phone']))->find();
            if($uinfo){
                $dueInfo = $userDueDetail->where(array('user_id'=>$uinfo['id']))->find();
                if($dueInfo){
                    if($dueInfo['add_time'] > '2015-07-01 00:00:00.000000' && $dueInfo['add_time'] <= '2015-07-06 23:59:59.999000'){
                        $dueCount += 1;
                        if($userInvitationObj->where(array('id'=>$val['id']))->save(array('success_time'=>time(),'invited_success'=>1,'invited_cardno'=>$uinfo['card_no'],'invited_user_id'=>$uinfo['id']))) {
                            $userAccountObj->where(array('user_id'=>$val['user_id']))->setInc('bk_chance', 1);
                        }
                    }
                }
            }
        }
        echo '处理完成!共处理'.$dueCount.'条漏掉数据. 处理时间: '.date('Y-m-d H:i:s', time());
    }

    /**
     * 核对钱包数据
     */
    public function checkwallet(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        $userAccountObj = M("UserAccount");
        $userWalletRecordsObj = M("UserWalletRecords");
        $userWalletInterestObj = M("UserWalletInterest");

        $list = $userAccountObj->field('user_id,wallet_enable_interest,wallet_totle')->where('wallet_totle>0')->select();
        $html = '<table>';
        $html .= '<tr><th>用户ID</th><th>总额</th><th>可计息</th><th>流水合计</th><th>已收利息合计</th><th>状态</th></tr>';
        $html .= '<tbody>';
        foreach($list as $key => $val){
            $ls = $userWalletRecordsObj->where('user_id='.$val['user_id'].' and ((type=1 and pay_status=2) or type=2)')->sum('value'); // 流水合计
            $lx = $userWalletInterestObj->where(array('user_id'=>$val['user_id']))->sum('interest'); // 利息合计
            $html .= '<tr>';
            $html .= '<td>'.$val['user_id'].'</td>';
            $html .= '<td>'.$val['wallet_totle'].'</td>';
            $html .= '<td>'.$val['wallet_enable_interest'].'</td>';
            $html .= '<td>'.$ls.'</td>';
            $html .= '<td>'.$lx.'</td>';
            $html .= '<td>'.($val['wallet_totle']==round(($ls+$lx),4)?'<span style="color:green;">正常</span>':'<span style="color:red;">数据异常</span>').'</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        echo $html;
    }

    /**
     * 核对钱包数据(包括银行卡限额数据)
     */
    public function checkwalletplus(){
		//exit;
        $debug = false;
        if($debug){
            if(I('get.cc', false)) F("error_wallet_user_ids", null);
        }
        $finishIndex = 23467;
        $id = I('get.ids', 0, 'int');
        if($id) {
            $id += 1;
            echo '<a href="'.C('ADMIN_ROOT').'/Test/checkwalletplus?ids='.$id.'">下一个</a><br>';
        }
        echo '<style>body{background-color:#F0F0F0;}</style>';
        $userIds = I('get.ids', '', 'strip_tags');
        $page = I('get.p', 0, 'int');
        $count = I('get.count', 0, 'int');
        header("Content-type: text/html; charset=utf-8");
        $userAccountObj = M("UserAccount");
        $userWalletRecordsObj = M("UserWalletRecords");
        $userWalletInterestObj = M("UserWalletInterest");
        $userBankObj = M("UserBank");
        $userDueDetailObj = M("UserDueDetail");
        $projectObj = M("Project");
        $rightColor = '#008000'; $outColor = '#FF69B4'; $errorColor = '#FF0000';

        // 充值过钱包的用户列表
        if(!$userIds){ // 全部
            if($page && $count) $list = $userAccountObj->field('user_id,wallet_enable_interest,wallet_interest,wallet_totle,wallet_product_interest')->limit(($page-1)*$count.','.$count)->select();
        }else{ // 置顶用户列表
            $list = $userAccountObj->field('user_id,wallet_enable_interest,wallet_interest,wallet_totle,wallet_product_interest')->where('user_id in ('.$userIds.')')->select();
        }
        if(empty($list)){
            echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/checkwalletplus?ids='.$id.'";</script>';
            exit;
        }
        foreach($list as $key => $val){
            echo 'ID:'.$val['user_id'].'<br>';
            // 银行卡充值总额
            $bankInList = $userWalletRecordsObj->where('user_id='.$val['user_id'].' and pay_type<>0 and type=1 and pay_status=2')->select();
            $bankInTotle = 0;
            $bankArr = array();
            foreach($bankInList as $k => $v){
                $bankInTotle += $v['value'];
                if(!$bankArr['bank_'.$v['user_bank_id']]){
                    $bankInfo = $userBankObj->where(array('id'=>$v['user_bank_id']))->find();
                    $bankArr['bank_'.$v['user_bank_id']] = $bankInfo;
                    $bankArr['bank_'.$v['user_bank_id']]['money_in'] = $v['value'];
                    $bankArr['bank_'.$v['user_bank_id']]['money_out'] = 0;
                }else{
                    $bankArr['bank_'.$v['user_bank_id']]['money_in'] += $v['value'];
                }
            }
            echo '银行卡充值总额: '.$bankInTotle.'<br>';

            // 还本付息记录列表
            $projectInList = $userWalletRecordsObj->where('user_id='.$val['user_id'].' and pay_type=0 and type=1 and pay_status=2')->select();
            $projectInTotle1 = 0; // 还本付息总额(银行卡购买)
            $projectInTotle2 = 0; // 还本付息总额(钱包购买)
            foreach($projectInList as $k => $v){
                $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$v['user_due_detail_id']))->find();
                if($userDueDetailInfo['from_wallet'] == 1){ // 由钱包购买
                    $projectInTotle2 += $v['value'];
                }else{ // 银行卡购买
                    $projectInTotle1 += $v['value'];
                    $bankInfo = $userBankObj->where('bank_card_no=\''.$userDueDetailInfo['card_no'].'\' and has_pay_success=2')->find();
                    if(!$bankArr['bank_'.$bankInfo['id']]){
                        $bankArr['bank_'.$bankInfo['id']] = $bankInfo;
                        $bankArr['bank_'.$bankInfo['id']]['money_in'] = $v['value'];
                        $bankArr['bank_'.$bankInfo['id']]['money_out'] = 0;
                    }else{
                        $bankArr['bank_'.$bankInfo['id']]['money_in'] += $v['value'];
                    }
                }
            }
            echo '还本付息总额: '.$projectInTotle1.'(卡) + '.$projectInTotle2.'(包)<br>';

            // 提现总额记录列表
            $bankOutList = $userWalletRecordsObj->where('user_id='.$val['user_id'].' and type=2 and user_bank_id>0 and user_due_detail_id=0')->select();
            $bankOutTotle = 0; // 提现总额
            foreach($bankOutList as $k => $v){
                $bankOutTotle += $v['value'];
                $bankArr['bank_'.$v['user_bank_id']]['money_out'] += $v['value'];
            }
            echo '提现总额: '.$bankOutTotle.'<br>';

            // 购买产品总额
            $projectOutTotle = $userWalletRecordsObj->where('user_id='.$val['user_id'].' and type=2 and user_bank_id=0 and user_due_detail_id>0')->sum('value');
            echo '购买产品总额: '.$projectOutTotle.'<br>';
            // 结算利息总额
            $interestTotle = $userWalletInterestObj->where(array('user_id'=>$val['user_id']))->sum('interest');
            echo '钱包结算利息总额: '.$interestTotle.'<br>';
            echo '钱包购买产品利息: '.round($projectInTotle2+$projectOutTotle, 4).'<br>';

            // 银行卡流水 开始
            $walletTotle = $interestTotle; // 钱包剩余总额
            $interest = $interestTotle; // 钱包可用利息
            $productInterest = 0; // 钱包购买产品产生利息
            $enabledInterest = $interestTotle; // 可计息金额
            echo '===========流水开始=============<br>';
            $recordList = $userWalletRecordsObj->field(true)->where('user_id='.$val['user_id'].' and ((type=1 and pay_status=2) or (type=2))')->order('add_time asc')->select();
            $bankArr2 = array(); $bankIdArr2 = array();
            foreach($recordList as $k => $v){
                $walletTotle += $v['value'];
                echo '['.date('Y/m/d H:i:s', strtotime($v['add_time'])).'] ';
                if($v['enable_interest'] == 1){
                    echo '<span style="color:'.$rightColor.';">[审]</span>';
                    $enabledInterest += $v['value'];
                }else{
                    echo '<span style="color:'.$outColor.';">[审]</span>';
                }
                if($v['type'] == 1){ // 转入
                    if($v['user_bank_id'] > 0){ // 银行卡转入
                        $bankInfo = $userBankObj->where(array('id'=>$v['user_bank_id']))->find();
                        if(!in_array($bankInfo['id'], $bankIdArr2)){
                            $bankArr2[$bankInfo['id']] = $bankInfo;
                            $bankArr2[$bankInfo['id']]['money'] = 0;
                            $bankArr2[$bankInfo['id']]['interest'] = 0;
                            $bankIdArr2[] = $bankInfo['id'];
                        }
                        $bankArr2[$bankInfo['id']]['money'] += $v['value'];
                        echo $bankInfo['bank_name'].'('.$bankInfo['id'].') 转入金额 <span style="color:'.$rightColor.';">'.number_format($v['value'], 2).'</span>';
                    }else{ // 还本付息转入
                        $dueDetail = $userDueDetailObj->where(array('id'=>$v['user_due_detail_id']))->find();
                        $projectType = $projectObj->where(array('id'=>$dueDetail['project_id']))->getField('type');
                        if($dueDetail['from_wallet'] == 1){ // 钱包购买转入
                            if(in_array($projectType, array(104,109,110))) { // 基金类产品计算收益
                                $fundDueInterest = round(cal_fund_percent($dueDetail['project_id'])*$dueDetail['due_capital'], 2); // 基金类产品最终收益利息
                                $productInterest += ($fundDueInterest > 0 ? $fundDueInterest : 0);
                                echo '还本付息(包)转入金额 <span style="color:'.$rightColor.';">'.number_format($v['value'], 2).'('.$fundDueInterest.')</span>';
                            }else if($projectType == 148){ // 博息宝产品
                                echo '博息宝计算条目';
                                //$productInterest += $dueDetail['due_interest'];
                                //echo '还本付息(包)转入金额 <span style="color:'.$rightColor.';">'.number_format($v['value'], 2).'(+'.$dueDetail['due_interest'].')</span>';
                            }else{
                                $productInterest += $dueDetail['due_interest'];
                                echo '还本付息(包)转入金额 <span style="color:'.$rightColor.';">'.number_format($v['value'], 2).'(+'.$dueDetail['due_interest'].')</span>';
                            }
                        }else{ // 银行卡购买转入
                            $bankInfo = $userBankObj->where(array('bank_card_no'=>$dueDetail['card_no'],'has_pay_success'=>2))->find();
                            if(!in_array($bankInfo['id'], $bankIdArr2)){
                                $bankArr2[$bankInfo['id']] = $bankInfo;
                                $bankArr2[$bankInfo['id']]['money'] = 0;
                                $bankArr2[$bankInfo['id']]['interest'] = 0;
                                $bankIdArr2[] = $bankInfo['id'];
                            }
                            $bankArr2[$bankInfo['id']]['money'] += $v['value'];
                            echo '还本付息(卡<'.$bankInfo['bank_name'].$bankInfo['id'].'>)转入金额 <span style="color:'.$rightColor.';">'.number_format($v['value'], 2).'</span>';
                        }
                    }
                }else{ // 转出
                    if($v['user_bank_id'] > 0){ // 提现到银行卡
                        $bankInfo = $userBankObj->where(array('id'=>$v['user_bank_id']))->find();
                        if($bankArr2[$bankInfo['id']]['money'] + $v['value'] < 0){
                            $bankArr2[$bankInfo['id']]['interest'] += abs($bankArr2[$bankInfo['id']]['money'] + $v['value']);
                            $bankArr2[$bankInfo['id']]['money'] = 0;
                        }else{
                            $bankArr2[$bankInfo['id']]['money'] += $v['value'];
                        }
                        echo '提现到银行卡 '.$bankInfo['bank_name'].'('.$bankInfo['id'].') <span style="color:'.$outColor.';">'.number_format($v['value'], 2).'</span>';
                    }else{ // 购买产品

                        echo '购买产品 <span style="color:'.$outColor.';">'.number_format($v['value'], 2).'</span>';
                    }
                }
                echo '<br>';
            }
            foreach($bankArr2 as $kk => $vv){
                $interest -= round($vv['interest'], 2);
                echo $vv['bank_name'].'('.$vv['id'].') 可转出 '.number_format($vv['money'], 2).' / '.number_format($vv['wallet_money'], 2).' (其中扣除利息:'.number_format($vv['interest'], 2).')  ';
                if(round($vv['money'], 2) != round($vv['wallet_money'], 2)){
                    echo '<span style="color:'.$errorColor.';">×</span>';
                }else{
                    echo '<span style="color:'.$rightColor.';">√</span>';
                }
                echo '<br>';
            }
            echo '===========流水结束=============<br>';
            if(!$debug){
                echo '钱包:';
                echo (round($walletTotle, 4) == $val['wallet_totle'] ? '<span style="color:'.$rightColor.';">'.$val['wallet_totle'].'</span>' : '<span style="color:'.$errorColor.';">'.$val['wallet_totle'].'</span>|'.round($walletTotle, 4));
                echo ' | 可提利息:'.(round($val['wallet_interest']+$val['wallet_product_interest'], 4) == round($interest + $productInterest, 4) ? '<span style="color:'.$rightColor.';">'.$val['wallet_interest'].' + '.$val['wallet_product_interest'].'</span>' : '<span style="color:'.$errorColor.';">'.$val['wallet_interest'].' + '.$val['wallet_product_interest'].'</span>|<span style="color:'.$rightColor.'">'.(round($interest, 4)+$productInterest)).'</span>';
                echo ' | 计息金额:'.($val['wallet_enable_interest'] == round($enabledInterest, 4) ? '<span style="color:'.$rightColor.';">'.$val['wallet_enable_interest'].'</span>' : '<span style="color:'.$errorColor.';">'.$val['wallet_enable_interest'].'</span>|<span style="color:'.$rightColor.'">'.round($enabledInterest, 4)).'</span><br>';
                echo '<br><br><br>';
            }else{
                $error = false;
                if(round($vv['money'], 2) != round($vv['wallet_money'], 2)){
                    echo 1;
                    $error = true;
                } else if(round($walletTotle, 4) != $val['wallet_totle']){
                    echo 2;
                    $error = true;
                } else if(round($val['wallet_interest']+$val['wallet_product_interest'], 4) != round($interest + $productInterest, 4)){
                    echo 3;
                    $error = true;
                } else if($val['wallet_enable_interest'] != round($enabledInterest, 4)){
                    echo 4;
                    $error = true;
                }
                if($error){
                    $errorUserIds = F("error_wallet_user_ids");
                    if(!$errorUserIds) $errorUserIds = ($id-1);
                    else $errorUserIds .= ",".($id-1);
                    F("error_wallet_user_ids", $errorUserIds);
                }
                if($id > $finishIndex) {
                    echo "Error User:<br>";
                    echo F("error_wallet_user_ids");
                }else{
                    echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/checkwalletplus?ids='.$id.'";</script>';
                }
            }
        }
    }

    /**
     * 核对钱包数据(详细记录流水)
     */
    public function checkwalletlist(){
		exit;
        $userIds = I('get.ids', '', 'strip_tags');
        header("Content-type: text/html; charset=utf-8");
        $userAccountObj = M("UserAccount");
        $userWalletRecordsObj = M("UserWalletRecords");
        $userWalletInterestObj = M("UserWalletInterest");
        $userBankObj = M("UserBank");
        $userDueDetailObj = M("UserDueDetail");

        // 充值过钱包的用户列表
        if($userIds){ // 全部
            $list = $userAccountObj->field('user_id,wallet_enable_interest,wallet_interest,wallet_totle,wallet_product_interest')->where('user_id in ('.$userIds.') and (wallet_totle>0 or wallet_interest_totle>0)')->select();
        }

        foreach($list as $key => $val){
            $recordList = $userWalletRecordsObj->field(true)->where('user_id='.$val['user_id'].' and ((type=1 and pay_status=2) or (type=2))')->order('add_time asc')->select();
            $bankArr = array(); $bankIdArr = array();
            foreach($recordList as $k => $v){
                echo '['.date('Y/m/d H:i:s', strtotime($v['add_time'])).'] ';
                if($v['type'] == 1){ // 转入
                    if($v['user_bank_id'] > 0){ // 银行卡转入
                        $bankInfo = $userBankObj->where(array('id'=>$v['user_bank_id']))->find();
                    }else{ // 还本付息转入
                        $bankCard = $userDueDetailObj->whrere(array('id'=>$v['user_due_detail_id']))->getField('card_no');
                        $bankInfo = $userBankObj->where(array('bank_card_no'=>$bankCard,'has_pay_success'=>2))->find();
                    }
                    if(!in_array($bankInfo['id'], $bankIdArr)){
                        $bankArr[$bankInfo['id']] = $bankInfo;
                        $bankArr[$bankInfo['id']]['money'] = 0;
                        $bankArr[$bankInfo['id']]['interest'] = 0;
                        $bankIdArr[] = $bankInfo['id'];
                    }
                    $bankArr[$bankInfo['id']]['money'] += $v['value'];
                    echo $bankInfo['bank_name'].'('.$bankInfo['id'].') 转入金额 <span style="color:green;">'.number_format($v['value'], 2).'</span>';
                }else{ // 转出
                    if($v['user_bank_id'] > 0){ // 提现到银行卡
                        $bankInfo = $userBankObj->where(array('id'=>$v['user_bank_id']))->find();
                        if($bankArr[$bankInfo['id']]['money'] + $v['value'] < 0){
                            $bankArr[$bankInfo['id']]['interest'] += abs($bankArr[$bankInfo['id']]['money'] + $v['value']);
                            $bankArr[$bankInfo['id']]['money'] = 0;
                        }else{
                            $bankArr[$bankInfo['id']]['money'] += $v['value'];
                        }
                        echo '提现到银行卡 '.$bankInfo['bank_name'].'('.$bankInfo['id'].') <span style="color:red;">'.number_format($v['value'], 2).'</span>';
                    }else{ // 购买产品
                        echo '购买产品 <span style="color:red;">'.number_format($v['value'], 2).'</span>';
                    }
                }
                echo '<br>';
            }
            foreach($bankArr as $kk => $vv){
                echo $vv['bank_name'].'('.$vv['id'].') 可转出 '.number_format($vv['money'], 2).' / '.number_format($vv['wallet_money'], 2).' (其中扣除利息:'.number_format($vv['interest'], 2).')  ';
                if(round($vv['money'], 2) != round($vv['wallet_money'], 2)){
                    echo '<span style="color:red;">×</span>';
                }else{
                    echo '<span style="color:green;">√</span>';
                }
                echo '<br>';
            }
        }
    }

    public function phone(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        $phones = array('13858101529','13917319851','13512026960','13867157462','13818334910','15362260489','18962213678','13407186198','15148117791','13604676029','15988396686','13456991853','13858509981','13676288908','13905704662','15221225658','15018628689','18235677535','13683228184','13812837239','18602852226','13514439515','13134336856','13783492679','13475074398','18210252165','13373492116','13784498272','13901139130','13502531626','15620566980','13954159007','13683273675','13605718756','13935700296','15011580703','15303592676','18631698919','13156019897','18656073925','13903478683','13666643182','15293094706','18663129850','13810804393','15147185041','13653865061','18704373200','13561818809','13916145234','18913670610','13951868435','13995630239','15319724067','15053527986','13899969239','15623515944','13485171898','13671552111','13958920413','18225655602','18931875569','13999316827','13575913522','13757669549','13956178707','18751266746','18693118889','15986200563','18604789866','13704687629','13162181805','15139275182','13776985247','13213223213','15069999971','18070390693','15925786842','15990199629','18258085249','15904341448','18739725737','18832453074','13502552581','18758263482','13451142801','13866304865','13910652715','15005160701','15243531383','18953479092','15800866264','15996472089','13454737756','13695079768','13972590448','18678185706','13722785479','13516709008','13615283707','13971976057','13525479498','15610302669','18635176977','13718542910','13712997735','13566051473','13811312137','13504168810','13931991418','18250872870','13953733041','15068512195','13722319581','18518179395','13325051122','13037025806','13642179561','13709866898','17706841129','13571824003','18641976766','18844039964','15037162677','15988191117','15558050302','13485177073','15121033400','18901418719','13857180578','15332441001','18964820651','13511731271','18962382250','13736591637','15070096458','15306549317','13600544030','13861089180','18728408186','13912922385','13311188705','15066670705','18996111525','15034506408','13935453329','15601864315','13953402849','18609777983','13994278680','13839963622','13706129580','15854398989','15954962838','15092352237','13460702510','18368302429','13718376047','13736403292','13827702385','13605567236','13819409294','15861804058','18869914040','18561379212','18804370117','13933117719','18611938353','15081563586','18655580168','15060838405','13513665506','18997080258','13817326892','13793980930','15037282870','13594273971','15063068455','13574885848','13469190799','13575728787','13823930483','13761893026','13622103420','13977149600','13630464849','13460210790','15035734550','13382833957','13873318479','13604347732','13910144695','13836510180','18668183260','13426219205','15872714051','13738296060','15929552917','18389697206','13992838470','15054493756','13693143676','13738253018','13956481801','13919121522','13319853197','13770304813','13403316311','13147182076','15853642005','13569483872','15988576158','18606038260','15603515044','15754957573','13653311201','13513793959','13759625258','13829399098','15220751826','18501591927','13918159934','13167368323','15589195848','13777839190','13819109803','13588426206','15034776284','13460914867','18762635130','13910257389','13867114998','13793980139','15946497629','18999893686','18636850588','15137418892','15066120726','18806815228','13111003049','15233381118','18969077060','13819133902','15345586206','15807102481','18710038659','13012611066','13028029099','13663255208','13391286801','13066689665','13111233408','13993680782','18655148066','18627107277','18210138375','15965997966','13828770789','13685229940','13563297217','15858472868','15829017069','15509808605','13357831684','15088233358','13292389196','13970844411','13811201298','13960501895','13353560588','13042716338','18960539002','13809378353','13706186366','15096396726','18603212003','15835264198','15839496902','13764271191','18831888434','13752149409','18367175788','13852090718','13126532769','15002779305');
        $yd = array(134,135,136,137,138,139,150,151,152,157,158,159,182,187,188,147);
        $lt = array(130,131,132,155,156,186,145);
        $dx = array(133,153,189);
        $result = '';
        foreach($phones as $key => $val){
            $start = substr($val, 0, 3);
            if(in_array($start, $yd)){
                $result .= ',移动';
            }else if(in_array($start, $lt)){
                $result .= ',联通';
            }else if(in_array($start, $dx)){
                $result .= ',电信';
            }else{
                $result .= ',未知';
            }
        }
        echo $result;
    }

    public function active(){
        exit;
        $userDueDetailObj = M("UserDueDetail");

        $list = $userDueDetailObj->where("add_time>='2015-07-14 00:00:00.000000' and add_time<='2015-07-20 23:59:59.999000'")->select();
        $userId = array();
        $newPerson = 0;
        $oldPerson = 0;
        foreach($list as $key => $val){
            if(!in_array($val['user_id'], $userId)){
                array_push($userId, $val['user_id']);
                if(!$userDueDetailObj->where("id<".$val['id']." and user_id=".$val['user_id'])->find()){
                    $newPerson += 1;
                }else{
                    $oldPerson += 1;
                }
            }
        }
        echo 'New:'.$newPerson."\t"."Old:".$oldPerson."\r"."Totle:".($newPerson+$oldPerson);
    }

    public function userlist(){
        exit;
        $userDueDetailObj = M("UserDueDetail");
        $startDatetime = date('Y-m-d',strtotime($userDueDetailObj->getField('add_time')));
        $endDatetime = '2015-08-05';
        $html = '';
        $html .= '<table><thead><tr><th>日期</th><th>投资人数</th><th>投资金额</th></tr></thead>';
        $html .= '<tbody>';
        while(true){
            $html .= '<tr>';
            $html .= '<td>'.$startDatetime.'</td>';
            $html .= '<td>'.count($userDueDetailObj->where("add_time>='".$startDatetime." 00:00:00.000000' and add_time<='".$startDatetime." 23:59:59.999000'")->group('user_id')->select()).'</td>';
            $html .= '<td>'.$userDueDetailObj->where("add_time>='".$startDatetime." 00:00:00.000000' and add_time<='".$startDatetime." 23:59:59.999000'")->sum('due_capital').'</td>';
            $html .= '<tr>';
            $startDatetime = date('Y-m-d', strtotime('+1 days', strtotime($startDatetime)));
            if($startDatetime == $endDatetime) break;
        }
        $html .= '</tbody></table>';
        echo $html;
    }

    public function tj(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        $userDueDetailObj = M("UserDueDetail");

        $month4Uid = array();
        $month4Uid2 = array();
        $month4UidStr = '';
        $month4UidStr2 = '';
        $money4 = 0;
        $money4_2 = 0;
        $money4_5 = 0;
        $money4_5_2 = 0;
        $money4_6 = 0;
        $money4_6_2 = 0;
        $money4_7 = 0;
        $money4_7_2 = 0;
        $month5Uid = array();
        $month5Uid2 = array();
        $month5UidStr = '';
        $month5UidStr2 = '';
        $money5 = 0;
        $money5_2 = 0;
        $money5_6 = 0;
        $money5_6_2 = 0;
        $money5_7 = 0;
        $money5_7_2 = 0;
        $month6Uid = array();
        $month6Uid2 = array();
        $month6UidStr = '';
        $month6UidStr2 = '';
        $money6 = 0;
        $money6_2 = 0;
        $money6_7 = 0;
        $money6_7_2 = 0;
        $month7Uid = array();
        $month7Uid2 = array();
        $month7UidStr = '';
        $month7UidStr2 = '';
        $money7 = 0;
        $money7_2 = 0;
        $moneyTotle = 0;
        $moneyTotle2 = 0;
        $list = $userDueDetailObj->field('id,user_id,due_capital,add_time')->where("user_id>0 and add_time>='2015-04-01 00:00:00.000000' and add_time<'2015-08-01 00:00:00.000000'")->order('add_time asc')->select();
        $list2 = $userDueDetailObj->field('id,user_id,due_capital,due_time')->where("user_id>0 and due_time>='2015-04-01 00:00:00.000000' and due_time<'2015-08-01 00:00:00.000000'")->order('due_time asc')->select();
        foreach($list as $key => $val){
            $moneyTotle += $val['due_capital'];
            $date = date('Y-m', strtotime($val['add_time']));
            switch($date){
                case '2015-04':
                    if(!in_array($val['user_id'], $month4Uid)){
                        array_push($month4Uid, $val['user_id']);
                        $month4UidStr .= ','.$val['user_id'];
                    }
                    $money4 += $val['due_capital'];
                    break;
                case '2015-05':
                    if(in_array($val['user_id'], $month4Uid)){
                        $money4_5 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month5Uid)){
                        $money5 += $val['due_capital'];
                    }else{
                        array_push($month5Uid, $val['user_id']);
                        $month5UidStr .= ','.$val['user_id'];
                        $money5 += $val['due_capital'];
                    }
                    break;
                case '2015-06':
                    if(in_array($val['user_id'], $month4Uid)){
                        $money4_6 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month5Uid)) {
                        $money5_6 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month6Uid)){
                        $money6 += $val['due_capital'];
                    }else{
                        array_push($month6Uid, $val['user_id']);
                        $month6UidStr .= ','.$val['user_id'];
                        $money6 += $val['due_capital'];
                    }
                    break;
                case '2015-07':
                    if(in_array($val['user_id'], $month4Uid)){
                        $money4_7 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month5Uid)) {
                        $money5_7 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month6Uid)) {
                        $money6_7 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month7Uid)){
                        $money7 += $val['due_capital'];
                    }else{
                        array_push($month7Uid, $val['user_id']);
                        $month7UidStr .= ','.$val['user_id'];
                        $money7 += $val['due_capital'];
                    }
                    break;
            }
        }
        foreach($list2 as $key => $val){
            $moneyTotle2 += $val['due_capital'];
            $date = date('Y-m', strtotime($val['due_time']));
            switch($date){
                case '2015-04':
                    if(!in_array($val['user_id'], $month4Uid2)){
                        array_push($month4Uid2, $val['user_id']);
                        $month4UidStr2 .= ','.$val['user_id'];
                    }
                    $money4_2 += $val['due_capital'];
                    break;
                case '2015-05':
                    if(in_array($val['user_id'], $month4Uid2)){
                        $money4_5_2 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month5Uid2)){
                        $money5_2 += $val['due_capital'];
                    }else{
                        array_push($month5Uid2, $val['user_id']);
                        $month5UidStr2 .= ','.$val['user_id'];
                        $money5_2 += $val['due_capital'];
                    }
                    break;
                case '2015-06':
                    if(in_array($val['user_id'], $month4Uid2)){
                        $money4_6_2 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month5Uid2)) {
                        $money5_6_2 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month6Uid2)){
                        $money6_2 += $val['due_capital'];
                    }else{
                        array_push($month6Uid2, $val['user_id']);
                        $month6UidStr2 .= ','.$val['user_id'];
                        $money6_2 += $val['due_capital'];
                    }
                    break;
                case '2015-07':
                    if(in_array($val['user_id'], $month4Uid2)){
                        $money4_7_2 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month5Uid2)) {
                        $money5_7_2 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month6Uid2)) {
                        $money6_7_2 += $val['due_capital'];
                    }else if(in_array($val['user_id'], $month7Uid2)){
                        $money7_2 += $val['due_capital'];
                    }else{
                        array_push($month7Uid2, $val['user_id']);
                        $month7UidStr2 .= ','.$val['user_id'];
                        $money7_2 += $val['due_capital'];
                    }
                    break;
            }
        }
        echo '4月:'.($money4-$money4_2).'  '.($money4_5-$money4_5_2).'  '.($money4_6-$money4_6_2).'  '.($money4_7-$money4_7_2).'<br>';
        //echo $month4UidStr.'<br>';
        echo '5月:'.($money5-$money5_2).'  '.($money5_6-$money5_6_2).'  '.($money5_7-$money5_7_2).'<br>';
        //echo $month5UidStr.'<br>';
        echo '6月:'.($money6-$money6_2).'  '.($money6_7-$money6_7_2).'<br>';
        //echo $month6UidStr.'<br>';
        echo '7月:'.($money7-$money7_2).'<br>';
        //echo $month7UidStr.'<br>';
        echo '总额(进):'.$moneyTotle.'<br>';
        echo '总额(还):'.$moneyTotle2.'<br>';
        echo '总额:'.($moneyTotle-$moneyTotle2).'<br>';
        exit;
    }

    /**
     * 用户阶段筛选统计
     */
    public function userarea(){
        exit;
        $userDueDetailObj = M("UserDueDetail");

        $man = 0; $woman = 0;
        $data = array(
            array('count'=>0,'price'=>0),
            array('count'=>0,'price'=>0),
            array('count'=>0,'price'=>0),
            array('count'=>0,'price'=>0),
            array('count'=>0,'price'=>0),
            array('count'=>0,'price'=>0),
        );
        $sql = "select id,card_no from s_user where id in (select user_id from s_user_due_detail where add_time<='2015-07-31 23:59:59.999000' group by user_id)";
        $list = M()->query($sql);
        foreach($list as $key => $val){
            // 判断男女
            $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
            if($sex % 2 != 0) $man += 1;
            else $woman += 1;
            // 岁数
            $year = substr($val['card_no'], 6, 4);
            $nowYear = date('Y', time());
            $age = $nowYear - $year;
            if($age < 18){
                $data[0]['count'] += 1;
                $data[0]['price'] += $userDueDetailObj->where("user_id=".$val['id']." and add_time<='2015-07-31 23:59:59.999000'")->sum('due_capital');
            }else if($age >= 18 && $age <= 25){
                $data[1]['count'] += 1;
                $data[1]['price'] += $userDueDetailObj->where("user_id=".$val['id']." and add_time<='2015-07-31 23:59:59.999000'")->sum('due_capital');
            }else if($age >= 26 && $age <= 35){
                $data[2]['count'] += 1;
                $data[2]['price'] += $userDueDetailObj->where("user_id=".$val['id']." and add_time<='2015-07-31 23:59:59.999000'")->sum('due_capital');
            }else if($age >= 36 && $age <= 45){
                $data[3]['count'] += 1;
                $data[3]['price'] += $userDueDetailObj->where("user_id=".$val['id']." and add_time<='2015-07-31 23:59:59.999000'")->sum('due_capital');
            }else if($age >= 46 && $age <= 55){
                $data[4]['count'] += 1;
                $data[4]['price'] += $userDueDetailObj->where("user_id=".$val['id']." and add_time<='2015-07-31 23:59:59.999000'")->sum('due_capital');
            }else if($age >= 56){
                $data[5]['count'] += 1;
                $data[5]['price'] += $userDueDetailObj->where("user_id=".$val['id']." and add_time<='2015-07-31 23:59:59.999000'")->sum('due_capital');
            }
        }
        echo '男:'.$man.' 女:'.$woman.'<br>';
        dump($data);
    }

    /**
     * 单月30W+用户信息
     */
    public function user30plus(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        $userDueDetailObj = M("UserDueDetail");
        $userObj = M("User");
        $sql = "SELECT user_id,sum(due_capital) as sumc FROM `s_user_due_detail` WHERE ( user_id>0 and add_time>='2015-07-01 00:00:00.000000' and add_time<='2015-07-31 23:59:59.999000' ) GROUP BY user_id having sum(due_capital)>=300000;";
        $userList = $userDueDetailObj->query($sql);
        foreach($userList as $key => $val){
            $uinfo = $userObj->where(array('id'=>$val['user_id']))->find();
            echo $uinfo['username']."\t".$uinfo['real_name']."\t".$val['sumc']."\t";
            $lastorder = $userDueDetailObj->where("user_id=".$val['user_id']." and add_time>='2015-07-01 00:00:00.000000' and add_time<='2015-07-31 23:59:59.999000'")->order('add_time desc')->find();
            echo date('Y-m-d',strtotime($lastorder['add_time']))."<br>";
        }
    }

    /**
     * 项目下发信息表日期同步
     */
    public function issuedproject(){
        exit;
        $projectIssuedObj = M("ProjectIssued");

        $list = $projectIssuedObj->select();
        foreach($list as $key => $val){
            $projectIssuedObj->where(array('id'=>$val['id']))->save(array('online_time'=>date('Y-m-d', strtotime('-1 days',strtotime($val['issued_time'])))));
        }
    }

    /**
     * 按产品周期统计数据
     */
    public function product_cycle_statistics(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        $sql = 'select id,title,user_interest,DATEDIFF(end_time,start_time) as count from s_project where term_type=1 and is_delete=0 order by count asc,user_interest asc';
        $list = M()->query($sql);
        $mainList = array();
        echo '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
        echo '<thead><tr><th>周期</th><th>利率</th><th>总投资额</th><th>总投人数</th><th>首投金额</th><th>首投人数</th><th>复投金额</th><th>复投人数</th><th>二投金额</th><th>二投人数</th></tr></thead>';
        echo '<tbody>';
        foreach($list as $key => $val){
            if(!$mainList['m_'.$val['count'].'_'.$val['user_interest']]){
                $mainList['m_'.$val['count'].'_'.$val['user_interest']]['project_id'] = $val['id'];
                $mainList['m_'.$val['count'].'_'.$val['user_interest']]['cycle'] = $val['count'];
                $mainList['m_'.$val['count'].'_'.$val['user_interest']]['interest'] = $val['user_interest'];
            }else{
                $mainList['m_'.$val['count'].'_'.$val['user_interest']]['project_id'] .= ','.$val['id'];
            }
        }
        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");
        foreach($mainList as $key => $val){
            $mainList[$key]['totle_money'] = $userDueDetailObj->where("project_id in (".$val['project_id'].") and user_id>0")->sum('due_capital');
            $mainList[$key]['totle_person'] = count($userDueDetailObj->where("project_id in (".$val['project_id'].") and user_id>0")->group('user_id')->select());

            echo '<tr><td>'.$mainList[$key]['cycle'].'</td><td>'.$mainList[$key]['interest'].'%<td>'.number_format($mainList[$key]['totle_money'], 2).'</td><td>'.$mainList[$key]['totle_person'].'</td>';
            echo '<td></td><td></td><td></td><td></td><td></td><td></td></tr>';
        }
        //dump($mainList);
        echo '</tbody></table>';
    }

    /**
     * 钱包数据挖掘
     */
    public function wallet_need(){
        exit;
        header("Content-type: text/html; charset=utf-8");
        $userAccountObj = M("UserAccount");
        $userWalletRecordsObj = M("UserWalletRecords");
        $uids = I('get.ids', '', 'strip_tags');
        if($uids){
            $list = $userAccountObj->where("user_id in (".$uids.")")->select();
        }else{
            $list = $userAccountObj->select();
        }
        $inMoney = array(); $outMoney = array();
        $day = 0; $money = 0;
        $datetime = '';
        $result = 0;
        $count = count($list);
        foreach($list as $key => $val){
            $money = 0;
            $day = 0;
            $inMoney = array(); $outMoney = array();
            //echo '用户:'.$val['user_id'].'<br>';
            $recordList = $userWalletRecordsObj->where("user_id=".$val['user_id']." and ((type=1 and pay_status=2) or type=2)")->order('add_time asc')->select();
            foreach($recordList as $k => $v){
                $datetime = date('Y-m-d', strtotime($v['add_time']));
                if($v['type'] == 1){ // 转入
                    if(!$inMoney[$datetime]){ // 不存在
                        $inMoney[$datetime]['money'] = $v['value'];
                        $inMoney[$datetime]['date'] = $datetime;
                    }else{ // 已存在
                        $inMoney[$datetime]['money'] += $v['value'];
                    }
                }else{ // 转出
                    $lastmoney = abs($v['value']); // 剩余金额
                    $money += $v['value'];
                    foreach($inMoney as $kk => $vv){
                        //echo $lastmoney.'<br>';
                        if($inMoney[$kk]['money'] - $lastmoney > 0){ // 够减的情况
                            // 计算留存天数
                            //echo '@@@ '.$lastmoney.' || '.count_days($vv['date'], date('Y-m-d', strtotime($v['add_time']))).' || '.$vv['date'].'<br>';
                            $day += $lastmoney*count_days($vv['date'], date('Y-m-d', strtotime($v['add_time'])));
                            $inMoney[$kk]['money'] -= $lastmoney;
                            break;
                        }else{ // 不够减的情况
                            $lastmoney -= $inMoney[$kk]['money'];
                            //echo '@@@ '.$inMoney[$kk]['money'].' || '.count_days($vv['date'], date('Y-m-d', strtotime($v['add_time']))).' || '.$vv['date'].'<br>';
                            $day += abs($inMoney[$kk]['money'])*count_days($vv['date'], date('Y-m-d', strtotime($v['add_time'])));
                            $inMoney[$kk]['money'] = 0;
                        }
                    }
                }
                //echo date('Y-m-d H:i:s', strtotime($v['add_time'])).':'.$v['value'].'<br>';
            }
            //echo '==================================='.$day.'|'.$money.'<br>';
            //echo $day/$money;
            //echo '<br><br>';
            if(abs($money) > 0) echo $val['user_id']."\t".number_format(abs($money), 2)."\t".number_format(abs($day/$money), 2)."<br>";
            $result += ($day/$money);
        }
        echo '----------------------------------------------------------------<br>';
        echo number_format($result/$count, 2);
    }

    /**
     * 统计用户首投和二次投资
     */
    public function user_one_two(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $startTime = '2015-08-11';
        $endTime = '2015-08-24';
        $userDueDetailObj = M("UserDueDetail");
        $oneArr = array(); $twoArr = array();
        while(true){
            $oneTimes = 0; $twoTimes = 0;
            $userIds = $userDueDetailObj->where("add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->group("user_id")->select();

            foreach($userIds as $key => $val){
                if(!$userDueDetailObj->where("user_id=".$val['user_id']." and add_time<'".$startTime." 00:00:00.000000'")->getField('id')){
                    $oneTimes +=1 ;
                    array_push($oneArr, $val['user_id']);
                }else if($userDueDetailObj->where("user_id=".$val['user_id']." and add_time<'".$startTime." 00:00:00.000000'")->count() == 1){
                    $twoTimes += 1;
                    array_push($twoArr, $val['user_id']);
                }
            }
            echo $startTime."\t".$oneTimes."\t".$twoTimes."<br>";
            if($startTime == $endTime) break;
            $startTime = date('Y-m-d', strtotime('+1 days', strtotime($startTime)));
        }
    }

    /**
     * 首次绑定银行卡用户统计
     */
    public function user_bank_one(){

    }

    /**
     * 订单号检查
     */
    public function order_check(){
		exit;
        if(!IS_POST){

            $this->display();
        }else{
            $orderWallet = $_POST['order_wallet'];
            $orderProduct = $_POST['order_product'];
            if($orderWallet) $listWallet = explode("\r\n", $orderWallet);
            if($orderProduct) $listProduct = explode("\r\n", $orderProduct);
            $rechargeLogObj = M("RechargeLog");
            $userWalletRecordsObj = M("UserWalletRecords");

            $errorWalletOrder = ''; $errorProductOrder = '';
            foreach($listProduct as $key => $val){
                if(!$rechargeLogObj->where(array('recharge_no'=>$val))->getField('id')) $errorProductOrder .= ','.$val;
            }
            if($errorProductOrder) $errorProductOrder = substr($errorProductOrder, 1);
            foreach($listWallet as $key => $val){
                if(!$userWalletRecordsObj->where(array('recharge_no'=>$val))->getField('id')) $errorWalletOrder .= ','.$val;
            }
            if($errorWalletOrder) $errorWalletOrder = substr($errorWalletOrder, 1);
            echo 'Wallet Order:<br>';
            echo $errorWalletOrder."<br>";
            echo 'Product Order:<br>';
            echo $errorProductOrder;
        }
    }

    /**
     * 搏息宝统计
     */
    public function bxb_statistics(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");

        $userTotleArr = array();

        $list = $projectObj->field('id,title')->where(array('type'=>148,'is_delete'=>0))->order('add_time asc')->select();
        echo '<table>';
        echo '<thead><tr><th>产品名称</th><th>投资用户</th><th>人均投资</th><th>投资次数</th><th>投资期数</th></tr></thead>';
        echo '<tbody>';
        $termCount = 0;
        $userCount = 0;
        $userPrice = 0;
        foreach($list as $key => $val){
            $userArr = array();
            $userCount = 0;
            $termCount = 0;
            $userPrice = 0;
            echo '<tr>';
            echo '<td>'.$val['title'].'</td>';
            $dueList = $userDueDetailObj->field('id,user_id,due_capital')->where(array('project_id'=>$val['id']))->select();
            foreach($dueList as $k => $v){
                if(!in_array($v['user_id'], $userArr)){
                    $userCount += 1;
                    array_push($userArr, $v['user_id']);
                    if(!in_array($v['user_id'], $userTotleArr)){
                        array_push($userTotleArr, $v['user_id']);
                    }else{
                        $termCount += 1;
                    }
                }
                $userPrice += $v['due_capital'];
            }
            echo '<td>'.$userCount.'</td>';
            echo '<td>'.round($userPrice/$userCount,2).'</td>';
            echo '<td>'.count($dueList).'</td>';
            echo '<td>'.$termCount.'</td>';
            echo '</tr>';
            unset($userArr);
        }
        echo '</tbody></table>';
    }

    /**
     * 打新股统计
     */
    public function dxg_statistics(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $endTime = '2015-08-17 23:59:59.999000';
        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");

        $list = $projectObj->field('id,title')->where(array('type'=>104,'is_delete'=>0))->select();
        $userTotle = 0; // 打新总用户数
        $continuousCount = 0; // 连续打新用户
        $continuousCountArr = array();
        $userTwiceTotle = 0; // 打新复投用户
        $userIds = '';
        $userTotleArr = array();
        foreach($list as $key => $val){
            $dueList = $userDueDetailObj->field('id,user_id')->where("project_id=".$val['id']." and user_id>0")->select();
            foreach($dueList as $k => $v){
                if(!in_array($v['user_id'], $userTotleArr)){
                    $userTotle += 1;
                    array_push($userTotleArr, $v['user_id']);
                    $userIds .= ','.$v['user_id'];
                }else{
                    if(!in_array($v['user_id'], $continuousCountArr)){
                        $continuousCount += 1;
                        array_push($continuousCountArr, $v['user_id']);
                    }
                }
            }
        }
        if($userIds) $userIds = substr($userIds, 1);
        $userTwiceTotle = count($userDueDetailObj->where("user_id in (".$userIds.") and add_time>'".$endTime."'")->group('user_id')->having('count(id)>1')->select());
        echo '打新基金总用户: '.$userTotle.'<br>';
        echo '打新基金复投用户: '.$userTwiceTotle.'<br>';
        echo '打新失败流失率: '.number_format((($userTotle - $userTwiceTotle) / $userTotle)*100, 2).'%<br>';
        $userAll = count($userDueDetailObj->where("user_id>0")->group("user_id")->select()); // 总用户数量
        $userTwiceAll = count($userDueDetailObj->where("user_id>0")->group("user_id")->having("count(id)>1")->select()); // 总复投用户
        echo '正常客户流失率: '.number_format((($userAll - $userTwiceAll) / $userAll)*100, 2).'%<br>';
        echo '连续打新用户数: '.$continuousCount;
    }

    /**
     * 分级基金数据统计(未平仓)
     */
    public function fund_statistics_nopc(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $type = I('get.type', 0, 'int'); // A:110 B:109
        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");

        $userTotle = 0; // 打新总用户数
        $userIds = '';
        $userTotleArr = array();
        $userTwiceTotle = 0; // 打新复投用户
        $userTwiceIds = '';
        $userTwiceArr = array();
        $list = $projectObj->field('id,title')->where("type=".$type." and is_delete=0 and advance_end_time is NULL")->select();
        foreach($list as $key => $val){
            $dueList = $userDueDetailObj->field('id,user_id,add_time')->where("project_id=".$val['id']." and user_id>0")->select();
            foreach($dueList as $k => $v){
                if(!in_array($v['user_id'], $userTotleArr)){
                    $userTotle += 1;
                    array_push($userTotleArr, $v['user_id']);
                    $userIds .= ','.$v['user_id'];
                }
                // 计算二次购买用户
                if(!in_array($v['user_id'], $userTwiceArr)){
                    if($userDueDetailObj->where("user_id=".$v['user_id']." and add_time>'".$v['add_time']."'")->getField('id')){
                        $userTwiceTotle += 1;
                        array_push($userTwiceArr, $v['user_id']);
                        $userTwiceIds .= ','.$v['user_id'];
                    }
                }
            }
        }
        if($userIds) $userIds = substr($userIds, 1);
        if($userTwiceIds) $userTwiceIds = substr($userTwiceIds, 1);
        if($type == 110){
            echo '未平仓A基金总用户: '.$userTotle.'<br>';
            echo '未平仓A基金复投用户: '.$userTwiceTotle.'<br>';
            echo '未平仓A基金用户流失率: '.number_format((($userTotle - $userTwiceTotle) / $userTotle)*100, 2).'%<br>';
        }else if($type == 109){
            echo '未平仓B基金总用户: '.$userTotle.'<br>';
            echo '未平仓B基金复投用户: '.$userTwiceTotle.'<br>';
            echo '未平仓B基金用户流失率: '.number_format((($userTotle - $userTwiceTotle) / $userTotle)*100, 2).'%<br>';
        }
    }

    /**
     * 分级基金数据统计(已平仓)
     */
    public function fund_statistics_pc(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $type = I('get.type', 0, 'int'); // A:110 B:109
        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");

        $userTotle = 0; // 打新总用户数
        $userIds = '';
        $userTotleArr = array();
        $userTwiceTotle = 0; // 打新复投用户
        $userTwiceIds = '';
        $userTwiceArr = array();
        $userAll = 0; // 平台总用户
        $userAll = count($userDueDetailObj->field('user_id')->where("user_id>0")->group("user_id")->select()); // 总用户数量
        $allPriceAfterPc = 0; // 平仓后平台用户投资总金额
        $totlePriceAfterPc = 0; // 平仓后用户投资总金额
        $allCountAfterPc = 0; // 平仓后平台用户投资次数
        $totleCountAfterPc = 0; // 平仓后用户投资次数
        $allPersonAfterPc = 0; // 平仓后投资总用户数
        $list = $projectObj->field('id,title,advance_end_time')->where("type=".$type." and is_delete=0 and advance_end_time is not NULL")->select();
        foreach($list as $key => $val){
            $userTotle = 0; $userTwiceTotle = 0;
            $userIds = ''; $userTwiceIds = '';
            $userTotleArr = array(); $userTwiceArr = array();
            echo $val['title'].'(平仓:'.date('Y-m-d', strtotime($val['advance_end_time'])).')'.'<br>';
            $dueList = $userDueDetailObj->field('id,user_id,add_time')->where("project_id=".$val['id']." and user_id>0")->select();
            $allPriceAfterPc = $userDueDetailObj->where("user_id>0 and add_time>='".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000'")->sum('due_capital');
            $allCountAfterPc = $userDueDetailObj->where("user_id>0 and add_time>='".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000'")->count();
            $allPersonAfterPc = count($userDueDetailObj->where("user_id>0 and add_time>='".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000'")->group("user_id")->select());
            foreach($dueList as $k => $v){
                if(!in_array($v['user_id'], $userTotleArr)){
                    $userTotle += 1;
                    array_push($userTotleArr, $v['user_id']);
                    $userIds .= ','.$v['user_id'];
                }
                // 计算二次购买用户
                if(!in_array($v['user_id'], $userTwiceArr)){
                    if($userDueDetailObj->where("user_id=".$v['user_id']." and add_time>'".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000'")->getField('id')){
                        $userTwiceTotle += 1;
                        array_push($userTwiceArr, $v['user_id']);
                        $userTwiceIds .= ','.$v['user_id'];
                    }
                }
            }
            if($userIds) $userIds = substr($userIds, 1);
            if($userTwiceIds) $userTwiceIds = substr($userTwiceIds, 1);
            $totlePriceAfterPc = $userDueDetailObj->where("user_id in (".$userTwiceIds.") and add_time>'".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000'")->sum("due_capital");
            $totleCountAfterPc = $userDueDetailObj->where("user_id in (".$userTwiceIds.") and add_time>'".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000'")->count();
            if($type == 110){
                echo '已平仓A基金总用户: '.$userTotle.'<br>';
                echo '已平仓A基金复投用户: '.$userTwiceTotle.'<br>';
                echo '已平仓A基金用户流失率: '.number_format((($userTotle - $userTwiceTotle) / $userTotle)*100, 2).'%<br>';
                echo '平台用户人均投资金额: '.number_format($allPriceAfterPc / $allPersonAfterPc, 2).'<br>';
                echo '基金用户人均投资金额: '.number_format($totlePriceAfterPc / $userTwiceTotle, 2).'<br>';
                echo '平台用户人均投资次数: '.number_format($allCountAfterPc / $allPersonAfterPc, 2).'<br>';
                echo '基金用户人均投资次数: '.number_format($totleCountAfterPc / $userTwiceTotle, 2).'<br>';
                $tmp = M()->query("select a.id,a.user_id from s_user_due_detail a left join s_project b on b.id=a.project_id where a.user_id in (".$userIds.") and a.add_time>'".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000' and b.type in (109,110) group by a.user_id");
                echo '连续基金用户数: '.count($tmp).'<br>';
            }else if($type == 109){
                echo '已平仓B基金总用户: '.$userTotle.'<br>';
                echo '已平仓B基金复投用户: '.$userTwiceTotle.'<br>';
                echo '已平仓B基金用户流失率: '.number_format((($userTotle - $userTwiceTotle) / $userTotle)*100, 2).'%<br>';
                echo '平台用户人均投资金额: '.number_format($allPriceAfterPc / $allPersonAfterPc, 2).'<br>';
                echo '基金用户人均投资金额: '.number_format($totlePriceAfterPc / $userTwiceTotle, 2).'<br>';
                echo '平台用户人均投资次数: '.number_format($allCountAfterPc / $allPersonAfterPc, 2).'<br>';
                echo '基金用户人均投资次数: '.number_format($totleCountAfterPc / $userTwiceTotle, 2).'<br>';
                echo '连续基金用户数: '.count(M()->query("select a.id from s_user_due_detail a left join s_project b on b.id=a.project_id where a.user_id in (".$userIds.") and a.add_time>'".date('Y-m-d', strtotime($val['advance_end_time']))." 23:59:59.999000' and b.type in (109,110) group by a.user_id")).'<br>';
            }
            echo '<br>';
        }
    }

    /**
     * 平台总的3,4,5投用户数量(按每日)
     */
    public function all_three_four_five_persons(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $startDate = '2015-09-01';
        $endDate = '2015-09-08';
        echo '<table><thead>';
        echo '<tr><th>日期</th><th>三投</th><th>四投</th><th>五投</th></tr></thead>';
        echo '<tbody>';
        while($startDate < $endDate){
            $userIdList = array();
            $three = 0; $four = 0; $five = 0;
            echo '<tr>';
            echo '<td>'.$startDate.'</td>';

            $sql = "select * from (select id,user_id,due_capital,add_time,1 as frm from s_user_due_detail where user_id>0 and add_time>='".$startDate." 00:00:00.000000' and add_time<='".$startDate." 23:59:59.999000' ";
            $sql.= "UNION all ";
            $sql.= "select id,user_id,0,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and add_time>='".$startDate." 00:00:00.000000' and add_time<='".$startDate." 23:59:59.999000') as t order by add_time";
            $userPayList = M()->query($sql);
            foreach($userPayList as $key => $val){
                if(!in_array($val['user_id'], $userIdList)) {
                    array_push($userIdList, $val['user_id']);
                    $sql2 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=".$val['user_id']." and add_time<'".$startDate." 00:00:00.000000'";
                    $sql2.= " UNION all ";
                    $sql2.= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=".$val['user_id']." and add_time<'".$startDate." 00:00:00.000000') as t order by add_time asc";
                    $beforTimes = count(M()->query($sql2)); // 今天之前下过几笔订单(产品+钱包)
                    $sql3 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=".$val['user_id']." and add_time>='".$startDate." 00:00:00.000000'";
                    $sql3.= " UNION all ";
                    $sql3.= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=".$val['user_id']." and add_time>='".$startDate." 00:00:00.000000') as t order by add_time asc";
                    $nowTimes = count(M()->query($sql3)); // 今天下过的订单数量(产品+钱包)
                    switch($beforTimes){
                        case 2: // 3投用户
                            $three += 1;
                            if($beforTimes + $nowTimes >= 4) $four += 1;
                            if($beforTimes + $nowTimes >= 5) $five += 1;
                            break;
                        case 3: // 4投用户
                            $four += 1;
                            if($beforTimes + $nowTimes >= 5) $five += 1;
                            break;
                        case 4: // 5投用户
                            $five += 1;
                            break;
                    }
                }
            }
            echo '<td>'.$three.'</td><td>'.$four.'</td><td>'.$five.'</td>';
            echo '</tr>';
            $startDate = date('Y-m-d', strtotime('+1 days', strtotime($startDate)));
        }
        echo '</tbody></table>';
    }

    /**
     * 每日统计数据记录
     */
    public function statistics_daily_data(){

        header("Content-type: text/html; charset=utf-8");
        $dt = I('get.dt', '', 'strip_tags');
        if(!isDateFormat($dt)) exit;
        $auto = I('get.auto', 0, 'int'); // 是否自动
        $clearCache = I('get.cc', 0, 'int'); // 是否清除缓存

        if($clearCache){
            F('statistics_daily_data_cache', '');
        }

        $statisticsDailyObj = M("StatisticsDaily");
        $userDueDetailObj = M("UserDueDetail");
        $userWalletRecordsObj = M("UserWalletRecords");
        $activationDeviceObj = M("ActivationDevice");
        $projectObj = M("Project");
        $rechargeLogObj = M("RechargeLog");
        $contractProjectObj = M("ContractProject");
        $contractObj = M("Contract");
        $existID = $statisticsDailyObj->where(array('dt'=>$dt))->getField('id');

        //变量定义
        $allUids = '';
        $productUids = '';
        $walletUids = '';
        $firstPayUids = '';
        $repayUids = '';
        $firstProductPayUids = '';
        $firstWalletPayUids = '';
        $allUidsArr = array();
        $productUidsArr = array();
        $walletUidsArr = array();
        $firstPayUidsArr = array();
        $repayUidsArr = array();
        $firstProductPayUidsArr = array();
        $firstWalletPayUidsArr = array();

        $rows['dt'] = $dt;
        $payCount = 0; // 每日购买理财(包括钱包)用户总数

        // 购买产品用户UID
        $sql = "select user_id from s_user_due_detail where user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' group by user_id";
        $uidsResult = M()->query($sql);
        foreach($uidsResult as $key => $val){
            $productUids .= ','.$val['user_id'];
        }
        if($productUids) {
            $productUids = substr($productUids, 1);
            $productUidsArr = explode(',', $productUids);
        }

        // 购买钱包用户UID
        $sql = "select user_id from s_user_wallet_records where type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' group by user_id";
        $uidsResult = M()->query($sql);
        foreach ($uidsResult as $key => $val){
            $walletUids .= ','.$val['user_id'];
        }
        if($walletUids) {
            $walletUids = substr($walletUids, 1);
            $walletUidsArr = explode(',', $walletUids);
        }
        if($productUids && $walletUids)
        {
            $allUids = $productUids.','.$walletUids;
        }else if($productUids && !$walletUids){
            $allUids = $productUids;
        }else if(!$productUids && $walletUids){
            $allUids = $walletUids;
        }

        // 购买产品+钱包用户UID
        if($allUids) {
            $allUids = implode(',', array_unique(explode(',', $allUids)));
            $allUidsArr = explode(',', $allUids);
            $payCount = count($allUidsArr);
        }

        $rows['pay_count'] = $payCount;//每日投资理财(包括钱包)用户总数

        // 首投用户UID
        if($allUids){
            $sql = "select * from (select user_id,1 as fr,add_time from s_user_due_detail where user_id in (".$allUids.") and add_time<'".$dt." 00:00:00.000000' ";
            $sql.= "union all ";
            $sql.= "select user_id,2 as fr,add_time from s_user_wallet_records where user_id in (".$allUids.") and add_time<'".$dt." 00:00:00.000000') as t group by user_id";
            $uidsResult = M()->query($sql);
            foreach ($uidsResult as $key => $val){
                $repayUids .= ','.$val['user_id'];
            }
            if($repayUids) {
                $repayUids = substr($repayUids, 1);
                $repayUidsArr = explode(',', $repayUids);
            }
            $firstPayUidsArr = array_diff($allUidsArr, $repayUidsArr);
            $firstPayUids = implode(',', $firstPayUidsArr);
            $sql = "select user_id from s_user_due_detail where add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' and user_id in (".$firstPayUids.") group by user_id";
            $uidsResult = M()->query($sql);
            foreach($uidsResult as $key => $val){
                $firstProductPayUids .= ','.$val['user_id'];
            }
            if($firstProductPayUids) {
                $firstProductPayUids = substr($firstProductPayUids, 1);
                $firstProductPayUidsArr = explode(',', $firstProductPayUids);
                $firstWalletPayUidsArr = array_diff($firstPayUidsArr, $firstProductPayUidsArr);
                $firstWalletPayUids = implode(',', $firstWalletPayUidsArr);
            }
        }

        // 每日银行卡转理财
        $rows['b2p_money'] = $userDueDetailObj->where("user_id>0 and from_wallet=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
        // 每日银行卡转钱包
        $rows['b2w_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
        // 每日钱包转理财
        $rows['w2p_money'] = $userDueDetailObj->where("user_id>0 and from_wallet=1 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');;
        // 每日理财转钱包
        $rows['p2w_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');;
        // 每日理财还款（转到银行卡）
        // 稳一稳到期还款产品$sql = "select id from s_project where end_time>='".$dt." 00:00:00.000000' and end_time<='".$dt." 23:59:59.999000' and term_type=1";
        $sql = "select id from s_project where end_time>='".$dt." 00:00:00.000000' and end_time<='".$dt." 23:59:59.999000' and term_type=1";
        $tmpList = M()->query($sql);
        $rows['p2b_money'] = 0;
        $rows['p2b_count'] = 0;
        foreach($tmpList as $key => $val){
            //$rows['p2b_money'] += round($userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->sum('due_amount'), 2);
            $rows['p2b_money'] += round($userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->sum('due_capital'), 2); // 只算本金
            $rows['p2b_count'] += $userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->count();
        }
        // 每日钱包提现
        /**
         * 钱包提现有时间段之分,昨天15:00之后至今天15:00之前的提现订单于今天打款,明天确认份额, 今天15:00之后至第二天15:00之前的提现订单于第二天打款, 第三天确认份额, 以此类推(仅提现有时间段之分,其他确认份额都按天算)
         */
        $before_three = date("Y-m-d",strtotime($dt)-(24*3600))." 15:00:00.000000";
        $after_three = $dt." 15:00:00.000000";
        $rows['w2b_money'] = abs($userWalletRecordsObj->where("type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$before_three."' and add_time<='".$after_three."'")->sum('value'));
        // 每日理财转银行卡订单数
        $rows['w2b_count'] = $userWalletRecordsObj->where("type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->count();
        // 每日每个产品销售信息json字符串
        /********************************** 每日产品统计开始 ****************************************/
        $list = $userDueDetailObj->field('project_id,sum(due_capital) totlecapital')->where("add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->group('project_id')->order('project_id')->select();
        foreach($list as $key => $val){
            $list[$key]['project'] = $projectObj->field('id,title,amount,user_interest,financing,start_time,end_time,remark')->where(array('id'=>$val['project_id']))->find();
            $list[$key]['project']['days'] = count_days($list[$key]['project']['end_time'], $list[$key]['project']['start_time']);
            $more_money = $userDueDetailObj->where("project_id=".$val['project_id']." and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital') - $list[$key]['project']['amount'];
            if($more_money < 0) $more_money = 0;
            $list[$key]['money_more'] = $more_money;
            $ghost_money = $userDueDetailObj->where("user_id=0 and project_id=".$val['project_id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
            $list[$key]['ghost_money'] = $ghost_money;
            $yibao_money = $rechargeLogObj->where("type=2 and status=2 and user_id>0 and project_id=".$val['project_id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('amount');
            $list[$key]['yibao_money'] = $yibao_money;
            $wallet_money = $userDueDetailObj->where("from_wallet=1 and project_id=".$val['project_id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
            //$wallet_money = $rechargeLogObj->where("type=3 and status=2 and user_id>0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('amount');
            $list[$key]['wallet_money'] = $wallet_money;
            // 计算还款手续费(连连每笔1块,盛付通1W以下1块(包括),1W以上2块)<3家银行用连连:邮政(01000000)，华夏(03040000)，兴业(03090000)>
            //$cardList = $userDueDetailObj->field('due_capital,card_no')->where("add_time>='".$start_time."' and add_time<='".$end_time."'")->select();
            $sql = "select a.due_capital,a.card_no,b.bank_code from s_user_due_detail a left join s_user_bank b on b.bank_card_no=a.card_no and b.has_pay_success=2 where a.user_id>0 and a.project_id=".$val['project_id']." and a.add_time>='".$dt." 00:00:00.000000' and a.add_time<='".$dt." 23:59:59.999000'";
            $payFeeList = M()->query($sql);
            $fee = 0;
            $llCount = 0;
            $stfCount = 0;
            foreach($payFeeList as $k => $v){
                if(in_array($v['bank_code'], array('01000000','03040000','03090000'))){
                    $llCount += 1;
                    if($v['due_capital'] <= 10000){
                        $fee += 1;
                    }else{
                        $fee += 2;
                    }
                }else{
                    $stfCount += 1;
                    $fee += 1;
                }
            }
            $list[$key]['fee_info'] = array(
                'fee' => $fee,
                'll_count' => $llCount,
                'stf_count' => $stfCount,
            );
            // 获取产品对应合同相关数据
            $contractId = $contractProjectObj->where(array('project_name'=>$list[$key]['project']['title']))->getField('contract_id');
            if($contractId){
                $list[$key]['contract_info'] = $contractObj->where(array('id'=>$contractId))->find();
            }
        }
        $walletArr = array(
            'project' => array('id'=>0,'title'=>'石头钱包','financing'=>'王伟军'),
            'money_more' => 0,
            'ghost_money' => 0,
            'wallet_money' => 0,
        );
        $walletArr['totlecapital'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
        if($walletArr['totlecapital'] > 0){
            $walletArr['yibao_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
            array_unshift($list, $walletArr);
        }
        $rows['json_product_daily'] = json_encode($list);
        /********************************** 每日产品统计结束 ****************************************/

        // 每日理财产品投资用户总数
        $rows['pay_p_count'] = count($productUidsArr);
        // 每日钱包投资用户总数
        $rows['pay_w_count'] = count($walletUidsArr);
        // 每日投资用户UID字符串(每个UID之间用','间隔)
        $rows['str_pay_uid_daily'] = $allUids;
        // 每日首投用户UID字符串(每个UID之间用','间隔)
        $rows['str_first_pay_uid_daily'] = $firstPayUids;
        // 每日复投用户UID字符串(每个UID之间用','间隔)
        $rows['str_repay_uid_daily'] = $repayUids;

        // 每日首投理财用户数
        $rows['first_pay_p_count'] = count($firstProductPayUidsArr);
        // 每日首投钱包用户数
        $rows['first_pay_w_count'] = count($firstWalletPayUidsArr);
        // 每日二投理财用户数
        $rows['second_pay_p_count'] = 0;
        // 每日二投钱包用户数
        $rows['second_pay_w_count'] = 0;
        // 每日三投理财用户数
        $rows['third_pay_p_count'] = 0;
        // 每日三投钱包用户数
        $rows['third_pay_w_count'] = 0;
        // 每日四投理财用户数
        $rows['fourth_pay_p_count'] = 0;
        // 每日四投钱包用户数
        $rows['fourth_pay_w_count'] = 0;
        // 每日五投理财用户数
        $rows['fifth_pay_p_count'] = 0;
        // 每日五投钱包用户数
        $rows['fifth_pay_w_count'] = 0;
        // 每日复投用户数
        $rows['repay_count'] = count($repayUidsArr);
        // 每日复投金额
        if($repayUids) {
            $repay_product_money = $userDueDetailObj->where("user_id in (".$repayUids.") and from_wallet=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
            $repay_wallet_money = $userWalletRecordsObj->where("user_id in (".$repayUids.") and type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
            $rows['repay_money'] = $repay_product_money + $repay_wallet_money;
        }
        // 当日激活当日投理财产品用户数
        if($firstProductPayUids) $rows['activation_pay_p_count'] = $activationDeviceObj->where("device_serial_id in (select device_serial_id from s_user where id in (".$firstProductPayUids.")) and active_time>='".$dt." 00:00:00.000000' and active_time<='".$dt." 23:59:59.999000'")->count();
        // 当日激活当日投钱包用户数
        if($firstWalletPayUids) $rows['activation_pay_w_count'] = $activationDeviceObj->where("device_serial_id in (select device_serial_id from s_user where id in (".$firstWalletPayUids.")) and active_time>='".$dt." 00:00:00.000000' and active_time<='".$dt." 23:59:59.999000'")->count();
        // 每日理财产品投资额
        $rows['pay_p_money'] = $userDueDetailObj->where("user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
        // 每日钱包投资额
        $rows['pay_w_money'] = $userWalletRecordsObj->where("type=1 and (pay_type=2 or pay_type =1) and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
        // 每日首投理财金额
        $rows['first_pay_p_money'] = 0;
        // 每日首投钱包金额
        $rows['first_pay_w_money'] = 0;
        // 每日二投理财金额
        $rows['second_pay_p_money'] = 0;
        // 每日二投钱包金额
        $rows['second_pay_w_money'] = 0;
        // 每日三投理财金额
        $rows['third_pay_p_money'] = 0;
        // 每日三投钱包金额
        $rows['third_pay_w_money'] = 0;
        // 每日四投理财金额
        $rows['fourth_pay_p_money'] = 0;
        // 每日四投钱包金额
        $rows['fourth_pay_w_money'] = 0;
        // 每日五投理财金额
        $rows['fifth_pay_p_money'] = 0;
        // 每日五投钱包金额
        $rows['fifth_pay_w_money'] = 0;

        // 计算用户1~5投数量和金额
        foreach($allUidsArr as $key => $val){
            $sql = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time<'".$dt." 00:00:00.000000' UNION ALL ";
            $sql.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time<'".$dt." 00:00:00.000000') as t order by add_time asc";
            $tmpList = M()->query($sql);
            switch(count($tmpList)){
                case 0: // 首投用户
                    $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                    $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                    $tmpList2 = M()->query($sql2);
                    if(count($tmpList2) > 0){ // 首投
                        if($tmpList2[0]['fr'] == 1){ // 购买产品
                            $rows['first_pay_p_money'] += $tmpList2[0]['money'];
                        }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                            $rows['first_pay_w_money'] += $tmpList2[0]['money'];
                        }
                    }
                    if(count($tmpList2) > 1){ // 二投
                        if($tmpList2[1]['fr'] == 1){ // 购买产品
                            $rows['second_pay_p_count'] += 1;
                            $rows['second_pay_p_money'] += $tmpList2[1]['money'];
                        }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                            $rows['second_pay_w_count'] += 1;
                            $rows['second_pay_w_money'] += $tmpList2[1]['money'];
                        }
                    }
                    if(count($tmpList2) > 2){ // 三投
                        if($tmpList2[2]['fr'] == 1){ // 购买产品
                            $rows['third_pay_p_count'] += 1;
                            $rows['third_pay_p_money'] += $tmpList2[2]['money'];
                        }else if($tmpList2[2]['fr'] == 2){ // 充值钱包
                            $rows['third_pay_w_count'] += 1;
                            $rows['third_pay_w_money'] += $tmpList2[2]['money'];
                        }
                    }
                    if(count($tmpList2) > 3){ // 四投

                        if($tmpList2[3]['fr'] == 1){ // 购买产品
                            $rows['fourth_pay_p_count'] += 1;
                            $rows['fourth_pay_p_money'] += $tmpList2[3]['money'];
                        }else if($tmpList2[3]['fr'] == 2){ // 充值钱包
                            $rows['fourth_pay_w_count'] += 1;
                            $rows['fourth_pay_w_money'] += $tmpList2[3]['money'];
                        }
                    }
                    if(count($tmpList2) > 4){ // 五投
                        if($tmpList2[4]['fr'] == 1){ // 购买产品
                            $rows['fifth_pay_p_count'] += 1;
                            $rows['fifth_pay_p_money'] += $tmpList2[4]['money'];
                        }else if($tmpList2[4]['fr'] == 2){ // 充值钱包
                            $rows['fifth_pay_w_count'] += 1;
                            $rows['fifth_pay_w_money'] += $tmpList2[4]['money'];
                        }
                    }
                    break;
                case 1: // 二投用户
//                    if($tmpList[0]['fr'] == 1){ // 购买产品
//                        $rows['second_pay_p_count'] += 1;
//                        $rows['second_pay_p_money'] += $tmpList[1]['money'];
//                    }else if($tmpList[0]['fr'] == 2){ // 充值钱包
//                        $rows['second_pay_w_count'] += 1;
//                        $rows['second_pay_w_money'] += $tmpList[1]['money'];
//                    }
                    $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                    $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                    $tmpList2 = M()->query($sql2);
                    if(count($tmpList2) > 0){ // 二投
                        if($tmpList2[0]['fr'] == 1){ // 购买产品
                            $rows['third_pay_p_count'] += 1;
                            $rows['third_pay_p_money'] += $tmpList2[0]['money'];
                        }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                            $rows['third_pay_w_count'] += 1;
                            $rows['third_pay_w_money'] += $tmpList2[0]['money'];
                        }
                    }
                    if(count($tmpList2) > 1){ // 三投
                        if($tmpList2[1]['fr'] == 1){ // 购买产品
                            $rows['third_pay_p_count'] += 1;
                            $rows['third_pay_p_money'] += $tmpList2[1]['money'];
                        }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                            $rows['third_pay_w_count'] += 1;
                            $rows['third_pay_w_money'] += $tmpList2[1]['money'];
                        }
                    }
                    if(count($tmpList2) > 2){ // 四投
                        if($tmpList2[2]['fr'] == 1){ // 购买产品
                            $rows['fourth_pay_p_count'] += 1;
                            $rows['fourth_pay_p_money'] += $tmpList2[2]['money'];
                        }else if($tmpList2[2]['fr'] == 2){ // 充值钱包
                            $rows['fourth_pay_w_count'] += 1;
                            $rows['fourth_pay_w_money'] += $tmpList2[2]['money'];
                        }
                    }
                    if(count($tmpList2) > 3){ // 五投
                        if($tmpList2[3]['fr'] == 1){ // 购买产品
                            $rows['fifth_pay_p_count'] += 1;
                            $rows['fifth_pay_p_money'] += $tmpList2[3]['money'];
                        }else if($tmpList2[3]['fr'] == 2){ // 充值钱包
                            $rows['fifth_pay_w_count'] += 1;
                            $rows['fifth_pay_w_money'] += $tmpList2[3]['money'];
                        }
                    }
                    break;
                case 2: // 三投用户
//                    if($val['fr'] == 1){ // 购买产品
//                        $rows['third_pay_p_count'] += 1;
//                        $rows['third_pay_p_money'] += $tmpList[2]['money'];
//                    }else if($val['fr'] == 2){ // 充值钱包
//                        $rows['third_pay_w_count'] += 1;
//                        $rows['third_pay_w_money'] += $tmpList[2]['money'];
//                    }
                    $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                    $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                    $tmpList2 = M()->query($sql2);
                    if(count($tmpList2) > 0){ // 三投
                        if($tmpList2[0]['fr'] == 1){ // 购买产品
                            $rows['fourth_pay_p_count'] += 1;
                            $rows['fourth_pay_p_money'] += $tmpList2[0]['money'];
                        }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                            $rows['fourth_pay_w_count'] += 1;
                            $rows['fourth_pay_w_money'] += $tmpList2[0]['money'];
                        }
                    }
                    if(count($tmpList2) > 1){ // 四投
                        if($tmpList2[1]['fr'] == 1){ // 购买产品
                            $rows['fourth_pay_p_count'] += 1;
                            $rows['fourth_pay_p_money'] += $tmpList2[1]['money'];
                        }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                            $rows['fourth_pay_w_count'] += 1;
                            $rows['fourth_pay_w_money'] += $tmpList2[1]['money'];
                        }
                    }
                    if(count($tmpList2) > 2){ // 五投
                        if($tmpList2[2]['fr'] == 1){ // 购买产品
                            $rows['fifth_pay_p_count'] += 1;
                            $rows['fifth_pay_p_money'] += $tmpList2[2]['money'];
                        }else if($tmpList2[2]['fr'] == 2){ // 充值钱包
                            $rows['fifth_pay_w_count'] += 1;
                            $rows['fifth_pay_w_money'] += $tmpList2[2]['money'];
                        }
                    }
                    break;
                case 3: // 四投用户
//                    if($val['fr'] == 1){ // 购买产品
//                        $rows['fourth_pay_p_count'] += 1;
//                        $rows['fourth_pay_p_money'] += $tmpList[3]['money'];
//                    }else if($val['fr'] == 2){ // 充值钱包
//                        $rows['fourth_pay_w_count'] += 1;
//                        $rows['fourth_pay_w_money'] += $tmpList[3]['money'];
//                    }
                    $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                    $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                    $tmpList2 = M()->query($sql2);
                    if(count($tmpList2) > 0){ // 四投
                        if($tmpList2[0]['fr'] == 1){ // 购买产品
                            $rows['fifth_pay_p_count'] += 1;
                            $rows['fifth_pay_p_money'] += $tmpList2[0]['money'];
                        }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                            $rows['fifth_pay_w_count'] += 1;
                            $rows['fifth_pay_w_money'] += $tmpList2[0]['money'];
                        }
                    }
                    if(count($tmpList2) > 1){ // 五投
                        if($tmpList2[1]['fr'] == 1){ // 购买产品
                            $rows['fifth_pay_p_count'] += 1;
                            $rows['fifth_pay_p_money'] += $tmpList2[1]['money'];
                        }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                            $rows['fifth_pay_w_count'] += 1;
                            $rows['fifth_pay_w_money'] += $tmpList2[1]['money'];
                        }
                    }
                    break;
                case 4: // 五投用户
//                    if($val['fr'] == 1){ // 购买产品
//                        $rows['fifth_pay_p_count'] += 1;
//                        $rows['fifth_pay_p_money'] += $tmpList[4]['money'];
//                    }else if($val['fr'] == 2){ // 充值钱包
//                        $rows['fifth_pay_w_count'] += 1;
//                        $rows['fifth_pay_w_money'] += $tmpList[4]['money'];
//                    }
                    $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                    $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                    $tmpList2 = M()->query($sql2);
                    if(count($tmpList2) > 0){ // 五投
                        if($tmpList2[0]['fr'] == 1){ // 购买产品
                            $rows['fifth_pay_p_count'] += 1;
                            $rows['fifth_pay_p_money'] += $tmpList2[0]['money'];
                        }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                            $rows['fifth_pay_w_count'] += 1;
                            $rows['fifth_pay_w_money'] += $tmpList2[0]['money'];
                        }
                    }
                    break;
            }
        }

        foreach($rows as $key => $val){
            if(is_null($val)) $rows[$key] = 0;
        }

        if(!$auto) dump($rows);
        if(!$existID){ // 不存在每日统计信息
            if(!$statisticsDailyObj->add($rows)){
                if(!$auto){
                    $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
                    echo $dt.'数据添加失败, <a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'">'.$nextDt.'</a>&nbsp;&nbsp;<a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$dt.'">重试</a>';
                }else{
                    $cache = F('statistics_daily_data_cache');
                    F('statistics_daily_data_cache', $cache."\r\n".$dt."添加失败");
                    $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
                    if($nextDt < date('Y-m-d', time())) {
                        echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'&auto='.$auto.'";</script>';
                    }
                }
            }else{
                $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
                if($nextDt < date('Y-m-d', time())) {
                    if(!$auto){
                        echo $dt.'数据添加成功~! <a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'">'.$nextDt.'</a>';
                    }else{
                        echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'&auto='.$auto.'";</script>';
                    }
                }
            }
        }else{ // 已存在统计信息
            if(!$statisticsDailyObj->where(array('id'=>$existID))->save($rows)){
                if(!$auto){
                    $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
                    echo $dt.'数据更新失败, <a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'">'.$nextDt.'</a>&nbsp;&nbsp;<a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$dt.'">重试</a>';
                }else{
                    $cache = F('statistics_daily_data_cache');
                    F('statistics_daily_data_cache', $cache."\r\n".$dt."更新失败");
                    $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
                    if($nextDt < date('Y-m-d', time())) {
                        echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'&auto='.$auto.'";</script>';
                    }else{
                        echo F('statistics_daily_data_cache');
                    }
                }
            }else{
                $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
                if($nextDt < date('Y-m-d', time())) {
                    if(!$auto){
                        echo $dt.'数据更新成功~! <a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'">'.$nextDt.'</a>';
                    }else{
                        echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/statistics_daily_data?dt='.$nextDt.'&auto='.$auto.'";</script>';
                    }
                }else{
                    echo F('statistics_daily_data_cache');
                }
            }
        }
    }

    /**
     * 每日统计用户数据记录
     */
    public function statistics_user_daily_data(){
        header("Content-type: text/html; charset=utf-8");
        $dt = I('get.dt', '', 'strip_tags');
        if(!isDateFormat($dt)) exit;
        $auto = I('get.auto', 0, 'int'); // 是否自动
        $constantObj = M("Constant");
        $userObj = M("User");
        $activationDeviceObj = M("ActivationDevice");
        $userDueDetailObj = M("UserDueDetail");
        $userWalletRecordsObj = M("UserWalletRecords");
        $statisticsUserDailyObj = M("StatisticsUserDaily");

        // 查询当天所有用户的购买理财产品记录和充值钱包记录
        $sql = "select * from (select user_id,1 as fr,due_capital as money,add_time,from_wallet,1 as user_bank_id,0 as user_due_detail_id from s_user_due_detail where user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
        $sql.= "select user_id,2 as fr,value as money,add_time,0 as from_wallet,user_bank_id,user_due_detail_id from s_user_wallet_records where type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by user_id asc,add_time asc";
        $result = M()->query($sql);
        $records = array();
        $uid = 0;
        $channel_id = 0;
        $device_serial_id = '';
        $uinfo = null;
        foreach($result as $key => $val){
            if($uid != $val['user_id']) {
                $uid = $val['user_id'];
                $uinfo = $userObj->field('channel_id,device_serial_id')->where(array('id'=>$uid))->find();
                $channel_id = $uinfo['channel_id'];
                $device_serial_id = $uinfo['device_serial_id'];
            }
            $records[$uid]['channel_id'] = $channel_id;
            $records[$uid]['device_serial_id'] = $device_serial_id;
            $records[$uid]['sub'][] = $val;
        }
        foreach($records as $key => $val){
            unset($rows);
            $rows['repay_count'] = 0;
            $rows['repay_money'] = 0;
            $rows['first_pay'] = 0;
            $rows['second_pay'] = 0;
            $rows['third_pay'] = 0;
            $rows['fourth_pay'] = 0;
            $rows['fifth_pay'] = 0;
            $rows['first_pay_money'] = 0;
            $rows['second_pay_money'] = 0;
            $rows['third_pay_money'] = 0;
            $rows['fourth_pay_money'] = 0;
            $rows['fifth_pay_money'] = 0;
            // 用户之前今天之前购买过的产品(包括钱包)订单数量
            $preCount = $userDueDetailObj->where("user_id=".$key." and add_time<'".$dt." 00:00:00.000000'")->count();
            $preCount += $userWalletRecordsObj->where("user_id=".$key." and type=1 and pay_status=2 and add_time<'".$dt." 00:00:00.000000'")->count();
            $curCount = 0; // 当前购买产品次数
            $json_product_list = json_encode($val['sub']);
            $b2p_count = 0; $b2w_count = 0; $w2p_count = 0; $p2b_count = 0; $p2w_count = 0; $w2b_count = 0;
            $b2p_money = 0; $b2w_money = 0; $w2p_money = 0; $p2b_money = 0; $p2w_money = 0; $w2b_money = 0;
            $rows['dt'] = $dt;
            $rows['uid'] = $key;
            $rows['channel_id'] = $val['channel_id'];
            $rows['json_product_list'] = $json_product_list;
            if($activationDeviceObj->where("device_serial_id='".$val['device_serial_id']."' and active_time>='".$dt." 00:00:00.000000' and active_time<='".$dt." 23:59:59.999000'")->getField('id')) $rows['activity_pay'] = 1;
            else $rows['activity_pay'] = 0;
            foreach($val['sub'] as $k => $v){
                if($preCount + $curCount > 0) {
                    $rows['repay_count'] += 1;
                    $rows['repay_money'] += $v['money'];
                }
                $curCount += 1;
                switch($preCount + $curCount){
                    case 1:
                        $rows['first_pay'] = $v['fr'];
                        $rows['first_pay_money'] = $v['money'];
                        break;
                    case 2:
                        $rows['second_pay'] = $v['fr'];
                        $rows['second_pay_money'] = $v['money'];
                        break;
                    case 3:
                        $rows['third_pay'] = $v['fr'];
                        $rows['third_pay_money'] = $v['money'];
                        break;
                    case 4:
                        $rows['fourth_pay'] = $v['fr'];
                        $rows['fourth_pay_money'] = $v['money'];
                        break;
                    case 5:
                        $rows['fifth_pay'] = $v['fr'];
                        $rows['fifth_pay_money'] = $v['money'];
                        break;
                }
                if($v['fr'] == 1){ // 理财产品购买
                    if($v['from_wallet'] > 0){ // 钱包购买
                        $w2p_count += 1;
                        $w2p_money += $v['money'];
                    }else{ // 银行卡购买
                        $b2p_count += 1;
                        $b2p_money += $v['money'];
                    }
                }else if($v['fr'] == 2){ // 充值钱包
                    if($v['user_bank_id'] > 0 && $v['user_due_detail_id'] == 0){ // 银行卡充值
                        $b2w_count += 1;
                        $b2w_money += $v['money'];
                    }else if($v['user_bank_id'] == 0 && $v['user_due_detail_id'] > 0){ // 产品还本付息
                        $p2w_count += 1;
                        $p2w_money += $v['money'];
                    }
                }
            }
            // 钱包提现次数和金额
            $w2b_count = (int)$userWalletRecordsObj->where("user_id=".$key." and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->count();
            $w2b_money = (float)$userWalletRecordsObj->where("user_id=".$key." and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
            // 产品还本付息到银行卡次数和金额
            $paybackList = $userDueDetailObj->field('user_id,due_capital,add_time,from_wallet,to_wallet')->where("user_id=".$key." and from_wallet=0 and to_wallet=0 and due_time>='".$dt." 00:00:00.000000' and due_time<='".$dt." 23:59:59.999000'")->select();
            $json_payback_list = json_encode($paybackList);
            $rows['json_payback_list'] = $json_payback_list;
            foreach($paybackList as $k => $v){
                $p2b_count += 1;
                $p2b_money += $v['due_capital'];
            }

            $rows['b2p_count'] = $b2p_count; $rows['b2w_count'] = $b2w_count; $rows['w2p_count'] = $w2p_count;
            $rows['p2b_count'] = $p2b_count; $rows['p2w_count'] = $p2w_count; $rows['w2b_count'] = $w2b_count;
            $rows['b2p_money'] = $b2p_money; $rows['b2w_money'] = $b2w_money; $rows['w2p_money'] = $w2p_money;
            $rows['p2b_money'] = $p2b_money; $rows['p2w_money'] = $p2w_money; $rows['w2b_money'] = $w2b_money;

            $existId = $statisticsUserDailyObj->where(array('dt'=>$dt,'uid'=>$key))->getField('id');
            if(!$existId){
                $statisticsUserDailyObj->add($rows);
            }else{
                $statisticsUserDailyObj->where(array('id'=>$existId))->save($rows);
            }
        }
        echo "成功更新数据 ".count($records)." 条<br>";
        $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
        if(!$auto){
            echo '下个日期: <a href="'.C('ADMIN_ROOT').'/Test/statistics_user_daily_data?dt='.$nextDt.'">'.$nextDt.'</a>';
        }else{
            if($nextDt < date('Y-m-d', time())) echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/statistics_user_daily_data?dt='.$nextDt.'&auto=1";</script>';
        }
    }

    /**
     * 每日统计理财产品收益数据
     */
    public function statistics_daily_profit_data(){
        header("Content-type: text/html; charset=utf-8");
        $dt = I('get.dt', '', 'strip_tags');
        if(!isDateFormat($dt)) exit;
        $auto = I('get.auto', 0, 'int'); // 是否自动

        $rows['dt'] = $dt;

        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");
        $userWalletRecordsObj = M("UserWalletRecords");
        $statisticsDailyProfitObj = M("StatisticsDailyProfit");
        $userWalletInterestObj = M("UserWalletInterest");

        $projectInterest = array();
        $result = $userDueDetailObj->field('project_id')->where("user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->group("project_id")->select();
        foreach($result as $key => $val){
            $interest = $projectObj->field('id,contract_interest,user_interest,end_time')->where(array('id'=>$val['project_id'],'term_type'=>1))->find();
            if($interest) $projectInterest[$val['project_id']] = $interest;
        }
        $incomeSum = 0; $expensesSum = 0;
        foreach($projectInterest as $key => $val){
            $dueList = $userDueDetailObj->field('due_capital,from_wallet,to_wallet')->where("user_id>0 and project_id=".$val['id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->select();
            $days = count_days(date('Y-m-d', strtotime($val['end_time'])).' 08:00:00', date('Y-m-d', strtotime('+1 days', strtotime($dt))).' 08:00:00');
            $sum = 0; $payback_count = 0;
            foreach($dueList as $k => $v){
                $sum += $v['due_capital'];
                if($v['from_wallet'] == 0 && $v['to_wallet'] == 0) $payback_count += 1;
            }
            $incomeSum += $sum*$days*0.15/365 + $sum*0.001;
            $expensesSum += $sum*$days*$val['user_interest']/100/365 + $sum*0.002 + $payback_count;
        }

        $rows['p_income'] = round($incomeSum, 2);
        $rows['p_expenses'] = round($expensesSum, 2);

        // 计算钱包收益
        $incomeSum2 = 0; $expensesSum2 = 0; $wallet2ProductSum = 0;
        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($dt)));
        $projectInterest2 = array();
        $result = $userDueDetailObj->field('project_id')->where("user_id>0 and add_time>='".$yestoday." 00:00:00.000000' and add_time<='".$yestoday." 23:59:59.999000'")->group("project_id")->select();
        foreach($result as $key => $val){
            $interest = $projectObj->field('id,contract_interest,user_interest,end_time')->where(array('id'=>$val['project_id'],'term_type'=>1))->find();
            if($interest) $projectInterest2[$val['project_id']] = $interest;
        }
        foreach($projectInterest2 as $key => $val){
            $wallet2ProductSum += $userDueDetailObj->where("user_id>0 and from_wallet=1 and project_id=".$val['id']." and add_time>='".$yestoday." 00:00:00.000000' and add_time<='".$yestoday." 23:59:59.999000'")->sum('due_capital');
        }
        $incomeSum2 = $wallet2ProductSum*0.002;
        $expensesSum2 = $userWalletInterestObj->where("interest_time>='".$dt." 00:00:00.000000' and interest_time<='".$dt." 23:59:59.999000'")->sum('interest');
        $expensesSum2+= $userWalletRecordsObj->where("type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value')*0.002;
        $expensesSum2+= $userWalletRecordsObj->where("type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.00000' and add_time<='".$dt." 23:59:59.999000'")->count();

        $rows['w_offline_income'] = 0; // 每日线下投资收入
        $rows['w_income'] = round($incomeSum2, 2);
        $rows['w_expenses'] = round($expensesSum2, 2);

        $existId = $statisticsDailyProfitObj->where(array('dt'=>$dt))->getField('id');
        if(!$existId){
            $statisticsDailyProfitObj->add($rows);
        }else{
            $statisticsDailyProfitObj->where(array('id'=>$existId))->save($rows);
        }
        dump($rows);
        $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($dt)));
        if(!$auto){
            echo '下个日期: <a href="'.C('ADMIN_ROOT').'/Test/statistics_daily_profit_data?dt='.$nextDt.'">'.$nextDt.'</a>';
        }else{
            if($nextDt < date('Y-m-d', time())) echo '<script>window.location.href="'.C('ADMIN_ROOT').'/Test/statistics_daily_profit_data?dt='.$nextDt.'&auto=1";</script>';
        }
    }

    /**
     * 处理每日数据总计用户购买理财(包括钱包)总数
     */
    public function urgent_statistics_daily_data(){
		exit;
        header("Content-type: text/html; charset=utf-8");
        $statisticsDailyObj = M("StatisticsDaily");
        $list = $statisticsDailyObj->field('id,str_pay_uid_daily')->select();
        foreach($list as $key => $val){
            $count = 0;
            $count = count(explode(',', $val['str_pay_uid_daily']));
            $statisticsDailyObj->where(array('id'=>$val['id']))->save(array('pay_count'=>$count));
        }
        echo '完成~!';
    }

}