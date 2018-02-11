<?php
namespace Home\Controller;
use Think\Controller;

class MediaController extends BaseController{

    /**
     * 媒体动态列表
     */
    public function index(){
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("PublicNew:404");
        $this->assign('meta_title', '媒体动态');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
    }

    /**
     * 媒体动态详细
     */
    public function detail(){
        $id = I('get.id', 0, 'int');
        $mediaDynamicsObj = M("MediaDynamics");
        $detail = $mediaDynamicsObj->where(array('id'=>$id,'is_show'=>1))->find();
        if(!$detail){
            header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
            $this->display("PublicNew:404");
            exit;
        }
        $this->assign('detail', $detail);
        $this->assign('meta_title', $detail['title']);
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
        $this->display();
    }

}