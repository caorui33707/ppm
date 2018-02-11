<?php
namespace Admin\Controller;

/**
 * 后台公共控制器
 */
class AdminController extends \Think\Controller {

    /**
     * 后台控制器初始化
     */

    protected  $uid;

    protected function _initialize(){
        check_ip();
		if (!checkAdminAuth()) {
//            if (!IS_AJAX) {
//                $this->error('您尚未登录！正在跳转登录页面', U('Public/login'));
//            } else {
//                $this->ajaxReturn(array('status' => 0, 'info' => '你已登出,请重新登录'));
//            }
        }
        $user = session(ADMIN_SESSION);
        $this->uid = $uid = $user['uid'] ;
        // 是否是超级管理员 ，UID == 1 的就是 管理员  //管理员允许访问任何页面 
        if(($uid == 1)){
            return true;//管理员允许访问任何页面
        }

        //  检测当前页是否有权限访问      // ' admin/index/index'
        $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);


        static $Auth    =   null;
        if(!$Auth) $Auth = new \Think\Auth();
        if(!$Auth->check($rule, $uid, array('in', '1,2'), $mode='url')) {
            if (!IS_AJAX) {
                $this->error('没有权限访问本页面!');
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '没有权限访问本页面或该方法'));
            }
        }
    }

}  