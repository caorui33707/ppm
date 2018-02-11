<?php
namespace Home\Controller;
use Think\Controller;

class MobilController extends Controller{

    public function _initialize(){
        header("Content-Type: text/html;charset=utf-8");
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

}