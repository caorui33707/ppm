<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller{

    public function _initialize(){

    }

    public function _empty(){
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("Public:404");
        //status_403();
    }

}