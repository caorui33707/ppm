<?php
namespace Home\Controller;
use Think\Controller;

class ProductController extends BaseController{
        
    protected $api;
    
    public function __construct(){
        parent::__construct();
        
        vendor('Api.ApiRequest');
        $this->api = new \ApiRequest();
    }

    
    public function index(){
        $this->assign("path",'product');
        $this->display('list');
    }
    
    public function test(){
        $user = session(USER_ONLINE_SESSION);
        $data['token'] = '';
        if($user) {
            $data['token'] = $user['token'];
        }
        
        $newProjectsRes = $this->api->post('ProjectList',$data);
        print_r($newProjectsRes);
    }
    
    /**
     * 产品首页
     */
    public function get_product_list(){
        
        $this->get_project_list();
        
        return;
        
        
        $data['token'] = '';
        $user = session(USER_ONLINE_SESSION);
        if($user) {
            $data['token'] = $user['token'];
        }
        
        $response = $this->api->post('ProjectList',$data);
                
        $pro = [];
        
        if($response->code == 0) {
            
            $content = $response->result;
            
           // print_r($content);
            $len = count($content);    
                  
            for ($i=0;$i<$len;$i++) {    
                         
                $pro[$i]['categoryName'] = $content[$i]->categoryName;              
                $_plen = count($content[$i]->spList);                
                
                $_list = [];  
                              
                for ($k=0;$k<$_plen;$k++) {
                    $_list[$k]['id'] = $content[$i]->spList[$k]->id;
                    $_list[$k]['title'] = $content[$i]->spList[$k]->title;                    
                    $_list[$k]['tagName'] = $content[$i]->spList[$k]->tagName;
                    $_list[$k]['able'] = $content[$i]->spList[$k]->able;
                    $_list[$k]['duration'] = $content[$i]->spList[$k]->duration;                    
                    $_list[$k]['moneyMin'] = $content[$i]->spList[$k]->moneyMin;
                    $_list[$k]['moneyMax'] = $content[$i]->spList[$k]->moneyMax;                    
                    $_list[$k]['percent'] = $content[$i]->spList[$k]->percent;
                    $_list[$k]['endTime'] = date("Y-m-d",($content[$i]->spList[$k]->endTime/1000));
                    $_list[$k]['startTime'] = date("Y-m-d",($content[$i]->spList[$k]->startTime/1000));                    
                    $_list[$k]['newPreferential'] = $content[$i]->spList[$k]->newPreferential;                    
                    $_list[$k]['acceptingBank'] = $content[$i]->spList[$k]->acceptingBank;
                    $_list[$k]['userInterest'] = $content[$i]->spList[$k]->userInterest;
                    $_list[$k]['userPlatformSubsidy'] = $content[$i]->spList[$k]->userPlatformSubsidy;
                    $_list[$k]['rate'] = 0;
                    if($_list[$k]['userPlatformSubsidy']>0) {
                        $_list[$k]['rate'] = $_list[$k]['userInterest'] - $_list[$k]['userPlatformSubsidy'];
                    }
                }
                $pro[$i]['list'] = $_list;
            }
        }
       echo json_encode($pro);
    }
    
    
    public function get_project_list(){
        $data['token'] = '';
        $user = session(USER_ONLINE_SESSION);
        if($user) {
            $data['token'] = $user['token'];
        }
        
        $newProjectsRes = $this->api->post('getNewProjects',$data);
        //print_r($newProjectsRes);
       
        $project_list = [];
        $new_pro = [];
        
        if($newProjectsRes->code == 0) {
            
            $content = $newProjectsRes->result;
            $_len = count($content);
            
            for ($i=0;$i<$_len;$i++) {
                
                $new_pro[$content[$i]->categoryId]['categoryName'] = $content[$i]->categoryName;
                $new_pro[$content[$i]->categoryId]['categoryId'] = $content[$i]->categoryId;
                
                $_plen = count($content[$i]->spList);                
                $_list = [];  
                for ($k=0;$k<$_plen;$k++) {
                    $_list[$k]['id'] = $content[$i]->spList[$k]->id;
                    $_list[$k]['title'] = $content[$i]->spList[$k]->title;                    
                    $_list[$k]['tagName'] = $content[$i]->spList[$k]->tagName;
                    $_list[$k]['able'] = $content[$i]->spList[$k]->able;
                    $_list[$k]['duration'] = $content[$i]->spList[$k]->duration;                    
                    $_list[$k]['moneyMin'] = $content[$i]->spList[$k]->moneyMin;
                    $_list[$k]['moneyMax'] = $content[$i]->spList[$k]->moneyMax;                    
                    $_list[$k]['percent'] = $content[$i]->spList[$k]->percent;
                    $_list[$k]['endTime'] = date("Y-m-d",($content[$i]->spList[$k]->endTime/1000));
                    $_list[$k]['startTime'] = date("Y-m-d",($content[$i]->spList[$k]->startTime/1000));                    
                    $_list[$k]['newPreferential'] = $content[$i]->spList[$k]->newPreferential;                    
                    $_list[$k]['acceptingBank'] = $content[$i]->spList[$k]->acceptingBank;
                    $_list[$k]['userInterest'] = $content[$i]->spList[$k]->userInterest;
                    $_list[$k]['userPlatformSubsidy'] = $content[$i]->spList[$k]->userPlatformSubsidy;
                    $_list[$k]['rate'] = 0;
                    if($_list[$k]['userPlatformSubsidy']>0) {
                        $_list[$k]['rate'] = $_list[$k]['userInterest'] - $_list[$k]['userPlatformSubsidy'];
                    }
                    $_list[$k]['isComplete'] = $content[$i]->spList[$k]->isComplete;
                    if($user) {
                        $_list[$k]['expiryDates'] = $content[$i]->expiryDates;
                    } else {
                        $_list[$k]['expiryDates'] = 1;
                    }
                }
                $new_pro[$content[$i]->categoryId]['list'] = $_list;
            }
            if($new_pro) {
                array_push($project_list, $new_pro);
            }
        }
        
        
        $response = $this->api->post('ProjectList',$data);
        $pro = [];
        if($response->code == 0) {
            $content = $response->result;
            $_len = count($content);
        
            for ($i=0;$i<$_len;$i++) {
                
                if($content[$i]->categoryId<100) {
                 
                    $pro[$content[$i]->categoryId]['categoryName'] = $content[$i]->categoryName;
                    $pro[$content[$i]->categoryId]['categoryId'] = $content[$i]->categoryId;
                    
                    $_plen = count($content[$i]->spList);
                    $_list = [];
                    for ($k=0;$k<$_plen;$k++) {
                       // if($content[$i]->spList[$k]->newPreferential !=1) {
                            $_list[$k]['id'] = $content[$i]->spList[$k]->id;
                            $_list[$k]['title'] = $content[$i]->spList[$k]->title;
                            $_list[$k]['tagName'] = $content[$i]->spList[$k]->tagName;
                            $_list[$k]['able'] = $content[$i]->spList[$k]->able;
                            $_list[$k]['duration'] = $content[$i]->spList[$k]->duration;
                            $_list[$k]['moneyMin'] = $content[$i]->spList[$k]->moneyMin;
                            $_list[$k]['moneyMax'] = $content[$i]->spList[$k]->moneyMax;
                            $_list[$k]['percent'] = $content[$i]->spList[$k]->percent;
                            $_list[$k]['endTime'] = date("Y-m-d",($content[$i]->spList[$k]->endTime/1000));
                            $_list[$k]['startTime'] = date("Y-m-d",($content[$i]->spList[$k]->startTime/1000));
                            $_list[$k]['newPreferential'] = $content[$i]->spList[$k]->newPreferential;
                            $_list[$k]['acceptingBank'] = $content[$i]->spList[$k]->acceptingBank;
                            $_list[$k]['userInterest'] = $content[$i]->spList[$k]->userInterest;
                            $_list[$k]['userPlatformSubsidy'] = $content[$i]->spList[$k]->userPlatformSubsidy;
                            $_list[$k]['rate'] = 0;
                            if($_list[$k]['userPlatformSubsidy']>0) {
                                $_list[$k]['rate'] = $_list[$k]['userInterest'] - $_list[$k]['userPlatformSubsidy'];
                            }
                            $_list[$k]['isComplete'] = $content[$i]->spList[$k]->isComplete;
                            
                            if($user) {
                                $_list[$k]['expiryDates'] = $content[$i]->expiryDates;
                            } else {
                                $_list[$k]['expiryDates'] = 0;
                            }
                        //}
                    }
                    $pro[$content[$i]->categoryId]['list'] = $_list;
                }
            }
            if($pro) {
                
                
               // print_r($pro);
                
                
                array_push($project_list, $pro);
            }
        }
        
        
        echo json_encode(array_merge_recursive($new_pro,$pro));
        
        
        
    }
   
