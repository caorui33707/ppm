<?php
namespace Home\Controller;
use Think\Controller;

class MobileController extends CommonController{

    private $master_company;
    private $rmbRepositoryModel;

    public function _initialize(){
        header("Content-Type: text/html;charset=utf-8");
        $this->master_company = '杭州富谦网络科技有限公司';
    }

    /**
     * 常见问题
     */
    public function faq(){
        $this->display();
    }

    /**
     * 打新股答疑
     */
    public function dxg(){
        $this->display();
    }

    /**
     * 用户协议
     */
    public function protocol(){
        $this->display();
    }

    /**
     * 微信扫一扫跳转页面
     */
    public function weixinjump(){
        if(!is_weixin()){ // 如果不是微信扫一扫
            if(get_device_type() == 'ios'){
                if(!C('APP_DOWNLOAD_IOS')){
                    redirect(C('WEB_ROOT').'/mobile/comingsoon.html');
                }else{
                    redirect(C('APP_DOWNLOAD_IOS'));
                }
            }else if(get_device_type() == 'android'){
                if(!C('APP_DOWNLOAD_ANDROID')){
                    redirect(C('WEB_ROOT').'/mobile/comingsoon.html');
                }else{
                    redirect(C('APP_DOWNLOAD_ANDROID'));
                }
            }else{
                redirect(C('WEB_ROOT').'/mobile/nosupportdevice.html');
            }
        }else{ // 如果是微信打开该页面则显示使用第三方浏览器打开界面
            $this->display();
        }
    }

    /**
     * 扫一扫下载不支持设备显示页面
     */
    public function nosupportdevice(){
        $this->display();
    }

    /**
     * 移动端查看产品描述内容
     */
    public function projectdescr(){        
        //重定向到ｃｇ．ｐｐｍｉａｏ．ｃｎ
        $project_id = I('get.id', 0, 'int'); // 产品ID
        $project_descr_type = I('get.target', 0, 'int'); // 查看描述类型(1:投资方向/2:还款来源);
        // header("Location: http://cg.ppmiao.com/mobile/projectdescr/{$project_id}/{$project_descr_type}");
        // return;
        
        
        $this->assign('device_type', get_device_type());
        if(in_array($project_descr_type, array(1,2))){
            $projectObj = M('Project');

            switch($project_descr_type){
                case 1:
                    $detail = $projectObj->field('ticket_checking,repayment_source_title,invest_direction_descr,invest_direction_image,repayment_source_descr')->where(array('id'=>$project_id,'is_delete'=>0))->find();
                    if(!$detail){
                        echo '产品不存在或已被删除';exit;
                    }
                    //$this->assign('title', $detail['invest_direction_title']);
                    $this->assign('content', $detail['invest_direction']);
                    $this->assign('repayment_from', $detail['invest_direction_descr']);
                    if($detail['invest_direction_descr']){

                        if($detail['invest_direction_descr']) $this->assign('descr', $detail['ticket_checking']);
                        if($detail['invest_direction_image']) $this->assign('image', format_project_image($detail['invest_direction_image']));						
                        if($detail['repayment_source_descr']) $this->assign('bank_image', format_project_image($detail['repayment_source_descr']));
                        $this->display('projectdescr2');exit;
                    }
                    break;
                case 2:
                    $detail = $projectObj->field('repayment_source_title,repayment_source,repayment_source_descr,repayment_source_image')->where(array('id'=>$project_id,'is_delete'=>0))->find();
                    if(!$detail){
                        echo '产品不存在或已被删除';exit;
                    }
                    //$this->assign('title', $detail['repayment_source_title']);
                    $this->assign('content', $detail['repayment_source']);
                    // 移动端显示富文本内容,PC端显示格式化内容
//                    if($detail['repayment_source_descr']){
//                        if($detail['repayment_source_descr']) $this->assign('descr', format_project_descr($detail['repayment_source_descr']));
//                        if($detail['repayment_source_image']) $this->assign('image', format_project_image($detail['repayment_source_image']));
//
//                        $this->display('projectdescr2');exit;
//                    }
                    break;
            }
            $this->display();
        }else{
            header('HTTP/1.1 403 Forbidden');
            echo "Access forbidden";
        }
    }

