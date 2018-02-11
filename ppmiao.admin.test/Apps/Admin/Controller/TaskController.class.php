<?php
namespace Admin\Controller;

/**
 * 任务系统控制器
 * @package Admin\Controller
 */
class TaskController extends AdminController{

    /**
     * 任务列表
     */
    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数
            $type = I('get.type', 0, 'int'); // 产品类型
            $taskObj = M('Task');

            $conditions = array();
            if ($type) $cond[] = "type=" . $type;
            if ($cond) $conditions = implode(' and ', $cond);
            $counts = $taskObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $orderby = 'add_time desc';
            $list = $taskObj->where($conditions)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $params = array(
                'page' => $page,
                'type' => $type,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }else{
            $type = I('post.type', 0, 'int');
            redirect(C('ADMIN_ROOT') . '/task/index/type/'.$type);
        }
    }

    /**
     * 添加任务
     */
    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $name = trim(I('post.name', '', 'strip_tags'));
            $type = I('post.type', 0, 'int');
            $coin = I('post.coin', 0, 'int');
            $is_show = I('post.is_show', 0, 'int');
            $st = I('post.st', '', 'strip_tags');
            $et = I('post.et', '', 'strip_tags');
            $url = I('post.url', '', 'strip_tags');
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];

            $taskObj = M('Task');

            $rows = array(
                'name' => $name,
                'type' => $type,
                'coin' => $coin,
                'is_show' => $is_show,
                'url' => $url,
                'description' => '',
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if($st) $rows['start_time'] = $st;
            if($et) $rows['end_time'] = $et;
            if(!$taskObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/task/index'));
        }
    }

    /**
     * 编辑任务
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $type = I('get.type', 0, 'int');

            // 获取任务详细信息
            $taskObj = M('Task');
            $detail = $taskObj->where(array('id' => $id))->find();
            if (!$detail) {
                $this->error('任务信息不存在或已被删除');
                exit;
            }
            $params = array(
                'page' => $page,
                'type' => $type,
            );
            $this->assign('params', $params);
            $this->assign('detail', $detail);
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $page = I('post.p', 1, 'int');
            $_type = I('post._type', 0, 'int');
            $id = I('post.id', 0, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            $type = I('post.type', 0, 'int');
            $coin = I('post.coin', 0, 'int');
            $is_show = I('post.is_show', 0, 'int');
            $st = I('post.st', '', 'strip_tags');
            $et = I('post.et', '', 'strip_tags');
            $url = I('post.url', '', 'strip_tags');
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];

            $taskObj = M('Task');

            $rows = array(
                'name' => $name,
                'type' => $type,
                'coin' => $coin,
                'is_show' => $is_show,
                'url' => $url,
                'description' => '',
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if($st) $rows['start_time'] = $st;
            if($et) $rows['end_time'] = $et;
            if(!$taskObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/task/index/p/'.$page.'/type/'.$_type));
        }
    }

    /**
     * 更新任务状态
     */
    public function update_status(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        $taskObj = M('Task');
        $detail = $taskObj->where(array('id'=>$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'任务信息不存在或已被删除'));
        if($detail['is_show'] == 1){
            $is_show = 0;
        }else{
            $is_show = 1;
        }
        $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        if(!$taskObj->where(array('id'=>$id))->save(array('is_show'=>$is_show,'modify_time'=>$time,'modify_user_id'=>$uid))) $this->ajaxReturn(array('status'=>0,'info'=>'更新状态失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

}