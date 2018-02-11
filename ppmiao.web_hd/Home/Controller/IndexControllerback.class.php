<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends BaseController {

    public function index(){
        if(is_mobile()) {
            redirect(C('MOBILE_SITE'));
            exit;
        }
        $this->assign('meta_title', '石头理财 - 理财就这么简单');
        $this->assign('meta_keywords', '石头理财，理财，基金，理财产品，手机理财软件');
        $this->assign('meta_description', '石头理财是互联网金融理财的综合平台，通过手机理财软件，操作便捷，包含基金类产品，股票类产品，家庭理财类产品，股权配资类产品等等相关的金融理财服务');
        $this->display('index2');
    }

    /**
     * 理财推荐
     */
    public function recommend(){
        redirect(C('WEB_ROOT').'/introduction.html');
    }

    /**
     * 产品介绍
     */
    public function introduction(){
        // 获取接口数据验证信息
        $time = time();
        $requestVerifyRows = array(
            'time' => $time,
            'verify' => md5(C('CheckCode').$time),
        );
        $this->assign('request_verify', $requestVerifyRows);
        // 获取本地用户设备激活数
        $activationDeviceObj = M('ActivationDevice');
        $activationCount = S("ActivationCount");
        if(!$activationCount) {
            $activationCount = $activationDeviceObj->count();
            S("ActivationCount", $activationCount, 3600);
        }
        $this->assign('device_activation', C('DEVICE_ACTIVATION')+$activationCount);
        $this->assign('meta_title', '产品介绍');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

    /**
     * 关于石头
     */
    public function about(){
        $this->assign('meta_title', '关于石头');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display();
    }

    /**
     * 加入我们
     */
    public function join(){
        $this->assign('meta_title', '加入我们');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display('join2');
    }

    /**
     * 更多职位
     */
    public function jobs(){
        $this->assign('meta_title', '更多职位');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display();
    }

    /**
     * 联系我们
     */
    public function contact(){
        $this->assign('meta_title', '联系我们');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display();
    }

    /**
     * 留言反馈
     */
    public function feedback(){
        if(!IS_POST){
            $this->assign('meta_title', '我要吐槽');
            $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
            $this->assign('meta_description', '石头理财');
            $this->display();
        }else{
            $content = trim(I('post.content', '', 'strip_tags'));
            $contact = trim(I('post.contact', '', 'strip_tags'));
            if(!$content){
                $this->error('请写点内容吧~');exit;
            }
            $rows = array(
                'user_id' => 0,
                'device_type' => 4,
                'content' => $content,
                'contact_way' => $contact,
                'is_read' => 0,
                'add_time' => date('Y-m-d H:i:s', time()),
                'add_user_id' => 0,
            );
            $suggestObj = M('Suggest');
            if(!$suggestObj->add($rows)){
                $this->error('吐槽失败...');
            }else{
                $this->success('吐槽成功~!', C('WEB_ROOT').'/feedback.html', 3);
            }
        }
    }

    /**
     * 常见问题
     */
    public function faq(){
        $this->assign('meta_title', '常见问题');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
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
//        $this->assign('meta_title', '合作机构');
//        $this->assign('meta_keywords', '');
//        $this->assign('meta_description', '');
        $this->display('PublicNew:404');
    }

    /**
     * 隐私保护
     */
    public function protection(){
        $this->assign('meta_title', '隐私保护');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

    /**
     * 服务条款
     */
    public function provision(){
        $this->assign('meta_title', '服务条款');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
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

}