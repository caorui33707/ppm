<?php
namespace Home\Controller;
use Think\Controller;

class MediaController extends BaseController{

    /**
     * ý�嶯̬�б�
     */
    public function index(){
        header("HTTP/1.0 404 Not Found");//ʹHTTP����404״̬��
        $this->display("PublicNew:404");
        $this->assign('meta_title', 'ý�嶯̬');
        $this->assign('meta_keywords', '');
        $this->assign('meta_description', '');
    }

    /**
     * ý�嶯̬��ϸ
     */
    public function detail(){
        $id = I('get.id', 0, 'int');
        $mediaDynamicsObj = M("MediaDynamics");
        $detail = $mediaDynamicsObj->where(array('id'=>$id,'is_show'=>1))->find();
        if(!$detail){
            header("HTTP/1.0 404 Not Found");//ʹHTTP����404״̬��
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