    /**
     * 移动端活动展示页面
     */
    public function activities(){
        $id = I('get.id', 0, 'int'); // 消息ID

        $messageObj = M('Message');
        //$messageGroupObj = M('MessageGroup');
        //$groupDetail = $messageGroupObj->where(array('id'=>$id,'is_delete'=>0))->find();
        $detail = $messageObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$detail){
            $this->display('Mobile:404');exit;
        }
        if($detail['url']){
            redirect($detail['url']);exit;
        }
        //$messageObj->where(array('id'=>$id))->setInc('views'); // 更新浏览量
        $this->assign('title', $detail['title']);
        $this->assign('content', $detail['description']);
        $this->assign('is_padding', $detail['is_padding']);
        $this->display();
    }

    /**
     * app炫耀一下
     */
    public function showoff(){
        $key = I('get.key', 0, 'strip_tags');
        if(!$key) status_403();

        $paradeObj = M('Parade');
        $info = $paradeObj->where(array('md5'=>$key))->find();
        if(!$info) status_403();
        $this->assign('money', $info['total_interest']);
        $this->display();
    }

    /**
     * 即将上线页面
     */
    public function comingsoon(){
        $this->display();
    }

    /**
     * 标合同信息页面(app端查看)
     */
    public function contract(){
        $version = $_GET['version'];
        $version = explode('.',$version);
        $versionNo = implode('',$version);

        $projectId = I('get.pid', 0, 'int');
        $userId = I('get.uid', 0, 'int');
        $dueId = I('get.did', 0, 'int');
        $type = I('get.type', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名

        if(!$projectId || !$userId || !$dueId){
            status_403();
        }
        $projectObj = M('Project');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        if(md5($projectId.$userId.$dueId.$seckey) != $sign) {
            status_403();
        }
        
        $projectInfo = $projectObj->field('id,amount,title,start_time,end_time,user_interest')->where(array('id'=>$projectId))->find();
        
        //$projectInfo['nickname'] =$this->substr_cut($projectInfo['real_name']);
        
        if(!$projectInfo) {
            status_403();
        }
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$dueId,'project_id'=>$projectId,'user_id'=>$userId))->find();
//        var_dump($userDueDetailInfo);
        if(!$userDueDetailInfo) {
            
            status_403();
        }

        $userDueDetailInfo['uinfo'] = $userObj->field('username,real_name,card_no')->where(array('id'=>$userId))->find();
        //$userDueDetailInfo['uinfo']['real_name'] = $this->substr_cut($userDueDetailInfo['uinfo']['real_name']);
        //$userDueDetailInfo['uinfo']['username'] = substr($userDueDetailInfo['uinfo']['username'], 0, 3) . "****" . substr($userDueDetailInfo['uinfo']['username'], 7, 4);
		
		//通过 projectId 取合同id
		$contractProjectOne = M('ContractProject')->field('contract_id')->where(array('project_id'=>$projectId))->find();
		//通过合同id,取金额,票号
		$contractInfo = M('Contract')->field('price,ticket_number')->where(array('id'=>$contractProjectOne['contract_id']))->find();
		//取订单号
		$rechargeLogOne = M('RechargeLog')->field('recharge_no')->where(array('project_id'=>$projectId,'user_id'=>$userId))->find();
		
		
		$projectInfo['financing'] = '**'.mb_substr($projectInfo['financing'], 2,2,'utf-8').'******';
		
		$projectInfo['legal_person'] = mb_substr($projectInfo['legal_person'], 0,1,'utf-8').'**';
		
		$projectInfo['legal_location'] =  mb_substr($projectInfo['legal_location'], 0,6,'utf-8').'**********';
		
		//$projectInfo['idcard'] =  mb_substr($projectInfo['idcard'], 0,-4,'utf-8').'****';
		
		
		$this->assign('contractInfo', $contractInfo);
		
		$this->assign('rechargeLog', $rechargeLogOne);
        $this->assign('detail', $projectInfo);
        $this->assign('due_detail', $userDueDetailInfo);

        $add_time = strtotime($userDueDetailInfo['add_time']);
        $up_time = strtotime('2017-06-28 11:00:00');//代码上线时间

        if($add_time > $up_time){
            $company['legal_person'] = '唐玉矿';
            $company['legal_location']='浙江省杭州市拱墅区康桥路108号1幢二层2018室';
        }else{
            $company['legal_person'] = '吴**';
            $company['legal_location']='杭州市西湖区中天MCCⅡ座1307室';
        }
        $this->assign('company', $company);


        if($add_time > $up_time){
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') && ($versionNo >= 100 && $versionNo <= 124)){
                $this->display();
            }else{
                if($type){
                    $contractid = $userDueDetailInfo['protocolid'];
                }else{
                    $contractid = $userDueDetailInfo['contractid'];
                }
                //
                if($contractid != ''){
//                    $url = 'http://contract.ppmiao.com/contract/'.$userDueDetailInfo['contractid'].'/show';
                    $url = 'http://contract.yufa.ppmiao.com/contract/'.$contractid.'/show';
                    echo file_get_contents($url);
                }else{

                    if($add_time > $up_time){
                        sleep(3);
                        $new_due = $userDueDetailObj->where(array('id'=>$dueId,'project_id'=>$projectId,'user_id'=>$userId))->find();

                        if($type){
                            $contractid = $new_due['protocolid'];
                        }else{
                            $contractid = $new_due['contractid'];
                        }

//                        $url = 'http://contract.ppmiao.com/contract/'.$new_due['contractid'].'/show';
                        $url = 'http://contract.yufa.ppmiao.com/contract/'.$contractid.'/show';
                        echo file_get_contents($url);
                    }else{
                        $this->display();
                    }



                }


            }
        }else{
            $this->display();
        }




    }
    
    /**
    * 产品购买协议
    * @date: 2016-12-27 下午5:18:01
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function buy_contract() {
        $projectId = I('get.pid', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名
        
        if(!$projectId){
            status_403();
        }
        
        $projectObj = M('Project');

        if(md5($projectId.$seckey) != $sign) {
            status_403();
        }
        /*
        $url = "https://www.ppmiao.com?c=Mobile&a=tmp_buy_contract&pid={$projectId}&sign={$sign}";
        
        //$json = substr(file_get_contents($url),3);
        
        $json = substr(file_get_contents($url),3);
        
        $arr=json_decode($json,true); 
        
        if($arr['stauts'] == 1) {
            $this->assign('contractInfo', $arr['contractInfo']);
            $this->assign('detail', $arr['detail']);
            $this->display();
        } else {
            status_403();
        }
        */
        //print_r($arr);
        
        $projectInfo = $projectObj->field('id,amount,title,start_time,end_time,user_interest,fid')->where(array('id'=>$projectId))->find();
        if(!$projectInfo) {
            status_403();
        }
        
        //通过 projectId 取合同id
        $contractProjectOne = M('ContractProject')->field('contract_id')->where(array('project_id'=>$projectId))->find();
        //通过合同id,取金额,票号
        $contractInfo = M('Contract')->field('price,ticket_number')->where(array('id'=>$contractProjectOne['contract_id']))->find();
        
        $financing = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['fid'])->find();
        
        $projectInfo['financing'] = '**'.mb_substr($projectInfo['financing'], 2,2,'utf-8').'******';
        $projectInfo['legal_person'] = mb_substr($financing['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['legal_location'] =  mb_substr($financing['address'], 0,6,'utf-8').'**********';        
        $projectInfo['idcard'] = $financing['license'];        
        $this->assign('contractInfo', $contractInfo);
        $this->assign('detail', $projectInfo);
        $this->display();
    }
    
    public function tmp_buy_contract(){
        
        $ret = array();
        
        $projectId = I('get.pid', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名
        
        
        $projectObj = M('Project');

       
        
        $projectInfo = $projectObj->field('id,amount,title,type,start_time,end_time,user_interest,financing,fid')->where(array('id'=>$projectId))->find();
        if(!$projectInfo) {
            $ret['stauts'] = 0;            
            echo json_encode($ret);
            exit;
        }
        
        //通过 projectId 取合同id
        $contractProjectOne = M('ContractProject')->field('contract_id')->where(array('project_id'=>$projectId))->find();
        //通过合同id,取金额,票号
        $contractInfo = M('Contract')->field('price,ticket_number')->where(array('id'=>$contractProjectOne['contract_id']))->find();
        
        $financing = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['fid'])->find();
        
        $projectInfo['financing'] = '**'.mb_substr($projectInfo['financing'], 2,2,'utf-8').'******';
        $projectInfo['legal_person'] = mb_substr($financing['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['legal_location'] =  mb_substr($financing['address'], 0,6,'utf-8').'**********';        
        $projectInfo['idcard'] = $financing['license'];        
        
        $ret['stauts'] = 1;
        $ret['contractInfo'] = $contractInfo;
        $ret['detail'] = $projectInfo;
        
        echo json_encode($ret);
        exit;
    }
    
    
    /**
     * 钱包购买协议
     * @date: 2017-01-20 
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function buy_wallet() {
        $type = I('get.type', 0, 'strip_tags'); //购买前，1购买后
        $user_id = I('get.user_id', 0, 'int');
        $amount = I('get.amount', 0, 'strip_tags');
        $sign = I('get.sign', '', 'strip_tags'); // 签名
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        
        if(md5($type.$seckey.$amount.$user_id) != $sign) {
            
            \Think\Log::write($type.$seckey.$amount.$user_id,'INFO');
            \Think\Log::write('签名不正确','INFO');
            status_403();
        }
        
        if(!$user_id || !$sign) {
            \Think\Log::write('参数不完整','INFO');
            status_403();
        }        
        $user = '';
        $date = '';
        if ($type == 1) {        
            $user = M('User')->field('username,real_name,card_no')->where('id='.$user_id)->find();
            $date = date("Y 年 m 年 d 日");
        } 
        
        $this->assign('params',array('user'=>$user,'amount'=>$amount,'date'=>$date));
        $this->display();
    }
    
    /**
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     * @param string $user_name 姓名
     * @return string 格式化后的姓名
     */
    public function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

    }
    /**
     * 股权类产品回购协议
     */
    public function protocols(){
        $projectId = I('get.pid', 0, 'int');
        $userId = I('get.uid', 0, 'int');
        $dueId = I('get.did', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名

        if(!$projectId || !$userId || !$dueId) status_403();
        $projectObj = M('Project');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        if(md5($projectId.$userId.$dueId.$seckey) != $sign) status_403();
        $projectInfo = $projectObj->field('id,amount,nickname,title,type,start_time,end_time,user_interest,idcard')->where(array('id'=>$projectId,'type'=>139))->find();
        if(!$projectInfo) status_403();
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$dueId,'project_id'=>$projectId,'user_id'=>$userId))->find();
        if(!$userDueDetailInfo) status_403();

        $userDueDetailInfo['uinfo'] = $userObj->field('username,real_name,card_no')->where(array('id'=>$userId))->find();
        $projectModelEquityObj = M('ProjectModelEquity');
        $detailExt = $projectModelEquityObj->where(array('project_id' => $projectId))->find();
        $this->assign('detail_ext', $detailExt);
        $this->assign('detail', $projectInfo);
        $this->assign('due_detail', $userDueDetailInfo);
        $this->display();
    }

    /**
     * 银行限额说明页面
     */
    public function bank_limit(){
        $app_ver = I('get.app_ver','','strip_tags');
        if(!$app_ver) {
            $ver = I('get.version','','strip_tags');
            if ($ver) {
                $exist = strstr($ver, 'app_ver');
                if($exist) {
                    $fied = 'bank_name,limit_day,limit_once,limit_month,image';
                    $bankLimitObj = M('bankLimit');
                    $this->assign('llList',$bankLimitObj->field($fied)->where('pay_id=1')->select());
                    $this->assign('bfList',$bankLimitObj->field($fied)->where('pay_id=2')->select());
                    $this->display('new_bank_limit');
                } else{
                    $this->display();
                }
            } else{
                $this->display();
            }
        } else {
            $fied = 'bank_name,limit_times,limit_day,limit_month,small_icon,status';
            $list = M('Bank')->field($fied)->select();
            foreach ($list as $k=>$v){
                if ($v['limit_times']) {
                    $list[$k]['limit_times'] = is_numeric($v['limit_times']) ? $v['limit_times']/10000 . '万' : $v['limit_times'];
                } else {
                    $list[$k]['limit_times'] = '-';
                }

                if ($v['limit_day']) {
                    $list[$k]['limit_day'] = is_numeric($v['limit_day']) ? $v['limit_day']/10000 . '万' : $v['limit_day'];
                } else {
                    $list[$k]['limit_day'] = '-';
                }

                if ($v['limit_month']) {
                    $list[$k]['limit_month'] = is_numeric($v['limit_month']) ? $v['limit_month']/10000 . '万' : $v['limit_month'];
                } else {
                    $list[$k]['limit_month'] = '-';
                }
            }
            $this->assign('List',$list);
            $this->display('new_bank_limit');
        }
    }

    /**
     * 钱包说明页面
     */
    public function wallet(){
		
        $this->display();
    }


    /**
     * 票据质押借款担保协议
     * @date: 2018-01-26 下午5:00:01
     * @author: c r
     * @param: variable
     * @return:
     */
    public function borrow_guaranty_contract() {
        $projectId = I('get.pid', 0, 'int');
        $uid = I('get.uid', 0, 'int');
        $dueId = I('get.did', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名

        if(!$projectId){
            status_403();
        }

        $projectObj = M('Project');
       // dump(md5($projectId.$seckey));
        if(md5($projectId.$seckey) != $sign) {
            status_403();
        }

        $projectInfo = $projectObj->field('id,amount,title,start_time,end_time,user_interest,fid,gid,contract_interest,draft_type')->where(array('id'=>$projectId))->find();

        if(!$projectInfo) {
            status_403();
        }

        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$dueId,'project_id'=>$projectId,'user_id'=>$uid))->find();
//        if(!$userDueDetailInfo) {
//            status_403();
//        }

        //通过 projectId 取合同id
        $contractProjectOne = M('ContractProject')->field('contract_id')->where(array('project_id'=>$projectId))->find();
        //通过合同id,取金额,票号
        $contractInfo = M('Contract')->field('name,price,ticket_number')->where(array('id'=>$contractProjectOne['contract_id']))->find();

        $financing = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['fid'])->find();

        $guaranty = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['gid'])->find();//担保 信息

        $projectInfo['financing'] = '**'.mb_substr($projectInfo['financing'], 2,2,'utf-8').'******';
        $projectInfo['legal_person'] = mb_substr($financing['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['guarant_person'] = mb_substr($guaranty['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['legal_location'] =  mb_substr($financing['address'], 0,6,'utf-8').'**********';
        $projectInfo['idcard'] = $financing['license'];
        $projectInfo['capital_amount'] = $this->num2rmb($projectInfo['amount']);
        $projectInfo['interest'] = $userDueDetailInfo['interest_coupon']+ $projectInfo['contract_interest'];

        if(in_array($projectInfo['draft_type'],array(0,1))){
            $projectInfo['draft_number'] = 1;
        }else{
            $projectInfo['draft_number'] = 2;
        }


        $real_name = '';
        if($uid){
            $real_name = M('user')->where('id ='.$uid)->getField('real_name');
        }
        $projectInfo['real_name'] = $real_name;
        $projectInfo['master_company'] = $this->master_company;

        $this->assign('contractInfo', $contractInfo);
        $this->assign('detail', $projectInfo);
        $this->assign('due_detail',$userDueDetailInfo);
        $this->display();
    }

    /*
     * 汇票质押委托协议
     * @date: 2018-01-29 上午11:11:18
     * @author: c r
     * @param: variable
     * @return:
     * */
    public function entrust_agreement(){
        $projectId = I('get.pid', 0, 'int');
        $uid = I('get.uid', 0, 'int');
        $dueId = I('get.did', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名
        if(!$projectId){
            status_403();
        }

        $projectObj = M('Project');

        if(md5($projectId.$seckey) != $sign) {
            status_403();
        }

        $projectInfo = $projectObj->field('id,amount,accepting_bank,title,start_time,end_time,user_interest,fid,gid')->where(array('id'=>$projectId))->find();
        if(!$projectInfo) {
            status_403();
        }

        //通过 projectId 取合同id
        $contractProjectOne = M('ContractProject')->field('contract_id')->where(array('project_id'=>$projectId))->find();
        //通过合同id,取金额,票号
        $contractInfo = M('Contract')->field('name,price,ticket_number,end_time')->where(array('id'=>$contractProjectOne['contract_id']))->find();

        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$dueId,'project_id'=>$projectId,'user_id'=>$uid))->find();
//        if(!$userDueDetailInfo) {
//            status_403();
//        }

        $financing = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['fid'])->find();

        $real_name = '';
        if($uid){
            $real_name = M('user')->where('id ='.$uid)->getField('real_name');
        }
        $projectInfo['real_name'] = $real_name;
        $projectInfo['legal_person'] = mb_substr($financing['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['master_company'] = $this->master_company;
        $this->assign('detail', $projectInfo);
        $this->assign('due_detail',$userDueDetailInfo);
        $this->assign('contractInfo', $contractInfo);
        $this->display();
    }

    /*
     * 票据质押借款协议
     * @date: 2018-01-29 下午06:11:18
     * @author: c r
     * @param: variable
     * @return:
     */
    public function bill_borrow_contract(){
        $projectId = I('get.pid', 0, 'int');
        $uid = I('get.uid', 0, 'int');
        $dueId = I('get.did', 0, 'int');
        $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
        $sign = I('get.sign', '', 'strip_tags'); // 签名

        if(!$projectId){
            status_403();
        }

        $projectObj = M('Project');

        if(md5($projectId.$seckey) != $sign) {
            status_403();
        }

        $projectInfo = $projectObj->field('id,amount,title,start_time,end_time,user_interest,fid,gid,contract_interest,draft_type')->where(array('id'=>$projectId))->find();
        if(!$projectInfo) {
            status_403();
        }

        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$dueId,'project_id'=>$projectId,'user_id'=>$uid))->find();
