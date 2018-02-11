<?php
namespace Admin\Controller;

/**
 * 返券活动配置 20161025
 */
class ActivityController extends AdminController
{

    private $pageSize = 15;
    
    /**
     * 
     * create_time 2016/10/25
     */
    public function index(){
        
        if(!IS_POST){
                        
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('ActivityConf')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('ActivityConf')->where(array('is_delete'=>0))->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                
                $list[$key]['cnt'] = M('ActivityAward')->where('a_id = '.$val['id'].' and is_delete=0')->count();
                
                if(strtotime($val['start_time']) > time()) {
                    $list[$key]['state'] = '待上线';
                } else if(time() > strtotime($val['start_time']) && time() < strtotime($val['end_time'])){
                    $list[$key]['state'] = '进行中';
                } else if(time() > strtotime($val['end_time'])) {
                    $list[$key]['state'] = '已结束';
                }
            }
            
            $params = array(
                'page' => $page,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }
    }
    
    /**
     * create_time 2016/10/25
     */
    public function add(){
        if (! IS_POST) {
            $this->display();
        } else {
            $name = trim(I('post.name', '', 'strip_tags'));
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $tag = trim(I('post.tag', -1, 'int'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'活动名称不能为空'));
            
            if(M('ActivityConf')->where(array('name'=>$name))->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'活动名称`'.$name.'`已经存在'));
            }
            
            if(!$start_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动开始时间'));
            if(!$end_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动结束时间'));
            
            $dd = array(
                'name' => $name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time(),
                'tag' => $tag,
                'memo' => $memo
            );
            $id = M('ActivityConf')->add($dd);
            if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/activity/index'));
        }
    }
    
                
     /**
     * create_time 2016/10/25
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('ActivityConf')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('该活动不存在或已被删除');
                exit;
            }
            
            //设置活动开始时间、tag 能否修改、
            $enable = 0;
            
            if(strtotime($detail['start_time']) < time()) {
                //已经开始了
                $enable = 1;
            }
            $this->assign('enable', $enable);
            $this->assign('detail', $detail);
            $this->display();
            
        }else{
            
            $id = I('post.id', 0, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $tag = trim(I('post.tag', 0, 'int'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            $enable = trim(I('post.enable', '0', 'int'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'活动名称不能为空'));
            
            if(M('ActivityConf')->where("name='$name'".' and id !='.$id)->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'活动名称`'.$name.'`已经存在'));
            }
            
            if(!$enable){
                $start_time = trim(I('post.start_time', '', 'strip_tags'));
                if(!$start_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动开始时间'));
                $tag = trim(I('post.tag', 0, 'int'));
                
                $dd['start_time'] = $start_time;
                $dd['tag'] = $tag;
            }
            
            if(!$end_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动结束时间'));
            
            $dd['name'] = $name;
            $dd['end_time'] = $end_time;
            $dd['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['update_time'] = time();
            $dd['memo'] = $memo;
            
            if(!M('ActivityConf')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/activity/index'));
        }
    }
    
    /**
     * 删除
     * create_time 2016/10/25
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('ActivityConf')->where('id')->where('id='.$id.' and is_delete = 0')->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
        
               
        if (! M('ActivityConf')->where(array(
            'id' => $id
        ))->save(array(
            'is_delete' => 1,
            'update_time' => time(),
            'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid']
        ))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
            
        $this->ajaxReturn(array('status'=>1));
    }
    
    
}