<?php
/**
 * 处理任务-幽灵账号自动购买
 * 2016/12/28 陈俊杰
 */
namespace Task\Controller;
use Think\Controller;

class GhostBuyController extends Controller {



    /*
     * 主函数
     */
    public function main(){
        $time = time();
        $projects = $this->getProjects($time);
        foreach($projects as $project){
            $this->done($project['id']);
        }
    }

    /**
     * @param $id product_id
     * 执行逻辑函数
     */
    public function done($id){

        $time = time();
        $projectObj = M('Project');
        $project = $projectObj->field('id,money_min,money_max,able,last_auto_buy_time,start_auto_buy_time,is_autobuy,status,is_delete')->where('id='.$id)->find();

        $bought = $this->checkProject($project['last_auto_buy_time'],$project['start_auto_buy_time'],$project['is_autobuy'],$project['is_delete'],$project['status']);

        if ($bought === true) {
            if($project['money_max'] == 0){
                $money_key = rand(3,9);
                $money = $money_key*5000;
            }else{

                if($project['able'] < $project['money_max']){
                    $money_max = $project['able'];
                }else{
                    $money_max = $project['money_max'];
                }



                $money_rand_max = floor( $money_max/100 );
//                $money_rand_min = floor( $project['money_min']/100 );
                $money_rand_min = 80;

                $money_key = rand($money_rand_min,$money_rand_max);
                $money = $money_key*100;


                $yu = $money % $project['money_min'];
                $money = $money-$yu;
            }




            if($project['able'] < 9800){//可购买金额少于5000 就直接买完
                $money = $project['able'];
            }


            $re = $this->buy($project['id'],$money);
            echo json_encode($re);
        } else {
            echo json_encode(['error'=>$bought]);
        }

    }

    /**
     * @param $time
     * @return mixed
     *  获取需要幽灵账户自动购买的项目
     */
    public function getProjects($time)
    {
        $projectObj = M('Project');
        $conditions = ['status = 2','is_delete = 0','is_autobuy = 1','start_auto_buy_time < '.$time ];

        $projects = $projectObj->where($conditions)->Field('id')->select();
//        var_dump($projectObj->getLastSql());
        return $projects;
    }


    /**
     * @param $last_auto_buy_time
     * @param $start_auto_buy_time
     * @param $is_autobuy
     * @param $is_delete
     * @param $status
     * @return bool|string
     *
     * 判断标是否可以自动购买
     */
    public function checkProject($last_auto_buy_time,$start_auto_buy_time,$is_autobuy,$is_delete,$status)
    {

        $now_time = time();
        $wait_time = $now_time - $last_auto_buy_time;
//        echo $start_auto_buy_time . $now_time;
        if ($status != 2) {
            return 'The status is incorrect';
        }elseif ($is_delete != 0) {
            return 'been deleted';
        }elseif ($is_autobuy == 0) {
            return 'Auto buy closed';
        } elseif ($start_auto_buy_time > $now_time) {
            return 'Time has not arrived';
        }else{
            return true;
        }


    }


    /**
     * 自动生成一个幽灵账户的手机号码
     */
    function auto_create_phone_for_ghost(){
        $qz = array(134,135,136,137,138,139,150,151,152,157,158,159,182,187,188,147,130,131,132,155,156,186,145,133,153,189); // 运营商手机号码前缀
        $middle = rand(0, 9999); // 中段号码(4位)
        $last = rand(0, 9999); // 尾号(4位)
        for($i = 3-strlen($middle); $i >= 0 ; $i--){
            $middle = '0'.$middle;
        }
        for($i = 3-strlen($last); $i >= 0; $i--){
            $last = '0'.$last;
        }
        return $qz[rand(0, count($qz)-1)].$middle.$last;
    }

    /**
     * 计算两个日期之间的天数差
     * @param $a
     * @param $b
     * @return float
     */
    function count_days($a, $b){
        return floor(abs(strtotime($a)-strtotime($b))/86400);
    }


