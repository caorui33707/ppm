<?php

/**
 * 邀请活动 
 */
namespace Home\Controller;

use Think\Controller;

class ActivityController extends BaseController
{   
    
    private $key = "fuQian_pp";
    private $jump_Apple = 'https://www.ppmiao.com/qrcode.html';
    
    //*正试      
    
    ##start  
    private $inviteUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/inviteByH5.htm';//"http://server.ppmiao.com/stone-rest/payment/activity/inviteFriend/inviteByH5.htm";
    private $msgUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/sendSms.htm';
    private $jump_Android = 'https://image.ppmiao.com/download/app-tuijianhaoyou-release-86-2.0.0.apk';
    private $tgllbUrl = "https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/liuLiangBao.htm";
    private $dfw_api_url = 'https://notify.ppmiao.com/';
    
    //新手标活动
    private $newProjectsUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getNewProjects.htm';
    
    //五一活动
    private $laborDayActivityUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getLaborDayActivity.htm';
    private $exchangeLaborDayAwardUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/exchangeLaborDayAward.htm';
    private $dailySigninUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/dailySignin.htm';
    private $receiveLaborDayAwardUrl  = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/receiveLaborDayAward.htm';
    private $getAwardLogUrl  = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getAwardLog.htm';
 
    ##end
    
    
    //获取微信分享的sdk
    private $getWeixinShareSdkUrl = 'https://wechat.ppmiao.com/ap/jssdk';
    
    /*
    #test  start
    private $dfw_api_url = 'http://server.ppmiao.com/';
    private $inviteUrl = "http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/inviteByH5.htm";
    private $msgUrl = 'http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/sendSms.htm';
    private $jump_Android = 'https://www.ppmiao.com/download/app-tuijianhaoyou-release-71-1.1.7.apk';
    private $tgllbUrl = "http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/liuLiangBao.htm";
    //新手标活动
    //private $newProjectsUrl = 'http://server.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getNewProjects.htm';

    private $newProjectsUrl = 'http://114.55.85.42:10503/ppmiao-rest/payment/activity/inviteFriend/getNewProjects.htm';
    //五一活动
    private $laborDayActivityUrl = 'http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/getLaborDayActivity.htm';
    private $exchangeLaborDayAwardUrl = 'http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/exchangeLaborDayAward.htm';
    private $dailySigninUrl = 'http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/dailySignin.htm';
    private $receiveLaborDayAwardUrl  = 'http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/receiveLaborDayAward.htm';
    private $getAwardLogUrl  = 'http://114.55.85.42:10504/ppmiao-rest/payment/activity/inviteFriend/getAwardLog.htm';
    #test end
    */
   
    public function invite_new(){
        $mobile = I('get.mobile','','strip_tags');
        $mobile = base64_decode($mobile); 
        $this->assign('mobile',$mobile);
        $this->assign('mobile2',substr_replace($mobile,'*****',3,5)); 
        $this->display('new_invite'); 
    }
    
    public function invite()
    {
        $mobile = I('get.mobile','','strip_tags');
        $mobile = base64_decode($mobile);
        $this->assign('mobile',$mobile);
        $this->assign('mobile2',substr_replace($mobile,'*****',3,5));
        $this->display('new_login');
    }

