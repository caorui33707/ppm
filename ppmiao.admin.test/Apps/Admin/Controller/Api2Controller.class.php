<?php
namespace Admin\Controller;


/**
 * 
 * @author hui.xu
 *
 */
class Api2Controller {
    
    private $key = "fuQian_pp";
    
    public function auto_sup(){
        
        $project_id = I('post.project_id', 0,'int');
        $sign = I('post.sign', '','strip_tags');
        
        if(!$project_id || !$sign) {
            \Think\Log::write('参数不完整','INFO');
            exit('-1');
        }
        
        if(md5($this->key.$project_id) != $sign) {
            \Think\Log::write('签名不对','INFO');
            exit('-2');
        }
        
        $upId = $this->getUpProject($project_id);
        if($upId>0){
            $ret = publish($upId);
            $this->update_project_some_data('99',$upId,$project_id,$ret);
        }
    }
    
    public function test(){
        echo 'a';
    }
    
    public function getUpProject($id){
        
        $projectObj = M('Project');
        
        $detail = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$detail) {
            \Think\Log::write('没有查到Id:·'.$id.'·的标','INFO');
            return '-1';
        }

        if($detail['able']>=30000){
            \Think\Log::write('标Id:'.$id.' 金额不符合上标要求','INFO');
            return '-2';
        }
        
        if($detail['project_group_id']<=0){            
            $data = F($id);
            if(!$data) {
                $txt = "亲，".$detail['title']."剩".$detail['able']."元可售";
                $res = $this->send_sms($txt);
                if($res == 1) {
                    F($id,'1');
                }
            }
            //短信通知            
            \Think\Log::write('标Id:'.$id.' 没有组别','INFO');
            return '-3';
        }
        
        if($detail['activate_project_id']>0){
            \Think\Log::write('标Id:'.$id.' 该标已经触发过上标了','INFO');
            return '-4';
        }
        
        $current_date = date("Y-m-d H:i:s");
        
        $conds = array(            
            //'start_time' => array('egt',$current_date),
            'project_group_id'=>$detail['project_group_id'],
            'status' => 6,
            'is_delete' =>0,
            //'activate_project_id'=>0,
            //'id'=> array('neq',$id),  
        );
        
        $upProject = $projectObj->where($conds)->order('end_time asc')->find();
                                
        
        if(!$upProject){
            
            $data = F($id);
            if(!$data) {
                $txt = "亲，".$detail['title']."剩".$detail['able']."元可售";
                $res = $this->send_sms($txt);
                if($res == 1) {
                    F($id,'1');
                }
            }
            
            \Think\Log::write('标Id:'.$id.' 该标没有同组待上线的标的','INFO');
            return '-5';
        }
                
        $contractInfo = M('Contract')->where(array('name'=>$upProject['contract_no']))->find();
        
        if(!$contractInfo) {
            \Think\Log::write('标Id:'.$id.'触发Id为：'.$upProject['id'].'没有取到合同信息','INFO');
            return '-6';
        }
        
        //到计时标
        
        $hour = date("H:i:s");  
        
        $start_time = date("Y-m-d").' '.$hour;            
        $end_time = date("Y-m-d",strtotime($upProject['end_time'])).' '.$hour;
        $days = count_days($start_time, $end_time);
        
        if($contractInfo['price'] > 1000000) {
            $contractInfo['price'] = 1000000;
        }
        
        if($upProject['new_preferential'] == 9){//月月加薪
            $rate = $upProject['contract_interest'];
        } else{
            $rate = $upProject['user_interest'];
        }
        
        $amount = substr(intval($contractInfo['price'] / ($rate/100/365 * $days + 1)),0,-2).'00';
        
        $contractProjectInfo = M('contractProject')->where(array('project_id'=>$upProject['id']))->find();
         
        //生成合同里的标信息
        if(!$contractProjectInfo){
            $rows = array(
                'contract_id' => $contractInfo['id'],
                'project_id' => $upProject['id'],
                'project_name' => $upProject['title'],
                'price' => $amount,
                'remark' => '自动上标程序处理',
                'add_time' => time(),
                'add_user_id' => 1,
                'modify_time' => time(),
                'modify_user_id' => 1,
            );
            $update_status = M('ContractProject')->add($rows);
        } else {
            $rows = array(
                'contract_id' => $contractInfo['id'],
                'project_id' => $upProject['id'],
                'project_name' => $upProject['title'],
                'price' => $amount,
                'remark' => '自动上标程序处理更新',
                'modify_time' => time(),
                'modify_user_id' => 1,
            );
            $update_status = M('ContractProject')->where('id='.$contractProjectInfo['id'])->save($rows);
        }
        
