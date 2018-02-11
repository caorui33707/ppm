<?php
namespace Admin\Controller;

/**
 * 抽奖活动配置
 */
class LotteryController extends AdminController
{

    private $pageSize = 15;
    
    /**
     * 
     * create_time 2016/10/28
     */
    public function lottery_base_index(){

        if(!IS_POST){
                        
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('LotteryBase')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('LotteryBase')->where(array('is_delete'=>0))->order('id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                
                $list[$key]['cond'] = M('LotteryCond')->where('lottery_id = '.$val['id'].' and is_delete=0')->count();
                $list[$key]['award'] = M('LotteryAward')->where('lottery_id = '.$val['id'].' and is_delete=0')->count();
                
                if($val['start_time'] > time()) {
                    $list[$key]['state'] = '待上线';
                    $list[$key]['color'] = '000000';
                } else if(time() > $val['start_time'] && time() < $val['end_time']){
                    $list[$key]['state'] = '进行中';
                    $list[$key]['color'] = '#FF0000';
                } else if(time() > $val['end_time']) {
                    $list[$key]['state'] = '已结束';
                    $list[$key]['color'] = '#A1A1A1';
                }
            }
            
            $params = array(
                'page' => $page,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display('base_index');
        }
    }
    
    
    
    /**
     * 抽奖基本信息配置
     * create_time 2016/10/28
     */
    public function lottery_base_add(){
        if (! IS_POST) {
            $this->display('base_add');
        } else {
            $name = trim(I('post.name', '', 'strip_tags'));
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $tag = trim(I('post.tag', 0, 'int'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'活动名称不能为空'));
            
            if(M('LotteryBase')->where(array('name'=>$name))->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'活动名称`'.$name.'`已经存在'));
            }
            
            if(!$start_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动开始时间'));
            if(!$end_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动结束时间'));
            
            $dd = array(
                'name' => $name,
                'start_time' => strtotime($start_time),
                'end_time' => strtotime($end_time),
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time(),
                'tag' => $tag,
                'memo' => $memo
            );
            $id = M('LotteryBase')->add($dd);
            if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_base_index'));
        }
    }
    
                
     /**
     * create_time 2016/10/28
     */
    public function lottery_base_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('LotteryBase')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('该抽奖不存在或已被删除');
                exit;
            }
            //设置活动开始时间、tag 能否修改、
            $enable = 0;
            if($detail['start_time'] < time()) {
                //已经开始了
                $enable = 1;
            }
            $this->assign('enable', $enable);
            $this->assign('detail', $detail);
            $this->display('base_edit');
        }else{
            
            $id = I('post.id', 0, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $tag = trim(I('post.tag', 0, 'int'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            $enable = trim(I('post.enable', '0', 'int'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'活动名称不能为空'));
            
            if(M('LotteryBase')->where("name='$name'".' and id !='.$id)->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'活动名称`'.$name.'`已经存在'));
            }
            
            if(!$enable){
                $start_time = trim(I('post.start_time', '', 'strip_tags'));
                if(!$start_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动开始时间'));
                $tag = trim(I('post.tag', 0, 'int'));
                
                $dd['start_time'] = strtotime($start_time);
                $dd['tag'] = $tag;
            }
            
            if(!$end_time) $this->ajaxReturn(array('status'=>0,'info'=>'请选择活动结束时间'));
            
            $dd['name'] = $name;
            $dd['end_time'] = strtotime($end_time);
            $dd['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['update_time'] = time();
            $dd['memo'] = $memo;
            
            if(!M('LotteryBase')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_base_index'));
        }
    }
    
    /**
     * 删除
     * create_time 2016/10/25
     */
    public function lottery_base_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('LotteryBase')->where('id')->where('id='.$id.' and is_delete = 0')->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
        
               
        if (! M('LotteryBase')->where(array(
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
    
    /**
     * 抽奖条件
     */
    public function lottery_cond_index(){
        $param['lottery_id'] = I('get.base_id', 0, 'int');
        $param['lottery_stauts'] = 0;
        $list = '';
        if($param['lottery_id']) {
            $end_time = M('LotteryBase')->where('id='.$param['lottery_id'])->getField('end_time');
            if(time() > $end_time){
                $param['lottery_stauts'] = 1;
            }
            $list = M('LotteryCond')->where(array('is_delete'=>0,'lottery_id'=>$param['lottery_id']))->order('id asc')->select();
        }
        $this->assign('param', $param);
        $this->assign('list', $list);
        $this->display('cond_index');
    }
    
    public function lottery_cond_add(){
        
        $lottery_id = I('get.lottery_id', 0, 'int');
        
        if (! IS_POST) {
            $this->assign('lottery_id',$lottery_id);
            $this->display('cond_add');
        } else {
            $lottery_id = I('post.lottery_id', 0, 'int');
            $min_amount = trim(I('post.min_amount', 0, 'int'));
            $max_amount = trim(I('post.max_amount', 0, 'int'));
            $invest_days = trim(I('post.invest_days', 0, 'int'));
            $count = I('post.count', 1, 'int');
            if($min_amount <= 0 ){
                $this->ajaxReturn(array('status'=>0,'info'=>'投资金额区间左边的表单必须大于0'));
            }
            $dd = array(
                'lottery_id' => $lottery_id,
                'invest_days' => $invest_days,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'count'=>$count,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time(),
            );
            $id = M('LotteryCond')->add($dd);
            if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_cond_index/base_id/'.$lottery_id));
        }
    }
    
    public function lottery_cond_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('LotteryCond')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('该记录不存在或已被删除');
                exit;
            }
            
            /*
            $lottery_base = M('LotteryBase')->where('id='.$detail['lottery_id'])->find();
            //设置活动开始时间、tag 能否修改、
            $enable = 0;
            if($lottery_base['start_time'] < time()) {
                //已经开始了
                $enable = 1;
            }
            $this->assign('enable', $enable);
            */
            $this->assign('detail', $detail);
            $this->display('cond_edit');
        }else{
            $id = I('post.id', 0, 'int');
            $lottery_id = I('post.lottery_id', 0, 'int');
            $min_amount = trim(I('post.min_amount', 0, 'int'));
            $max_amount = trim(I('post.max_amount', 0, 'int'));
            $invest_days = trim(I('post.invest_days', 0, 'int'));
            $count = I('post.count', 1, 'int');
            
            if($min_amount <= 0 ){
                $this->ajaxReturn(array('status'=>0,'info'=>'投资金额区间左边的表单必须大于0'));
            }
            
            $dd = array(
                'lottery_id' => $lottery_id,
                'invest_days' => $invest_days,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'count'=>$count,
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'update_time' => time(),
            );
        
            if(!M('LotteryCond')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_cond_index/base_id/'.$lottery_id));
            
        }
    }
    
    public function lottery_cond_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('LotteryCond')->where('id')->where('id='.$id.' and is_delete = 0')->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
        
         
        if (! M('LotteryCond')->where(array(
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
    
    /**
     * 奖品列表
     */
    public function lottery_award_index(){
        $param['lottery_id'] = I('get.base_id', 0, 'int');
        $param['lottery_stauts'] = 0;
        $param['probability'] = 0;
        $list = '';
        if($param['lottery_id']) {
            $end_time = M('LotteryBase')->where('id='.$param['lottery_id'])->getField('end_time');
            if(time() > $end_time){
                $param['lottery_stauts'] = 1;
            }
            $list = M('LotteryAward')->where(array('is_delete'=>0,'lottery_id'=>$param['lottery_id']))->order('id asc')->select();
            
            foreach($list as $key => $val){
            
                if($val['type'] == 1) {
                    $list[$key]['use_cnt'] = M('UserRedenvelope')->where(array('source'=>$val['source'],'status'=>1))->count();
                } else if($val['type'] == 2){
                    $list[$key]['use_cnt'] = M('UserCashCoupon')->where(array('source'=>$val['source'],'status'=>1))->count();
                } else if($val['type'] == 3){
                    $list[$key]['use_cnt'] = M('UserInterestCoupon')->where(array('source'=>$val['source'],'status'=>1))->count();
                } else {
                    $list[$key]['use_cnt'] = '-';
                }
                $list[$key]['total'] = M('LotteryLog')->where("user_id>0 and lottery_id=" . $val['lottery_id'] . ' and lottery_award_id=' . $val['id'])->count();
                $param['probability'] += $val['probability'];
            }
        }
        $this->assign('param', $param);
        $this->assign('list', $list);
        $this->display('award_index');
    }
    
    /**
     * 奖品添加
     */
    public function lottery_award_add(){
        
        $lottery_id = I('get.lottery_id', 0, 'int');
        
        if (! IS_POST) {
            $this->assign('lottery_id',$lottery_id);
            $max_id = M('LotteryAward')->max('id');
            $this->assign('prize_id',M('LotteryAward')->max('id') + 1);
            $this->display('award_add');
        } else {
            
            $prizeId = trim(I('post.prizeId', 0, 'int'));
            
            $lottery_id = I('post.lottery_id', 0, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));
            $probability = trim(I('post.probability', 0, 'int'));
            
            $type = trim(I('post.type', 0, 'int'));
            
            if(!$prizeId){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入奖品Id,必须是唯一的 '));
            }
            
            if(M('LotteryAward')->where(array('id'=>$prizeId))->count() > 0) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入奖品Id,`'.$prizeId.'`已经存在!'));
            }
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'请输入奖品名称 '));
            
            $total_probability = M('LotteryAward')->where(array('is_delete'=>0,'lottery_id'=>$lottery_id))->sum('probability');
            
            if($total_probability + $probability > 1000){
                $this->ajaxReturn(array('status'=>0,'info'=>'该活动的中奖概率已经超了上线1000.请调整'));
            }
            
            if ($type == 0) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择奖品类型 '));
            } 
            
            $dd['id'] = $prizeId;
            $dd['lottery_id'] = $lottery_id;
            $dd['name'] = $name;
            $dd['probability'] = $probability;
            $dd['type'] = $type;
            
            if($type >=1 && $type <=3){
                
                $title = trim(I('post.title', '', 'strip_tags'));
                
                $sub_title = trim(I('post.sub_title', '', 'strip_tags'));
                
                $min_invest_amount  = trim(I('post.min_invest_amount', 0, 'int'));
                
                $min_invest_days  = trim(I('post.min_invest_days', 0, 'int'));
                
                $amount  = trim(I('post.amount', 0, 'float'));
                
                $valid_days  = trim(I('post.valid_days', 0, 'int'));
                
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
                
                $dd['title'] = $title;
                $dd['sub_title'] = $sub_title;
                $dd['min_invest_amount'] = $min_invest_amount;
                $dd['min_invest_days'] = $min_invest_days;
                $dd['valid_days'] = $valid_days;
                $dd['amount'] = $amount;
            }
            
            $dd['add_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['create_time'] = time();
            
            
            if(M('LotteryAward')->add($dd)) {
                if($type >=1 && $type <=3){
                    $s = '';
                    if($type == 1){
                        $s = 'cj_h'.$prizeId;
                    } else if($type == 2){
                        $s = 'cj_x'.$prizeId;
                    } else {
                        $s = 'cj_j'.$prizeId;
                    }
                    M('LotteryAward')->where(array('id'=>$prizeId))->save(array('source'=>$s));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_award_index/base_id/'.$lottery_id));
            }
            $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
        }
    }
    
    public function lottery_award_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('LotteryAward')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('该记录不存在或已被删除');
                exit;
            }
        
            /*
             $lottery_base = M('LotteryBase')->where('id='.$detail['lottery_id'])->find();
             //设置活动开始时间、tag 能否修改、
             $enable = 0;
             if($lottery_base['start_time'] < time()) {
             //已经开始了
             $enable = 1;
             }
             $this->assign('enable', $enable);
             */
            $this->assign('detail', $detail);
            $this->display('award_edit');
        }else{
            
            $id = I('post.id', 0, 'int');
            $lottery_id = I('post.lottery_id', 0, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));
            $probability = trim(I('post.probability', 0, 'int'));
            
            $type = trim(I('post.type', 0, 'int'));
            
            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'请输入奖品名称 '));
            //if(!$probability) $this->ajaxReturn(array('status'=>0,'info'=>'请输入中奖概率 '));
            
            $total_probability = M('LotteryAward')->where(array('is_delete'=>0,'lottery_id'=>$lottery_id))->sum('probability');
            
            if($total_probability + $probability > 1000){
                //$this->ajaxReturn(array('status'=>0,'info'=>'该活动的中奖概率已经超了上线1000.请调整'));
            }
            
            $dd['lottery_id'] = $lottery_id;
            $dd['name'] = $name;
            $dd['probability'] = $probability;
            
            //$dd['type'] = $type;
            
            if($type >=1 && $type <=3){
            
                $title = trim(I('post.title', '', 'strip_tags'));
            
                $sub_title = trim(I('post.sub_title', '', 'strip_tags'));
            
                $min_invest_amount  = trim(I('post.min_invest_amount', 0, 'int'));
            
                $min_invest_days  = trim(I('post.min_invest_days', 0, 'int'));
            
                $amount  = trim(I('post.amount', 0, 'float'));
            
                $valid_days  = trim(I('post.valid_days', 0, 'int'));
            
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
            
                $dd['title'] = $title;
                $dd['sub_title'] = $sub_title;
                $dd['min_invest_amount'] = $min_invest_amount;
                $dd['min_invest_days'] = $min_invest_days;
                $dd['valid_days'] = $valid_days;
                $dd['amount'] = $amount;
            }
            
            $dd['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['update_time'] = time();
            
            if(!M('LotteryAward')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_award_index/base_id/'.$lottery_id));
        }
    }
    
    public function lottery_award_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('LotteryAward')->where('id')->where('id='.$id.' and is_delete = 0')->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
        
         
        if (! M('LotteryAward')->where(array(
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
    
    /**
     * 
     * 抽奖日志
     * 
     */
    public function lottery_log(){
        
        $lottery_id = I('get.lotteryId','0','int');
        //$cond = '1 = 1';
        
        //if($lottery_id > 0) {
        $cond .= 'lottery_id='.$lottery_id;
        //}
        $counts = M('LotteryLog')->where($cond)->count();
        
        $Page = new \Think\Page($counts, $this->pageSize);
        $show = $Page->show();
        $list = M('LotteryLog')->where($cond)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        foreach($list as $key => $val){

            $list[$key]['real_name'] = ' - ';
            if($val['user_id'] > 0) {
                $list[$key]['real_name'] = M('User')->where('id='.$val['user_id'])->getField('real_name');
            }
            $list[$key]['lottery_name'] = M('LotteryBase')->where('id='.$val['lottery_id'])->getField('name');
            $list[$key]['lottery_award_name'] = M('LotteryAward')->where('id='.$val['lottery_award_id'])->getField('name');
        }
        
        $lottery_list = M('LotteryBase')->field('id,name,start_time,end_time')->where('is_delete = 0')->select();
        foreach($lottery_list as $key => $val){
            if($val['start_time'] > time()){
                $lottery_list[$key]['lottery_status'] = '没开始';
            }
            elseif ($val['start_time'] < time() && time()<$val['end_time'])
            {
                $lottery_list[$key]['lottery_status'] = '进行中';
            } else{
                $lottery_list[$key]['lottery_status'] = '已结束';
            }
        }
        
        $params = array(
            'lottery_list'=>$lottery_list,
            'lottery_id'=>$lottery_id
        );
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display('lottery_log');
    }
    
    /**
     * 添加中奖假数据
     */
    public function lottery_log_add(){
        if(IS_POST){
            $lottery_id = I('post.lottery_id','-1','int');
            if($lottery_id<=0){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择抽奖活动'));
            }
            
            $lottery_award_id = I('post.lottery_award_id','-1','int');
            
            if($lottery_award_id<=0){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择奖品'));
            }
            
            $user_name = I('post.user_name','','strip_tags');
            $user_id = 0;
            
            $dd = array(
                'user_id'=>0,
                'create_time'=>time(),
                'user_name'=>$user_name,
                'lottery_id'=>$lottery_id,
                'lottery_award_id'=>$lottery_award_id
            );
            if(M('LotteryLog')->add($dd)) {
                $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/lottery/lottery_log'));
            } else{
                $this->ajaxReturn(array('status'=>0,'info'=>'添加失败，请稍后再试！'));
            }
            
        } else{
            $this->assign('lottery_list',M('LotteryBase')->field('id,name')->where('is_delete=0')->order('id desc')->select());
            $this->assign('mobile',auto_create_phone_for_ghost());
            $this->display('lottery_log_add');
        }
    }
    
    public function get_lottery_award(){
        $lottery_id = I('post.lottery_id',0,'int');
        if($lottery_id > 0){
            $list = M('LotteryAward')->field('id,name')->where('lottery_id='.$lottery_id.' and is_delete=0')->select();
            if($list){
                $this->ajaxReturn(array('status'=>1,'data'=>$list));
            }
        }
         $this->ajaxReturn(array('status'=>0));
    }
    
}