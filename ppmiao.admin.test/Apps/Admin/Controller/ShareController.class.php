<?php
namespace Admin\Controller;

use OSS\Core\OssException;

/**
 * 分享内容
 * @package Admin\Controller
 */
class ShareController extends AdminController{
    
    private $pageSize = 10;
    

    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码

            $counts = M('share')->where('is_delete = 0')->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('share')->where('is_delete = 0')->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
           
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
            
            $title = trim(I('post.title', '', 'strip_tags'));
            $content = trim(I('post.content', '', 'strip_tags'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            $jump_url = trim(I('post.jump_url', '', 'strip_tags'));
            
            if($title == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题 '));
            }
            if($content == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入内容 '));
            }
            if($memo == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入备注'));
            }
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
                       $this->ajaxReturn(array('status'=>0,'info'=>'图片上传失败'));
                   }
                   \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
                
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择上传图片'));
            }
            
            if($jump_url == ''){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入跳转url'
                ));
            
            } else {
                if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$jump_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的跳转url'
                    ));
                }
            }

            $rows = array(
                'title' => $title,
                'content' => $content,
                'img' => $image,
                'memo' => $memo,
                'jump_url' => $jump_url,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time()
            );
            if(!M('share')->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/share/index'));
        }
    }

    /**
     * 编辑广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('share')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('分享信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            
            $title = trim(I('post.title', '', 'strip_tags'));
            $content = trim(I('post.content', '', 'strip_tags'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            $jump_url = trim(I('post.jump_url', '', 'strip_tags'));
            
            if($title == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题 '));
            }
            if($content == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入内容 '));
            }
            if($memo == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入备注'));
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
            
            if($jump_url == ''){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入跳转url'
                ));
            
            } else {
                if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$jump_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的跳转url'
                    ));
                }
            }

            $rows = array(
                'title' => $title,
                'content' => $content,
                'img' => $image,
                'memo' => $memo,
                'jump_url' => $jump_url,
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'update_time' => time()
            );
            
            if(!M('share')->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/share/index'));
        }
    }

    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if(!M('share')->where(array('id'=>$id))->save(array('is_delete'=>1,'update_time'=>time())))
             $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

}