    /**
     * 产品详细
     */
    public function detail(){        
        $id = I('id',0,'int');  
        
        $user = session(USER_ONLINE_SESSION);
        
        $amt = 0;
        
        if($user) {
            
            $postData = [
                'token'=>$user['token']
            ];
            
            $response = $this->api->post('userAccountAssets',$postData);
            
            if($response->code == 0) {
                $amt = $response->result->waitAmount;
            }
            
        }
        
        $detail = '';
        if($id>0){            
            $detail = M('Project')->field('id,title,amount,able,
                    duration,user_interest,percent,new_preferential,tag_name,
                    money_min,money_max,accepting_bank,end_time,status,
                    repayment_source_descr,invest_direction_image,user_platform_subsidy')->where('id='.$id .' and is_delete=0')->find();
            
            if($detail) {
                $detail['pirce'] = M('contractProject')->where('project_id='.$detail['id'])->getField('price');
                $detail['end_time'] = date("Y/m/d",strtotime($detail['end_time']));
                $this->assign('image', format_project_image($detail['invest_direction_image']));//相关协议
                $detail['rate'] = 0;
                if($detail['user_platform_subsidy']>0) {
                    $detail['rate'] = $detail['user_interest'] - $detail['user_platform_subsidy'];
                }
                $postData['projectId'] = $id;
                    
                $response = $this->api->post('projectInvestLog',$postData);
                $InvestLog = [];
                if($response->code == 0){
                    $len = count($response->result);
                    for ($i=0;$i<$len;$i++){
                        $InvestLog[$i]['username'] = $response->result[$i]->username;
                        $InvestLog[$i]['invSucc'] = $response->result[$i]->invSucc;
                        $InvestLog[$i]['addTime'] = date("Y-m-d H:i:s",($response->result[$i]->addTime)/1000);
                    }
                }
                
                $detail['duration'] = (strtotime($detail['end_time']) - strtotime(date("Y-m-d")))/86400;
                
                //$detail['days'] = $detail['duration']- 1;
                
                $detail['progress'] = $detail['percent'] *3.5;
                
                $this->assign('investLog',$InvestLog);
                $this->assign('totalNum',$len);
            }
        }
        $this->assign('amt',$amt);
        $this->assign('user',$user['username']);
        $this->assign('detail',$detail);
        $this->assign("path",'product');
        $this->display();
    }
    
    public function buy(){
        
        $user = session(USER_ONLINE_SESSION);
        
        if(!$user) {
            redirect(C('WEB_ROOT').'/user/login.html');
        }        
        
        
        $postData = [
            'token'=>$user['token']
        ];
        
        $response = $this->api->post('userAccountAssets',$postData);
        $waitAmount = 0;
        if($response->code == 0) {
            $waitAmount = $response->result->waitAmount;
        }
        
        $id = I('id',0,'int');
        
        $buyAmt = I('amt',0,'int');
        
        //$postData['id'] = $id;
        
        //$couponListRes = $this->api->post('CouponsForInvest',$postData);
        
        //$couponLis = [];
        
        //if($couponListRes->code == 0){ 
            
            //$array = json_decode(json_encode($couponListRes->result),true);
            
            //$couponLis = $this->arraySequence($array, 'categoryId');
            //print_r($couponLis);
        //}
        
        $detail = M('Project')->field('id,title,end_time,user_interest,able,money_min,money_max,new_preferential')->where('id='.$id)->find();
        
        $detail['end_time'] = date("Y/m/d",strtotime($detail['end_time']));
        
        $detail['days'] = (strtotime($detail['end_time']) - strtotime(date("Y-m-d")))/86400;
        
        $income =  round(($detail['user_interest'] * $detail['days'] * $buyAmt)/365/100,2);
        
        $this->assign('waitAmount',$waitAmount);
        $this->assign('detail',$detail);
        $this->assign('buyAmt',$buyAmt);
        $this->assign('income',$income);
        $this->assign('user',$user['username']);
        //$this->assign('couponLis',$couponLis);  
        //$this->assign('couponCount',count($couponListRes->result));
        $this->assign("path",'product');
        $this->display();
        
    }
    
    
    private function arraySequence($array, $field, $sort = 'SORT_DESC')
    {
        $arrSort = array();
        foreach ($array as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort[$field], constant($sort), $array);
        return $array;
    }
    
    
    public function getCouponLis(){
        //可用红包券-可用加息券-不可用红包券-不可用加息券
        
        $user = session(USER_ONLINE_SESSION);
              
        $postData = [
            'token'=>$user['token']
        ];
        
        $couponListRes = $this->api->post('CouponsForInvest',$postData);
        
        //print_r($couponListRes);

        $id = I('id');
        
        $buyAmt = I('buyAmt',0,'int');
        
        $project = M('Project')->field('end_time,new_preferential')->where('id='.$id.' and is_delete=0')->find();
        
        $days = (strtotime(date("Y-m-d",strtotime($project['end_time']))) - strtotime(date("Y-m-d")))/86400;
        
        
        $couponLisOn = [];
        $couponLisOff = [];
        
        $_redCouponOn = [];
        $_redCouponOff = [];
        $_rateCouponOn = [];
        $_rateCouponOff = [];
        
        if($couponListRes->code == 0){
        
            $array = json_decode(json_encode($couponListRes->result),true);
            //print_r($array);
            for($i=0;$i<count($array);$i++){
                
                if($array[$i]['categoryId'] == 1) {
                    if($buyAmt>=$array[$i]['minInvest']
                        && $days>=$array[$i]['minDue']
                        && $this->isTagOk($array[$i]['applyTag'],$project['new_preferential'])) {
                            
                            $_rateCouponOn[] = $array[$i];
                            
                    } else {
                        $_rateCouponOff[] = $array[$i];
                    }
                    
                    /*
                    foreach ($array[$i] as $key=>&$val){
                        if($key== 'expireTime'){
                            $array[$i][$key] = date("Y-m-d",$val/1000);
                        }
                        
                        if($key== 'valiteTime'){
                            $array[$i][$key] = date("Y-m-d",$val/1000);
                        }
                    }   */                             
                    
                    
                } else {
                    if($buyAmt>=$array[$i]['minInvest']
                            && $days>=$array[$i]['minDue']
                            && $this->isTagOk($array[$i]['applyTag'],$project['new_preferential'])) {
                                $_redCouponOn[] = $array[$i];
                        } else {
                            $_redCouponOff[] = $array[$i];
                        }
                    }
            }
            
            //排序
            if($_redCouponOn) {
                $_redCouponOn = $this->arraySequence($_redCouponOn, 'amount');
            } 
            
            if($_redCouponOff) {
                $_redCouponOff = $this->arraySequence($_redCouponOff, 'amount');
            }
            
            if($_rateCouponOn) {
                $_rateCouponOn = $this->arraySequence($_rateCouponOn, 'interestRate');
            }
            
            if($_rateCouponOff) {
                $_rateCouponOff = $this->arraySequence($_rateCouponOff, 'interestRate');
            }
            //合并
            $couponLisOn = array_merge($_redCouponOn, $_rateCouponOn);
            $couponLisOff = array_merge($_redCouponOff, $_rateCouponOff);
            
            $this->ajaxReturn(array('status'=>1,'couponLisOn'=>$couponLisOn,'couponLisOff'=>$couponLisOff));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>$response->errorMsg));
        }
        
    }
    
    
    private function isTagOk($applyTag,$newPreferential) {
        if ($applyTag == "") {
            return false;
        }
        foreach (explode(':',$applyTag) as $key => $val){
            if($val == $newPreferential) {
                return true;
            }
        }
        return false;
    }
    

    /**
     * 产品购买支付页面
     */
    public function pay(){
        $user = session(USER_ONLINE_SESSION);
        if(!$user) {
            $this->ajaxReturn(array('status'=>'0','info'=>'您还没有登录，或者登录已超时，请重新登录'));
        }
        $id = trim(I('id',0,'int'));
        $amount = trim(I('amount',0,'int'));
        $redpackets = trim(I('redpackets',0,'int'));
        $interestCoupon = trim(I('interestCoupon',0,'int'));
        $realAmount = trim(I('realAmount',0,'int'));
        
        $postData = [
            'token'=>$user['token'],
            'id' =>$id,
            'amount'=>$amount,
            //'redpackets' => $redpackets,
            //'interestCoupon'=>$interestCoupon,
            'realAmount' =>$realAmount
        ];
        
        if($interestCoupon>0) {
            $postData['interestCoupon'] = $interestCoupon;
        }
        if($redpackets>0) {
            $postData['redpackets'] = $redpackets;
        }
        
       // print_r($postData);
        
        $response = $this->api->post('invest',$postData);
        //print_r($response);
        
        if($response->code == 0) {
            $amt = $response->result->amount;
            $interest = $response->result->interest;
            $dueTime = date('Y-m-d',($response->result->dueTime)/1000);
            $this->ajaxReturn(array('status'=>'1',
                    'info'=>'/product/pay_succ.html?id='.$id.'&amt='.$amt.'&interest='.$interest.'&duetime='.$dueTime));
        } else {
            $this->ajaxReturn(array('status'=>'2','info'=>$response->errorMsg));
        }
    }
    
    public function pay_succ(){
        $user = session(USER_ONLINE_SESSION);
        if($user) {
            
        }
        $data['id'] = I('id',0,'int');
        $data['title'] = M('Project')->where('id='.$data['id'])->getField('title');
        $data['amt'] = I('amt',0,'int');
        $data['interest'] = I('interest',0,'strip_tags');
        $data['duetime'] = I('duetime','','strip_tags');
        
        $this->assign('data',$data);
        $this->display();
    }

}