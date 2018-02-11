<?php
namespace Admin\Controller;

class JxController extends AdminController
{
    
    public function test_1() {
        
        $userIds = '1,2,3,4,5,6,7';
        
        $regsArr = ''; // 极光推送注册ID组
        $userArr = M('User')->field('id,registration_id')->where("id in (".$userIds.")")->select();
        $pos = 0;
        $userGroup = array();
        foreach($userArr as $key => $val){
            $pos += 1;
            $regsArr .= ','.$val['registration_id'];
            if($pos >= 50){ // 50个极光推送ID为一组
                if($regsArr) $regsArr = substr($regsArr, 1);
                $userGroup[] = $regsArr;
                $regsArr = '';
                $pos = 0;
            }
        }
        if($regsArr) {
            $regsArr = substr($regsArr, 1);
            $userGroup[] = $regsArr;
        }
       // $projectTitle = $projectObj->where(array('id'=>$id))->getField('title');
        $result[] = array();
        foreach($userGroup as $key => $val){
            
            echo $val;
            
            //if(trim($val)) $result[] = pushMsg('您购买的产品['.$projectTitle.']本息已到账，请注意查收。', $val);
        }
        
        
    }
    
    public function sendbag() {
        exit;
        
        $row['status'] = 0;
        $row['type'] = 1;
        $row['scope'] = '2';
        $row['add_user_id'] = 1;
        
        
        
        $list = array();// M("User")->field("id")->where("id>151")->select();
        
        echo "共计：".count($list);
        
        foreach ($list as $val) {
            
            
            
            $row['user_id'] = $row['modify_user_id'] = $val['id'];
            
            
            $row['title'] = '新手注册奖励红包';
            $row['content'] = '新手注册奖励红包';
            $row['amount'] = '10';
            $row['min_invest'] = '1000';	//红包最小投资金额：
            $row['min_due'] = '15';	//红包最小投资期限：
            $row['create_time'] =  date("Y-m-d H:i:s");
            $row['expire_time'] = date("Y-m-d H:i:s",time()+60 * 86400).'.'.getMillisecond().'000';
            
            print_r($row);
            echo "<br/>";
            echo "<br/>";
            
           // $res = M("UserRedenvelope")->add($row);
            
            
            $row['title'] = '新手购买奖励红包';
            $row['content'] = '新手购买奖励红包';
            $row['amount'] = '20';
            $row['min_invest'] = '3000';	//红包最小投资金额：
            $row['min_due'] = '15';	//红包最小投资期限：
            $row['create_time'] =  date("Y-m-d H:i:s");
            $row['expire_time'] = date("Y-m-d H:i:s",time()+ 60 * 86400).'.'.getMillisecond().'000';
            $row['user_id'] = $val['id'];
            
            
            print_r($row);
            echo "<br/>";
            echo "<br/>";
           // $res = M("UserRedenvelope")->add($row);
            
            $row['title'] = '新手红包';
            $row['content'] = '新手红包';
            $row['amount'] = '30';
            $row['min_invest'] = '20000';	//红包最小投资金额：
            $row['min_due'] = '60';	//红包最小投资期限：
            $row['create_time'] =  date("Y-m-d H:i:s");
            $row['expire_time'] = date("Y-m-d H:i:s",time()+60 * 86400).'.'.getMillisecond().'000';
            
            
            print_r($row);
            echo "<br/>";
            echo "<br/>";
           // $res = M("UserRedenvelope")->add($row);
        }
        
    }
    