    /**
     * 执行购买操作
     */
    public function buy($project_id,$money,$user_id=0){




//        $user_id = 0;
//        echo $project_id;
        // 给幽灵账户生成随机手机号码
        $randPhone = $this->auto_create_phone_for_ghost();


        if(!$project_id) return array('status'=>0,'info'=>'product do not exist');

        if(!is_numeric($money)) return array('status'=>0,'info'=>'Must be a number');

        $projectObj = M('Project');

        $detail = $projectObj->where(array('id'=>$project_id,'is_delete'=>0))->find();
        if(!$detail) return array('status'=>0,'info'=>'product do not exist');

        $rechargeLogObj = M('RechargeLog');
        $investmentDetailObj = M('InvestmentDetail');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');

        $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
        $uid = 1;
        $recharge_no = 'YL'.date('YmdHis', time()).getMillisecond().rand(100,999);
        
        
        $isGhostUser = false;
        
        if( $userinfo = $this->getAutoBuyUser($detail['auto_buy_users'])){
            $user_id = -$userinfo[2];
            $randPhone = $userinfo[0];
            
            $isGhostUser = true;
        }

        if($money > $detail['able']) return array('status'=>0,'info'=>'able is not enough');

        $rechargeLogObj->startTrans();
        $rowsRechargeLog = array(
            'user_id' => $user_id,
            'project_id' => $project_id,
            'recharge_no' => $recharge_no,
            'trade_no' => '',
            'type' => 0,
            'amount' => $money,
            'status' => 2,
            'input_params' => '',
            'notify_params' => '',
            'card_no' => '',
            'ghost_phone' => $randPhone,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        if(!$rechargeLogObj->add($rowsRechargeLog)) return array('status'=>0,'info'=>'购买失败');
        if(!$projectObj->where(array('id'=>$project_id))->setDec('able', $money)){
            $rechargeLogObj->rollback();
            return array('status'=>0,'info'=>'购买失败');
        }

        $percent = number_format((1-(($detail['able'] - $money)/$detail['amount']))*100, 2);
        $projectObj->where(array('id'=>$project_id))->save(array('percent'=>$percent));
        
        $rowsInvestmentDetail = array(
            'user_id' => $user_id,
            'project_id' => $project_id,
            'inv_total' => $money,
            'inv_succ' => $money,
            'device_type' => 5,
            'auto_inv' => 1,
            'recharge_no' => $recharge_no,
            'status' => 2,
            'bow_type' => 173,
            'card_no' => '',
            'ghost_phone' => $randPhone,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        
        $invest_detail_id = $investmentDetailObj->add($rowsInvestmentDetail);
        if(!$invest_detail_id){
            $rechargeLogObj->rollback();
            return array('status'=>0,'info'=>'购买失败');
        }

        $repay_id = $repaymentDetailObj->where(array('project_id'=>$project_id))->getField('id');
        if(!$repay_id){
            $rechargeLogObj->rollback();
            return array('status'=>0,'info'=>'购买失败');
        }

        $rowsUserDueDetail = array(
            'user_id' => $user_id,
            'project_id' => $project_id,
            'repay_id' => $repay_id,
            'invest_detail_id' => $invest_detail_id,
            'due_amount' => $money,
            'due_capital' => $money,
            'due_interest' => 0,
            'period' => 1,
            'duration_day' => $this->count_days($detail['end_time'], date('Y-m-d H:i:s', strtotime('+1 day'))),
            'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')).'.'.getMillisecond().'000',
            'due_time' => $detail['end_time'],
            'status' => 1,
            'bow_type' => 173,
            'card_no' => '',
            'ghost_phone' => $randPhone,
            'repayment_no' => '',
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        if(!$userDueDetailObj->add($rowsUserDueDetail)){
            $rechargeLogObj->rollback();
            return array('status'=>0,'info'=>'购买失败');
        }

        if($money == $detail['able']){ // 已全部买完,更改标的状态
            $update_status = $projectObj->where(array('id'=>$project_id))->save(array('status'=>3,'soldout_time'=>$time));
            if($update_status===false){
                $rechargeLogObj->rollback();
                return array('status'=>0,'info'=>'购买失败');
            }
            
            $repay_amt = M('userDueDetail')->where('project_id='.$project_id.' and user_id>0')->sum('due_amount');
            $req['flag'] = 2;
            if(!$repay_amt) {
                $repay_amt = 0;
                $req['flag'] = 3;
            }
            
            $req['prod_id'] = $project_id;
            $req['funddata'] = "{\"payout_plat_type\":\"01\",\"payout_amt\":\"0\"}";
            
            $list[] = array('repay_amt'=>$repay_amt,
                'repay_fee'=>'0',
                'repay_num'=>'1',
                'repay_date'=>date('Y-m-d',strtotime($detail['end_time']))
            );
            
            $req['repay_plan_list'] = json_encode($list);
            
            vendor('Fund.FD');
            vendor('Fund.sign');
            
            $plainText =  \SignUtil::params2PlainText($req);
            
            $sign =  \SignUtil::sign($plainText);
            
            $req['signdata'] = \SignUtil::sign($plainText);
            $fd  = new \FD();
            
            $res_str = $fd->post('/project/establishorabandon',$req);
            
            $uid = 999;
            $data['project_id'] = $project_id;
            $data['flag'] = $req['flag'];
            $data['amt'] = $repay_amt;
            $data['repay_date'] = $detail['end_time'];
            $data['memo'] = $res_str;
            $data['user_id'] = $uid;
            $data['create_time'] = date("Y-m-d H:i:s");
            if(M('projectEstablishLog')->add($data)) {
                if($req['flag'] == 3){
                    $projectObj->where(array('id'=>$project_id))->save(array('status'=>8));
                }
            }
        }
        $update_status = $projectObj->where(array('id'=>$project_id))->save(array('last_auto_buy_time'=>time()));

        $rechargeLogObj->commit();
        
        
        
        //写入抽奖假数据
        //指定的假数用户，生成假的中奖记录
        if($isGhostUser == true) {
            if(time()<=1491577200 && $detail['new_preferential'] == 6 ) {
                //加息券、现金券，100京东
                $awardArr = array(
                    array('2','随机加息券'),
                    array('3','随机现金券'),
                    array('4','100元京东卡')
                );
                $award = $awardArr[rand(0, 2)];
                $dd = array(
                    'uid'=>$user_id,
                    'mobile'=>$randPhone,
                    'pos'=>0,
                    'award_desc'=>$award[1],
                    'award_type'=>$award[0],
                    'add_time'=>date('Y-m-d H:i:s'),
                    'modify_time'=>date('Y-m-d H:i:s'),
                );
                M('userLotteryRichman')->add($dd);
            }
        }

        $money = $projectObj->where(array('id'=>$project_id,'is_delete'=>0))->getField('able');
        
        if($money < 30000) {
            try {
                $sign = md5('fuQian_pp'.$project_id);             
                $data = array(
                    'project_id'=>$project_id,
                    'sign'=>$sign
                );
                $ret = $this->post($data);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        return array('status'=>1);
    }

    public function getAutoBuyUser($users){
        $arr = explode(',',$users);
        if(!$users){
            return false;
        }else{
            $user_id = $arr[array_rand($arr,1)];
            /*
            $arr =  $this->getAutoBuyUserInfo($user_id);

            if(!isset($arr[1])){
                return false;
            }else{
                $arr[] = -$user_id;
                return $arr;
            }
            */
            
            $arr = GhostUser($user_id);
            if(!isset($arr[1])){
                return false;
            }else{
                //$arr[] = -$user_id;
                return $arr;
            }
            
            
        }
    }

    private function getAutoBuyUserInfo($k){
        $arr = array(
            '-101'=>array('13600009252','赵丽坤'),
            '-102'=>array('13300006969','芮宇浩'),
            '-103'=>array('13300001518','余栋韬'),
            '-104'=>array('17800001844','杨岚芝'),
            '-105'=>array('15200005715','赵量'),
            '-106'=>array('13000006566','沈立军'),
            '-107'=>array('13400003922','李娜'),
            '-108'=>array('18600008078','陈大鹏'),
            '-109'=>array('18800000708','林轩'),
            '-110'=>array('15800009477','王艳 '),
            '-111'=>array('18700000140','钱东'),
            '-112'=>array('13500006064','李天涯'),
            '-113'=>array('18200004205','王勇'),
            '-114'=>array('15100009049','朱健康'),
            '-115'=>array('15300002022','郑浩'),
            '-116'=>array('17800001844','廖建东'),
            '-117'=>array('15900003419','陆剑波'),
            '-118'=>array('15200004790','方辉'),
            '-119'=>array('18800003520','方勇'),
            '-120'=>array('18200009291','吴建杰'),            
            '-121'=>array('13807228569','饶小梦'),
            '-122'=>array('15874859755','徐晓慧'),
            '-123'=>array('18682000030','沈文'),
            '-124'=>array('13654663321','章立彪'),
            '-125'=>array('13822556877','石磊'),
            '-126'=>array('13847772158','史家聪'),
            '-127'=>array('15162248801','刘宜德 '),
            '-128'=>array('15177417650','赵嘉琪'),
            '-129'=>array('18200006005','俞晓佳'),
            '-130'=>array('13300001171','韦俐芬'),
        );
        if ($k>0) $k = -$k;
        return $arr[$k];
    }
    
    public function test(){
       
    }
    
   
    
    private function post($data,$path=''){
        $url = 'http://cg.pro.ppmiao.cn/admin.php/Api2/auto_sup';
        $data = http_build_query($data);
        $opts = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
                "Content-length:".strlen($data)."\r\n" .
                "Cookie: foo=bar\r\n" .
                "\r\n",
                'content' => $data,
            )
        );
        $cxContext = stream_context_create($opts);
        $ret = file_get_contents($url, false, $cxContext);
        return $ret;
    }
}