    public function login()
    {
        $code = I('post.code',0,'int');
        $invitedMobile = I('post.invitedMobile','','strip_tags');
        $mobile = I('post.mobile','','strip_tags');
        
        if(!$this->checkMobile($invitedMobile)){
            //$this->ajaxReturn(array('status'=>0,'info'=>'输入的手机号码有误！'));
        }
        /*
        if($code != cookie('code'.$invitedMobile)) {
            $this->ajaxReturn(array('status'=>0,'info'=>'手机验证码不对'));
        }*/
        
        $sign = md5($mobile.':'.$invitedMobile.$this->key);
        
        $dd = array(
            'mobile' => $mobile,
            'sign' => $sign,
            'validateCode'=>$code,
            'invitedMobile' => $invitedMobile
        );
        
        $ret = trim($this->send_post($this->inviteUrl, $dd));
        
        if($ret == 'N6') {
            $this->ajaxReturn(array('status'=>0,'info'=>'手机验证码输入错误!'));
        }else if($ret == 'N4') {//被邀请人已注册
            $this->ajaxReturn(array('status'=>1,'info'=>'?c=Activity&a=result'));
        } else if($ret == 'Y0') { //成功
            $this->ajaxReturn(array('status'=>1,'info'=>'?c=Activity&a=inviteSuccess'));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'注册失败，请联系客服人员！错误码：'.$ret));
        }
    }
    
    public function sendCode() {
        
        $invitedMobile = I('post.invitedMobile','','strip_tags');
        
        if(!$this->checkMobile($invitedMobile)){
           //$this->ajaxReturn(array('status'=>0,'info'=>'输入的手机号码不正确！'));
        }
       
        $sign = md5($invitedMobile.$this->key);
        
        $data = array(
            'mobile' => $invitedMobile,
            'sign' => $sign,
        );
        
        $ret = trim($this->send_post($this->msgUrl, $data));
                
        cookie('code'.$invitedMobile,$ret,120);
       
        $this->ajaxReturn(array('status'=>1,'info'=>'ok'));
    }

    public function inviteSuccess()
    {
        if (get_device_type()=='ios') {
            $url = $this->jump_Apple;
        } else{
            $url = $this->jump_Android;
        }
        $this->assign('url',$url);
        $this->display('new_success');
    }

    public function result_new()
    {
        if (get_device_type()=='ios') {
            $url = $this->jump_Apple;
        } else{
            $url = $this->jump_Android;
        }
        $this->assign('url',$url);
        $this->display('new_channel');
    }

    public function result()
    {
        if (get_device_type()=='ios') {
            $url = $this->jump_Apple;
        } else{
            $url = $this->jump_Android;
        }
        $this->assign('url',$url);
        $this->display('new_result');
    }
    
    private function randStr($len = 6, $format = 'ALL')
    {
        switch ($format) {
            case 'ALL':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
            case 'CHAR':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~';
                break;
            case 'NUMBER':
                $chars = '0123456789';
                break;
            default:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
        }
        mt_srand((double) microtime() * 1000000 * getmypid());
        $str = "";
        while (strlen($str) < $len)
            $str .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $str;
    }
    
    private function checkMobile($phonenumber) {
        if(preg_match("/1[3458]{1}\d{9}$/",$phonenumber)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 邀请列表
     */
    public function invite_list(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        $s = trim(I('get.type','0','strip_tags'));
        
        //送手机话费
        
        //##################################################################//
        /*
        $isLogin = true;
        $count = 0;
        $amount = 0;
        //$mobile = trim(I('get.mobile','','strip_tags'));
        
        if(!$mobile){
            $isLogin = false;
        } else {
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');
        
            if(!$uid) {
                $isLogin = false;
            } else {
                $sql = "select s.user_id,s.id from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential = 6
                and s.start_time >= '2016-10-24 18:00:00'
                and s.add_time <= '2016-10-30 00:00:00'
                and p.is_delete = 0 and p.duration >=90 and s.due_capital>=20000
                and s.user_id = $uid";
        
                $ret = M()->query($sql);
                $count = count($ret);
        
                $evt20161024_count = M('evt20161024')->where(array('uid'=>$uid))->count();
        
                $count = $count - $evt20161024_count;
        
                $amount = $evt20161024_count * 100;
            }
        }
        $this->assign('isLogin',$isLogin);
        $this->assign('count',$count);
        $this->assign('amount',$amount);
        $this->assign('mobile',$mobile);
        $this->display('event20161024');
        
        exit;
        //##################################################################//
        */
        
        if($s == 0) {
            /*
            $award_amount = 0;
            $total_invest_amount = 0;
            $invite_num = 0;
            $list = '';
            if($mobile == 'null' || $mobile == '(null)') $mobile = '';
            if($mobile) {
                $user_id = M('User')->where(array('username'=>$mobile))->getField('id');
                
                $list = M('userInviteList')->where(array('user_id'=>$user_id))->select();
                foreach ($list as $key => $val){
                    $list[$key]['invited_phone'] = substr_replace($val['invited_phone'],'*****',3,5);
                }
                
                $award_amount = M('userInviteList')->where(array('user_id'=>$user_id))->sum('amount');
                $total_invest_amount = M('userInviteList')->where(array('user_id'=>$user_id))->sum('first_invest_amount');
                $invite_num =  M('userInviteList')->where('user_id='.$user_id ." and amount > 0 ")->count();
                
                if($total_invest_amount >= 10000) {
                    $award_amount +=15;
                }
                if($total_invest_amount >= 50000) {
                    $award_amount +=50;
                }
                if($total_invest_amount >= 100000) {
                    $award_amount +=100;
                }
                if($total_invest_amount >= 200000) {
                    $award_amount +=200;
                }
                if($total_invest_amount >= 500000) {
                    $award_amount +=500;
                }
                if($total_invest_amount >= 1000000) {
                    $award_amount +=1000;
                }
                
            } 
            $this->assign('list',$list);
            //佣金
            $this->assign('award_amount',$award_amount);
            //累计投资金额
            $this->assign('total_invest_amount',$total_invest_amount);
            //邀请人数
            $this->assign('invite_num',$invite_num);
            $this->assign('mobile',base64_encode($mobile));
            $this->assign('device_type',get_device_type());
            $this->display('event20161123');
            
            */
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');
            
            $list = M('UserInviteList')->where(array('user_id'=>$uid))->select();
            $ret = array();
            $total_amount = 0;
            foreach ($list as $val){
                $val['invited_phone'] = substr_replace($val['invited_phone'],'*****',3,5);
                $total_amount +=$val['amount'];
                $ret[] = $val;
            }
            $this->assign('list',$ret);
            $this->assign('total_amount',$total_amount);
            $this->assign('mobile',base64_encode($mobile));
            $this->assign('device_type',get_device_type());
            $this->assign('list',$ret);
            $this->display('invite_share');
        } 
        
        //a轮活动3 累计活动 ， 活动：6  二重奏
        else if($s == '1') {
            
            if($mobile) {
                
                $uid = M('User')->where(array('username'=>$mobile))->getField('id');
                
                $total_amount = '0元';
                
                if($uid) {
                    $sql_1 = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                              where p.id = s.project_id and p.new_preferential = 6
                              and p.start_time >= '2016-09-22 20:00:00'
                              and s.add_time <= '2016-10-07 23:59:59'
                              and p.is_delete = 0 and s.user_id = $uid";
                    $ret_1 = M()->query($sql_1);
                    if($ret_1) {
                        
                        if($ret_1[0]['amount']>0){
                            $total_amount = $ret_1[0]['amount'].'元';
                        } else {
                            $total_amount = '您还未投资';
                        }
                    }
                }
                
            } else {
                
                $total_amount = '登录后显示金额';
                
            }
            $this->assign('amount',$total_amount);
            $this->display('event2016092103');
        }
        //a 轮 四重奏
        else if($s == '2') {
            
            if($mobile) {
                $uid = M('User')->where(array('username'=>$mobile))->getField('id');
                $total_amount = '0 元';
                $rank = '';
                if($uid) {
                    //除新人票
                    $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                            where p.id = s.project_id and p.new_preferential != 1
                            and p.start_time >= '2016-09-22 20:00:00'
                            and s.add_time <= '2016-10-22 23:59:59'
                            and p.is_delete = 0
                            and s.user_id = $uid";
                    
                    $ret = M()->query($sql);
                   
                    if($ret) {
                        if($ret[0]['amount']>0){
                            $total_amount = $ret[0]['amount'].'元';
                        } else{
                            $total_amount = '您还未投资';
                        }
                    } else {
                        $total_amount = '您还未投资';
                    }
                }
            } else {
                
                $total_amount = '登录后显示金额';
                
                $rank = '登录后显示排名';
            }
            
            
            $cacheData = null;//S('rankList');
            
            //if($cacheData) {
                
                $sql = "select sum(s.due_capital) as amount ,s.user_id,u.username,u.real_name from s_project p ,s_user_due_detail s,s_user u
                            where p.id = s.project_id and p.new_preferential != 1 and s.user_id>0 and s.user_id = u.id
                            and p.start_time >= '2016-09-22 20:00:00'
                            and s.add_time <= '2016-10-22 23:59:59'
                            and p.is_delete = 0 GROUP BY user_id ORDER BY sum(s.due_capital) DESC";
                //echo $sql;
                $list = M()->query($sql);
                
                $ret2 = array();
                $n = 1;
                foreach ($list as $key => $val) {
                     
                    $dd['rank'] = $n++;
                    $dd['real_name'] = $this->substr_cut($val['real_name']);
                    $dd['username'] = substr_replace($val['username'],'****',3,4);
                    $dd['amount'] = $val['amount'];
                
                    $ret2[$val['user_id']] = $dd;
                }
                
                //S('rankList',$ret,60);
                
                //$cacheData = $ret;
            //}
            
            if($uid) {
                $rank = $ret2[$uid]['rank'];
                
                if($rank) {
                    if($rank >100) {
                        $rank = '您的排名在百名之外';
                    } else {
                        $rank .= '名';
                    }
                } else {
                    $rank = '您还未投资'; 
                }
            }
            
            $this->assign('amount',$total_amount);
            $this->assign('rank',$rank);
            
            $this->assign('list',$ret2);
            $this->display('event2016092105');
        }
    }
    
    
    public function hot(){
        $this->display('hot_event');
    }
    
    /**
     * 七夕活动 
     */
    public function sevenxi(){
        $this->display('sevenxi');
    }
    /*
     * 开学季8.25
     */
    public function event20160825() {
        $this->display('event20160825');
    }
    /*
     * 浓情升级，双倍利息
     * 
     */
    public function event20160830() {
        $this->display('event20160830');
    }
    /*
     * 中秋0913
     */
    public function event20160913() {
        $this->display('event20160913');
    }
    
    
    /**
     * a轮活动1
     * a 轮 1重奏
     * 
     */
    public function event2016092101() {
        $this->display('event2016092101');
    }
    
    /**
     * a轮活动2
     * 展示
     */
    public function event2016092102() {
        $this->display('event2016092102');
    }
    
    /**
     * a轮活动3 累计活动 ， 活动：6
     */
    public function event2016092103() {
        
        //开始时间，
        //结束时间，
        /*
        $uid = M('User')->where(array('username'=>$mobile))->getField('id');
        
        $project = M('Project')->where($where)->select();
        
        
        $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s 
            where p.id = s.project_id and p.new_preferential = 6 
            and p.start_time >= '2016-09-22 20:00:00'
            and s.add_time <= '2016-10-07 23:59:59' 
            and p.is_delete = 0 
            and s.user_id = $uid";
        
        $ret = M()->query($sql);
        
        $total_amount = 0;
        
        if($ret) {
            $total_amount = $ret[0]['amount'];
        }
        
        $this->display('event2016092103');
        */
    }
    
    /**
     * a轮活动 三重奏
     */
    public function event2016092104() {
        $r = 0;
        if(time()>=strtotime('2016-10-23')) {
            $r = 1;
        }
        $this->assign('ret',$r);
        $this->display('event2016092104');
    }
    
    /**
     * 送话费手机
     */
    public function event20161024(){
        $isLogin = true;
        $count = 0;
        $amount = 0;
        $mobile = trim(I('get.mobile','','strip_tags'));
        
        if(!$mobile){
            $isLogin = false;
        } else {
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');
            
            if(!$uid) {
                $isLogin = false;
            } else {
                $sql = "select s.user_id,s.id from s_project p ,s_user_due_detail s
                        where p.id = s.project_id and p.new_preferential = 6
                        and s.start_time >= '2016-10-24 18:00:00'
                        and s.add_time <= '2016-10-30 00:00:00'
                        and p.is_delete = 0 and p.duration >=90 and s.due_capital>=20000
                        and s.user_id = $uid";
                
                $ret = M()->query($sql);
                $count = count($ret);
                
                $evt20161024_count = M('evt20161024')->where(array('uid'=>$uid))->count();
                
                $count = $count - $evt20161024_count;
                
                $amount = $evt20161024_count * 100;
            }
        }
        $this->assign('isLogin',$isLogin);
        $this->assign('count',$count);
        $this->assign('amount',$amount);
        $this->assign('mobile',$mobile);
        $this->display('event20161024');
    }
    /*
     * 领取手机话费
     * 20161024
     */
    public function event20161024_get_cost() {
        
        $username = trim(I('post.mobile','','strip_tags'));
        
        $ret['status'] = 0;
        $add_amount = 0;
        if(!$username){
            $ret['status'] = -1;//参数为空
            echo json_encode($ret);
            exit;
        }
        
        $uid = M('User')->where(array('username'=>$username))->getField('id');
        
        if(!$uid) {
            $ret['status'] = -2;//无效注册
            echo json_encode($ret);
            exit;
        }
        
        if(time() > 1477843199) {
            $ret['status'] = -4;
            echo json_encode($ret);
            exit;
        }
        
        $sql = "select s.user_id,s.id as did from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential = 6
                and s.start_time >= '2016-10-24 18:00:00'
                and s.add_time <= '2016-10-30 23:59:59'
                and p.is_delete = 0 and p.duration >=90 and s.due_capital>=20000
                and s.user_id = $uid";

        $list = M()->query($sql);
        
        if($list) {
            
            $dd['uid'] = $uid;
            $dd['username'] = $username;
            
            $flag = false;
            
            foreach ($list as $val) {
                $did = $val['did'];
                $count = M('evt20161024')->where(array('uid'=>$uid,'did'=>$did))->count();
                if($count<=0){
                    $flag = true;
                    
                    $dd['did'] = $did;
                    $dd['create_time'] = time();
                    
                    if(M('evt20161024')->add($dd)){
                        $ret['add_amount'] += 100;
                    }
                }
            }
            if($flag){
                $ret['status'] = 1;//领取成功
            }else {
                $ret['status'] = 0;//无次数领取
            }
            echo json_encode($ret);
            exit;
        } else {
            $ret['status'] = -3;
            echo json_encode($ret);
            exit;
        }
        
    }
    
    /**
     * 
     */
    public function event20161031(){
        $this->display('event20161031');
    }
    
    /**
     * 备战双11，狂欢第二波
     */
    public function event20161102(){
        $this->display('event20161102');
    }
    
    /**
     * 立冬活动
     */
    public function event20161107(){
        $this->display('event20161107');
    }
    
    /**
     * 双十一，三波
     */
    public function event20161110(){
        $this->display('event20161110');
    }
    
    
    /**
     * 流量宝推广
     * 2016-11-14
     */
    public function tgllb(){
        //danlan、llb、diantai、jrtt2、fcjn,c ,dailuopan ,mmsc
        $ch = trim(I('get.ch','llb','strip_tags'));
        $this->assign('ch',$ch);
        
        //新的渠道8888
        $this->display('Activity/channel888/reg');
        return;
        
        
        if(strpos($ch, 'jrtt') !== false){
            //$ch = 'jrtt';
            $this->display('Activity/jrtt/login');
        }else if($ch == 'qixi'){
            $this->display('Activity/qixi/login');
        } else {
            $this->display('tgllb_login');
        }
    }

    /**
     * 喜剧总动员
     * 2017-11-15
     */
    public function xjzdy_login(){
        //xjzdy   
        $ch = 'xjzdy';//trim(I('get.ch','llb','strip_tags'));
        $this->assign('ch',$ch);
        
        
        if($ch == 'xjzdy'){
            $this->display('Activity/xjzdy/index');
        } else {
            $this->display('tgllb_login');
        }
    }
    
    /**
    * 电台推广告，送京东卡活动
    * @date: 2017-3-7 下午3:42:06
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function newtg(){
        //danlan、llb、diantai、jrtt2、fcjn
        $this->assign('ch',trim(I('get.ch','llb','strip_tags')));
        $this->display('newtg_login');
    }
    
    /**
     * 推广注册
     * @date: 2017-2-4 下午2:53:31
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function newtg_login() {
        $code = I('post.code','','strip_tags');
        $mobile = I('post.mobile','','strip_tags');
        //渠道
        $ch = trim(I('post.ch','','strip_tags'));
    
        if(!$code){
            $this->ajaxReturn(array('status'=>0,'info'=>'请输入手机验证码！'));
        }
    
        $sign = md5($mobile.':'.$this->key);
    
        $dd = array(
            'ch' => $ch,
            'mobile' => $mobile,
            'sign' => $sign,
            'validateCode'=>$code
        );
    
        $ret = trim($this->send_post($this->tgllbUrl, $dd));    
    
        if($ret == 'N2') {
            $this->ajaxReturn(array('status'=>0,'info'=>'手机验证码输入错误！'));
        } else if($ret == 'N3') {//被邀请人已注册
            $this->ajaxReturn(array('status'=>1,'info'=>'?c=Activity&a=newtg_login_succ&ch='.$ch));
        } else if($ret == 'Y0') { //成功
            $this->ajaxReturn(array('status'=>1,'info'=>'?c=Activity&a=newtg_reg_succ&ch='.$ch));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'注册失败，请联系客服人员！错误码：'.$ret));
        }
    }
    
    
    /**
     * 流量宝推广 登录成功
     * 2016-11-14
     */
    public function newtg_loign_succ() {
        $ch = trim(I('get.ch','llb','strip_tags'));
        $is_weixin = 0;
        if(is_weixin()){
            $is_weixin = 1;
        }
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('is_weixin',$is_weixin);
        $this->assign('device_type',get_device_type());
        $this->display('newtg_loign_succ');
    }
    
    /**
     * 流量宝推广注册成功
     * 2016-11-14
     */
    public function newtg_reg_succ() {
        $ch = I('get.ch','llb','strip_tags');
        $is_weixin = 0;
        if(is_weixin()){
            $is_weixin = 1;
        }
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('is_weixin',$is_weixin);
        $this->assign('device_type',get_device_type());
        $this->display('newtg_reg_succ');
    }
    
    
    
    
    /**
     * 感恩活动
     */
    public function event20161122(){
        $this->display('event20161122');
    }
    
    /**
     * 
     */
    public function event20161123(){
        
        $mobile = trim(I('get.mobile','','strip_tags'));
        
        $award_amount = 0;
        $total_invest_amount = 0;
        $invite_num = 0;
        $list = '';
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';
        if($mobile) {
            $user_id = M('User')->where(array('username'=>$mobile))->getField('id');

            $list = M('userInviteList')->where("user_id = $user_id and add_time >'2016-11-24'")->select();
            foreach ($list as $key => $val){
                $list[$key]['invited_phone'] = substr_replace($val['invited_phone'],'*****',3,5);
            }
        
            $award_amount = M('userInviteList')->where("user_id = $user_id and add_time >'2016-11-24'")->sum('amount');
            $total_invest_amount = M('userInviteList')->where(array('user_id'=>$user_id))->sum('first_invest_amount');
            $invite_num =  M('userInviteList')->where('user_id='.$user_id ." and amount > 0 and add_time>'2016-11-24'")->count();
        
            if($total_invest_amount >= 10000) {
                $award_amount +=15;
            }
            if($total_invest_amount >= 50000) {
                $award_amount +=50;
            }
            if($total_invest_amount >= 100000) {
                $award_amount +=100;
            }
            if($total_invest_amount >= 200000) {
                $award_amount +=200;
            }
            if($total_invest_amount >= 500000) {
                $award_amount +=500;
            }
            if($total_invest_amount >= 1000000) {
                $award_amount +=1000;
            }
        
        }
        $this->assign('list',$list);
        //佣金
        $this->assign('award_amount',$award_amount);
        //累计投资金额
        $this->assign('total_invest_amount',$total_invest_amount);
        //邀请人数
        $this->assign('invite_num',$invite_num);
        $this->assign('mobile',base64_encode($mobile));
        $this->assign('device_type',get_device_type());
        $this->display('event20161123');
    }
    
    
    public function getInviteData(){        
        $res = [];        
        $mobile = trim(I('get.mobile','','strip_tags'));        
        $award_amount = 0;
        $total_invest_amount = 0;
        $invite_num = 0;
        $list = '';
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';
        if($mobile) {
            $user_id = M('User')->where(array('username'=>$mobile))->getField('id');        
            $list = M('userInviteList')->where("user_id = $user_id and add_time >'2016-11-24'")->select();
            foreach ($list as $key => $val){
                $list[$key]['invited_phone'] = substr_replace($val['invited_phone'],'*****',3,5);
            }        
            $award_amount = M('userInviteList')->where("user_id = $user_id and add_time >'2016-11-24'")->sum('amount');
            $total_invest_amount = M('userInviteList')->where(array('user_id'=>$user_id))->sum('first_invest_amount');
            $invite_num =  M('userInviteList')->where('user_id='.$user_id ." and amount > 0 and add_time>'2016-11-24'")->count();
        
            if($total_invest_amount >= 10000) {
                $award_amount +=15;
            }
            if($total_invest_amount >= 50000) {
                $award_amount +=50;
            }
            if($total_invest_amount >= 100000) {
                $award_amount +=100;
            }
            if($total_invest_amount >= 200000) {
                $award_amount +=200;
            }
            if($total_invest_amount >= 500000) {
                $award_amount +=500;
            }
            if($total_invest_amount >= 1000000) {
                $award_amount +=1000;
            }
        }
        
        $res['list'] = $list;
        $res['award_amount'] = $award_amount;
        $res['total_invest_amount'] = $total_invest_amount;
        $res['invite_num'] = $invite_num;
        $res['mobile'] = base64_encode($mobile);
        return $this->ajaxReturn($res);
    }
    
    /**
     * 备战双12
     */
    public function event20161130() {
        $this->display('event20161130');
    }
    
    /**
     * 年未狂欢，豪气Pk
     * 12月1号 - 12月31
     */
    public function event20161201(){
        
        $mobile = trim(I('get.mobile','','strip_tags'));
        
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';
        
        $total_invest_amount = '';
        $current_rank = '';
        
        
        if ($mobile) {
            
            $uid = M('User')->where("username='$mobile'")->getField('id');
            
            if($uid) {
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                        where p.id = s.project_id and s.duration_day >= 30
                        and s.add_time >= '2016-12-01'
                        and s.add_time <= '2016-12-31 23:59:59'
                        and p.is_delete = 0
                        and s.user_id = $uid";
                
                $ret = M()->query($sql);
                 
                if($ret) {
                    if($ret[0]['amount']>0){
                        $total_invest_amount = $ret[0]['amount'].'元';
                    } else{
                        $total_invest_amount = '您还未投资';
                    }
                }
            }
        }
        
        $sql = "select SUM(s.due_capital) as amount ,s.user_id,u.username,u.real_name from s_project p ,s_user_due_detail s,s_user u
                where p.id = s.project_id and s.duration_day >= 30 and s.user_id>0 and s.user_id = u.id
                AND s.add_time >= '2016-12-01'
                AND s.add_time <= '2016-12-31 23:59:59'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY SUM(s.due_capital) DESC LIMIT 100";

        $list = M()->query($sql);
        
        $res = array();
        $n = 1;
        foreach ($list as $val) {
            $dd['rank'] = $n++;
            $dd['real_name'] = $this->substr_cut($val['real_name']);
            $dd['user_name'] = substr_replace($val['username'],'****',3,4);
            $dd['amount'] = $val['amount'];
            $res[$val['user_id']] = $dd;
        }
        unset($list);
        
        
        /*
        
        //系统假数据
        $sql2 = "SELECT SUM(s.due_capital) AS amount,s.user_id FROM s_project p ,s_user_due_detail s
                WHERE p.id = s.project_id AND s.duration_day >= 30
                AND s.user_id >=-101
                AND s.user_id <=-115
                AND s.add_time >= '2016-11-01'
                AND s.add_time <= '2016-12-31 23:59:59'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY sum(s.due_capital) DESC";
        
        $list = M()->query($sql2);
        
        foreach ($list as $val) {
            $info = $this->ghostaccount($val['user_id']);
            if($info) {
                $dd['real_name'] = $this->substr_cut($info[1]);
                $dd['user_name'] = substr_replace($info[0],'****',3,4);
                $dd['amount'] = $val['amount'];
                $res[$val['user_id']] = $dd;
            }
        }
        unset($list);
        
        $sort_arr = array();
        foreach ($res as $val) {
            $sort_arr[] = $val['amount'];
        }
        
        array_multisort($sort_arr, SORT_DESC, $res);
        
        $n = 1;
        
        $last_res = array();
        
        foreach ($res as $v){
            $dd['rank'] = $n++;
            $dd['real_name'] = $this->substr_cut($v['real_name']);
            $dd['amount'] = $v['amount'];
            $last_res[$v['user_id']] = $dd;
        }
        unset($res);
        
        print_r($last_res);
        
        */
        
        if ($uid) {
            $current_rank = $res[$uid]['rank'];
            if ($current_rank) {
                $current_rank .= '名';
            } else {
                if($total_invest_amount > 0) {
                    $current_rank = '您的排名在百名之外';
                } else{
                    $current_rank = '您还未投资';
                }
            }
        }
        
        $this->assign('total_invest_amount',$total_invest_amount);
        $this->assign('current_rank',$current_rank);
        
        $this->assign('list',$res);
        $this->display('event20161201');
    }
    
    
    
    /**
     * 年未狂欢，豪气Pk
     * 12月1号 - 12月31
     */
    public function event201612012(){
    
        $mobile = trim(I('get.mobile','','strip_tags'));
    
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';
    
        $total_invest_amount = '';
        $current_rank = '';
    
    
        if ($mobile) {
    
            $uid = M('User')->where("username='$mobile'")->getField('id');
    
            if($uid) {
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                where p.id = s.project_id and s.duration_day >= 30
                and s.add_time >= '2016-12-01'
                and s.add_time <= '2016-12-31 23:59:59'
                and p.is_delete = 0
                and s.user_id = $uid";
    
                $ret = M()->query($sql);
                 
                if($ret) {
                    if($ret[0]['amount']>0){
                        $total_invest_amount = $ret[0]['amount'].'元';
                    } else{
                        $total_invest_amount = '您还未投资';
                    }
                }
            }
        }
    
        $sql = "select SUM(s.due_capital) as amount ,s.user_id,u.username,u.real_name from s_project p ,s_user_due_detail s,s_user u
                where p.id = s.project_id and s.duration_day >= 30 and s.user_id>0 and s.user_id = u.id
                AND s.add_time >= '2016-12-01'
                AND s.add_time <= '2016-12-31 23:59:59'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY SUM(s.due_capital) DESC LIMIT 100";
    
        $list = M()->query($sql);
    
        $res = array();
    
        foreach ($list as $key => $val) {
            $dd['real_name'] = $this->substr_cut($val['real_name']);
            $dd['user_name'] = substr_replace($val['username'],'****',3,4);
            $dd['amount'] = $val['amount'];
            $dd['type'] = 0;
            $dd['user_id'] = $val['user_id'];
            $res[$val['user_id']] = $dd;
        }
        unset($list);
        
        //系统假数据
        $sql2 = "SELECT SUM(s.due_capital) AS amount,s.user_id FROM s_project p ,s_user_due_detail s
                WHERE p.id = s.project_id AND s.duration_day >= 30
                AND s.user_id >=-120
                AND s.user_id <=-101
                AND s.add_time >= '2016-12-01'
                AND s.add_time <= '2016-12-31 23:59:59'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY sum(s.due_capital) DESC";
        
        $list = M()->query($sql2);

        foreach ($list as $val) {
            $info = $this->ghostaccount($val['user_id']);
            if($info) {
                $dd['real_name'] = $this->substr_cut($info[1]);
                $dd['user_name'] = substr_replace($info[0],'****',3,4);
                $dd['amount'] = $val['amount'];
                $dd['user_id'] = $val['user_id'];
                $dd['type'] = '1';
                $res[$val['user_id']] = $dd;
            }
        }
        
        unset($list);
        
        $sort_arr = array();
        foreach ($res as $val) {
            $sort_arr[] = $val['amount'];
        }
        array_multisort($sort_arr, SORT_DESC, $res);
        
        $n = 1;
        
        foreach ($res as $k => $v){
            $res[$k]['rank'] = $n++;
            $res[$k]['real_name'] = $this->substr_cut($v['real_name']);
            $res[$k]['user_name'] = substr_replace($v['user_name'],'****',3,4);            
            $res[$k]['user_id'] = $v['user_id'];            
            $res[$k]['amount'] = $v['amount'];
            
            if ($uid) {
                if($uid == $res[$k]['user_id']) {
                    $current_rank = $res[$k]['rank'];
                } 
            }
        }
        
        if ($uid) {
            
            if ($current_rank) {
                $current_rank .= '名';
            } else {
                if($total_invest_amount > 0) {
                    $current_rank = '您的排名在百名之外';
                } else{
                    $current_rank = '您还未投资';
                }
            }
        }
    
        $this->assign('total_invest_amount',$total_invest_amount);
        $this->assign('current_rank',$current_rank);
    
        $this->assign('list',$res);
        $this->display('event20161201');
    }
    
    /**
     * 双十二爆款升有 
     * 2016-12-09
     */
    public function event20161209() {
        $this->display();
    }
    
    /**
     * 冬至活动
     * 2016-12-20
     */
    public function event20161220() {
        $this->display();
    }
    
    /**
    * 圣诞爆款
    * @date: 2016-12-22 下午4:42:31
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20161222() {
        $this->display();
    }
    
    /**
    * 元旦活动
    * @date: 2016-12-29 下午4:49:18
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20161229(){
        $this->display();
    }
    
    /**
    * 壕分现金红包
    * @date: 2017-1-11 上午11:51:05
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170111(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        
        $i_invest_amount = 0;        
        $total_invest_money = 0;
        
        $income_amount = 0;
        
        $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential = 6
                and s.add_time >= '2017-01-13 10:00:00'
                and s.add_time <= '2017-01-23 18:03:00'
                and p.is_delete = 0";
        
        $ret = M()->query($sql);
         
        if($ret) {
            if($ret[0]['amount']>0){
                $total_invest_money = $ret[0]['amount'];
            } else{
                $total_invest_money = 0;
            }
        }
        
        
        if($mobile) {
            
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');
            
            if($uid) {
                
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                        where p.id = s.project_id and p.new_preferential = 6
                        and s.add_time >= '2017-01-13 10:00:00'
                        and s.add_time <= '2017-01-23 18:03:00'
                        and p.is_delete = 0
                        and s.user_id = $uid";
                
                $ret = M()->query($sql);
                 
                if($ret) {
                    if($ret[0]['amount']>0){
                        $i_invest_amount = $ret[0]['amount'];
                    } else{
                        $i_invest_amount = 0;
                    }
                }
                
                if($i_invest_amount > 0 ) {
                    $income_amount = round($i_invest_amount / $total_invest_money * 100000,2);
                }
            }
        }
        $this->assign('i_invest_amount',$i_invest_amount);
        $this->assign('total_invest_money',$total_invest_money);   
        $this->assign('income_amount',$income_amount);
        $this->display();
    }
    /**
    * 庆金鸡临门，享迎新爆款
    * @date: 2017-1-18 下午6:00:09
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170118(){
        $this->display();
    }
    
    /**
    * 情人节活动
    * @date: 2017-2-13 上午11:10:18
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170214(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        $invest_amount = 0;
        $uid = 0;
        if($mobile) {        
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');        
            if($uid) {        
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                        where p.id = s.project_id and p.new_preferential = 2
                        and s.add_time >= '2017-02-14'
                        and s.add_time <= '2017-02-17'
                        and p.is_delete = 0
                        and s.user_id = $uid";
        
                $ret = M()->query($sql); 
                 
                if($ret) {
                    if($ret[0]['amount']>0){
                        $invest_amount = $ret[0]['amount'];
                    } else{
                        $invest_amount = 0;
                    }
                }        
            }
        }
        $this->assign('invest_amount',$invest_amount);
        $this->assign('uid',$uid);
        $this->display();
    }
    /**
    * 收货信息
    * @date: 2017-2-13 下午4:10:20
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function get_evt20170214() {
        $user_id = I('post.user_id',0,'int');
        if($user_id) {
            $info = M('evt20170214')->where('user_id='.$user_id)->find();
            if($info) {
                $this->ajaxReturn(array('status'=>1,'info'=>$info));
            }
        }
        $this->ajaxReturn(array('status'=>0,'info'=>''));
    }
    /**
    * 保存收货地址
    * @date: 2017-2-13 下午4:09:40
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function add_evt20170214() {
        $user_id = I('post.user_id',0,'int'); 
        $user_name = trim(I('post.user_name','','strip_tags'));
        $address = trim(I('post.address','','strip_tags'));
        $address2 = trim(I('post.address2','','strip_tags'));
        $mobile = trim(I('post.mobile','','strip_tags'));
        $type = I('post.type',0,'int');
        
        if(!$user_id) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请先登录'));
        }       
        if(!$user_name) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请填写收件人'));
        }
        if(!$address) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请填写所在地'));
        }
        if(!$address2) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请填写详细地址'));
        }
        if(!$mobile) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请填写手机号码'));
        }
        
        if($user_id) {
            $userObj = M('evt20170214')->where('user_id='.$user_id)->find();
            
            if($userObj) {
                
                $dd = array(
                    'user_name' => $user_name,
                    'mobile' => $mobile,
                    'type' => $type,
                    'address' => $address,
                    'address2' => $address2,
                    'create_time' => time(),
                );
                
                if(M('evt20170214')->where(array('user_id'=>$user_id))->save($dd)) {
                    $this->ajaxReturn(array('status'=>1,'info'=>'修改成功'));
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'失败，请联系客服'));
                }
                
            } else {
                $dd = array(
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'mobile' => $mobile,
                    'type' => $type,
                    'address' => $address,
                    'address2' => $address2,
                    'create_time' => time(),
                );
                
                if(M('evt20170214')->add($dd)) {
                    $this->ajaxReturn(array('status'=>1,'info'=>'添加成功'));
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'失败，请联系客服'));
                }
            }
        } 
    }
    
    /**
    * 龙抬头2.24
    * @date: 2017-2-24 下午3:08:08
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170224(){
        $this->display();
    }
    
    /**
     * 3.8女神活动
     * @date: 2017-03-03 下午3:08:08
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170303(){
        $this->display();
    }
    
    
    /**
    * 推广注册
    * @date: 2017-2-4 下午2:53:31
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function tg_login() {
        $code = I('post.code','','strip_tags');
        $mobile = I('post.mobile','','strip_tags');    
        //渠道
        $ch = trim(I('post.ch','','strip_tags'));
        
        if(!$code){
            $this->ajaxReturn(array('status'=>0,'info'=>'请输入手机验证码！'));
        }
        
        $sign = md5($mobile.':'.$this->key);
    
        $dd = array(
            'ch' => $ch,
            'mobile' => $mobile,
            'sign' => $sign,
            'validateCode'=>$code
        );
        
        $ret = trim($this->send_post($this->tgllbUrl, $dd));
    
        
        if(strpos($ch, 'jrtt') !== false){
            $ch = 'jrtt';
        }
        
        
        if($ret == 'N2') {
            $this->ajaxReturn(array('status'=>0,'info'=>'手机验证码不对'));
        } else if($ret == 'N3') {//被邀请人已注册
            /*
            if($ch == 'jrtt') {
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/jrtt_res.html?type=1'));
            }else if($ch == 'qixi'){
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/qixi_res.html?type=1'));
            } else if($ch == 'xjzdy'){
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/xjzdy_res.html?type=1'));
            }else {
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/tg_login_succ.html?ch='.$ch));
            }*/
            
            $this->ajaxReturn(array('status'=>1,'info'=>'/hd/tg_login_succ.html?ch='.$ch));
            
            
        } else if($ret == 'Y0') { //成功
            
            /*
            if($ch == 'jrtt') {
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/jrtt_res.html?type=2'));
            }else if($ch == 'qixi'){
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/qixi_res.html?type=2'));
            } else if($ch == 'wjdz'){
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/qixi_res.html?type=2'));
            } else if($ch == 'xjzdy'){
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/xjzdy_res.html?type=2'));
            }else {
                $this->ajaxReturn(array('status'=>1,'info'=>'/hd/tg_reg_succ.html?ch='.$ch));
            }*/
            
            $this->ajaxReturn(array('status'=>1,'info'=>'/hd/tg_reg_succ.html?ch='.$ch));
            
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'注册失败，请联系客服人员！错误码：'.$ret));
        }
    
        /*
         N:1 签名错误
         N:2 签名错误
         N:3 邀请人不存在
         N:4：被邀请人已注册
         N:5：邀请失败：
         Y: 成功
         */
    }

    /*
      喜剧总动员 成功页面
     */
    public function xjzdy_res(){
        $type = I('type');
        $ch = 'xjzdy';
        //$is_weixin = 0;
        //if(is_weixin()){
        //    $is_weixin = 1;
        //}
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('type',$type);
        //$this->assign('is_weixin',$is_weixin);
        //$this->assign('device_type',get_device_type());
        $this->display('Activity/xjzdy/res_succ');
    }


    public function jrtt_res(){
        $type = I('type');
        $ch = 'jrtt';
        //$is_weixin = 0;
        //if(is_weixin()){
        //    $is_weixin = 1;
        //}
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('type',$type);
        //$this->assign('is_weixin',$is_weixin);
        //$this->assign('device_type',get_device_type());
        $this->display('Activity/jrtt/res');
    }
    
    public function jrtt_res1(){
        $type = I('type');
        $ch = 'jrtt';
        //$is_weixin = 0;
        //if(is_weixin()){
        //    $is_weixin = 1;
        //}
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('type',$type);
        //$this->assign('is_weixin',$is_weixin);
        //$this->assign('device_type',get_device_type());
        $this->display('Activity/jrtt/res');
    }
    
    public function qixi_res(){
        $type = I('type');
        $ch = 'qixi';
        $is_weixin = 0;
        if(is_weixin()){
            $is_weixin = 1;
        }
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('type',$type);
        $this->assign('is_weixin',$is_weixin);
        $this->assign('device_type',get_device_type());
        $this->display('Activity/qixi/res');
    }
    
    /**
     * 流量宝推广 登录成功
     * 2016-11-14
     */
    public function tg_login_succ() {
        $ch = trim(I('get.ch','llb','strip_tags'));     
        $is_weixin = 0;
        if(is_weixin()){
            $is_weixin = 1;
        }
        $this->assign('is_weixin',$is_weixin);
        $this->assign('device_type',get_device_type());
        $this->assign('url',$this->_jump_url($ch));
        //$this->display('tg_loign_succ');
        
        $this->display('Activity/channel888/login_success');
    }
    
    /**
     * 流量宝推广注册成功
     * 2016-11-14
     */
    public function tg_reg_succ() {
        $ch = trim(I('get.ch','llb','strip_tags'));  
        $is_weixin = 0;
        if(is_weixin()){
            $is_weixin = 1;
        }
        $this->assign('is_weixin',$is_weixin);
        $this->assign('device_type',get_device_type());
        $this->assign('url',$this->_jump_url($ch));
        //$this->display('tg_reg_succ');
        
        $this->display('Activity/channel888/reg_success');
    }
    
    /**
    * 返回推广告渠包下载地址
    * @date: 2017-2-4 下午3:35:36
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    private function _jump_url($ch) {
        
        $url = '';        
        $ios_url = 'https://itunes.apple.com/cn/app/id1239264762?mt=8';
        
        if(strpos($ch, 'shenma') !== false){
            $ch = 'shenma';
        }
        
        if(strpos($ch, 'qutoutiao') !== false){
            $ch = 'qutoutiao';
        }
        
        if(strpos($ch, 'jrtt') !== false){
            $ch = 'jrtt';
        }
        
        if(strpos($ch, 'jumeitoutiao') !== false){
            $ch = 'jumeitoutiao';
        }
        
        if($ch == 'llb') {//流量
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-llb-release-93-2.3.0.apk';
            }
        } else if($ch == 'danlan') {//淡蓝
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-danlan-release-93-2.3.0.apk';
            }
        } else if($ch == 'diantai') {//电台
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-diantai-release-93-2.3.0.apk';
            }
        } else if($ch == 'InMoBi') {
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-inmobi-release-93-2.3.0.apk';
            }
        } else if($ch == 'jrtt'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-jrtt-release-93-2.3.0.apk';
            }
        } else if($ch == 'jrtt2') {
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-jrtt2-release-93-2.3.0.apk';
            }
        } else if($ch == 'fcjn'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-fcjn-release-93-2.3.0.apk';
            }
        } else if($ch == 'dianxin'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-dianxin-release-93-2.3.0.apk';//
            }
        } else if($ch == 'shenma'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-shenma-release-93-2.3.0.apk';
            }
        } else if($ch == 'xihuzhisheng') {
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-xihuzhisheng-release-93-2.3.0.apk';
            }
        } else if($ch == 'huisuoping'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-huisuoping-release-93-2.3.0.apk';
            }        
		} else if($ch == 'chubao'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-chubao-release-93-2.3.0.apk';//
            }
        } else if($ch == 'PPTV') {
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-PPTV-release-93-2.3.0.apk';
            }
        } else if($ch == '_907') {
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-_907-release-93-2.3.0.apk';
            }
        } elseif ($ch == 'wenjuanxing'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-wenjuanxing-release-93-2.3.0.apk';
            }
        } elseif ($ch == 'qutoutiao'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-qutoutiao-release-93-2.3.0.apk';
            }
        } elseif ($ch == 'zhonghua'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-zhonghua-release-93-2.3.0.apk';
            }
        } else if($ch == 'qixi'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-qixi-release-93-2.3.0.apk';
            }
        } else if($ch == '_918zs'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-_918zs-release-93-2.3.0.apk';
            }
        } else if($ch == 'tengxun'){
            if (get_device_type()=='ios') {
                $url = $ios_url;//'https://itunes.apple.com/cn/app/id1234916517?mt=8';
            } else{
                $url = 'https://image.ppmiao.com/download/app-tengxun-release-93-2.3.0.apk';
            }
        } else if($ch == 'jumeitoutiao') {
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-jumeitoutiao-release-93-2.3.0.apk';
            }
        } else if($ch == 'qq1'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-qq1-release-93-2.3.0.apk';
            }
        } else if($ch == 'qq2'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-qq2-release-93-2.3.0.apk';
            }
        } else if($ch == 'qq3'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-qq3-release-93-2.3.0.apk';
            }
        }else if($ch == 'qq4'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-qq4-release-93-2.3.0.apk';
            }
        }else if($ch == 'qq5'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-qq5-release-93-2.3.0.apk';
            }
        } else if($ch == 'aiqiyi') {
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-aiqiyi-release-93-2.3.0.apk';
            }
        } else if($ch == 'wydz'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-wydz-release-93-2.3.0.apk';
            }
        }else if($ch == 'dailuopan'){
           if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'http://image.ppmiao.com/download/app-dailuopan-release-93-2.3.0.apk';
            } 
        }else if ($ch == 'xjzdy') {
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'http://image.ppmiao.com/download/app-xjzdy-release-94-2.4.0.apk';//喜剧总动员 安卓apk;
            } 
        }else if($ch == 'quanzi') {
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-quanzi-release-93-2.3.0.apk';
            }
        }else if($ch == 'mmsc'){
            if (get_device_type()=='ios') {
                $url = $ios_url;
            } else{
                $url = 'https://image.ppmiao.com/download/app-mmsc-release-94-2.4.0.apk';
            }
        }
        return $url;
    }
    
    /**
     * 周年庆 共贺乔迁新禧，全民瓜分红包
     * @date: 2017-3-13 上午11:51:05
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170313(){
        $mobile = trim(I('get.mobile','','strip_tags'));    
        $i_invest_amount = 0;
        $total_invest_money = 0;    
        $income_amount = 0;    
        
        $start_time = '2017-03-15';
        $end_time = '2017-03-25';        
        
        $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential = 6
                and s.add_time >='$start_time'
                and s.add_time <'$end_time'
                and p.is_delete = 0";    
        $ret = M()->query($sql);         
        if($ret) {
            if($ret[0]['amount']>0){
                $total_invest_money = $ret[0]['amount'];
            } else{
                $total_invest_money = 0;
            }
        }   
    
        if($mobile) {    
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');    
            if($uid) {    
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                        where p.id = s.project_id and p.new_preferential = 6
                        and s.add_time >='$start_time'
                        and s.add_time <'$end_time'
                        and p.is_delete = 0
                        and s.user_id = $uid";
    
                $ret = M()->query($sql);                 
                if($ret) {
                    if($ret[0]['amount']>0){
                        $i_invest_amount = $ret[0]['amount'];
                    } else{
                        $i_invest_amount = 0;
                    }
                }    
                if($i_invest_amount > 0 ) {
                    $income_amount = round($i_invest_amount / $total_invest_money * 200000,2);
                }
            }
        }
        $this->assign('i_invest_amount',$i_invest_amount);
        $this->assign('total_invest_money',$total_invest_money);
        $this->assign('income_amount',$income_amount);
        $this->display();
    }
    
    /**
    * 函数用途描述
    * @date: 2017-3-13 下午4:38:02
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    
    public function event20170314(){    
        $mobile = trim(I('get.mobile','','strip_tags'));    
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';    
        $total_invest_amount = '';
        $current_rank = ''; 
        
        $start_time = '2017-03-15';
        $end_time = '2017-04-16';
        
        if ($mobile) {    
            $uid = M('User')->where("username='$mobile'")->getField('id');    
            if($uid) {
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                        where p.id = s.project_id and s.duration_day >=60
                        and s.add_time >= '$start_time'
                        and s.add_time < '$end_time'
                        and p.is_delete = 0
                        and s.user_id = $uid";
    
                $ret = M()->query($sql);                 
                if($ret) {
                    if($ret[0]['amount']>0){
                        $total_invest_amount = $ret[0]['amount'].'元';
                    } else{
                        $total_invest_amount = '您还未投资';
                    }
                }
            }
        }
    
        $sql = "select SUM(s.due_capital) as amount ,s.user_id,u.username,u.real_name from s_project p ,s_user_due_detail s,s_user u
                where p.id = s.project_id and s.duration_day >=60 and s.user_id>0 and s.user_id = u.id
                AND s.add_time >= '$start_time'
                AND s.add_time < '$end_time'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY SUM(s.due_capital) DESC LIMIT 100";
    
        $list = M()->query($sql);
    
        $res = array();
    
        foreach ($list as $key => $val) {
            $dd['real_name'] = $this->substr_cut($val['real_name']);
            $dd['user_name'] = substr_replace($val['username'],'****',3,4);
            $dd['amount'] = $val['amount'];
            $dd['type'] = 0;
            $dd['user_id'] = $val['user_id'];
            $res[$val['user_id']] = $dd;
        }
        unset($list);
    
        //系统假数据
        $sql2 = "SELECT SUM(s.due_capital) AS amount,s.user_id FROM s_project p ,s_user_due_detail s
                WHERE p.id = s.project_id AND s.duration_day >=60
                AND s.user_id >=-130
                AND s.user_id <=-101
                AND s.add_time >= '$start_time'
                AND s.add_time < '$end_time'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY sum(s.due_capital) DESC";
    
        $list = M()->query($sql2);
    
        foreach ($list as $val) {
            $info = $this->ghostaccount($val['user_id']);
            if($info) {
                $dd['real_name'] = $this->substr_cut($info[1]);
                $dd['user_name'] = substr_replace($info[0],'****',3,4);
                $dd['amount'] = $val['amount'];
                $dd['user_id'] = $val['user_id'];
                $dd['type'] = '1';
                $res[$val['user_id']] = $dd;
            }
        }
    
        unset($list);
    
        $sort_arr = array();
        foreach ($res as $val) {
            $sort_arr[] = $val['amount'];
        }
        array_multisort($sort_arr, SORT_DESC, $res);    
        $n = 1;    
        foreach ($res as $k => $v){
            $res[$k]['rank'] = $n++;
            $res[$k]['real_name'] = $this->substr_cut($v['real_name']);
            $res[$k]['user_name'] = substr_replace($v['user_name'],'****',3,4);
            $res[$k]['user_id'] = $v['user_id'];
            $res[$k]['amount'] = $v['amount'];
    
            if ($uid) {
                if($uid == $res[$k]['user_id']) {
                    $current_rank = $res[$k]['rank'];
                }
            }
        }
    
        if ($uid) {    
            if ($current_rank) {
                $current_rank .= '名';
            } else {
                if($total_invest_amount > 0) {
                    $current_rank = '您的排名在百名之外';
                } else{
                    $current_rank = '您还未投资';
                }
            }
        }    
        
        $this->assign('total_invest_amount',$total_invest_amount);
        $this->assign('current_rank',$current_rank);    
        $this->assign('list',$res);
        $this->display();
    }
    
    /**
    * 大富翁活
    * @date: 2017-3-22 上午11:30:34
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function dafuweng(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';
        $this->assign('mobile',$mobile);
        $this->display('Activity/dafuweng/index');
    }
    
    //抽奖
    public function dfw_lottery(){
        
        $mobile = trim(I('post.mobile','','strip_tags'));       
        
        $api_url = $this->dfw_api_url.'/ppmiao-rest/payment/activity/inviteFriend/lottery.htm';

        $data = array(
            'mobile'=>$mobile,
            'sign'=>md5($mobile.':'.$this->key)
        );     
           
        $res = json_decode($this->send_post($api_url, $data),true);        
        
        if($res) {
            if($res['code']<=0) {
                $this->ajaxReturn(array('status'=>0,'info'=>$res['result']));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>$res));
            }
        } else {
            $this->ajaxReturn(array('status'=>2,'info'=>'服务器繁忙'));
        }
    }
    
    //当前位子
    public function dfw_current_pos(){
        $mobile = trim(I('post.mobile','','strip_tags'));
        if(!$mobile) $this->ajaxReturn(array('status'=>2,'info'=>'请先登录'));
        
        $data = array(
            'mobile'=>$mobile,
            'sign'=>md5($mobile.':'.$this->key)
        );
        $api_url = $this->dfw_api_url.'/ppmiao-rest/payment/activity/inviteFriend/getCurrentLotteryPos.htm';
        $res = json_decode($this->send_post($api_url, $data),true);
        
        if($res) {
            if($res['code']<=0) {
                $this->ajaxReturn(array('status'=>0,'info'=>explode(',',$res['result'])));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>$res));
            }
        } else {
            $this->ajaxReturn(array('status'=>2,'info'=>'服务器繁忙'));
        }
    }
    
    
    //奖品列表
    public function dfw_prizelist(){
        
        $mobile = trim(I('post.mobile','','strip_tags'));
        if(!$mobile) $this->ajaxReturn(array('status'=>2,'info'=>'请先登录'));
        
        $data = array(
            'mobile'=>$mobile,
            'sign'=>md5($mobile.':'.$this->key)
        );
        
        $api_url = $this->dfw_api_url.'/ppmiao-rest/payment/activity/inviteFriend/getLotteryPrizeList.htm';
        $res = json_decode($this->send_post($api_url, $data),true);
        
        if($res) {
            if($res['code']<=0) {
                $this->ajaxReturn(array('status'=>0,'info'=>$res['result']['data']));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>$res));
            }
        } else {
            $this->ajaxReturn(array('status'=>2,'info'=>'服务器繁忙'));
        }
    }
    /**
     * 大富翁活
     * @date: 2017-3-22 上午11:30:34
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function dfw_all_prizelist(){
        
        $data = array(
            'sign'=>md5($this->key)
        );
        
        $api_url = $this->dfw_api_url.'/ppmiao-rest/payment/activity/inviteFriend/getAllDynamicLotteryPrize.htm';
        $res = json_decode($this->send_post($api_url, $data),true);
        
        if($res) {
            if($res['code']<=0) {
                $this->ajaxReturn(array('status'=>0,'info'=>$res['result']));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>$res));
            }
        } else {
            $this->ajaxReturn(array('status'=>2,'info'=>'服务器繁忙'));
        }
    }
    
    /**
     * 三月加息
     * @date: 2017-3-22 上午11:30:34
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170324(){
        $this->display();
    }
    
    /**
    * 新人活动
    * @date: 2017-4-7 下午2:07:08
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    
    public function event20170407(){
        $mobile = trim(I('get.mobile','','strip_tags'));


        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $my_url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $full_url = $this->getWeixinShareSdkUrl.'?url='.urlencode($my_url);
        $jssdk = file_get_contents($full_url);
        $this->assign('jssdk',$jssdk);
        $this->assign('mobile',$mobile);       
        $this->display();
    } 

    /**
    * 新人标活动取数据接口
    * @date: 2017-4-26 下午9:51:18
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    
    public function getEvent20170407Data(){
        
        $data = array();
        $compeleteCount = 0;
        $expiryDates = 0;
        $intTotal = 0;
        $awardCash = 0;
        $progress = 0;
        
        $mobile = trim(I('post.mobile','','strip_tags'));
        
        $dd =array(
            'mobile'=>$mobile
        );
        $res = json_decode($this->send_post($this->newProjectsUrl, $dd),true);

        if($res['code'] == 0){
             
            $compeleteCount = $res['result'][0]['compeleteCount'];
            $expiryDates = $res['result'][0]['expiryDates'];
            $intTotal = $res['result'][0]['intTotal'];
            $pList = $res['result'][0]['spList'];
        
            if($intTotal >= 30000 && $intTotal <50000){
                $awardCash = 20;
            } else if($intTotal >= 50000 && $intTotal <100000){
                $awardCash = 30;
            } else if($intTotal >= 100000 && $intTotal <140000){
                $awardCash = 40;
            }else if($intTotal >= 140000){
                $awardCash = 50;
            }
            if($compeleteCount>4 && $intTotal>=30000){
                $awardCash+=10;
            }
            
           // $awardCash = 20;
            
            if($awardCash == 20){
                $progress = 20;
            }else if($awardCash == 30){
                $progress = 40;
            }else if($awardCash == 40){
                $progress = 60;
            }else if($awardCash == 50){
                $progress = 80;
            }else if($awardCash == 60){
                $progress = 100;
            }
        
            if($pList) {
                foreach ($pList as $val) {
                    $rows['id'] = $val['id'];
                    $rows['userInterest'] = $val['userInterest'];
                    $rows['durationType'] = $val['durationType'];
                    $rows['userPlatformSubsidy'] = $val['userPlatformSubsidy'];

                    $rows['isComplete'] = $val['isComplete'];
                    // $rows['endTime'] = date("Y-m-d H:i:s",($val['endTime']['time'])/1000);
                    $rows['duration'] = floor((($val['endTime']['time']/1000) - strtotime(date("Y-m-d")))/86400);
        
                    //0结束，1完成 ，2可以投资,3已卖完
                    if($mobile) {
                        if($expiryDates == 0) {
                            $rows['status'] = 0;
                        }else {
                            if($val['isComplete']>0) {
                                $rows['status'] = 1;
                            } else {
                                if($val['status'] == 3){
                                    $rows['status'] = 3;
                                } else {
                                    $rows['status'] = 2;
                                }
                            }
                        }
                    } else {
                        if($val['status'] == 3){
                            $rows['status'] = 3;
                        } else {
                            $rows['status'] = 2;
                        }
                    }
                    $data[] = $rows;
                }
            }
            
            $ret = array(
                'compeleteCount'=>$compeleteCount,
                'expiryDates'=>$expiryDates,
                'intTotal'=>$intTotal,
                'awardCash'=>$awardCash,
                'progress'=>$progress,
                'list'=>$data,
            );
            $this->ajaxReturn(array('status'=>0,'info'=>$ret));
        } else {
            $this->ajaxReturn(array('status'=>1,'info'=>'服务器繁忙'));
        }
    }
    
    public function testAjax(){
        $this->ajaxReturn(array('status'=>0,'info'=>'ok'));
    }
    
    /**
    * 好友邀请活动
    * @date: 2017-4-13 下午4:56:58
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170413(){
        $this->assign('mobile',trim(I('get.mobile','','strip_tags')));
        $this->display('Activity/event20170413/index');
    }
    
    public function event20170413rule(){
        $this->display('Activity/event20170413/rule');
    }
    
    public function event20170413prize(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        $this->display('Activity/event20170413/prize');
    }
    
    /**
    * 51活动
    * @date: 2017-4-24 上午10:17:22
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170424(){
        $data = array();
        $mobile = trim(I('get.mobile','','strip_tags'));
        $dd =array(
            'mobile'=>$mobile
        );
        $res = json_decode($this->send_post($this->laborDayActivityUrl, $dd),true);

        if($res['code'] == 0){
            //用户的标兵勋章数
            $data['medalValTotal'] = $res['result']['medalValTotal'];
            //签到天数
            //$data['signInTotal'] = $res['result']['signInTotal'];

            $data['cashCouponTotal'] = $res['result']['cashCouponTotal'];
            $data['investTotal'] = $res['result']['investTotal'];

            $data['currentDay'] = date("j");

            $medalAwardsList = $res['result']['medalAwards'];
            $signInAwardsList = $res['result']['signInAwards'];
            $missionLogList = $res['result']['signInDates'];

            $signInDatesList = $res['result']['signInDates'];

            $investAwardMaps = $res['result']['investAwardMaps'];

            $endTime = ($res['result']['laborEndTime']['time'])/1000;

            $data['hdStatus'] = 0;

            if($endTime<time()){
                $data['hdStatus'] = 1;
            }

            $startTime = ($res['result']['labordayStartTime']['time'])/1000;
            $day = ceil((time() - $startTime)/86400);
            $dayPos = array();
            $_tmpPos = array(27,28,29,30,1,2,3,4,5,6,7,8,9,10,11);
            for ($i=0;$i<$day;$i++){
                array_push($dayPos,0);
            }
            //兑换奖励列表
            if($medalAwardsList) {
                $ret = array();
                foreach ($medalAwardsList as $val){
                    $rows['title'] = $val['title'];
                    $rows['id'] = $val['id'];
                    $rows['ico'] = $val['probability'];
                    $rows['total'] = $val['total'];
                    $ret[] = $rows;
                }
                $data['medalAwardsList'] = $ret;
            }
            //签到奖励列表
            if($signInAwardsList){
                $ret = array();
                unset($rows);
                foreach ($signInAwardsList as $val){
                    $rows['isCompelete'] = $val['isCompelete'];
                    $rows['id'] = $val['id'];
                    $ret[] = $rows;
                }
                $data['signInAwardsList'] = $ret;
            }

            //签到列表
            $isSign = false;
            if($missionLogList) {
                foreach ($missionLogList as $val){
                    $offset=array_search($val['date'],$_tmpPos);
                    $dayPos[$offset] = 1;
                    if($val['date'] == $data['currentDay'] ){
                        $isSign = true;
                    }
                }

            } else {
                $offset=array_search($data['currentDay'],$_tmpPos);
                if($dayPos[$offset] !=1){
                    $dayPos[$offset] = 2;
                }
            }

            $data['isSign'] = $isSign;//今天是否签到
            $data['missionLogCount'] = count($missionLogList);//累计签到次数
            $data['signInAwardsStr'] = implode(',',$dayPos);

            //田地开慌状态
            $data['ground_1'] = $data['ground_2'] = $data['ground_3'] = $data['ground_4'] = $data['ground_5'] = $data['ground_6'] = 0;

            if($investAwardMaps){

                if($investAwardMaps['5000']) {
                    $data['ground_1'] = $investAwardMaps['5000'][0]['isCompelete'];
                }

                if($investAwardMaps['10000']){
                    $data['ground_2'] = $investAwardMaps['10000'][0]['isCompelete'];
                }

                if($investAwardMaps['30000']) {
                   $data['ground_3'] =  $investAwardMaps['30000'][0]['isCompelete'];
                }

                if($investAwardMaps['50000']) {
                    $data['ground_4'] = $investAwardMaps['50000'][0]['isCompelete'];
                }

                if($investAwardMaps['100000']) {
                    $data['ground_5'] = $investAwardMaps['100000'][0]['isCompelete'];
                }

                if($investAwardMaps['200000']) {
                   $data['ground_6'] =  $investAwardMaps['200000'][0]['isCompelete'];
                }
            }
        }
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $my_url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $full_url = $this->getWeixinShareSdkUrl.'?url='.urlencode($my_url);
        $jssdk = file_get_contents($full_url);

        $this->assign('jssdk',$jssdk);
        $this->assign('data',$data);
        $this->assign('mobile',$mobile);
        $this->display('Activity/event20170424/index');
    }

    public function event20170424Prize(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        $dd =array(
            'mobile'=>$mobile,
            'type'=>1
        );

        $res = json_decode($this->send_post($this->getAwardLogUrl, $dd),true);
        if($res['code'] == 0){
            $prizeList = $res['result']['sLotteryLogs'];
            if($prizeList){
                $ret = array();
                $n = 1;
                foreach ($prizeList as $val){
                    $row['n'] = $n++;
                    $row['add_time'] = date("y-m-d H:i:s",$val['updateTime']);
                    $row['name'] = $val['name'];
                    $ret[] = $row;
                }
            }
        }

        $dd['type'] = 2;

        $res = json_decode($this->send_post($this->getAwardLogUrl, $dd),true);
        //print_r($res);
        if($res['code'] == 0){
            $prizeList = $res['result']['sLotteryLogs'];
            if($prizeList){

                $ret2 = array();
                unset($row);
                $n = 1;
                foreach ($prizeList as $val){
                    $row['n'] = $n++;
                    $row['add_time'] = date("y-m-d H:i:s",$val['updateTime']);
                    $row['name'] = $val['name'];
                    $ret2[] = $row;
                }
            }
        }

        $this->assign('mobile',$mobile);
        $this->assign('prizeList1',$ret);// 1 发放奖励
        $this->assign('prizeList2',$ret2);// 2 兑换奖励
        $this->assign('prizeListCount1',count($ret));// 1 发放奖励
        $this->assign('prizeListCount2',count($ret2));// 2 兑换奖励
        $this->display('Activity/event20170424/prize');
    }

    /**
    * 兑换列表
    * @date: 2017-4-24 下午9:17:46
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function getEvent20170424Data(){
        $data = array();
        $mobile = trim(I('post.mobile','','strip_tags'));
        $dd =array(
            'mobile'=>$mobile
        );
        $res = json_decode($this->send_post($this->laborDayActivityUrl, $dd),true);
        if($res['code'] == 0){

            $data['medalValTotal'] = $res['result']['medalValTotal'];
            $data['signInTotal'] = $res['result']['signInTotal'];

            $medalAwardsList = $res['result']['medalAwards'];
            if($medalAwardsList) {
                $ret = array();
                foreach ($medalAwardsList as $val){
                    $rows['title'] = $val['title'];
                    $rows['id'] = $val['id'];
                    $rows['ico'] = $val['probability'];
                    $rows['total'] = $val['total'];
                    $rows['name'] = $val['name'];
                    $ret[] = $rows;
                }
                $data['medalAwardsList'] = $ret;
            }
            $this->ajaxReturn(array('status'=>0,'info'=>$data));
        }
        else{
            $this->ajaxReturn(array('status'=>1,'info'=>'服务器繁忙'));
        }
    }

    /**
     * 兑换五一活动奖励
     * @date: 2017-4-24 下午4:23:56
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function exchangeLaborDayAward(){
        $mobile = trim(I('post.mobile','','strip_tags'));
        $lotteryAwardId = trim(I('post.lotteryAwardId','','strip_tags'));
        if(!$mobile){
            $this->ajaxReturn(array('status'=>0,'info'=>'请先登录 '));
        }
        $dd =array(
            'mobile'=>$mobile,
            'lotteryAwardId'=>$lotteryAwardId
        );

        $res = json_decode($this->send_post($this->exchangeLaborDayAwardUrl, $dd),true);

        if($res['code'] == 0){
            $this->ajaxReturn(array('status'=>2,'info'=>'兑换成功'));
        } else{
            if($res['code'] == 21013) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请先登录 '));
            } else {
                $this->ajaxReturn(array('status'=>1,'info'=>$res['errorMsg']));
            }
        }
    }

    /**
    * 51活动签到
    * @date: 2017-4-24 下午4:23:56
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function dailySignin(){
        $mobile = trim(I('post.mobile','','strip_tags'));
        if(!$mobile){
            $this->ajaxReturn(array('status'=>0,'info'=>'请先登录 '));
        }
        $dd =array(
            'mobile'=>$mobile,
        );
        $res = json_decode($this->send_post($this->dailySigninUrl, $dd),true);

        if($res['code'] == 0){
            $this->ajaxReturn(array('status'=>2,'info'=>$res));
        } else{
            if($res['code'] == 21013) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请先登录 '));
            } else {
                $this->ajaxReturn(array('status'=>1,'info'=>$res['errorMsg']));
            }
        }
    }

    /**
    * 领取51活动签到奖励
    * @date: 2017-4-24 下午7:08:38
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function receiveLaborDayAward(){
        $mobile = trim(I('post.mobile','','strip_tags'));
        $lotteryAwardId = trim(I('post.lotteryAwardId','','strip_tags'));
        if(!$mobile){
            $this->ajaxReturn(array('status'=>0,'info'=>'请先登录 '));
        }
        $dd =array(
            'mobile'=>$mobile,
            'lotteryAwardId'=>$lotteryAwardId
        );
        $res = json_decode($this->send_post($this->receiveLaborDayAwardUrl, $dd),true);


        if($res['code'] == 0){
            $this->ajaxReturn(array('status'=>2,'info'=>'领取成功'));
        } else{
            if($res['code'] == 21013) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请先登录 '));
            } else {
                $this->ajaxReturn(array('status'=>1,'info'=>$res['errorMsg']));
            }
        }
    }


    public function get_investTotal(){
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        $dd =array(
            'mobile'=>$mobile
        );
        if($mobile) {
            $res = json_decode($this->send_post($this->laborDayActivityUrl, $dd),true);
            $data['investTotal'] = $res['result']['investTotal'];
            $data['cashCouponTotal'] = $res['result']['cashCouponTotal'];
            $this->ajaxReturn(array('status'=>'0','investTotal'=>$data['investTotal'],'cashCouponTotal'=>$data['cashCouponTotal']));
        } else {
            $this->ajaxReturn(array('status'=>'0','info'=>0));
        }
    }
    
    /**
    * 5154加息活动
    * @date: 2017-4-30 下午9:41:53
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170428(){
        $this->display();
    }
    
    
    /**
    * 上市国资系活动（备选）
    * @date: 2017-5-11 下午2:16:50
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170511(){
        $hdId = 9;
        $data['hdMsg'] = '';
        $data['hdStatus'] = 0;
        $evtInfo = M('lotteryBase')->field('id,start_time,end_time,status')->where('id='.$hdId .' and is_delete=0')->find();
        if(!$evtInfo) {
            exit('活动没有配置');
        }
        
        if($evtInfo['start_time']>time()) {
            $data['hdStatus'] = 1;
            $data['hdMsg'] = '活动于'.date('m月d日',$evtInfo['start_time']).'开始';
        }
        
        if($evtInfo['end_time']<time()) {
            $data['hdStatus'] = 2;
            $data['hdMsg'] = '活动于'.date('m月d日',$evtInfo['end_time']).'结束';
        }
        
        $startTime = date("Y-m-d",$evtInfo['start_time']);
        $endTime = date("Y-m-d",$evtInfo['end_time']);
        
        $yesterdayList = '';        
        $currentDate = date('Y-m-d');
        
        //活动没有开始
        if($data['hdStatus'] == 1) {
            $yesterdayList = '';
            $todayList = '';
        } else if($data['hdStatus'] == 2) {            
            $todayList = '';//当天没有数据
            $yesterdayList = '';            
            $days = (strtotime($currentDate) - strtotime($endTime))/86400;          
            if($days<=1) {
                $yesterdaySql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$endTime' and udd.add_time<'$endTime 23:59:59.999' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $yesterdayList = M()->query($yesterdaySql);
            }
        } else {
            $tomorrow = date("Y-m-d",strtotime("+1 day"));
            $todaySql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$currentDate' and udd.add_time<'$tomorrow' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
            $todayList = M()->query($todaySql);
            
            if($startTime == $currentDate ){
                $yesterdayList = "";//活动今日开启，暂无昨日记录。
            } else {
                $anteayer = date("Y-m-d",strtotime("-1 day"));
                $yesterdaySql = "SELECT  sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$anteayer' and udd.add_time<'$currentDate' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $yesterdayList = M()->query($yesterdaySql);
            }
        }
        
        if($yesterdayList) {
            foreach ($yesterdayList as $key=>$val) {
                $yesterdayList[$key]['username'] = substr_replace($val['username'],'****',3,4);
                $yesterdayList[$key]['real_name'] = $this->substr_cut($val['real_name']);
            }
        }
        
        if($todayList) {
            foreach ($todayList as $key=>$val) {
                $todayList[$key]['username'] = substr_replace($val['username'],'****',3,4);
                $todayList[$key]['real_name'] = $this->substr_cut($val['real_name']);
            }
        }
        $this->assign('data',$data);
        $this->assign('yesterdayList',$yesterdayList);
        $this->assign('todayList',$todayList);
        $this->display();
    }
    
    /**
    * 520活动
    * @date: 2017-5-17 下午6:17:29
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170517(){
        $this->display();
    }
    
    /**
    * 端午活动
    * @date: 2017-5-23 下午3:23:57
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170523(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        if($mobile == 'null' || $mobile == '(null)') $mobile = '';
        $investTotal = '';//累计投资
        $cashCouponTotal = '';//总的返现金额
        $myRanking = 0;
        
        $hdStauts = 0;
        
        
        $apiUrl = 'http://server.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getDragonBoatFestivalActivity.htm';
        $dd =array('mobile'=>$mobile);
        $res = json_decode($this->send_post($apiUrl, $dd),true);
        //print_r($res);exit;
        $cashCouponAwards = '';
        if($res['code'] == 0){
            
            $investTotal = $res['result']['investTotal'];
            $cashCouponTotal = $res['result']['cashCouponTotal'];
            $myRanking = $res['result']['myRanking'];
            
            if(time() > ($res['result']['endTime']['time'])/1000) {
                $hdStauts = 1;
            }
            
            if(time() < ($res['result']['startTime']['time'])/1000) {
                $hdStauts = 2;
            }
            
            
            if($myRanking == 0){
                $myRanking = '您暂未投资';
            } else if($myRanking >100){
                $myRanking = '您的排名在百名之外';
            } else{
                $myRanking ='第'.$myRanking.'名';
            }
            
            $cashCouponAwards = $res['result']['cashCouponAwards'];
            $i = 0;
            foreach ($cashCouponAwards as $v) {
                $cashCouponAwardCounts['pos_'.$i] = $v['count'];
                $i++;
            }
            
            $investLists = $res['result']['investLists'];
            
            $rank = 1;
            foreach ($investLists as $key => $val) {
                if($rank == 1){
                    $prize = '小米平衡车';
                } else if($rank == 2){
                    $prize = '扫地机器人';
                } else if($rank == 3) {
                    $prize = '小米净化器';
                } else if($rank >=4 && $rank<7){
                    $prize = '千足金1g';
                } else {
                    $prize = '150元京东卡';
                }
                $investLists[$key]['rank'] = $rank ++;
                $investLists[$key]['prize'] = $prize;
                
                if($val['userName'] == '') {
                    $info = $this->ghostaccount($val['userId']);
                    if($info) {
                        $val['userName'] = $info[1];
                    }
                }
                $investLists[$key]['userName'] = $this->substr_cut($val['userName']);
            }
        }
         
        $this->assign('cashCouponAwardCounts',$cashCouponAwardCounts);
        $this->assign('investLists',$investLists);
        $this->assign('hdStatus',$hdStauts);
        $this->assign('investTotal',$investTotal);
        $this->assign('cashCouponTotal',$cashCouponTotal);
        $this->assign('myRanking',$myRanking);
        $this->assign('mobile',$mobile);
        $this->display('Activity/event20170523/index');
    }
    
    
    public function event20170523prize(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        $hdStauts = 0;
        $apiUrl = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getDragonAwardLog.htm';
        $dd =array('mobile'=>$mobile);
        $res = json_decode($this->send_post($apiUrl, $dd),true);
        $list = '';
        if($res['code'] == 0){
           if(time() > ($res['result']['endTime']['time'])/1000) {
                $hdStauts = 1;
           }
            
           $list = $res['result']['sLotteryLogs'];  
           foreach ($list as $key => $val){
               $list[$key]['createTime'] = date("m-d H:i:s",$val['createTime']);
           }
        }
        $this->assign('list',$list);
        $this->assign('mobile',$mobile);
        $this->assign('hdStatus',$hdStauts);
        $this->display('Activity/event20170523/prize');
    }
    
    
    /**
     * 存管活动
     * @date: 2017-5-17 下午6:17:29
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170526(){
        $this->display();
    }
    
    
    /**
     * 6.1活动
     * @date: 2017-5-17 下午6:17:29
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170527(){
        $this->display();
    }
    
    
    /**
     * 热辣六月
     * @date: 2017-6-10 上午11:51:05
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170610(){
        $mobile = trim(I('get.mobile','','strip_tags'));
        $i_invest_amount = 0;
        $total_invest_money = 0;
        $income_amount = 0;
    
        $start_time = '2017-06-10 10:00:00';
        $end_time = '2017-07-01';
    
        $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential !=1 and s.duration_day>=60
                and s.add_time >='$start_time'
                and s.add_time <'$end_time'
                and p.is_delete = 0";
        $ret = M()->query($sql);
        if($ret) {
            if($ret[0]['amount']>0){
                $total_invest_money = $ret[0]['amount'];
            } else{
                $total_invest_money = 0;
            }
        }
    
        if($mobile) {
            $uid = M('User')->where(array('username'=>$mobile))->getField('id');
            if($uid) {
                $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                    where p.id = s.project_id and p.new_preferential !=1 and s.duration_day>=60
                    and s.add_time >='$start_time'
                    and s.add_time <'$end_time'
                    and p.is_delete = 0
                    and s.user_id = $uid";
    
                $ret = M()->query($sql);
                if($ret) {
                    if($ret[0]['amount']>0){
                        $i_invest_amount = $ret[0]['amount'];
                    } else{
                        $i_invest_amount = 0;
                    }
                }
                if($i_invest_amount > 0 ) {
                    $income_amount = round($i_invest_amount / $total_invest_money * 100000,2);
                }
           }
        }
        $this->assign('i_invest_amount',$i_invest_amount);
        $this->assign('total_invest_money',$total_invest_money);
        $this->assign('income_amount',$income_amount);
        $this->display();
    }
    
    
    /**
     * 奥林匹克日
     * @date: 2017-5-11 下午2:16:50
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function event20170620(){
        $hdId = 11;
        $data['hdMsg'] = '';
        $data['hdStatus'] = 0;
        $evtInfo = M('lotteryBase')->field('id,start_time,end_time,status,tag')->where('id='.$hdId .' and is_delete=0')->find();
        if(!$evtInfo) {
            exit('活动没有配置');
        }
    
        if($evtInfo['start_time']>time()) {
            $data['hdStatus'] = 1;
            $data['hdMsg'] = '活动于'.date('m月d日 H:i',$evtInfo['start_time']).'开始';
        }
    
        if($evtInfo['end_time']<time()) {
            $data['hdStatus'] = 2;
            $data['hdMsg'] = '活动于'.date('m月d日 H:i',$evtInfo['end_time']).'结束';
        }
    
        $startTime = date("Y-m-d",$evtInfo['start_time']);
        $endTime = date("Y-m-d",$evtInfo['end_time']);
    
        $yesterdayList = '';
        $currentDate = date('Y-m-d');
    
        //活动没有开始
        if($data['hdStatus'] == 1) {
            $yesterdayList = '';
            $todayList = '';
        } else if($data['hdStatus'] == 2) {
            $todayList = '';//当天没有数据
            $yesterdayList = '';
            $days = (strtotime($currentDate) - strtotime($endTime))/86400;
            if($days<=1) {
                $yesterdaySql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$endTime' and udd.add_time<'$endTime 23:59:59.999' and pj.new_preferential= '".$evtInfo['tag']."' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $yesterdayList = M()->query($yesterdaySql);
            }
        } else {
            $tomorrow = date("Y-m-d",strtotime("+1 day"));
            $todaySql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$currentDate' and udd.add_time<'$tomorrow' and pj.new_preferential= '".$evtInfo['tag']."' and  pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
            $todayList = M()->query($todaySql);
    
            if($startTime == $currentDate ){
                $yesterdayList = "";//活动今日开启，暂无昨日记录。
            } else {
                $anteayer = date("Y-m-d",strtotime("-1 day"));
                $yesterdaySql = "SELECT  sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$anteayer' and udd.add_time<'$currentDate' and pj.new_preferential= '".$evtInfo['tag']."' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $yesterdayList = M()->query($yesterdaySql);
            }
        }
    
        if($yesterdayList) {
            foreach ($yesterdayList as $key=>$val) {
                $yesterdayList[$key]['username'] = substr_replace($val['username'],'****',3,4);
                $yesterdayList[$key]['real_name'] = $this->substr_cut($val['real_name']);
            }
        }
    
        if($todayList) {
            foreach ($todayList as $key=>$val) {
                $todayList[$key]['username'] = substr_replace($val['username'],'****',3,4);
                $todayList[$key]['real_name'] = $this->substr_cut($val['real_name']);
            }
        }
        $this->assign('data',$data);
        $this->assign('yesterdayList',$yesterdayList);
        $this->assign('todayList',$todayList);
        $this->display();
    }
    
    /**
    * 油卡活动      918电台
    * @date: 2017-6-21 下午1:28:26
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function youka(){
        $ch = trim(I('get.ch','xihuzhisheng','strip_tags'));
        $this->assign('ch',$ch);
        
        $this->display('Activity/xihuzhisheng/login');
        
        return;
        
        
        if($ch == '_918zs') {
            $this->display('918zs_login');
        } else {
            $this->display();
            
        }
    }
    
    /**
     * 推广注册
     * @date: 2017-2-4 下午2:53:31
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function youka_login() {
        $code = I('post.code','','strip_tags');
        $mobile = I('post.mobile','','strip_tags');
        //渠道
        $ch = I('post.ch','','strip_tags');
    
        if(!$code){
            $this->ajaxReturn(array('status'=>0,'info'=>'请输入手机验证码！'));
        }
    
        $sign = md5($mobile.':'.$this->key);
    
        $dd = array(
            'ch' => $ch,
            'mobile' => $mobile,
            'sign' => $sign,
            'validateCode'=>$code
        );
    
        $ret = trim($this->send_post($this->tgllbUrl, $dd));    
        
        //$ret = 'N3';
    
        if($ret == 'N2') {
            $this->ajaxReturn(array('status'=>0,'info'=>'手机验证码输入错误！'));
        } else if($ret == 'N3') {//被邀请人已注册
            $this->ajaxReturn(array('status'=>1,'info'=>'/hd/youka_succ.html?type=1&ch='.$ch));
        } else if($ret == 'Y0') { //成功
            $this->ajaxReturn(array('status'=>1,'info'=>'/hd/youka_succ.html?type=2&ch='.$ch));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'注册失败，请联系客服人员！错误码：'.$ret));
        }
    }
    
    public function youka_succ(){
        $ch = trim(I('get.ch','','strip_tags'));
        $type = I('get.type','','strip_tags');
        $is_weixin = 0;
        if(is_weixin()){
            $is_weixin = 1;
        }
        $this->assign('url',$this->_jump_url($ch));
        $this->assign('type',$type);
        $this->assign('is_weixin',$is_weixin);
        $this->assign('device_type',get_device_type());
        
        
        $this->display('Activity/xihuzhisheng/success');
        return;
        if($ch == '_918zs') {
            $this->display('918zs_result');
        } else {
            $this->display('youka_succ');
        }
    }
    
    public function ppmQixi(){
        $this->display('Activity/ppmqixi/index');
    }
    
    /**
    * 会员上线活动
    * @date: 2017-6-28 上午10:46:09
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170628(){
        
        
        $hId12 = 12;//爆款活动
        $hId13 = 13;//活动排行
        $hotStatus = 2;
        $rankStatus = 2;
        $hotEvtInfo = $this->getEvtInfo($hId12);
        
        if(!$hotEvtInfo) {
            exit('爆款活动没有配置');
        }
        if(time()>$hotEvtInfo['start_time'] && time()<$hotEvtInfo['end_time']){
            $hotStatus = 1;
        } else if(time()<$hotEvtInfo['start_time']) {
            $hotStatus = 2;
        } else if(time()>$hotEvtInfo['end_time']){
            $hotStatus = 3;
        }   
        
        $rankEvtInfo = $this->getEvtInfo($hId13);
        
        if(!$rankEvtInfo) {
            exit('排行活动没有配置');
        }
        if(time()>$rankEvtInfo['start_time'] && time()<$rankEvtInfo['end_time']){
            $rankStatus = 1;
        } else if(time()<$rankEvtInfo['start_time']) {
            $rankStatus = 2;
        } else if(time()>$rankEvtInfo['end_time']){
            $rankStatus = 3;
        }
        
        $day = date('d');
        $month = date('m');
        
        if($month == 7) {
            if($day>= 3 && $day <10){                
                $startTime = '2017-07-03';
                $endTime = '2017-07-10';
                $lastWeekSql = "";    
                $currentWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$startTime' and udd.add_time<'$endTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                
            } else if($day>=10 && $day<17){                
                $lastWeekStartTime = '2017-07-03';
                $lastWeekEndTime = '2017-07-10';    
                $currentWeekStartTime = '2017-07-10';
                $currentWeekEndTime = '2017-07-17';                
                $lastWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$lastWeekStartTime' and udd.add_time<'$lastWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $currentWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$currentWeekStartTime' and udd.add_time<'$currentWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
            } else if($day>=17 && $day<24){
                $lastWeekStartTime = '2017-07-10';
                $lastWeekEndTime = '2017-07-17';
                
                $currentWeekStartTime = '2017-07-17';
                $currentWeekEndTime = '2017-07-24';
                $lastWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$lastWeekStartTime' and udd.add_time<'$lastWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $currentWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$currentWeekStartTime' and udd.add_time<'$currentWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                
            } else if($day>=24 && $day<31){
                $lastWeekStartTime = '2017-07-17';
                $lastWeekEndTime = '2017-07-24';
                
                $currentWeekStartTime = '2017-07-24';
                $currentWeekEndTime = '2017-07-31';
                
                $lastWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$lastWeekStartTime' and udd.add_time<'$lastWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                $currentWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$currentWeekStartTime' and udd.add_time<'$currentWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
            } else{
                if(time()>$rankEvtInfo['end_time']) {
                    $lastWeekStartTime = '2017-07-24';
                    $lastWeekEndTime = '2017-07-31';
                    $lastWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$lastWeekStartTime' and udd.add_time<'$lastWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
                }
            }
        } else {
            if(time()>$rankEvtInfo['end_time']) {
                $lastWeekStartTime = '2017-07-24';
                $lastWeekEndTime = '2017-07-31';
                $lastWeekSql = "SELECT sum(udd.due_capital) as amount,udd.user_id,u.real_name,u.username from s_user_due_detail as udd left JOIN s_project as pj ON udd.project_id = pj.id LEFT JOIN s_user as u ON udd.user_id = u.id WHERE udd.user_id>0 and udd.add_time>='$lastWeekStartTime' and udd.add_time<'$lastWeekEndTime' and pj.is_delete = 0 GROUP BY udd.user_id ORDER BY SUM(udd.due_capital) DESC LIMIT 3";
            }
        }
          
        $lastWeekList = '';
        $currentWeekList = '';
        
        if($lastWeekSql) {
            $lastWeekList = M()->query($lastWeekSql);
            if($lastWeekList) {
                foreach ($lastWeekList as $key=>$val) {
                    $lastWeekList[$key]['username'] = substr_replace($val['username'],'****',3,4);
                    $lastWeekList[$key]['real_name'] = $this->substr_cut($val['real_name']);
                }
            }
        }
        
        if($currentWeekSql) {
            $currentWeekList = M()->query($currentWeekSql);
            if($currentWeekList){
                foreach ($currentWeekList as $key=>$val) {
                    $currentWeekList[$key]['username'] = substr_replace($val['username'],'****',3,4);
                    $currentWeekList[$key]['real_name'] = $this->substr_cut($val['real_name']);
                }
            }
        }
        
        $this->assign('hotStatus',$hotStatus);
        $this->assign('rankStatus',$rankStatus);
        
        $this->assign('currentWeekList',$currentWeekList);
        $this->assign('lastWeekList',$lastWeekList);
        
        $this->display();
    }
    
    
    private function getEvtInfo($hId) {
        return M('lotteryBase')->field('id,start_time,end_time,status,tag')->where('id='.$hId .' and is_delete=0')->find();
    }
    
    private function send_post($url, $post_data) {
    
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    
    private function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
    
    }
    
    private function ghostaccount($k){
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
        return $arr[$k];
    }

}