//        if(!$userDueDetailInfo) {
//            status_403();
//        }

        //通过 projectId 取合同id
        $contractProjectOne = M('ContractProject')->field('contract_id')->where(array('project_id'=>$projectId))->find();
        //通过合同id,取金额,票号
        $contractInfo = M('Contract')->field('price,ticket_number')->where(array('id'=>$contractProjectOne['contract_id']))->find();

        $financing = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['fid'])->find();

        $guaranty = M('financing')->field('address,license,legal_person')->where('id='.$projectInfo['gid'])->find();//担保 信息

        $projectInfo['financing'] = '**'.mb_substr($projectInfo['financing'], 2,2,'utf-8').'******';
        $projectInfo['legal_person'] = mb_substr($financing['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['guarant_person'] = mb_substr($guaranty['legal_person'], 0,1,'utf-8').'**';
        $projectInfo['legal_location'] =  mb_substr($financing['address'], 0,6,'utf-8').'**********';
        $projectInfo['idcard'] = $financing['license'];
        $projectInfo['capital_amount'] = $this->num2rmb($projectInfo['amount']);
        $projectInfo['interest'] = $userDueDetailInfo['interest_coupon']+ $projectInfo['contract_interest'];

        if(in_array($projectInfo['draft_type'],array(0,1))){
            $projectInfo['draft_number'] = 1;
        }else{
            $projectInfo['draft_number'] = 2;
        }

        $real_name = '';
        if($uid){
            $real_name = M('user')->where('id ='.$uid)->getField('real_name');
        }
        $projectInfo['real_name'] = $real_name;
        $projectInfo['master_company'] = $this->master_company;

        $this->assign('contractInfo', $contractInfo);
        $this->assign('detail', $projectInfo);
        $this->assign('due_detail',$userDueDetailInfo);
        $this->display();
    }

}