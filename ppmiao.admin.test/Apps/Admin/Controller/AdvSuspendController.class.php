<?php
namespace Admin\Controller;

/**
 * 悬浮ICON
 * @package Admin\Controller
 */
class AdvSuspendController extends AdminController{
    
    private $pageSize = 10;
    

    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('advSuspend')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('advSuspend')->where(array('is_delete'=>0))->order('status,create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['statusStr'] = $this->_status[$val['status']];
                $list[$key]['positionStr'] = $this->_position[$val['position']];
            }

            $params = array(
                'page' => $page,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }else{

        }
    }

    /**
     * 添加广告
     */
    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            $name = trim(I('post.name', '', 'strip_tags'));
            $url = trim(I('post.url', '', 'strip_tags'));
            $user_type = I('post.user_type', 0 ,'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $status = I('post.status', 0, 'int'); // 上下架
            
            if($name == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入活动名称 '));
            }
            if($url == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入活动url '));
            }
            
            if($user_type == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择用户类型！'));
            }
            
            if($status == -1) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择状态！'));
            }
            
            if(!$start_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择开始时间'));
            }
            if(!$end_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择结束时间'));
            }
            
            $image = '';
            
            if($_FILES){
                $config = array(
                    'maxSize'    =>    3145728,
                    'rootPath' => C('UPLOAD_PATH'),
                    'savePath'   =>    '',
                    'saveName'   =>    array('uniqid',''),
                    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
                    'autoSub'    =>    true,
                    'subName'    =>    array('date','Ymd'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                // 上传文件
                $info   =   $upload->upload();
                if($info) {
                    $image = $info['img']['savepath'].$info['img']['savename'];
                    
                    $ossPath = 'Uploads/focus/'.$image;
                    $file = C('localPath').$image;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择广告图'));
            }
      
            $rows = array(
                'name' => $name,
                'url' => $url,
                'user_type' => $user_type,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'img' => $image,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status,
                'user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time()
            );
            if(!M('advSuspend')->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advSuspend/index'));
        }
    }

    /**
     * 编辑广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('advSuspend')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('广告信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));
            $url = trim(I('post.url', '', 'strip_tags'));
            $user_type = I('post.user_type', 0 ,'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $status = I('post.status', 0, 'int'); // 上下架
            
            if($name == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入活动名称 '));
            }
            if($url == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入活动url '));
            }
            
            if($user_type == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择用户类型！'));
            }
            
            if($status == -1) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择状态！'));
            }
            
            if(!$start_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择开始时间'));
            }
            if(!$end_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择结束时间'));
            }
            
            $image = I('post.image', '', 'strip_tags');
                
            if($_FILES){
                $config = array(
                    'maxSize'    =>    3145728,
                    'rootPath' => C('UPLOAD_PATH'),
                    'savePath'   =>    '',
                    'saveName'   =>    array('uniqid',''),
                    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
                    'autoSub'    =>    true,
                    'subName'    =>    array('date','Ymd'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                // 上传文件
                $info   =   $upload->upload();
                if($info) {
                    $image = $info['img']['savepath'].$info['img']['savename'];
                    
                    $ossPath = 'Uploads/focus/'.$image;
                    $file = C('localPath').$image;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
            }
                
            $rows = array(
                'name' => $name,
                'url' => $url,
                'user_type' => $user_type,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'img' => $image,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status,
                'update_time' => time()
            );
            if(!M('advSuspend')->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advSuspend/index/p/'.$page));
        }
    }

    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if(!M('advSuspend')->where(array('id'=>$id))->save(array('is_delete'=>1,'update_time'=>time())))
             $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

}