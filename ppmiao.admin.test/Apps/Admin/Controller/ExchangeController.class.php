<?php
namespace Admin\Controller;

/**
* 积分兑换
* @date: 2016-12-28 上午11:40:38
* @author: hui.xu
*/
class ExchangeController extends AdminController
{

    private $pageSize = 15;
    
    /**
    * 积分兑换列表 
    * @date: 2016-12-28 上午11:41:29
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function index(){
        $level_id = trim(I('get.level_id', -1, 'int'));
        $type = trim(I('get.type', 0, 'int'));
        $page = trim(I('get.p', 1, 'int'));
        
        $cond = 'is_delete = 0 ';
        
        if($level_id != -1 ) {
            $cond .= ' and level_id = '.$level_id;
        }
        
        if($type > 0 ){
            $cond .= ' and type = '.$type;
        }
                    
        $count = M('exchange')->where($cond)->count();
        $Page = new \Think\Page($count, $this->PageSize);
        $show = $Page->show();
        $list = M('exchange')->where($cond)
            ->order('create_time desc ,update_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        foreach($list as $key => $val){
            
            $list[$key]['level_name'] = M('vipLevel')->where('level='.$val['level_id'] .' and is_delete=0')->getField('name');
            if($val['type'] == 1) {
                $list[$key]['use_cnt'] = M('UserRedenvelope')->where(array('source'=>$val['source'],'status'=>1))->count();
            } else if($val['type'] == 2){
                $list[$key]['use_cnt'] = M('UserCashCoupon')->where(array('source'=>$val['source'],'status'=>1))->count();
            } else if($val['type'] == 3){
                $list[$key]['use_cnt'] = M('UserInterestCoupon')->where(array('source'=>$val['source'],'status'=>1))->count();
            } else {
                $list[$key]['use_cnt'] = '-';
            }
        }
        
        $params = array(
            'level_list'=>M('vipLevel')->field('level,name')->where('is_delete = 0')->select(),
            'type' =>$type,
            'level_id'=>$level_id
        );
        
        $this->assign('params', $params);
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
    * 添加兑换
    * @date: 2016-12-28 上午11:44:04
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function add(){
        if (! IS_POST) {
            $level = M('vipLevel')->field('level,name')->where('is_delete = 0')->select();
            $this->assign('level_list',$level);
            $this->display();
        } else {
            $level_id = I('post.level_id', -1, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            
            $jf_val = trim(I('post.jf_val', 0, 'int'));
            
            $type = trim(I('post.type', 0, 'int'));
            
            $status = trim(I('post.status', 0, 'int'));
            
            if ($level_id < 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择vip等级'
                ));
            }
            
            if (! $name)
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入奖品名称 '
                ));
                
            if (! $jf_val)
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入兑换所需积分 '
                ));
            
            if ($type == 0) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择奖品类型 '));
            } 
            
            $image = '';
            
            if($_FILES){
                $config = array(
                    'maxSize'    =>    3145728,
                    'rootPath'   => C('UPLOAD_PATH'),
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
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择上传图片'));
            }
            
            
            $dd['level_id'] = $level_id;
            $dd['name'] = $name;
            $dd['type'] = $type;
            $dd['jf_val'] = $jf_val;
            $dd['status'] = $status;
            $dd['img'] = $image;
            
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
                $dd['sub_title'] = $title;
                $dd['min_invest_amount'] = $min_invest_amount;
                $dd['min_invest_days'] = $min_invest_days;
                $dd['valid_days'] = $valid_days;
                $dd['amount'] = $amount;
            }
            
            $dd['add_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['create_time'] = time();
            
            $rid = M('Exchange')->add($dd);
            
            if($rid) {
                if($type >=1 && $type <=3){
                    $s = '';
                    if($type == 1){
                        $s = 'exchange_h'.$rid;
                    } else if($type == 2){
                        $s = 'exchange_x'.$rid;
                    } else {
                        $s = 'exchange_j'.$rid;
                    }
                    M('Exchange')->where(array('id'=>$rid))->save(array('source'=>$s));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/exchange/index'));
            }
            $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
        }
    }
    
    
    
    /**
    * 兑换编辑
    * @date: 2016-12-28 上午11:44:39
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('Exchange')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('该记录不存在或已被删除');
                exit;
            }
            $this->assign('level_list',M('vipLevel')->field('level,name')->where('is_delete = 0')->select());
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            $jf_val = trim(I('post.jf_val', 0, 'int'));
            $status = trim(I('post.status', 0, 'int'));
            $type = trim(I('post.type', 0, 'int'));
            $image = trim(I('post.image', '', 'strip_tags'));
            $detail = M('Exchange')->where(array('id'=>$id,'is_delete'=>0))->find();
            
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
                }
            }
            
            
            //没有兑换过，可以修改以下信息。
            if($detail['total'] <= 0) {

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
            }
            
            $dd['name'] = $name;
            $dd['jf_val'] = $jf_val;
            $dd['status'] = $status;
            $dd['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['update_time'] = time();
            $dd['img'] = $image;
            
            if(!M('Exchange')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/exchange/index'));
        }
    }
    
    /**
    * 函数用途描述
    * @date: 2016-12-28 上午11:45:09
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        
        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        
        $info = M('exchange')->where('id')->where('id='.$id.' and is_delete = 0')->find();
        
        if(!$info) {
            $this->ajaxReturn(array('status'=>0,'info'=>'要删除的记录不存在'));
        }
        
        if($info['total'] > 0) {
            $this->ajaxReturn(array('status'=>0,'info'=>'该奖品已经有兑换记录，没法删除，可以该记录的状态'));
        }
         
        if (! M('exchange')->where(array(
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
    * 兑换明细 
    * @date: 2016-12-28 上午11:42:47
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function exchange_log(){
        $exchange_id = I('get.exchange_id','0','int');
        $counts = M('userExchangeLog')->where('exchange_id = '.$exchange_id)->count();
        $Page = new \Think\Page($counts, $this->pageSize);
        $show = $Page->show();
        $list = M('userExchangeLog')->where('exchange_id = '.$exchange_id)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $key => $val){

            $list[$key]['real_name'] = ' - ';
            
            $user_info = M('User')->field('username,real_name')->where('id='.$val['uid'])->find();
            
            if($user_info['real_name']) {
                $list[$key]['real_name'] = $user_info['real_name'];
            }
            $list[$key]['user_name'] = $user_info['username'];
        }
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display('exchange_log');
    }
    
}