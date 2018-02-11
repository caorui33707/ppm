<?php
namespace Admin\Controller;

/**
 * 系统日志管理
 * @package Admin\Controller
 */
class LogController extends AdminController{

    /**
     * 登录日志
     */
    public function login(){
        $count = 10;

        $adminLogLoginObj = M('AdminLogLogin');

        $counts = $adminLogLoginObj->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $adminLogLoginObj->order('time_add desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * 操作日志
     */
    public function operation(){
        $this->display();
    }

}