    public function test()
    {
        
        exit;
        $user_id = I('get.uid', 53, 'int');
        
        $user_id = 53;
        
        $inAmount = '';
        $buyAmount = '';
        $outAmount = '';
        
        $start_date = '2016-03-24';
        
        $cnt = count_days($start_date,date("Y-m-d H:i:s"));
        
        
        $start_time = '2016-03-24 00:00:00 000000';
                
        $rate = 6.35;
        
        $end_time = strtotime('2016-03-24');
        
        $lx = 0;
        
        for ($i = 1; $i <= $cnt; $i ++) {
            
            
            $end_time +=(86400-1);
            
           
            $end_date = date("Y-m-d",$end_time).' 23:23:59 999000';
            
            
            // 转入金额
            $inAmount = M('UserWalletRecords')->where("user_id = $user_id and type=1 and pay_status=2 and add_time >= '$start_time' and add_time <'$end_date'")->sum('value');
            
            // 用钱包 购买产品
            $buyAmount = M('UserWalletRecords')->where("user_id = $user_id and type=2 and user_bank_id=0 and user_due_detail_id >0 and add_time >= '$start_time' and add_time <'$end_date'")->sum('value');
            
            // 用户提现
            $outAmount = M('UserWalletRecords')->where("user_id = $user_id and type=2 and user_bank_id>0 and user_due_detail_id =0 and add_time >= '$start_time' and add_time <'$end_date'")->sum('value');
            
            
            echo '转入：'.$inAmount.'--购买产品：'.$buyAmount.' --提现：'.$outAmount .'-----';
            
            
            
            $v = $inAmount + $buyAmount + $outAmount + $lx;
            
            
            $userWalletAnnualizedRate = M('UserWalletAnnualizedRate')->where(array('add_time'=>date("Y-m-d",$end_time)))->find();
            
            $rate = 6.35;
            
            if($userWalletAnnualizedRate) {
                $rate = $userWalletAnnualizedRate['rate'];;
            }
            
           
            $rate = round($rate/100, 4);
            
            
            $interest = round($v * $rate / 365, 4); // 该日所得利息(四舍五入小数点后4位)
            
            
            $lx += $interest;
            
            echo date("Y-m-d",$end_time) . "  计算总金额：" . $v;
            
            echo " --- 利息：".$interest."<br/>";
            
        }
    }
    
