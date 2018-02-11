<?php
namespace Admin\Controller;

class NoticetController extends AdminController{
    
    private $pageSize = 10;

    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $noticeObj = M('Notice');

            $counts = $noticeObj->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = $noticeObj->where(array('is_delete'=>0))->order('status,create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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

    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            $status = I('post.status', 0, 'int');
            $content = trim(I('post.content', '', 'strip_tags'));
            $jump_url = trim(I('post.url', '', 'strip_tags'));
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            if(!$content) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入公告内容'
                ));
            }
            
            
            if($jump_url) {
                
               if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$jump_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的url'
                    ));
                }
            }
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择开始时间'
                ));
            }
            
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择结束时间'
                ));
            }
            
            $rows = array(
                'jump_url' => $jump_url,
                'status' => $status,
                'content' => $content,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time(),
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            if(!M('Notice')->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/noticet/index'));
        }
    }

    /**
     * 编辑文字广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('Notice')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('首页文字公告不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            
            $status = I('post.status', 0, 'int');
            $content = trim(I('post.content', '', 'strip_tags'));
            $jump_url = trim(I('post.url', '', 'strip_tags'));
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            if(!$content) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入公告内容'
                ));
            }
            
            
            if($jump_url) {
                 if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$jump_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的url'
                    ));
                }
            }
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择开始时间'
                ));
            }
            
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择结束时间'
                ));
            }

            $rows = array(
                'jump_url' => $jump_url,
                'status' => $status,
                'content' => $content,
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'update_time' => time(),
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            
            if(!M('Notice')->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/noticet/index/p/'.$page));
        }
    }

    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        if(!M('Notice')->where(array('id'=>$id))->save(array('is_delete'=>1,'edit_user_id'=>$uid,'update_time'=>time()))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }

}