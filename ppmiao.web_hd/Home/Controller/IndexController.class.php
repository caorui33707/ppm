<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends BaseController {
    
    
    private $pagesize = 6;

    public function index(){
        if(is_mobile()) {
            redirect(C('MOBILE_SITE'));
            exit;
        }
        //当天日期
        $date = date("Y-m-d",time());
        //零钱喵年化利率
        $UserWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        $rate_data = $UserWalletAnnualizedRateObj->where(array('add_time'=>$date))->find();
        $rate = ($rate_data['rate'])?$rate_data['rate']:6.35;
        //获取最新上架的产品
        $projectObj = M('Project');
        $conditions = 'status=2 and is_delete=0 and (start_time<=\''.date('Y-m-d H:i:s', time()).'\' or is_countdown=1)';
        $fields = 'id,user_interest';
        $list = $projectObj->field($fields)->where($conditions)->order('status asc,user_interest desc,duration asc,add_time desc')->limit(5)->select();
        foreach($list as $k => $v){
            $list[$k]['id'] = st_encrypt($v['id'], C('PRODUCT_KEY'));
            $list[$k]['user_interest'] = $v['user_interest'];
            $list[$k]['project_id'] = $v['id'];
        }
        
        
        //行业资询
        
        $news_list = M('WebContent')->field('id,title,intro,img,add_time')->where('type=2 and is_delete = 0')->order('add_time desc')->limit(3)->select();
        
        $ret_news_list = array();
        $ret_notice_list = array();
        if($news_list) {
            foreach ($news_list as $val){
                $val['add_time'] = date("",$val['add_time']);
                $ret_news_list[] = $val;
            }
            unset($news_list);
        }
        
        
        //网站公告
        $notice_list = M('WebContent')->field('id,title,add_time')->where('type=4 and is_delete = 0')->order('add_time desc')->limit(7)->select();
        
        if($notice_list) {
            foreach ($notice_list as $val){
                $val['add_time'] = date("",$val['add_time']);
                $ret_notice_list[] = $val;
            }
            unset($notice_list);
        }
        
        $_now = date("Y-m-d H:i:s");
        
        $projectlist = $projectObj->field('id,title,user_interest,money_min,new_preferential,accepting_bank,end_time')
                                ->where("status=2 and is_delete=0 and user_interest <=12 and start_time <='$_now'")->order('id desc')->limit(4)->select();
        $project_list = array();
        if($projectlist) {
            foreach ($projectlist as $val) {
                $val['id'] = st_encrypt($val['id'], C('PRODUCT_KEY'));
				$val['day'] = $this->count_days($val['end_time']);
                $project_list[] = $val;
            }
            unset($projectlist);
        }
        
        $web_base = M('WebBase')->where('id=1')->find();
        $web_base['total_user'] += M('User')->count();
        
        $res = array(
            'news_list' => $ret_news_list,
            'notice_list' => $ret_notice_list,
            'project_list'=> $project_list,
            'web_base'=>$web_base,
        );
        $this->assign("res",$res);
        $this->assign('year_rate',$rate);
        $this->assign('list',$list);
        $this->assign('meta_title', '票票喵 - 安全的网上理财平台');
        $this->assign('meta_keywords', '票票喵，理财，基金，理财产品，手机理财软件');
        $this->assign('meta_description', '票票喵是互联网金融理财的综合平台，通过手机理财软件，操作便捷，包含基金类产品，股票类产品，家庭理财类产品，股权配资类产品等等相关的金融理财服务');
        $this->assign("path",'index');
        $this->display();
    }
    /**
     * 安全保障
     */
    public function security(){
        $this->assign('meta_title', '安全保障');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'security');
        $this->display('security');
    }
    /**
     * 理财推荐
     */
    public function recommend(){
        redirect(C('WEB_ROOT').'/introduction.html');
    }
    
    public function about(){
        $this->assign('meta_title', '关于票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'about');
        $this->display('about');
    }

    /**
     * 加入我们
     */
    public function join(){
        $this->assign('meta_title', '加入我们 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'contact');
        $this->display('join');
    }

    /**
     * 更多职位
     */
    public function jobs(){
        $this->assign('meta_title', '更多职位 - 票票喵');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display();
    }

    /**
     * 联系我们
     */
    public function contact(){
        $this->assign('meta_title', '联系我们');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'contact');
        $this->display('contact');
    }

    
    /*
     * 投诉见意 
     * 
     */
    public function complaint(){
        
        if(IS_POST){
            
            if (!IS_AJAX) exit;
            
            $content = trim(I('post.content', '', 'strip_tags'));
            $phone = trim(I('post.phone', '', 'strip_tags'));
            if(!$content){
                $this->ajaxReturn(array('status' => 0, 'info' => '您还没有输入吐槽 内容~'));
            }
            if(!$phone) {
                $this->ajaxReturn(array('status' => 0, 'info' => '您的手机号没有输入有误'));
            }
            
            $rows = array(
                'content' => $content,
                'phone' => $phone,
                'add_time' => time(),
            );
            
            if(!M('WebComplaint')->add($rows)){
                $this->ajaxReturn(array('status' => 0, 'info' => "吐槽失败..."));
            }else{
                $this->ajaxReturn(array('status' => 1, 'info' => "吐槽成功..."));
            }
           
        } else {
            $res = array(
                'help_list_5' => $this->getHelpList(5)
            );
            $this->assign("res",$res);
            $this->assign('meta_title', '投诉见意 - 票票喵 ');
            $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
            $this->assign('meta_description', '票票喵');
            $this->assign("path",'contact');
            $this->display('complaint');
        }
        
        
    }

    /**
     * 常见问题
     */
    public function question(){
        $this->assign('meta_title', '常见问题');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->assign("path",'question');
        $this->display('PMQuestion');
    }

    /**
     * 法律监督
     */
    public function law(){
        $this->assign('meta_title', '法律监督');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

    /**
     * 公司资质
     */
    public function qualifications(){
        $this->assign('meta_title', '公司资质');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

    /**
     * 合作银行
     */
    public function bank(){
        $this->assign('meta_title', '合作银行');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

    /**
     * 合作机构
     */
    public function institution(){

        $this->display('PublicNew:404');
    }

    /**
     * 隐私保护
     */
    public function protection(){
        $this->assign('meta_title', '隐私保护');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display('PMPrivacy');
    }

    /**
     * 服务条款
     */
    public function provision(){
        $this->assign('meta_title', '服务条款');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display('PMService');
    }

    /**
     * 用户协议
     */
    public function protocol(){
        $this->assign('meta_title', '用户协议');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

    /**
     * ios上架APP Store专用隐私政策页面
     */
    public function privacy(){
        $this->assign('meta_title', '隐私政策 - 石头理财');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }
   

    /**
     * APP二维码下载
     */
    public function qrcode(){

        $deviceType = get_device_type();
		
        switch($deviceType){
            case 'ios':			
                redirect(C('APP_DOWNLOAD_MICRO'));
                break;
            case 'android':
                if(is_weixin()){
                    redirect(C('APP_DOWNLOAD_WEIXIN'));
                }else{
                    redirect(C('APP_DOWNLOAD_ANDROID'));
                }
                break;
            default:
                redirect(C('WEB_ROOT'));
        }
    }
    
    /**
     * 帮助中心
     */
    public function help(){
        
        /*
         *  6 => '帮助中心 - 票票喵',
        7 => '帮助中心 - 零钱喵',
        8 => '帮助中心 - 注册登录',
        9 => '帮助中心 - 充值提现',
        10 => '帮助中心 - 安全保障'
        
         */
        
        $res = array(
            'help_list_5' => $this->getHelpList(5),
            'help_list_6' => $this->getHelpList(6),
            'help_list_7' => $this->getHelpList(7),
            'help_list_8' => $this->getHelpList(8),
            'help_list_9' => $this->getHelpList(9),
            'help_list_10' => $this->getHelpList(10)
        );
        
        
        
 
        $this->assign('meta_title', '帮助中心 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'contact');
        
        $this->assign("res",$res);
        
        $this->display('help');
    }
    
    private function getHelpList($type){
        
        $cond = "type = $type and is_delete = 0 ";
        
        //还款公告
        if($type == 5) {
            $time = time() - 86400 * 60;
            $cond .= ' and add_time >'.$time;
        }
        
        $list =  M('WebContent')->field('title,content')->where($cond)->order('add_time desc')->select();
        
        return $list;
    
    }
    
    /**
     *  服务条款 
     */
    public function service(){
        $this->assign('meta_title', '服务条款');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'join');
        $this->display('service');
    }
    
    /**
     *  企业动态
     */
    public function dynamic(){
        
        $cond = 'type = 1 and is_delete = 0';
        
        $dynamic_list =  M('WebContent')->field('id,title,content,intro,add_time')->where($cond)->order('add_time desc')->limit(36)->select();
        
        $list = array();
        if($dynamic_list) {
            foreach ($dynamic_list as $val) {
                $val['add_time'] = date("m-d",$val['add_time']);
                $list[] = $val;
            }
            unset($dynamic_list);
        }
        $res = array(
            'list' => $list,
            'help_list_5' => $this->getHelpList(5)
        );

        $this->assign("res",$res);
        $this->assign('meta_title', '企业动态 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'contact');
        $this->display('dynamic');
    }
    
    /**
     * 行业资讯
     */
    public  function news(){
        
        $page = I('get.p', 1, 'int'); // 分页页码
        
        $cond = 'type=2 and is_delete=0';
        
        $totalCount = M('WebContent')->where($cond)->count();
        
        $Page = new \Think\PageCustomer($totalCount, $this->pagesize);
        
        $show = $Page->show(C('WEB_ROOT').'/news.html?p=');
        
        $list = M('WebContent')->field('id,title,intro,img,add_time')->where($cond)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $ret = array();
        
        if($list) {
            foreach ($list as $val) {
                $val['add_time'] = date("Y-m-d",$val['add_time']);
                $ret[] = $val;
            }
        }
        
        unset($list);
        
        $res = array(
            'help_list_5' => $this->getHelpList(5),
            'list' => $ret,
            'show'=>$show
        );
        
        $this->assign("res",$res);
        $this->assign('meta_title', '行业资讯 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'contact');
        $this->display('news');
    }
    
    /**
     * 票据百科
     */
    public function wiki(){
        
        $page = I('get.p', 1, 'int'); // 分页页码

        $totalCount = M('WebContent')->where('type=3 and is_delete=0')->count();
        
        $Page = new \Think\PageCustomer($totalCount, $this->pagesize); // 自定义分页类
        
        $show = $Page->show(C('WEB_ROOT').'/wiki.html?p=');
        
        $list = M('WebContent')->field('id,title,intro,img,add_time')->where('type=3 and is_delete = 0')->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $ret = array();
        
        if($list) {
            foreach ($list as $val) {
                $val['add_time'] = date("Y-m-d",$val['add_time']);
                $ret[] = $val;
            }
        }
        
        unset($list);
        
        $res = array(
            'help_list_5' => $this->getHelpList(5),
            'list'=>$ret,
            'show'=>$show
        );
        
        $this->assign("res",$res);
        $this->assign('meta_title', '票据百科 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'contact');
        $this->display('wiki');
    }
    /**
     * 理财产品
     */
    public function product(){
        
        $date = date("Y-m-d",time());
        //零钱喵年化利率
        $UserWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        $rate_data = $UserWalletAnnualizedRateObj->where(array('add_time'=>$date))->find();
        $rate = ($rate_data['rate'])?$rate_data['rate']:6.35;
        
        $now = date("Y-m-d H:i:s");
        
        //进行中的
        $project_list = M('Project')->field('id,title,amount,duration,user_interest,money_min,new_preferential,percent,end_time')
                ->where("status=2 and is_delete=0 and user_interest <=12 and start_time <='$now'")->limit(3)
                ->order('id desc')
                ->select();
        
        $list = array();
        if($project_list) {
            foreach ($project_list as $val) {
                $val['id'] = st_encrypt($val['id'], C('PRODUCT_KEY'));
				
				$val['day'] = $this->count_days($val['end_time']);
				
                $list[] = $val;
            }
            unset($project_list);
        }
        
        //已售罄
        $soldout_list = M('Project')->field('title,amount,duration,user_interest,money_min')->where('status=3 and user_interest <=12 and soldout_time !=""')->order('soldout_time desc')->limit(3)->select();
        //已还款
        $expire_list = M('Project')->field('title,amount,duration,user_interest,money_min')->where('status=5 and user_interest <=12')->order('repayment_time desc')->limit(3)->select();
        
        $res = array(
            'list'=>$list,
            'expire_list'=>$expire_list,
            'soldout_list'=>$soldout_list,
            'web_base'=>M('WebBase')->find(),
        );
        
        $this->assign("res",$res);
        $this->assign("rate",$rate);
        $this->assign('meta_title', '理财产品 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'product');
        $this->display();
    }
    
    /*
     * 零钱
     */
    public function change(){
        //当天日期
        $date = date("Y-m-d",time());
        //零钱喵年化利率
        $UserWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        $rate_data = $UserWalletAnnualizedRateObj->where(array('add_time'=>$date))->find();
        $rate = ($rate_data['rate'])?$rate_data['rate']:6.35;
        
        $this->assign('meta_title', '零钱喵 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("rate",$rate);
        $this->assign("path",'product');
        $this->display();
    }
    
    /*
     * 零钱
     */
    public function buy(){
        
        $data = filterSpecialChar(I('get.id', '', 'strip_tags'));
        $from = trim(filterSpecialChar(I('get.from', '', 'strip_tags')));
        
        if($from == '1') { //1为第三方
            $id = (int)$data;
        } else {
            $id = st_decrypt($data, C('PRODUCT_KEY'));
            if(!is_numeric($id)) { // 解密出来非数字则为错误页面
                $this->display('Public:404');exit;
            }
        }
        
        $projectInfo = M("Project")->field('title,amount,able,user_interest,money_min,percent,new_preferential,end_time,accepting_bank')->where('id='.$id)->find();

        if(!$projectInfo) {
            $this->display('Public:404');exit;
        }
        
        $repayment_time = M('RepaymentDetail')->where('project_id='.$id)->getField('repayment_time');
        
        $repayment_time = explode(".", $repayment_time);
        
        $projectInfo['repayment_time'] = $repayment_time[0];
		
		$projectInfo['day'] = $this->count_days($projectInfo['end_time']);
		
        
        $projectInfo['total_people'] = M('UserDueDetail')->where('project_id='.$id)->count();
        
        $this->assign('projectInfo', $projectInfo);
        $this->assign('meta_title', $projectInfo['title'].'零钱喵 - 票票喵');
        $this->assign('meta_keywords', '票票喵,P2P金融,理财,理财产品');
        $this->assign('meta_description', '票票喵');
        $this->assign("path",'product');
        $this->display();
    }
    
    public function detail(){
        $id = I('get.id', '1', 'int');
        
        $info = M('WebContent')->where('id='.$id .' and is_delete=0')->find();
        
        if(!$info) {
            $this->display('Public:404');exit;
        }
        
        if(empty($info['source'])) {
            $info['source'] = 'http://www.ppmiao.com';
        }
        
        
        $info['type_name'] = $this->get_type_name($info['type']);
        $info['type_url'] = $this->get_type_url($info['type']);
        $info['add_time'] = date("Y-m-d H:i：s",$info['add_time']);
        
        $prev = M('WebContent')->field('id,title')->where('id<'.$id .' and type='.$info['type'].' and is_delete = 0')->order('id desc')->find();
        $next = M('WebContent')->field('id,title')->where('id>'.$id .' and type='.$info['type'].' and is_delete = 0')->find();
        
        $this->assign("res", array(
            'next' => $next,
            'prev' => $prev,
            'help_list_5' => $this->getHelpList(5),
        ));

        $this->assign('meta_title', $info['title'].'零钱喵 - 票票喵');
        $this->assign("info",$info);
        $this->assign("path",'contact');
        $this->display();
    }
    
    /**
     * 
     */
    public function a_banner(){
        $this->display();
    }
    
    private function get_type_name($i)
    {
        $type = array(
            1 => '企业动态',
            2 => '行业资讯',
            3 => '票据百科',
            4 => '网站公告',
            5 => '还款通知',
            6 => '帮助中心 - 票票喵',
            7 => '帮助中心 - 零钱喵',
            8 => '帮助中心 - 注册登录',
            9 => '帮助中心 - 充值提现',
            10 => '帮助中心 - 安全保障'
        );
        
        return $type[$i];
    }

    private function get_type_url($i)
    {
        $type = array(
            1 => 'dynamic.html',
            2 => 'news.html',
            3 => 'wiki.html',
            4 => '###',
            5 => '###',
            6 => '###',
            7 => '###',
            8 => '###',
            9 => '###',
            10 => '###'
        );
        return $type[$i];
    }
	
	private function count_days($end) {
        return floor(abs(strtotime(date("Y-m-d")) - strtotime($end)) / 86400);
    }
	
	
    /**
     * 登录
     * @date: 2017-2-24 下午6:03:30
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function login(){
        
        if(!IS_AJAX) {   
            $user = session(ONLINE_SESSION);
            if($user) {
                redirect(C('WEB_ROOT').'/profile.html');
            }   
            $this->display('login');
        } else {
            $username = strtolower(filterSpecialChar(trim(I('post.username', '', 'strip_tags'))));
            $password = trim(I('post.password', '', 'strip_tags'));
            $jump = I('post.jump', '', 'strip_tags');
            if(!$username) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入营业执照号'));
            }
            if(!$password) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入密码'));
            }
            $user = M('companyUser')->where(array('username'=>$username))->find();
            if(!$user) {
                $this->ajaxReturn(array('status'=>0,'info'=>'该营业执照号未注册，请先注册'));
            }
            
            if($user['password'] != md5($password)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'营业执照号或密码错误，请重新输入'));
            }
    
            if($user['status']>3) {
                $this->ajaxReturn(array('status'=>0,'info'=>'登录失败，营业执照号已被禁用'));
            }
    
            $ip = get_client_ip();
    
            M('companyUser')->where(array('id'=>$user['id']))->save(array('last_login_ip'=>get_client_ip(),'last_login_time'=>time()));
            
            M('companyLoginLog')->add(array('company_id'=>$user['id'],'create_time'=>time(),'login_ip'=>get_client_ip()));
            
            $platcust = M('financing')->where('id='.$user['fid'])->getField('platform_account');
            
            $auth = array(
                'uid'             => $user['id'],
                'username'        => $user['username'],
                'platcust'        => $platcust,
                'fid'           =>$user['fid'],
                //'status'          => $user['status'],
            );
    
            session(ONLINE_SESSION, $auth);
            
            if($user['status'] == 3) {
                $this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT').'/profile.html'));
            } else {
                $this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT').'/review.html'));
            } 
        }
    }
    
    public function logout(){
        session(ONLINE_SESSION,null);
        redirect(C('WEB_ROOT').'/login.html');
    }
    
    public function profile(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        $status = M('companyUser')->where('id='.$user['uid'])->getField('status');
        if($status != 3) {
            //$this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT').'/review.html'));
            redirect(C('WEB_ROOT').'/review.html');
        }
        $this->assign('companyName',M('companyDetail')->where('company_id='.$user['uid'])->getField('company_name'));
        $this->display();
    }
    
    /**
     * 注册
     * @date: 2017-2-24 下午6:03:30
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function sign(){
        if(!IS_AJAX) {
            $this->display('sign');
        } else {            
            $username = strtolower(filterSpecialChar(trim(I('post.username', '', 'strip_tags'))));
            $password = trim(I('post.password', '', 'strip_tags'));
            $password2 = trim(I('post.password2', '', 'strip_tags'));
            $mobile = trim(I('post.mobile', '', 'strip_tags'));
            $code = trim(I('post.code', '', 'strip_tags'));
            
            if(!$username) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入营业执照号'));
            }                        
            if(!$password) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入密码'));
            }
            if($password2 != $password) {
                $this->ajaxReturn(array('status'=>0,'info'=>'两次输入密码不一致'));
            }
            if(!$mobile) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入手机号码'));
            }
            
            if(!S(md5($mobile))) {
                $this->ajaxReturn(array('status'=>0,'info'=>'短信验证码已经过期，请重新获取'));
            }
            
            if($code != S(md5($mobile))) {
                $this->ajaxReturn(array('status'=>0,'info'=>'短信验证码有误'));
            }
            $user = M('companyUser')->where(array('username'=>$username))->find();
            
            if($user) {
                $this->ajaxReturn(array('status'=>0,'info'=>'该营业执照号已经被注册'));
            }

            $ip = get_client_ip();
            $dd = array(
                'username'=>$username,
                'password'=>md5($password),
                'mobile'=>$mobile,
                'reg_time'=>time(),
                'reg_ip'=>$ip,
                'last_login_ip'=>$ip,
                'last_login_time'=>time()
            );
            
           $id = M('companyUser')->add($dd);
           
           if($id) {
               M('companyDetail')->add(array('company_id'=>$id,'license_no'=>$username,'create_time'=>time()));
           }
           
           M('companyLoginLog')->add(array('company_id'=>$id,'create_time'=>time(),'login_ip'=>get_client_ip()));
            
            $auth = array(
                'uid'             => $id,
                'username'        => $username,
            );
            session(ONLINE_SESSION, $auth);
            $this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT').'/edit_company_detail.html'));
        }
    }
    
    public function review(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        
        $data = '';
        $status = M('companyUser')->where('id='.$user['uid'])->getField('status');
        
        if($status == 3) {
            redirect(C('WEB_ROOT').'/login.html');
        } else if($status == 2){
            $data = M('companyReviewRecords')->where('company_id='.$user['uid'] .' and review_status=2')->order('id desc')->find();
        }
        $this->assign('data',$data);
        $this->assign('status',M('companyUser')->where('id='.$user['uid'])->getField('status'));
        $this->detail();
    }
    
    //补充企业信息
    public function edit_company_detail(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        if(M('companyUser')->where('id='.$user['uid'])->getField('status') == 3) {
            redirect(C('WEB_ROOT').'/profile.html');
            return;
        }
        
        if(!IS_AJAX) {
            $detail = M('companyDetail')->where('company_id='.$user['uid'])->find();
            $this->assign('detail',$detail);
            $this->display();
        } else {
            $company_id = trim(I('post.company_id', '0', 'int'));
            $company_name = filterSpecialChar(trim(I('post.company_name', '0', 'strip_tags')));
            $license_type = trim(I('post.license_type', '', 'strip_tags'));//执照类型 0普通1三证合一
            
            $legal_person = trim(I('post.legal_person', '', 'strip_tags'));//法人
            $idcard = trim(I('post.idcard', '', 'strip_tags'));
            
            
            if(!$company_name) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入企业名称'));
            }
                        
            $license_no_img = '';
            
            $upload = new \Think\Upload(C('upload_config'));
            
            if($_FILES['license_no_img']){
                
                $info = $upload->uploadOne($_FILES['license_no_img']);
                
                if($info) {
                    if($info['savepath'] && $info['savename']) {
                        $license_no_img = 'company/'.$info['savepath'].$info['savename'];
                    }
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'营业执照注册号图片上传失败，'));
                }
            } else {
                $license_no_img = trim(I('post.license_no_img_old', '', 'strip_tags'));
                if(!$license_no_img) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请上传营业执照注册号图片'));
                }
            }
                        
            if($license_type == 0) {
                
                $organizing_code_img = '';
                $tax_no_img = '';
                
                $organizing_code = trim(I('post.organizing_code', '', 'strip_tags'));//组织机构代码
                $tax_no = trim(I('post.tax_no', '', 'strip_tags'));//税务登记号
                
                if (!$organizing_code) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入组织机构代码'));
                }
                
                
                if($_FILES['organizing_code_img']){             
                    $info = $upload->uploadOne($_FILES['organizing_code_img']);
                    if($info) {
                        if($info['savepath'] && $info['savename']) {
                            $organizing_code_img = 'company/'.$info['savepath'].$info['savename'];
                        }
                    } else {
                        $this->ajaxReturn(array('status'=>0,'info'=>'组织机构代码图片上传失败，'));
                    }
                } else {
                    
                    $organizing_code_img = trim(I('post.organizing_code_img_old', '', 'strip_tags'));
                    if(!$organizing_code_img) {
                        $this->ajaxReturn(array('status'=>0,'info'=>'请上传组织机构代码图片'));
                    }
                }
                
                
                if (!$tax_no) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入税务登记号'));
                }
                
                if($_FILES['tax_no_img']){
                    $info = $upload->uploadOne($_FILES['tax_no_img']);
                    if($info) {
                        if($info['savepath'] && $info['savename']) {
                            $tax_no_img = 'company/'.$info['savepath'].$info['savename'];
                        }
                    } else {
                        $this->ajaxReturn(array('status'=>0,'info'=>'税务登记图片上传失败，'));
                    }
                } else {
                    $tax_no_img = trim(I('post.tax_no_img_old', '', 'strip_tags'));
                    if(!$tax_no_img) {
                        $this->ajaxReturn(array('status'=>0,'info'=>'请上传税务登记图片'));
                    }
                }
            }
                                    
            if(!$legal_person) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入法人姓名'));
            }
            
            if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$legal_person)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'输入的法人姓名有误'));
            }
            
            if(!is_idcard($idcard)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'输入的法人身份证号码错误'));
            }
            
            $idcard_fornt_img = '';
            $idcard_verso_img = '';
            if($_FILES['idcard_fornt_img']){
                $info = $upload->uploadOne($_FILES['idcard_fornt_img']);
                if($info) {
                    if($info['savepath'] && $info['savename']) {
                        $idcard_fornt_img = 'company/'.$info['savepath'].$info['savename'];
                    }
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'法人身份证正面照图片上传失败，'));
                }
            } else {
                
                $idcard_fornt_img = trim(I('post.idcard_fornt_img_old', '', 'strip_tags'));
                if(!$idcard_fornt_img) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请上传法人身份证正面照'));
                }
            }
            
            if($_FILES['idcard_verso_img']){
                $info = $upload->uploadOne($_FILES['idcard_verso_img']);
                if($info) {
                    if($info['savepath'] && $info['savename']) {
                        $idcard_verso_img = 'company/'.$info['savepath'].$info['savename'];
                    }
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'法人身份证反面照图片上传失败，'));
                }
            } else {
                
                $idcard_verso_img = trim(I('post.idcard_verso_img_old', '', 'strip_tags'));
                if(!$idcard_verso_img) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请上传法人身份证反面照'));
                }
            }
                
            $dd = array(               
                'company_name'=>$company_name,
                'license_type' =>$license_type,
                //'license_no' =>$license_no,
                'license_no_img' =>$license_no_img,
                'legal_person'=>$legal_person,
                'idcard'=>$idcard,
                'idcard_fornt_img'=>$idcard_fornt_img,
                'idcard_verso_img'=>$idcard_verso_img,
                'update_time'=>time(),
            );
            
            if($license_type == 0) {
                $dd['organizing_code'] = $organizing_code;
                $dd['organizing_code_img'] = $organizing_code_img;
                
                $dd['tax_no'] = $tax_no;
                $dd['tax_no_img'] = $tax_no_img;
            }
            
            if(M('companyDetail')->where(array('company_id'=>$user['uid']))->save($dd)) {                
                M('companyUser')->where(array('id'=>$user['uid']))->save(array('status'=>1));
                $this->ajaxReturn(array('status'=>1,'res'=>C('WEB_ROOT').'/review.html'));
            }            
            $this->ajaxReturn(array('status'=>0,'info'=>'联系客服'));
        }
    }
    
    public function pay(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            //$this->success('您还没有登录，请先登录~!', C('WEB_ROOT').'/login.html');
            redirect(C('WEB_ROOT').'/login.html');
        }
        $this->assign('uid',$user['uid']);
        $this->assign('sign',md5('ppm^tt)886!'.$user['uid']));
        $this->display();
    }
    //去充值 
    public function pay_to(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        $amount = I('amount', '', 'strip_tags');
        $bankcode = I('bankcode', '', 'strip_tags');
        $recharge_no = I('recharge_no','', 'strip_tags');      

        $data = [
            'platcust' =>$user['platcust'],
            'bankcode'=>$bankcode,
            'card_no'=>'',
            'amt' =>$amount,
        ];
        
        
        vendor('Fund.FD');
        vendor('Fund.sign');
                
        $plainText =  \SignUtil::params2PlainText($data);        
        $sign =  \SignUtil::sign($plainText);        
        $data['signdata'] = \SignUtil::sign($plainText);        
        $fd  = new \FD();
        
        $res = json_decode($fd->post('/trade/companycharge',$data),true);

        if($res['code'] == 0) {
            
            $date = date("Y-m-d H:i:s");
            $dd = array(
                'company_id'=>$user['uid'],
                'fid'=>$user['fid'],
                'pay_type'=>0,
                'recharge_no'=>$res['result']['rechargeNo'],
                'trade_no'=>'',
                'amount'=>$res['result']['amount'],
                'create_time'=>$date,
                'update_time'=>$date,
                'status'=>0,
                'platcust'=>$user['platcust'],
                'bank_code'=>$bankcode
                
            );
            
            if(M('companyRechargeLog')->add($dd)) {
                $this->assign('from',$res['result']['form']);
                $this->display('pay_jump');
            } else {
                $this->assign('err','订单创建失败');
                $this->display();
            }
        } else {
            $this->assign('err',$res['errorMsg']);
            $this->display();
        }
    }
    
    //充值回调
    public function pay_res(){
        //0失败，1成功，2等待
        $code = I('get.code', 2, 'int');
        $this->assign('code',$code);
        $this->display();
    }
    
    public function pay_notify(){
        
        $input = file_get_contents("php://input");
        \Think\Log::write('pay_notify:'.$input,'INFO');
        
        $order_no = I('order_no');
        $order_status = I('order_status');
        $host_req_serial_no = I('host_req_serial_no');
        $platcust = I('platcust');
        $pay_finish_time = I('pay_finish_time');
        
        if($order_no && $pay_finish_time && $platcust && $host_req_serial_no) {
            $dd = array(
                'update_time'=>date("Y-m-d H:i:s",$pay_finish_time/1000),
                'status' =>$order_status,
                'trade_no'=>$host_req_serial_no,
            );
            
            M('companyRechargeLog')->where("recharge_no='$order_no' and platcust='$platcust'")->save($dd);
            M('depositoryLog')->where("order_no='$order_no'")->save(array('order_status'=>$order_status));
            $this->ajaxReturn(['recode'=>'success'],'JSON');
        } else {
            $this->ajaxReturn(['recode'=>'fail'],'JSON');
        }
    }
	
    //订单日志
    public function orderlog(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            //$this->success('您还没有登录，请先登录~!', C('WEB_ROOT').'/login.html');
            redirect(C('WEB_ROOT').'/login.html');
        }
        $page = I('get.p', 1, 'int');         
        $totalCount = M('companyOrderLog')->where('company_id='.$user['uid'])->count();        
        $Page = new \Think\PageCustomer($totalCount, 10);        
        $show = $Page->show(C('WEB_ROOT').'/orderlog.html?p=');        
        $list = M('companyOrderLog')->where('company_id='.$user['uid'])->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $total_amount = M('companyOrderLog')->where('company_id='.$user['uid'].' and status=1')->sum('amount');
        if(!$total_amount)$total_amount=0;
        $this->assign("total_amount",$total_amount);
        
        $this->assign("list",$list);
        $this->assign("show",$show);
        $this->display();
    }
    
    //找回密码
    public function findpwd(){
        
        session(ONLINE_SESSION,null);
        
        if(!IS_AJAX) {
            $this->display();
        } else {
            
            $step = trim(I('post.step', '', 'strip_tags'));

            if($step == '1'){
                $userName = strtolower(filterSpecialChar(trim(I('post.userName', '', 'strip_tags'))));
                $verifyCode = trim(I('post.verify', '', 'strip_tags'));
                if(!$userName){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入营业执照号'));
                }      
                $companyInfo = M('companyUser')->field('id,mobile,username')->where("username='$userName'")->find();
                if(empty($companyInfo)){
                    $this->ajaxReturn(array('status'=>0,'info'=>'该营业执照号未注册，请重新输入'));
                }  
                
                $verify = new \Think\Verify();
                if(!$verify->check($verifyCode)){
                    $this->ajaxReturn(array('status'=>-1,'info'=>'验证码错误，请重新输入'));
                }
                $companyInfo['new_mobile'] =  substr_replace($companyInfo['mobile'],'****',3,4);
                $this->ajaxReturn(array('status'=>1,'info'=>$companyInfo)); 
            } else if($step == '2'){
                $code = trim(I('post.code', '', 'strip_tags'));
                $mobile = trim(I('post.mobile', '', 'strip_tags'));
                if(!$code) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入短信验证码'));
                }
                if($code != S(md5($mobile))) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'短信验证码有误，请重新获取'));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>'ok'));
            } else if($step == '3'){
                $pwd = trim(I('post.pwd', '', 'strip_tags'));
                $pwd2 = trim(I('post.pwd2', '', 'strip_tags'));
                $userName = strtoupper(filterSpecialChar(trim(I('post.userName', '', 'strip_tags'))));
                $mobile = trim(I('post.mobile', '', 'strip_tags'));
                
                if (!$pwd) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入新的登录密码'));
                }
                
                if ($pwd != $pwd2) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'两次输入的密码不一致'));
                }
                
                if(!$userName) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'您输入的营业执照号不存在，请刷新页面从新操作'));
                }
                
                if(!$mobile) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'您输入的手机号不存在，请刷新页面从新操作'));
                }
                
               if( M('companyUser')->where("username='$userName' and mobile='$mobile'")
                    ->save(array('password'=>md5($pwd),'last_login_time'=>time()))) 
               {
                        $this->ajaxReturn(array('status'=>1,'info'=>'ok'));
                    } else {
                        $this->ajaxReturn(array('status'=>0,'info'=>'找回密码失败,请联系客服！'));
                    }
            }
        }
    }
    
    //账户设置
    public function setting(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        $detail = M('companyDetail')->field('company_name,legal_person,idcard')->where('company_id='.$user['uid'])->find();
        $detail['mobile'] = substr_replace(M('companyUser')->where('id='.$user['uid'])->getField('mobile'),'****',3,4);
        
        $fid = M('companyUser')->where('id='.$user['uid'])->getField('fid');
        $f_info = '';
        if($fid) {
            $f_info = M('Financing')->field('platform_account,acct_name,bank_card_no,bank_id')->where('id='.$fid)->find();
            if($f_info){
                $f_info['bank_name'] = M('baseBanks')->where('bank_id='.$f_info['bank_id'])->getField('bank_name');
            }
        }
        
        
        if(strlen($detail['idcard']) == 15) {
            $detail['idcard'] = substr_replace($detail['idcard'],'*************',1,13);
        } else {
            $detail['idcard'] = substr_replace($detail['idcard'],'****************',1,16);
        }
        $this->assign('detail',$detail);
        $this->assign('info',$f_info);
        $this->display();
    }
    
    public function changephone(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        
        if(!IS_AJAX) {
            $this->display();
        } else{ 

            if(!$user) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请先登陆'));
            }
            
            $step = trim(I('post.step', '', 'strip_tags'));            
            if($step =='1'){                
                $mobile = trim(I('post.mobile', '', 'strip_tags'));
                $code = trim(I('post.code', '', 'strip_tags'));                
                if(!$mobile){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入新的手机号码'));
                }                
                if(!$code) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入手机短信验证码'));
                }                
                if($code != S(md5($mobile))) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'短信验证码有误，请重新获取'));
                }                
                $this->ajaxReturn(array('status'=>1,'info'=>$mobile)); 
                               
            } else {
                $password = trim(I('post.password', '', 'strip_tags'));
                $mobile = trim(I('post.mobie', '', 'strip_tags'));
                if(!$password){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入登陆密码'));
                }
                if(!$mobile){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入新的手机号码'));
                }
                $companyUserPwd = M('companyUser')->where('id='.$user['uid'])->getField('password');
                
                if(!$companyUserPwd){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请先登陆'));
                }
                if($companyUserPwd != md5($password)) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'您输入的密码不正确，请重新输入正确的密码'));
                }
                if( M('companyUser')->where('id='.$user['uid'])
                    ->save(array('mobile'=>$mobile,'last_login_time'=>time(),'last_login_ip'=>get_client_ip())))
                {
                    $this->ajaxReturn(array('status'=>1,'info'=>'ok'));
                }
                $this->ajaxReturn(array('status'=>0,'info'=>'修改失败，请联系客服'));
                
            }
        }
    }
    
    //修改密码
    public function modifypassword(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        
        if(!IS_AJAX) {
            $this->display();
        } else{
            if(!$user) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请先登陆'));
            }
            $step = trim(I('post.step', '', 'strip_tags'));
            if($step =='1'){
                $password = trim(I('post.password', '', 'strip_tags'));
                if(!$password){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入登录密码'));
                }
                $companyUserPwd = M('companyUser')->where('id='.$user['uid'])->getField('password');
                
                if($companyUserPwd!=md5($password)){
                    $this->ajaxReturn(array('status'=>0,'info'=>'您输入的登录密码不正确，请重新输入！'));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>'ok'));
            } else {
                $password = trim(I('post.password', '', 'strip_tags'));
                $password2 = trim(I('post.password2', '', 'strip_tags'));
                
                if (!$password) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入新的登录密码'));
                }
                
                if ($password != $password2) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'两次输入的密码不一致'));
                }
                
                if( M('companyUser')->where('id='.$user['uid'])
                    ->save(array('password'=>md5($password),'last_login_time'=>time())))
                {
                    session(ONLINE_SESSION,null);
                    $this->ajaxReturn(array('status'=>1,'info'=>'ok'));
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'修改密码失败，请联系客服！'));
                }
            }
        }
    }
    
    public function log(){
        $this->display('rechargelog');
    }
    
    
    /**
    * 资金明细
    * @date: 2017-7-12 下午4:18:39
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function money_detail(){
        
        $user = session(ONLINE_SESSION);
        
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        $page = I('get.p', 1, 'int');
        $totalCount = M('companyRechargeLog')->where('company_id='.$user['uid'])->count();
        $Page = new \Think\PageCustomer($totalCount, 10);
        $show = $Page->show(C('WEB_ROOT').'/money_detail.html?p=');
        $list = M('companyRechargeLog')->where('company_id='.$user['uid'])->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign("list",$list);
        $this->assign("show",$show);
        $this->display();
    }
    
    /**
    * 还款和已还款列表
    * @date: 2017-7-12 下午4:18:18
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function repayment_list(){
        $user = session(ONLINE_SESSION);
        if(!$user) {
            redirect(C('WEB_ROOT').'/login.html');
        }
        $type = trim(I('get.type', 1, 'int'));
        
        $cond = 'fid='.$user['fid'] .' and is_delete = 0';
        
        if($type == 2){//待还款
            $cond .= ' and status =3 and repay_review in(0,1,4)';
            $order = 'ASC';
            $status = '1';
        } else {//4已还款
            $cond .= ' and repay_review in(2,3)';
            $order = 'DESC';
            $status = '2';
        }
        
        $page = I('get.p', 1, 'int');
        $totalCount = M('Project')->where($cond)->count();
        $Page = new \Think\PageCustomer($totalCount, 10);
        $show = $Page->show(C('WEB_ROOT').'/repayment_list.html?type='.$type.'&p=');
        $list = M('Project')->field('id,title,fid,end_time')->where($cond)->order('end_time '.$order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
       
        foreach ($list as $key=>$val){
            $list[$key]['end_time'] = date("Y-m-d H:i:s",strtotime($val['end_time']));
            $list[$key]['interest'] = M('userDueDetail')->where('project_id='.$val['id'].' and user_id>0 and status='.$status)->sum('due_interest');
            $list[$key]['capital'] = M('userDueDetail')->where('project_id='.$val['id'].' and user_id>0 and status='.$status)->sum('due_capital');
            $list[$key]['amt'] = $list[$key]['interest'] + $list[$key]['capital'];
            
            //if ($list[$key]['amt']<=0){
            //    unset($list[$key]);
            //}
        }
        
        $this->assign("list",$list);
        $this->assign("show",$show);
        $this->assign('type',$type);
        $this->display();
    }
    
    //融资人还款
    public function repay(){
        
        if(IS_AJAX){
            
            $user = session(ONLINE_SESSION);
            
            if(!$user) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请先登录'));
            }
            
            $prod_id = I('prod_id', 0, 'int');
            
            $projectObj = M('Project');
            
            
            $prodInfo = $projectObj->field('id,title,end_time,status,repay_review')->where('id='.$prod_id)->find();
            
            if(!$prodInfo){
                $this->ajaxReturn(array('status'=>0,'info'=>'标的不存在'));
            }
            
            if($prodInfo['repay_review'] == 0){
                $this->ajaxReturn(array('status'=>0,'info'=>'平台审核通过后才能还款，如需立即还款，请通知平台财务审核！'));
            }
            
            if($prodInfo['repay_review'] == 4){
                $this->ajaxReturn(array('status'=>0,'info'=>'该标财务还款审核失败,请联系客服人员！'));
            }
            
            if($prodInfo['repay_review'] == 2){
                $this->ajaxReturn(array('status'=>0,'info'=>'该标已经还过款了，无需重复还款！'));
            }
            
            $repay_date = date("Ymd",strtotime($prodInfo['end_time']));
            $repay_amt = M('userDueDetail')->where('project_id='.$prod_id.' and user_id>0 and status=1')->sum('due_amount');
            
            
            $out_amt = M('projectChargeoffLog')->where("project_id=".$prod_id.' and order_status=1')->sum('out_amt');
            
            if($repay_amt>$out_amt) {
                //$amt = $repay_amt - $out_amt;
                //$this->ajaxReturn(array('status'=>0,'info'=>"该标还没有{$amt}资金没有出账,请联系客服人员！"));
            }
            
            //查询金额
            $amt_res = $this->get_account_info($user);
            
            if($amt_res['code'] == 0) {
                //$res['frozen_amount'],'balance'=>$res['balance']
                if($amt_res['balance']<$repay_amt){
                   $total_amt = $amt_res['frozen_amount'] + $amt_res['balance'];
                   if($total_amt >=$repay_amt) {
                       $this->ajaxReturn(array('status'=>1,'info'=>'可用余额不足，在途余额明日10点后可用于还款','flag'=>3));
                   }
                   $this->ajaxReturn(array('status'=>1,'info'=>'余额不足，请先充值 ;今天充值，明日10点后可用于还款！','flag'=>2));
                }
                
                
                $data = [
                    'prod_id' =>$prod_id,
                    'repay_date'=>$repay_date,
                    'repay_amt'=>$repay_amt,
                    'real_repay_date'=>$repay_date,
                    'real_repay_amt'=>$repay_amt,
                    'platcust'=>$user['platcust'],
                    'trans_amt'=>$repay_amt,
                ];
                
                vendor('Fund.FD');
                vendor('Fund.sign');
                
                $plainText =  \SignUtil::params2PlainText($data);
                $sign =  \SignUtil::sign($plainText);
                $data['signdata'] = \SignUtil::sign($plainText);
                $fd  = new \FD();
                
                $ret_str = $fd->post('/trade/repay',$data);
                
                \Think\Log::write('还款:'.$ret_str,'INFO');
                
                $res = json_decode($ret_str,true);
                
                
                \Think\Log::write('还款1:'.$res['code'],'INFO');
                
                if($res['code'] == '0') {
                
                    $date = date("Y-m-d H:i:s");
                    $dd = array(
                        'company_id'=>$user['uid'],
                        'fid'=>$user['fid'],
                        'pay_type'=>1,
                        'recharge_no'=>$res['result']['detail_no'],
                        'trade_no'=>'',
                        'amount'=>$res['result']['trans_amt'],
                        'create_time'=>$date,
                        'update_time'=>$date,
                        'status'=>1,
                        'platcust'=>$user['platcust'],
                        'bank_code'=>'',
                        'memo' => $prodInfo['title']
                    );
                    M('companyRechargeLog')->add($dd);
                    $projectObj->where('id='.$res['result']['prod_id'])->save(['repay_review'=>2]);
                    $this->ajaxReturn(array('status'=>1,'info'=>'还款成功','flag'=>1));
                } else {
                    //还款失败
                    $this->ajaxReturn(array('status'=>0,'info'=>'还款失败，错误信息：'.$res['errorMsg'].'，请联系客服！'));
                }
            } else {
                $this->ajaxReturn(array('status'=>0,'info'=>'还款资金查询失败，请联系客服！'));
            }
        }
    }
    
    /**
    * 账户资金
    * @date: 2017-7-13 下午1:28:42
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function account_info(){
        $user = session(ONLINE_SESSION);
        if(!$user) redirect(C('WEB_ROOT').'/login.html');
        $res = $this->get_account_info($user);
        if($res['code'] == '0') {
            $total_amt = $res['frozen_amount'] + $res['balance'];
            $this->ajaxReturn(array('status'=>1,'frozen_amount'=>$res['frozen_amount'],'balance'=>$res['balance'],'total_amt'=>$total_amt));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'资金查询失败，请联系客服！'));
        }
    }
    
    //账户信息
    private function get_account_info($user){
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $result = [];
        
        $data = [
            'account' =>$user['platcust'],
            'acct_type' => '13',
            'fund_type' => '01',
        ];
        
        $plainText =  \SignUtil::params2PlainText($data);
        $sign =  \SignUtil::sign($plainText);
        $data['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        $ret =$fd->post('/account/balace',$data);
        
        \Think\Log::write('融资方现金查询：'.$ret,'INFO');
        
        $tmp = json_decode($ret,true);
        
        if($tmp['code'] == '0'){
            $result['code'] = '0';
            $result['balance'] = $tmp['result']['balance'];
            
            $data['fund_type'] = '02';
            
            unset($data['signdata']);
            
            $plainText =  \SignUtil::params2PlainText($data);
            $sign =  \SignUtil::sign($plainText);
            $data['signdata'] = \SignUtil::sign($plainText);
            
            $ret =$fd->post('/account/balace',$data);
            
            \Think\Log::write('融资方冻结资金查询：'.$ret,'INFO');
            
            $ret = json_decode($ret,true);
            $result['frozen_amount'] = $ret['result']['balance'];
        } else {
            $result['code'] = '-1';
        }
        return $result;
    }
    
    
    //提现发送短信
    public function msgcode(){
        $user = session(ONLINE_SESSION);
        if(!$user) redirect(C('WEB_ROOT').'/login.html');
        
        $data = [
            'platcust' =>$user['platcust'],
        ];
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $plainText =  \SignUtil::params2PlainText($data);
        $sign =  \SignUtil::sign($plainText);
        $data['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();

        $res = json_decode($fd->post('/trade/msgcode',$data),true);
        
        if($res['code'] == 0) {
            $this->ajaxReturn(array('status'=>1));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>$res['result']));
        }
    }
    
    //提现
    public function takeout(){
        $user = session(ONLINE_SESSION);
        if(!$user) redirect(C('WEB_ROOT').'/login.html');
        
        $data = [
            'platcust' =>$user['platcust'],
        ];
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $plainText =  \SignUtil::params2PlainText($data);
        $sign =  \SignUtil::sign($plainText);
        $data['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        $res = json_decode($fd->post('/trade/msgcode',$data),true);
        
        if($res['code'] == 0) {
            $this->ajaxReturn(array('status'=>1));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>$res['result']));
        }
    }
    

}