    public function test2(){
        
        exit;
    
        $start_time = date('Y-m-d', time());//当前日期
        
        $start_time = "2016-04-15";
    
        $befor_yestoday = date('Y-m-d', strtotime('-1 days', strtotime($start_time))); // 指定日期前一日
        $befoe_befor_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($start_time))); // 指定日期前前一日
    
        $cond[] = "(type=1 and pay_status=2 and add_time>='".$befor_yestoday." 00:00:00.000000' and add_time<='".$befor_yestoday." 23:59:59.999000')"; // 转入
        $cond[] = "(type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$befoe_befor_yestoday." 15:00:01.000000' and add_time<='".$befor_yestoday." 15:00:00.999000')"; // 转出（提现）
        $cond[] = "(type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$befor_yestoday." 00:00:00.000000' and add_time<='".$befor_yestoday." 23:59:59.999000')"; // 转出（购买产品）
        $conditions = implode(' or ', $cond);
        $conditions = "(".$conditions.") and enable_interest=0";
        
       // echo $conditions.”《 ;
    
        $userWalletRecordsObj = M('UserWalletRecords');
    
        $counts = $userWalletRecordsObj->where($conditions)->count();
    
        $list = $userWalletRecordsObj->field('id')->where($conditions)->order('add_time asc')->select();
        $choose_id_str='';
        $return_choose_id='';
        if($list){
            foreach($list as $k=>$v){
                $choose_id_str.=','.$v['id'];
            }
            if($choose_id_str){
                $return_choose_id = substr($choose_id_str,1);
            }
        }
        if($return_choose_id){
            echo $return_choose_id;
            return $return_choose_id;
        } else{
            echo '222';
        }
    }
    
    public function test3($ids=351){
        
        exit;
    
        //$startTime = date('Y-m-d', time());
        
        $startTime = "2016-04-15";
    
        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($startTime)));
    
        $userWalletRecordsObj = M('UserWalletRecords');
        $userAccountObj = M('UserAccount');
        $taskInterestObj = M('TaskInterest');
        $userObj = M('User');
    
        if(!$ids) return 1;
        $list = $userWalletRecordsObj->where("id in (".$ids.") and enable_interest=0")->select();
        
        
        
        
        $value = abs($list[0]['value']); // 转出金额
        
        
        $userInfo = $userAccountObj->where(array('user_id'=>$list[0]['user_id']))->find(); // 用户账户信息
        
        echo $value .'----'. $userInfo['wallet_enable_interest'];
        
        
        /*
        if($value <= $userInfo['wallet_enable_interest']){
            $userWalletRecordsObj->startTrans();
            if($userAccountObj->where(array('user_id'=>$list['user_id']))->setDec('wallet_enable_interest', $value)){
                if(!$userWalletRecordsObj->where(array('id'=>$list['id'],'enable_interest'=>0))->save(array('enable_interest'=>1))) {
                    $userWalletRecordsObj->rollback();
                }else{
                    $userWalletRecordsObj->commit();
                }
            }else{
                $userWalletRecordsObj->commit();
            }
        }else{//记录转出异常的数据
            $single_user_info = $userObj->where(array('id'=>$list['user_id']))->find();//用户信息
            $data = array(
                'username'=>$single_user_info['username'],
                'real_name'=>$single_user_info['real_name'],
                'type'=>2,
                'add_time'=>date('Y-m-d H:i:s',time()),
                'desc'=>'转出金额大于可计息金额,数据异常,请检查'
            );
            $taskInterestObj->add($data);
        }
        
        
        exit;
        /*
        if(!$list) return 1;
    
        $errMsg = ''; // 错误消息
        foreach($list as $key => $val){
            if($val['add_time'] > $yestoday.' 23:59:59.999000'){ // 时间不满足,无法确认份额
                continue;
            }else{
                if($val['type'] == 1){ // 转入
                    $userWalletRecordsObj->startTrans();
                    if($userAccountObj->where(array('user_id'=>$val['user_id']))->setInc('wallet_enable_interest', $val['value'])){
                        if(!$userWalletRecordsObj->where(array('id'=>$val['id'],'enable_interest'=>0))->save(array('enable_interest'=>1,'modify_time'=>date('Y-m-d H:i:s',time()).'.'.getMillisecond().'000'))) {
                            $userWalletRecordsObj->rollback();
                        }else{
                            $userWalletRecordsObj->commit();
                        }
                    }
                }else if($val['type'] == 2){ // 转出
                    $value = abs($val['value']); // 转出金额
                    $userInfo = $userAccountObj->where(array('user_id'=>$val['user_id']))->find(); // 用户账户信息
                    if($value <= $userInfo['wallet_enable_interest']){
                        $userWalletRecordsObj->startTrans();
                        if($userAccountObj->where(array('user_id'=>$val['user_id']))->setDec('wallet_enable_interest', $value)){
                            if(!$userWalletRecordsObj->where(array('id'=>$val['id'],'enable_interest'=>0))->save(array('enable_interest'=>1))) {
                                $userWalletRecordsObj->rollback();
                            }else{
                                $userWalletRecordsObj->commit();
                            }
                        }else{
                            $userWalletRecordsObj->commit();
                        }
                    }else{//记录转出异常的数据
                        $single_user_info = $userObj->where(array('id'=>$val['user_id']))->find();//用户信息
                        $data = array(
                            'username'=>$single_user_info['username'],
                            'real_name'=>$single_user_info['real_name'],
                            'type'=>2,
                            'add_time'=>date('Y-m-d H:i:s',time()),
                            'desc'=>'转出金额大于可计息金额,数据异常,请检查'
                        );
                        $taskInterestObj->add($data);
                    }
                }
            }
        }
        return 1;
        
        */
    }
    
    function test4() {
        
        exit;
        
        $startTime = '2016-04-26';
        
        $yestoday = date('2016-04-26', strtotime('-1 days', strtotime($startTime)));
        
        $userWalletRecordsObj = M('UserWalletRecords');
        $userAccountObj = M('UserAccount');
        $taskInterestObj = M('TaskInterest');
        $userObj = M('User');
        
   
        $list = $userWalletRecordsObj->where("id = 494 and enable_interest=0")->select();
        
       // var_dump($list);
        
        echo "<br/>";
        
        
        if(!$list) return 1;
        
        $errMsg = ''; // 错误消息
        foreach ($list as $key => $val) {
            if ($val['add_time'] > $yestoday . ' 23:59:59.999000') { // 时间不满足,无法确认份额
                
                echo "2121";
                
                continue;
            } else {
                
                echo '111111111111111';
                
                if ($val['type'] == 1) { // 转入
                } else 
                    if ($val['type'] == 2) { // 转出
                        
                        $value = abs($val['value']); // 转出金额
                        
                        $userInfo = $userAccountObj->where(array('user_id' => $val['user_id']))->find(); // 用户账户信息
                            
                        //print_r($userInfo);
                        
                        if ($value <= $userInfo['wallet_enable_interest']) {} else { // 记录转出异常的数据
                        }
                    }
            }
        }
        return 1;
    }
    
    public function test5( $no_operated_user = '') {
        
        
        exit;
        $datetime = date("Y-m-d",strtotime('-1 days',time()));//昨天日期
        $default_rate = 6.35;//默认利率
        
        $userAccountObj = M('UserAccount');
        $userWalletInterestObj = M('UserWalletInterest');
        $userWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        $userWalletRecordsObj = M("UserWalletRecords");
        
        // 检查当天是否已确定利率
        $userWalletAnnualizedRate = $userWalletAnnualizedRateObj->where(array('add_time'=>$datetime))->find();
        if(!$userWalletAnnualizedRate){// default rate
            $rate = $default_rate;
        }else{//set rate
            $rate = $userWalletAnnualizedRate['rate'];
        }
        if($no_operated_user){
            $cond[] = "user_id not in(".$no_operated_user.")";
        }
        $cond[] = "wallet_enable_interest>0";
        $cond[] = "wallet_last_interest_time<'".$datetime." 00:00:00.000000'";
        //$cond[] = "wallet_last_interest_time<'".$datetime." 23:59:59.000000'";
        $conditions = implode(' and ', $cond);
        $list = $userAccountObj->where($conditions)->select();
        if(!$list){//没有任何可处理记录
        
            return 4;
        }
        $rate = round($rate/100, 4); // 利率
        $time = $datetime.' '.date('H:i:s', time()).'.'.getMillisecond().'000';
    }
    
    /**
     * 还款数据
     */
    public function test6(){
      
        //$datetime = I('get.dt', date('Y-m-d', time()), 'strip_tags');
        $datetime = "2016-04";
        $userDueDetailObj = M('UserDueDetail');
        $now = date('Y-m', time());
        $date = get_the_month($datetime);
        $datetime = date('Y-m', strtotime($datetime));

        $projectObj = M('Project');
        $userDueDetailObj = M('UserDueDetail');

        //$cacheData = F('repayment_data_'.str_replace('-', '_', $datetime));
        $cacheData = null;
        if(!$cacheData){
            $maxDay = date('d', strtotime($date[1])); // 某个最大号数
            $categories = "";
            $totlePrice = ""; // 投资总金额
            $totleInterest = "";
            $price = 0; // 投资金额
            $ghostProce = 0; // 幽灵账户购买
            $interest = 0; // 利息
            $plusDescr = array();

            for($i = 1; $i <= $maxDay; $i++){
                $plus = ''; // tooltip额外描述内容
                $price = 0;
                $interest = 0;
                $categories .= ",'" . $i. "日'";
                $idList = $projectObj->field('id,user_interest,start_time,end_time')->where("is_delete=0 and end_time>='".$datetime.'-'.$i." 00:00:00.000000' and end_time<='".$datetime.'-'.$i." 23:59:59.999000'")->select();
                if($idList){
                    $ids = '';
                    foreach($idList as $key => $val){
                        $ids .= ','.$val['id'];
                        $subPrice = $userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->sum('due_capital');
                        $price += $subPrice;
                        $plus .= count_days(date('Y-m-d', strtotime($val['end_time'])), date('Y-m-d', strtotime($val['start_time']))).'天  '.$subPrice.'元  '.$val['user_interest'].'%<br>';
                    }
                    if($ids) {
                        $ids = substr($ids, 1);
                        //$price = $userDueDetailObj->where("project_id in (".$ids.") and user_id>0")->sum('due_capital'); // 去掉幽灵账户购买的总金额
                        $interest = $userDueDetailObj->where("project_id in (".$ids.") and user_id>0")->sum('due_interest'); // 去掉幽灵账户购买的总利息
                        
                        //if(is_null($interest)) $interest = '0';
                        
                           // if($interest == null) 
                        if(!$interest)$interest = 0;
                        
                        $totlePrice .= ",".$price;
                        $totleInterest .= ",".$interest;
                    }else{
                        $totlePrice .= ",0";
                        $totleInterest .= ",0";
                    }
                }else{
                    $totlePrice .= ",0";
                    $totleInterest .= ",0";
                }
                $plusDescr[$i-1]['datetime'] = $i.'日';
                $plusDescr[$i-1]['descr'] = $plus;
            }
            if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
            if($totlePrice) $totlePrice = substr($totlePrice, 1);
            if($totleInterest) $totleInterest = substr($totleInterest, 1);
            $rows = array(
                'plusDescr' => $plusDescr,
                'categories' => $categories,
                'totlePrice' => $totlePrice,
                'totleInterest' => $totleInterest,
            );
            if($datetime < $now){
                F('repayment_data_'.str_replace('-', '_', $datetime), $rows);
            }
        }else{
            $plusDescr = $cacheData['plusDescr'];
            $categories = $cacheData['categories'];
            $totlePrice = $cacheData['totlePrice'];
            $totleInterest = $cacheData['totleInterest'];
        }
        /*
        $this->assign('plus_descr', $plusDescr); // 图表tooltip额外显示内容信息
        $this->assign('categories', $categories);
        $this->assign('totle_price', $totlePrice);
        $this->assign('totle_interest', $totleInterest);
        $this->assign('dt', $datetime);
        $this->display();*/
        
        print_r($plusDescr);
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        
        echo $categories;
        echo "<br/>";
        echo $totlePrice;
        echo "<br/>";
        echo $totleInterest;
        echo "<br/>";
    }

}