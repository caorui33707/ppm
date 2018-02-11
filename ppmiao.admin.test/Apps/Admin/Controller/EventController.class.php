<?php
namespace Admin\Controller;

/**
 * 活动相关
 */
class EventController extends AdminController
{

    private $pageSize = 15;
    
    /**
     * 
     * create_time 2016/08/10
     */
    public function event_conf_index(){
        
        if(!IS_POST){
            $this->assign('list', M('EventConf')->order('id desc')->select());
            $this->display();
        }
    }
    
    /**
     * 添加活动配置
     * create_time 2016/08/10
     */
    public function event_conf_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $name = trim(I('post.name', '', 'strip_tags'));
            
            $type = trim(I('post.type', 0, 'int'));
            $act = trim(I('post.act', 0, 'int'));
                        
            $begin_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            
            $memo = trim(I('post.memo', '', 'strip_tags'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'活动名称不能为空'));
            if(!$type) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动类型'));
            if(!$act) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动事件'));
            
            if(!$begin_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动开始时间'));
            if(!$end_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动结束时间'));
            
            if(!$memo) $this->ajaxReturn(array('status'=>0,'info'=>'备注不能为空'));
            
            
            $title = trim(I('post.title','','strip_tags'));
            
            $content = trim(I('post.content','','strip_tags'));
            
            $amount = trim(I('post.amount', '', 'strip_tags'));//金额
            
            $min_invest = trim(I('post.min_invest', 0, 'float'));	//红包最小投资金额：
            
            $min_due = trim(I('post.min_due', 0, 'int'));	//红包最小投资期限：
            
            $due_time = trim(I('post.due_date', 0, 'int'));	//红包到期天数：
            
            
            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'标题不能为空'));
            if(!$content) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动类型'));
            
            
            if($title) {
                if($type == 1) {
                    $count = M('UserRedenvelope')->where(array('title'=>$title))->count();
                    if($count) {
                        $this->ajaxReturn(array('status'=>0,'info'=>$title.'已经存在'));
                    }
                    $count = M('EventConf')->where(array('name'=>$title,'type'=>1))->count();
                    if($count) {
                        $this->ajaxReturn(array('status'=>0,'info'=>$title.'已经存在'));
                    }
                }
            }
            
            
            if(!$content) {
                if($type == 1) {//红包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写红包内容'));
                } else {//券包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写券包子标题'));
                }
            }
            
            if(!$amount) {
                if($type == 1) {//红包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写正确的红包金额'));
                } else {//券包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写正确的券包利率'));
                }
            }
            
            if(!$min_invest) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最小投资金额'));
            
            if(!$min_due) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最少投资期限'));
            
            if(!$due_time) $this->ajaxReturn(array('status'=>0,'info'=>'请输入红包有效天数'));
            
            
            $info['title'] = $title;
            
            if($type == 1){
                $info['content'] = $content;
                $info['amount'] = $amount;
            } else{
                $info['subtitle'] = $content;
                $info['interest_rate'] = $amount;
                
                $coupon_id = M('UserInterestCoupon')->max('coupon_id');
                $max_id = M('EventConf')->max('id');
                
                $info['coupon_id'] = $coupon_id + $max_id;
            }
            $info['min_invest'] = $min_invest;
            $info['min_due'] = $min_due;
            $info['min_day'] = $due_time;
            
            $params = json_encode($info); 
    