        $save_data = array(
            'start_time' => $start_time,
            'duration' => $days,
            'amount' => $amount,
            'able' => $amount,
            'end_time'=>$end_time,
        );
        
        $update_project = $projectObj->where(array('id'=>$upProject['id']))->save($save_data);
        
        if($update_status !== false && $update_project !== false) {
            $projectObj->commit();
        } else {
           \Think\Log::write('标Id:'.$id.'触发Id为：'.$upProject['id'].'更新标的金额时间或者合同产品失败','INFO');
           return '-7';
        }
        
        $repaymentDetailObj = M('RepaymentDetail');
        $count = $repaymentDetailObj->where(array('project_id' => $upProject['id']))->count(); // 项目还本付息表条目列表
        
        $datetime = $current_date.'.'.getMillisecond().'000';
        
        unset($rows);
        
        if (!$count) { // 还未生成还本付息表,生成
            if ($upProject['repayment_type'] == 1) { // 一次性还本付息
                $rows['project_id'] = $upProject['id'];
                $rows['repayment_time'] = $end_time;//$upProject['end_time'];
                $rows['period'] = 1;
                $rows['status'] = 1;
                $rows['add_time'] = $datetime;
                $rows['add_user_id'] = 1;
                $rows['modify_time'] = $datetime;
                $rows['modify_user_id'] = 1;
                if(!$repaymentDetailObj->add($rows)){
                    \Think\Log::write('标Id:'.$id.'触发Id为：'.$upProject['id'].'生成还款记录失败','INFO');
                    return '-8';
                }
            }
        } else {
            if ($upProject['repayment_type'] == 1) {
                $rows['repayment_time'] = $end_time;//$upProject['end_time'];
                $rows['period'] = 1;
                $rows['status'] = 1;
                $rows['modify_time'] = $rows['add_time'] = $datetime;
                $rows['modify_user_id'] = $rows['add_user_id'] = 1;
                $update_status = $repaymentDetailObj->where('project_id='.$upProject['id'])->save($rows);
                if($update_status === false){
                    \Think\Log::write('标Id:'.$id.'触发Id为：'.$upProject['id'].'更新还款记录失败','INFO');
                    return '-9';
                }
            }
        }
        return $upProject['id'];
    }
    
    
    //标的自动上线的一些数据
    public function update_project_some_data($uid,$upId,$id,$ret){
        if($ret) {
            $projectObj = M('Project');
            $repaymentDetailObj = M('RepaymentDetail');
            $contractProjectObj = M('ContractProject');
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
            if($ret['code'] == 0){
                $projectObj->where(array('id' => $upId))->save(array('status' =>2,'modify_user_id'=>$uid,'modify_time' =>$time));
                $projectObj->where(array('id' => $id))->save(array('activate_project_id' =>$upId));
                \Think\Log::write('标的id:'.$upId.',自动上线成功！是由标id:'.$id.'关联的','INFO');
            } else {
                $projectObj->where(array('id' => $upId))->save(array('status' =>7,'modify_user_id'=>$uid,'modify_time' => $time));
                $repaymentDetailObj->where('project_id='.$upId)->limit(1)->delete();
                $contractProjectObj->where('project_id='.$upId)->limit(1)->delete();
                \Think\Log::write('标Id:'.$upId.'自动上线失败，错误信息：'.$ret['errorMsg'],'INFO');
            }
        } else {
            \Think\Log::write('标Id:'.$upId.'自动上线失败，错误信息：提交银行银行审核未响应，请联系技术','INFO');
        }
    }
    
    
    
    public function getPlatformData(){
        
        $callback = $_GET['jsoncallback'];
        
        $rechargeLogObj = M('RechargeLog');
        $userObj = M('User');
        $n = 1.3156;
     
        $ret['totalUser'] = 0;
        $userList = $userObj->query("SELECT CASE province_no 
            WHEN 11 THEN '北京市'
            WHEN 12 THEN '天津市'
            WHEN 13 THEN '河北省'
            WHEN 14 THEN '山西省'
            WHEN 15 THEN '内蒙古自治区'
            WHEN 21 THEN '辽宁省'
            WHEN 22 THEN '吉林省'
            WHEN 23 THEN '黑龙江省'
            WHEN 31 THEN '上海市'
            WHEN 32 THEN '江苏省'
            WHEN 33 THEN '浙江省'
            WHEN 34 THEN '安徽省'
            WHEN 35 THEN '福建省'
            WHEN 36 THEN '江西省'
            WHEN 37 THEN '山东省'
            WHEN 41 THEN '河南省'
            WHEN 42 THEN '湖北省'
            WHEN 43 THEN '湖南省'
            WHEN 44 THEN '广东省'
            WHEN 45 THEN '广西壮族自治区'
            WHEN 46 THEN '海南省'
            WHEN 50 THEN '重庆市'
            WHEN 51 THEN '四川省'
            WHEN 52 THEN '贵州省'
            WHEN 53 THEN '云南省'
            WHEN 54 THEN '西藏自治区'
            WHEN 61 THEN '陕西省'
            WHEN 62 THEN '甘肃省'
            WHEN 63 THEN '青海省'
            WHEN 64 THEN '宁夏回族自治区'
            WHEN 65 THEN '新疆维吾尔自治区'
            WHEN 71 THEN '台湾省'
            WHEN 81 THEN '香港特别行政区' 
            WHEN 91 THEN '澳门特别行政区'
         ELSE '未投资'  END  AS province,num FROM (SELECT DISTINCT(LEFT(card_no,2)) AS province_no,COUNT(*) AS num FROM s_user GROUP BY LEFT(card_no,2) ) a");
        
        foreach ($userList as $key => $val) {
            $userList[$key]['num'] = intval($val['num'] * $n);
            $ret['totalUser'] += $userList[$key]['num'];
        }
        
        $startTime = date("Y-m-d");
        //$endTime = date("Y-m-d",strtotime("+1 day"));
        
        $conditions = "user_id>0 and status=2 and add_time>='$startTime'";// and add_time<='$endTime'";
        $ret['todayTotleMoney'] = $rechargeLogObj->where($conditions)->sum('amount');
        
        $sql = "SELECT SUM(amount) as amount from s_recharge_log WHERE  user_id >0 and status=2 and project_id in(SELECT id FROM s_project WHERE status in(2,3,4) and is_delete=0)";
        $result = $rechargeLogObj->query($sql);
        $totalMoney = 0; 
        if($result) {
            $totalMoney = round($result[0]['amount'] * $n,-2);
        }
        $ret['stockAmount'] = $totalMoney;
        $ret['userList'] = $userList;
        
        echo $callback.'('.json_encode($ret).')';
        exit;
        
    }
    
    /**
     * 发送短信
     */
    private function send_sms($content = 'test...'){        
        $phones = '18857883324,18857151339';
        //$phones = '18606502829,18069429008,15968849580';
        $errorMsg = array(
            101 => '无此用户',
            102 => '密码错',
            103 => '提交过快（提交速度超过流速限制）',
            104 => '系统忙（因平台侧原因，暂时无法处理提交的短信）',
            105 => '敏感短信（短信内容包含敏感词）',
            106 => '消息长度错（>536或<=0）',
            107 => '包含错误的手机号码',
            108 => '手机号码个数错（群发>50000或<=0;单发>200或<=0）',
            109 => '无发送额度（该用户可用短信数已使用完）',
            110 => '不在发送时间内',
            111 => '超出该账户当月发送额度限制',
            112 => '无此产品，用户没有订购该产品',
            113 => 'extno格式错（非数字或者长度不对）',
            115 => '自动审核驳回',
            116 => '签名不合法，未带签名（用户必须带签名的前提下）',
            117 => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',
            118 => '用户没有相应的发送权限',
            119 => '用户已过期',
        );
        
        if($phones && $content){        
            $params = 'account='.C('SMS_INTDERFACE.account');
            $params .= '&pswd='.C('SMS_INTDERFACE.pswd');
            $params .= '&mobile='.$phones;
            $params .= '&msg='.$content;
            $params .= '&needstatus=false';
            
            echo 'http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params;
            
            $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params);
            $arr = explode("\n", $smsData);
            foreach($arr as $key => $val){
                $arr[$key] = explode(',', $val);
            }
            $msgid = trim($arr[1][0]);
            if($arr[0][1] != 0) {
                return $errorMsg[$arr[0][1]];
            }
            return 1;
        }
    }
    /*
    public function ff(){
        $f = $this->send_sms('a test..');
        echo $f;
    }*/
}