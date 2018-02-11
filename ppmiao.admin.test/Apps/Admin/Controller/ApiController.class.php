<?php
namespace Admin\Controller;

/**
 * api请求接口控制器
 * @package Admin\Controller
 */
class ApiController extends AdminController{

    public function baidu(){
        $this->display();
    }

    /**
     * 石头API
     */
    public function stone(){
        $this->display();
    }

}