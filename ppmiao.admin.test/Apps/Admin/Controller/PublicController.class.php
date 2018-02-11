<?php
namespace Admin\Controller;

/**
 * Admin分组登录 不要继承CommonAction
 */
class PublicController extends \Think\Controller {

    public function login(){

        check_ip();
        if(IS_POST){

            if(checkAdminAuth()){
                redirect(U('Index/index'));exit;
            }
            $username = I('post.username', '', 'htmlspecialchars');
            $password = I('post.password', '', 'htmlspecialchars');
            if(C('ALLOW_VERIFY')) $verify = I('post.verify', '', '');

            if(empty($username)) {
                $this->error('帐号不能为空！');exit;
            }elseif (empty($password)){
                $this->error('密码不能为空！');exit;
            }elseif (C('ALLOW_VERIFY') && empty($verify)){
                $this->error('验证码不能为空！');exit;
            }

            
            if(C('ALLOW_VERIFY') && !check_verify($verify, '')){
                $this->error('验证码错误!', U('Public/login'));exit;
            }
            
            $member = D('Member');

            $info = $member->field('id,username,status')->where(array('username'=>$username,'password'=>md5($password)))->find();




            if(is_array($info) && $info['status'] == 1){ // 判断管理员信息是否存在和管理员账号是否被禁用
                loginLog($info['id'], $info['username'], 0);
                //登录成功            
                //记录登录SESSION
                $ip = get_client_ip();
                $token = md5($info['id'].$info['username'].$ip.C('ADMIN_SECRET_KEY').$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT'].$_SERVER['HTTP_USER_AGENT']);
                $auth = array(
                    'uid'             => $info['id'],
                    'username'        => $info['username'],
                    'token'           => $token,
                );
                session(ADMIN_SESSION, $auth);
                $this->success('登录成功！', U('Index/index')); 
            } else {
                loginLog(0, $username, 1);
                $this->error('用户名或密码错误或账户被禁用！');
            }             
        } else {
            if(checkAdminAuth()){
                redirect(U('Index/index'));exit;
            }
//            if(!isMobile()) $this->display();
//            else $this->display(C('S_THEME').'login');

            $this->display();
        }
    }

    // 顶部页面
    public function top() {
        C('SHOW_RUN_TIME', false);			// 运行时间显示
        C('SHOW_PAGE_TRACE', false);
        $this->display();
    }

    public function drag(){
        C('SHOW_PAGE_TRACE',false);
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        $this->display();
    }

    // 尾部页面
    public function footer() {
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        C('SHOW_PAGE_TRACE',false);
        $this->display();
    }

    // 菜单页面
    public function menu() {
        if(isset($_SESSION[ADMIN_SESSION]['uid'])){
            //显示菜单项
            $menu = C('ACCESS_ARRAY'); // 获取后台权限配置数组
            $is_show_ghost = C('GHOST_ACCOUNT');
            if(isset($_SESSION['menu'.$_SESSION[ADMIN_SESSION]['uid']])){
                //如果已经缓存，直接读取缓存
                $menu   =   $_SESSION['menu'.$_SESSION[ADMIN_SESSION]['uid']];
            }else {
                $Auth = new \Think\Auth();
                $uid = $_SESSION[ADMIN_SESSION]['uid'];
                if($uid != 1){ // 如果不是超级管理员,校验权限(超级管理员不受权限影响)
                    //读取数据库模块列表生成菜单项
                    foreach($menu as $key => $val) {
                        if($val['sub']){
                            for($i = count($val['sub']); $i >= 0; $i--){
                                if(!$Auth->check('Admin/'.$menu[$key]['sub'][$i]['url'], $uid, array('in', '1,2'), $mode='url')) unset($menu[$key]['sub'][$i]);
                            }
                        }
                    }
                }
                
                if($is_show_ghost == false){ //隐藏幽灵购买菜单
                   unset($menu[0]['sub'][9]);
                } 
                
                //缓存菜单访问
                $_SESSION['menu'.$_SESSION[ADMIN_SESSION]['uid']] = $menu;
            }
            if(!empty($_GET['tag'])){
                $this->assign('menuTag', I('get.tag'));
            }
            $this->assign('menu', $menu);
        }
        C('SHOW_RUN_TIME',false);			// 运行时间显示
        C('SHOW_PAGE_TRACE',false);
        $this->display();
    }

    // 后台首页 查看系统信息
    public function main() {
        if (!checkAdminAuth()) {
            if (!IS_AJAX) {
                $this->error('您尚未登录！正在跳转登录页面', U('Public/login'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '你已登出,请重新登录'));
            }
        }

        $this->display();
    }

    //获取验证码
    public function verify(){
        $config = array(
            'fontSize'    =>    25,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            'useNoise'    =>    false, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    //退出登录 ,清除 session
    public function logout(){ 
        session(ADMIN_SESSION, null);
        session('[destroy]');
        $this->success('退出成功！', U('login')); 
    }

}