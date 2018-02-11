<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 全局基础控制器
 * @package Home\Controller
 */
class BaseController extends Controller{

    public function _empty(){
        status_403();
    }

}