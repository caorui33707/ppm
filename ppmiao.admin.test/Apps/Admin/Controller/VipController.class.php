<?php
namespace Admin\Controller;

/**
* vip 相关管理 
* @date: 2016-12-20 下午7:55:01
* @author: hui.xu
*/

class VipController extends AdminController{
    
    private $pageSize = 20;    
    
    /**
    * 任务类型 管理
    * @date: 2016-12-20 下午7:53:41
    * @author: hui.xu
    * @param: 
    * @return:
    */
    public function mission_type_index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('vipMissionType')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('vipMissionType')->where(array('is_delete'=>0))->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
    * 任务类型增加
    * @date: 2016-12-20 下午7:56:43
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_type_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $name = trim(I('post.name', '', 'strip_tags'));
            if(!$name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入任务类型名称'
                ));
            }
            
            $vipMissionType = M('vipMissionType')->where("name='$name'")->find();
            
            if($vipMissionType && $vipMissionType['is_delete'] == 0) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '任务类型名称`'.$name.'`已经存在！'
                ));
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
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择广告图'));
            }
            
        
            $dd = array(
                'name' => $name,
                'image' => $image,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time(),
            );
            
            if($vipMissionType['is_delete'] == 1) {
                if(!M('vipMissionType')->where(array('id'=>$vipMissionType['id']))
                    ->save(array('is_delete'=>0,
                                 'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                                 'update_time'=>time(),
                                 'image'=>$image
                    ))) {
                        if(!M('vipMissionType')->add($dd)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
                    }
            } else {
                if(!M('vipMissionType')->add($dd)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/vip/mission_type_index'));
        }
    }
    
    /**
    * 任务类型编辑
    * @date: 2016-12-20 下午7:57:04
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_type_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $p = I('get.p', 0, 'int');
            $detail = M('vipMissionType')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('任务类型不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->assign('p', $p);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            $image = trim(I('post.image', '', 'strip_tags'));

            if(!$name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '任务类型名称不能为空'
                ));
            }
            
            $vipMissionType = M('vipMissionType')->where("name='$name' and is_delete = 0")->find();
            
            if($vipMissionType && $vipMissionType['id'] != $id ) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '任务类型名称`'.$name.'`已存在'
                ));
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
                }
            }

            $dd = array(
                'name' => $name,
                'image' =>$image,
                'update_time'=>time(),
                'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid']
            );
    
            if(!M('vipMissionType')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/vip/mission_type_index/p/'.$page));
        }
    }
    
    /**
    * 任务类型删除
    * @date: 2016-12-20 下午7:57:21
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_type_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
    
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        if(!M('vipMissionType')->where(array('id'=>$id))->save(array('is_delete'=>1,'edit_user_id'=>$uid,'update_time'=>time()))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
    
    /**
    * 会员等级列表
    * @date: 2016-12-20 下午7:53:04
    * @author: hui.xu
    * @param: 
    * @return:list
    */
    public function level_index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('vipLevel')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('vipLevel')->where(array('is_delete'=>0))->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach ($list as $kye=>$val) {
                $list[$kye]['people'] = M('userVipLevel')->where('vip_level='.$val['level'])->count();
                $list[$kye]['mission_count'] = M('vipMission')->where('level_id='.$val['level'].' and is_delete=0')->count();
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
    * 会员等级等级增加
    * @date: 2016-12-20 下午7:52:42
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function level_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $name = trim(I('post.name', '', 'strip_tags'));
            $subtitle = trim(I('post.subtitle', '', 'strip_tags'));
            $grow_val = trim(I('post.grow_val', 0, 'int'));
            $level = trim(I('post.level', 0, 'int'));
            
            $mission_name = trim(I('post.mission_name', '', 'strip_tags'));
            
            if(!$name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入vip等级'
                ));
            }
            
            if(!$mission_name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入vip任务名称'
                ));
            }
        
            if(M('vipLevel')->where("name='$name'")->count()) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => 'vip等级名称`'.$name.'`已经存在！'
                ));
            }
            
            if(M('vipLevel')->where('level='.$level)->count()) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => 'vip等级值`'.$level.'`已经存在！'
                ));
            }

            $dd = array(
                'name' => $name,
                'mission_name'=>$mission_name,
                'grow_val' => $grow_val,
                'level' =>$level,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time(),
            );
        
            if(!M('vipLevel')->add($dd)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/vip/level_index'));
        }
    }
    /**
    * 会员等级编辑
    * @date: 2016-12-20 下午7:52:00
    * @author: hui.xu
    * @param: 
    * @return:
    */
    public function level_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 0, 'int');
            $detail = M('vipLevel')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('任务类型不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->assign('page', $page);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            $grow_val = trim(I('post.grow_val', 0, 'int'));
            $mission_name = trim(I('post.mission_name', '', 'strip_tags'));
            
            if(!$mission_name) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入vip任务名称'
                ));
            }
            
            $dd = array(
                'mission_name' =>$mission_name,
                'grow_val' => $grow_val,
                'update_time'=>time(),
                'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid']
            );
        
            if(!M('vipLevel')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/vip/level_index/p/'.$page));
        }
    }
    /**
    * 会员等级删除
    * @date: 2016-12-20 下午7:51:50
    * @author: hui.xu
    * @param: 
    * @return:
    */
    public function level_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        $level_id = I('post.level_id', -1, 'int');
        
        if(M('vipMission')->where('is_delete=0 and level_id='.$level_id)->count()) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请先删除该会员等级下面的任务'));
        }
        
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        if(!M('vipLevel')->where(array('id'=>$id))->save(array('is_delete'=>1,'edit_user_id'=>$uid,'update_time'=>time()))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
    
    /**
    * vip对应的任务列表
    * @date: 2016-12-21 下午12:04:54
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_index(){
        
        $level_id= trim(I('get.level_id',0,'int'));
                
        $cond = 'is_delete = 0 and level_id = '.$level_id;
        $page = I('get.p', 1, 'int'); // 页码
        $counts = M('vipMission')->where($cond)->count();
        $Page = new \Think\Page($counts, $this->pageSize);
        $show = $Page->show();
        $list = M('vipMission')->where($cond)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        //2 签到   3，钱包、4投资 5、邀请
        //$type_arr = array(2,3,4,6);
        
        foreach ($list as $key=>$val) {
            $list[$key]['mission_type_name'] = M('vipMissionType')->where('id='.$val['mission_type'])->getField('name');
            /*
            if(in_array($val['mission_type'], $type_arr)) {
                
            }*/
        }
        
        $params = array(
            'page' => $page,
            'level_name' => M('vipLevel')->where('level='.$level_id)->getField('name'),
        );
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
        
        
    }
    
    /**
    * 任务增加
    * @date: 2016-12-21 下午12:06:48
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_add(){
        if(!IS_POST){
            $mission_type_list = M('vipMissionType')->field('id,name')->where('is_delete=0')->select();
            $vip_level_list = M('vipLevel')->field('id,level,name')->where('is_delete=0')->select();
            
            $params = array(
                'mission_type_list'=>$mission_type_list,
                'vip_level_list'=>$vip_level_list
            );
            
            $this->assign('params',$params);
            $this->display();
        }else{
            
            $title = trim(I('post.title', '', 'strip_tags'));
            $intro = trim(I('post.intro', '', 'strip_tags'));
            
            if(!$title) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入任务标题'
                ));
            }
            
            if(!$intro) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入任务说明'
                ));
            }
            
            $mission_type = I('post.mission_type', 0, 'int');
            
            $val = '';
            
            if($mission_type == 2) {
                $signin_day = trim(I('post.signin_day', 0, 'int'));
                
                if(!$signin_day) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入签到天数'
                    ));
                }
                $val['signin_day'] = $signin_day;
                
            } else if($mission_type == 3) {
                
                $wallet_amount = trim(I('post.wallet_amount', 0, 'int'));
                $wallet_day = trim(I('post.wallet_day', 0, 'int'));
                $wallet_type = trim(I('post.wallet_type', 1, 'int'));
                
                
                if(!$wallet_amount) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入钱包金额，金额肯定是纯数字'
                    ));
                }
                
                $val['wallet_amount'] = $wallet_amount;
                
                if(!$wallet_day) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入钱包天数'
                    ));
                }
                
                $val['wallet_day'] = $wallet_day;
                $val['type'] = $wallet_type;
                
            } else if($mission_type == 4) {
                
                $invest_amount = trim(I('post.invest_amount', 0, 'int'));
                $invest_day = trim(I('post.invest_day', 0, 'int'));
                
                if(!$invest_amount) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入定期投资金额'
                    ));
                }
                
                $val['invest_amount'] = $invest_amount;
                
                if(!$invest_day) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入定期投资天数'
                    ));
                }
                
                $val['invest_day'] = $invest_day;
                
            } else if($mission_type == 6) {
                $invite_num = trim(I('post.invite_num', 0, 'int'));
                if(!$invite_num) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入邀请人数'
                    ));
                }
                
                $val['invite_num'] = $invite_num;
            }
            
            $jump = I('post.jump', 0, 'int');
            $jump_url = '';
            if($jump == 8){
                
                $jump_url = trim(I('post.jump_url', '', 'strip_tags'));
                
                if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$jump_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的url'
                    ));
                }
            }
            
            
            $jf_val = trim(I('post.jf_val', 0, 'int'));
            $grow_val = trim(I('post.grow_val', 0, 'int'));
                        
            $level_arr = $_REQUEST['level_id'];
            
            if(!$level_arr) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请关联到同步vip'
                ));
            }
            
            if($val && !empty($val)) {
                $val = json_encode($val);
            }

            $dd = array(
                'title'=>$title,
                'intro'=>$intro,
                'mission_type'=>$mission_type,
                'value'=>$val,
                'jf_val'=>$jf_val,
                'grow_val'=>$grow_val,
                'jump'=>$jump,
                'jump_url'=>$jump_url,
                'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'create_time'=>time(),
            );
            
            foreach ($level_arr as $val) {
                $dd['level_id'] = $val;
                if(!M('vipMission')->add($dd)) {
                    break;
                    $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
                }
            }
        
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/vip/level_index'));
        }    
    }
    
    /**
    * 任务编辑
    * @date: 2016-12-21 下午12:07:36
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('vipMission')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('任务不存在或已被删除');exit;
            }  
                
            $type_arr = array(2,3,4,6);
            
            if(in_array($detail['mission_type'],$type_arr)) {
                $val = json_decode($detail['value'],true);
                if($detail['mission_type'] == 2) {
                    $detail['signin_day'] = $val['signin_day'];
                } else if($detail['mission_type'] == 3){
                    $detail['wallet_amount'] = $val['wallet_amount'];
                    $detail['wallet_day'] = $val['wallet_day'];
                    $detail['wallet_type'] = $val['type'];
                } else if($detail['mission_type'] == 4){
                    $detail['invest_amount'] = $val['invest_amount'];
                    $detail['invest_day'] = $val['invest_day'];
                } else {
                    $detail['invite_num'] = $val['invite_num'];
                }
            }
            
            $mission_type_list = M('vipMissionType')->field('id,name')->where('is_delete=0')->select();
            $vip_level_list = M('vipLevel')->field('id,level,name')->where('is_delete=0')->select();
            $params = array(
                'mission_type_list'=>$mission_type_list,
                'vip_level_list'=>$vip_level_list
            );
            $this->assign('detail', $detail);
            $this->assign('params',$params);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $level_id = I('post.level_id', 0, 'int');
            
            $title = trim(I('post.title', '', 'strip_tags'));
            $intro = trim(I('post.intro', '', 'strip_tags'));
                
            if(!$title) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入任务标题'
                ));
            }
        
            if(!$intro) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入任务说明'
                ));
            }
        
            $mission_type = I('post.mission_type', 0, 'int');
        
            $val = '';
        
            if($mission_type == 2) {
                $signin_day = trim(I('post.signin_day', 0, 'int'));
        
                if(!$signin_day) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入签到天数'
                    ));
                }
                $val['signin_day'] = $signin_day;
        
            } else if($mission_type == 3) {
        
                $wallet_amount = trim(I('post.wallet_amount', 0, 'int'));
                $wallet_day = trim(I('post.wallet_day', 0, 'int'));
                
                $wallet_type = trim(I('post.wallet_type', 1, 'int'));
        
                if(!$wallet_amount) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入钱包金额，金额肯定是纯数字'
                    ));
                }
        
                if(!$wallet_day) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入钱包天数'
                    ));
                }
                $val['wallet_amount'] = $wallet_amount;
                $val['wallet_day'] = $wallet_day;
                
                $val['type'] = $wallet_type;
        
            } else if($mission_type == 4) {
        
                $invest_amount = trim(I('post.invest_amount', 0, 'int'));
                $invest_day = trim(I('post.invest_day', 0, 'int'));
        
                if(!$invest_amount) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入定期投资金额'
                    ));
                }
        
                if(!$invest_day) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入定期投资天数'
                    ));
                }
                $val['invest_amount'] = $invest_amount;
                $val['invest_day'] = $invest_day;
        
            } else if($mission_type == 6) {
                $invite_num = trim(I('post.invite_num', 0, 'int'));
                if(!$invite_num) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入邀请人数'
                    ));
                }
        
                $val['invite_num'] = $invite_num;
            }
        
            $jump = I('post.jump', 0, 'int');
            $jump_url = '';
            if($jump == 8){
        
                $jump_url = trim(I('post.jump_url', '', 'strip_tags'));
        
                if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$jump_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的url'
                    ));
                }
            }
        
            $jf_val = trim(I('post.jf_val', 0, 'int'));
            $grow_val = trim(I('post.grow_val', 0, 'int'));
        
            
            if($val && !empty($val)) {
                $val = json_encode($val);
            }
            
            $dd = array(
                'title'=>$title,
                'intro'=>$intro,
                'mission_type'=>$mission_type,
                'value'=>$val,
                'jf_val'=>$jf_val,
                'grow_val'=>$grow_val,
                'jump'=>$jump,
                'jump_url'=>$jump_url,
                'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'update_time'=>time(),
            );
            
            if(!M('vipMission')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
        
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/vip/mission_index/level_id/'.$level_id));
        }
    }
    
    /**
    * 任务删除
    * @date: 2016-12-21 下午12:08:02
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function mission_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        if(!M('vipMission')->where(array('id'=>$id))->save(array('is_delete'=>1,'edit_user_id'=>$uid,'update_time'=>time()))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }




}