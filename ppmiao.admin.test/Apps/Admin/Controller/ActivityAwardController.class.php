<?php
namespace Admin\Controller;

/**
 * 活动奖励 20161026
 */
class ActivityAwardController extends AdminController
{

    private $pageSize = 15;
    
    /**
     * 
     * create_time 2016/10/26
     */
    public function index(){
        
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('ActivityAward')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('ActivityAward')->where(array('is_delete'=>0))->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['activity_conf'] = M('ActivityConf')->field('name,start_time,end_time,memo')->where('id = '.$val['a_id'])->find();
                
                //1红包\2现金券\3加息券
                if($val['type'] == 1) {
                    $list[$key]['use_cnt'] = M('UserRedenvelope')->where(array('source'=>$val['source'],'status'=>1))->count();
                } else if($val['type'] == 2){
                    $list[$key]['use_cnt'] = M('UserCashCoupon')->where(array('source'=>$val['source'],'status'=>1))->count();
                } else{
                    $list[$key]['use_cnt'] = M('UserInterestCoupon')->where(array('source'=>$val['source'],'status'=>1))->count();
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
     * create_time 2016/10/26
     */
    public function add(){
        if (! IS_POST) {
            $this->assign('list',M('ActivityConf')->field('id,name')->where('is_delete=0')->select());
            $this->display();
        } else {
            $a_id = trim(I('post.a_id', 0, 'int'));
            $type = trim(I('post.type', -1, 'int'));
            
            $min_amount = trim(I('post.min_amount', 0, 'int'));
            $max_amount = trim(I('post.max_amount', 0, 'int'));
            
            $title = trim(I('post.title', '', 'strip_tags'));
            
            $sub_title = trim(I('post.sub_title', '', 'strip_tags'));
            
            $min_invest_amount  = trim(I('post.min_invest_amount', 0, 'int'));
            
            $min_invest_days  = trim(I('post.min_invest_days', 0, 'int'));
            
            $amount  = trim(I('post.amount', 0, 'float'));
            
            $valid_days  = trim(I('post.valid_days', 0, 'int'));
            
            if(!$a_id) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动'));
            if(!$type) $this->ajaxReturn(array('status'=>0,'info'=>'请选择券包类型'));
            
            if ($min_amount >= $max_amount) {
                $this->ajaxReturn(array('status'=>0,'info'=>'投资金额区间右边的值必须大于左边'));
            }
            
            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题 '));
            if(!$sub_title) $this->ajaxReturn(array('status'=>0,'info'=>'请输入内容 '));
            
            if(!$amount) {
                if($type == 1)
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入红包金额 '));
                else if($type == 2) 
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入现金券金额 '));
                else 
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入加息券利率 '));
            }
                
            if($type == 2) {
                $min_invest_amount = 0;
                $min_invest_days = 0;
            } else {
                if(!$min_invest_amount) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最小投资金额'));
                if(!$min_invest_days) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最小投资期限 '));
            }
            
            if(!$valid_days) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入有效天数'));
            }
            
            $dd = array(
                'a_id' => $a_id,
                'type' => $type,
                'title' => $title,
                'sub_title' => $sub_title,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'valid_days' => $valid_days,
                'min_invest_days' => $min_invest_days,
                'min_invest_amount' => $min_invest_amount,
                'amount' => $amount,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time()
            );
            
            $rid = M('ActivityAward')->add($dd);
            
            if($rid) {
                
                if (M('ActivityAward')->where('a_id='.$a_id)->count()) {
                    M('ActivityConf')->where(array('id'=>$a_id))->save(array('status'=>0));
                }
                
                $s = '';
                if($type == 1){
                    $s = 'fq_h'.$rid;
                } else if($type == 2){
                    $s = 'fq_x'.$rid;
                } else {
                    $s = 'fq_j'.$rid;
                }
                
                M('ActivityAward')->where(array('id'=>$rid))->save(array('source'=>$s));
                
                $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/activityAward/index'));
                
            } else{
                $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            }
            
        }
    }
    
                
     /**
     * create_time 2016/10/26
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('ActivityAward')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('该记录不存在或已被删除');
                exit;
            }
            //判断奖励能否修改
            $enable = 0;
            $activity_start_time = M('ActivityConf')->where('id='.$detail['a_id'])->getField('start_time');
            if(strtotime($activity_start_time) < time()) {
                //已经开始了
                $enable = 1;
            }
            $this->assign('list',M('ActivityConf')->field('id,name')->where('is_delete=0')->select());
            $this->assign('enable', $enable);
            $this->assign('detail', $detail);
            $this->display();
        }else{
            
            $id = I('post.id', 0, 'int');
            
            $type = trim(I('post.type', 0, 'int'));
            
            $min_amount = trim(I('post.min_amount', 0, 'int'));
            $max_amount = trim(I('post.max_amount', 0, 'int'));
            
            $title = trim(I('post.title', '', 'strip_tags'));
            
            $sub_title = trim(I('post.sub_title', '', 'strip_tags'));
            
            $min_invest_amount  = trim(I('post.min_invest_amount', 0, 'int'));
            
            $min_invest_days  = trim(I('post.min_invest_days', 0, 'int'));
            
            $amount  = trim(I('post.amount', 0, 'float'));
            
            $valid_days  = trim(I('post.valid_days', 0, 'int'));
            
            if ($min_amount >= $max_amount) {
                $this->ajaxReturn(array('status'=>0,'info'=>'投资金额区间右边的值必须大于左边'));
            }
            
            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题 '));
            if(!$sub_title) $this->ajaxReturn(array('status'=>0,'info'=>'请输入内容 '));
            
            if(!$amount) {
                if($type == 1)
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入红包金额 '));
                else if($type == 2)
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入现金券金额 '));
                else
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入加息券利率 '));
            }
            
            if($type == 2) {
                $min_invest_amount = 0;
                $min_invest_days = 0;
            } else {
                if(!$min_invest_amount) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最小投资金额'));
                if(!$min_invest_days) $this->ajaxReturn(array('status'=>0,'info'=>'请输入最小投资期限 '));
            }
            
            if(!$valid_days) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入有效天数'));
            }
            
            $dd = array(
                'type' => $type,
                'title' => $title,
                'sub_title' => $sub_title,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'valid_days' => $valid_days,
                'min_invest_days' => $min_invest_days,
                'min_invest_amount' => $min_invest_amount,
                'amount' => $amount,
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'update_time' => time()
            );
            
            if(!M('ActivityAward')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/activityAward/index'));
        }
    }
    
    /**
     * 删除
     * create_time 2016/10/26
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('ActivityAward')->where('id')->where('id='.$id.' and is_delete = 0')->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
               
        if (M('ActivityAward')->where(array('id' => $id))->save(array('is_delete' => 1,'update_time' => time(),'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid']))) {
            
            if(M('ActivityAward')->where('a_id='.$info['a_id'])->count() <= 0) {
                M('ActivityConf')->where(array(
                    'id' => $info['a_id']
                ))->save(array(
                    'status' => 1
                ));
            }
            $this->ajaxReturn(array('status'=>1));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
    }
    
}