            $time = date('Y-m-d H:i:s', time());
            $dd = array(
                'name' => $name,
                'type' => $type,
                'act'=>$act,
                'create_time'=>time(),
                'begin_time' => $begin_time,
                'end_time'=>$end_time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'edit_time' => time(),
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'content'=>$params,
                'memo'=>$memo
            );
            $id = M('EventConf')->add($dd);
            if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/event/event_conf_index'));
        }
    }
    
                
     /**
     * 编辑产品分组
     * create_time 2016/08/10
     */
    public function event_conf_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('EventConf')->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('该活动不存在或已被删除');
                exit;
            }
            
            $param = json_decode($detail['content']);
            
            if($detail['type'] == 1) {
                $detail['content'] = $param->content;
                $detail['amount'] = $param->amount;
            } else {
                $detail['content'] = $param->subtitle;
                $detail['amount'] = $param->interest_rate;
                
                $detail['coupon_id'] = $param->coupon_id;
            }
            
            $detail['title'] = $param->title;
            
            $detail['min_invest'] = $param->min_invest;
            $detail['min_due'] = $param->min_due;
            $detail['min_day'] = $param->min_day;
            
            $this->assign('detail', $detail);
            $this->display();
            
        }else{
            
            $id = I('post.id', 0, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));
            
            $type = trim(I('post.type', 0, 'int'));
            $act = trim(I('post.act', 0, 'int'));
            
            $begin_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            
            $memo = trim(I('post.memo', '', 'strip_tags'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'活动名称不能为空'));
            if(!$type) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动类型'));
            if(!$act) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动事件'));
            
            if(!$begin_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动开始时间'));
            if(!$end_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动结束时间'));
            
            if(!$memo) $this->ajaxReturn(array('status'=>0,'info'=>'备注不能为空'));
            
            
            $title = I('post.title','','strip_tags');
            
            $content = I('post.content','','strip_tags');
            
            $amount = I('post.amount', '', 'strip_tags');//金额
            
            $min_invest = I('post.min_invest', 0, 'float');	//红包最小投资金额：
            
            $min_due = I('post.min_due', 0, 'int');	//红包最小投资期限：
            
            $due_time = I('post.due_date', 0, 'int');	//红包到期天数：
            
            
            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'标题不能为空'));
            if(!$content) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动类型'));
            
            /*
            if($title) {
                if($type == 1) {
                    $count = M('UserRedenvelope')->where("title='$title' and id != $id")->count();
                    if($count) {
                        $this->ajaxReturn(array('status'=>0,'info'=>$title.'已经存在，'));
                    }
                }
            }*/
            
            if(!$content) {
                if($type == 1) {//红包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写红包内容'));
                } else {//券包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写券包子标题'));
                }
            }
            
            if(!$amount) {
                if($type == 1) {//红包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写正确的红包金额'));
                } else {//券包
                    $this->ajaxReturn(array('status'=>0,'info'=>'请填写正确的券包利率'));
                }
            }
            
            if(!$min_invest) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最小投资金额'));
            
            if(!$min_due) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最少投资期限'));
            
            if(!$due_time) $this->ajaxReturn(array('status'=>0,'info'=>'请输入红包有效天数'));
            
            
            $info['title'] = $title;  //标题赞时不让 
            
            if($type == 1){
                $info['content'] = $content;
                $info['amount'] = $amount;
            } else{
                $info['subtitle'] = $content;
                $info['interest_rate'] = $amount;
                $info['coupon_id'] = trim(I('post.coupon_id', 0, 'int'));
            }
            
            $info['min_invest'] = $min_invest;
            $info['min_due'] = $min_due;
            $info['min_day'] = $due_time;
            
            $params = json_encode($info);
            
            $time = date('Y-m-d H:i:s', time());
            $dd = array(
                'name' => $name,
                'type' => $type,
                'act'=>$act,
                'begin_time' => $begin_time,
                'end_time'=>$end_time,
                'edit_time' => time(),
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'content'=>$params,
                'memo'=>$memo
            );
            
            if(!M('EventConf')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/event/event_conf_index'));
        }
    }
    
    /**
     * 删除
     * create_time 2016/08/10
     */
    public function event_conf_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('EventConf')->where('id')->where('id='.$id)->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
        
        //1 红包，2 券包
        
        $param = json_decode($info['content']);
        
        if ($info['type'] == 1) {

            if(M('UserRedenvelope')->where(array('title'=>$param->title))->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'该活动已经有用户使用，无法删除。可以改活动时间'));
            }
            
        } else if($info['type'] == 2) {
            if(M('UserInterestCoupon')->where('coupon_id='.$param->coupon_id .' and is_delete = 0')->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'该活动已经有用户使用，无法删除。可以改活动时间'));
            }
        }
        
        if(!M('EventConf')->where(array('id'=>$id))->delete()){
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
    
    
}