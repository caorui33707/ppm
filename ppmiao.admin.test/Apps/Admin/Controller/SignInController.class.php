<?php
namespace Admin\Controller;

/**
 * 签到管理 
*/

class SignInController extends AdminController{
    
    private $pageSize = 20;

    public function index(){
        
        if(IS_POST){
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $quest="";
            if($start_time) $quest .= '/st/'.str_replace(' ', '|', $start_time);
            if($end_time) $quest .= '/et/'.str_replace(' ', '|', $end_time);
            redirect(C('ADMIN_ROOT') . '/signIn/index'.$quest);
        }else{
            $start_time = I("get.st",date("Y-m-d",time()-(10*24*3600)),'strip_tags');//开始时间
            $end_time = I('get.et', date('Y-m-d', time()+(10*34*3600)), 'strip_tags');//结束时间
            if($start_time) $cond[] = "sign_date>='".$start_time."'";
            if($end_time) $cond[] = "sign_date<='".$end_time."'";
            $condition = implode(" and ",$cond);
            $params=array(
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
            $counts = M('signIn')->where($condition)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('signIn')->where($condition)->order("id desc")->limit($Page->firstRow.",".$Page->listRows)->select();
            
            $now_date = date("Y-m-d",time());
            $this->assign("params",$params);
            $this->assign("now_date",$now_date);
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }
    }

    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            $jf_val = trim(I('post.jf_val', 0,'int'));
            $grow_val = trim(I('post.grow_val', 0,'int'));
            $memo = trim(I('post.memo', '','strip_tags'));
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            if(!$jf_val) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入签到赠送积分'
                ));
            }
            
            if(!$grow_val) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入签到赠送成长值'
                ));
            }
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择开始时间'
                ));
            }
            
            $dd = array(
                'jf_val'=>$jf_val,
                'grow_val'=>$grow_val,
                'memo'=>$memo,
                'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'create_time' =>time()
            );
            
            $start_time = date('Y-m-d',strtotime($start_time));
            
           if($end_time) {
               $day = (strtotime($end_time) - strtotime($start_time))/86400;
               for ($i=0;$i<=$day;$i++) {
                   
                   $dd['sign_date'] = date('Y-m-d',(strtotime($start_time) + (86400 * $i)));
                   
                   if(M('signIn')->where("sign_date='".$dd['sign_date']."'")->find()) {
                       continue;
                   }
                   
                   if(!M('signIn')->add($dd)) {
                       $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
                   }
               }
               
           } else {
               $dd['sign_date'] = $start_time;
               if(M('signIn')->where("sign_date='".$dd['sign_date']."'")->find()) {
                   $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,这个日期的数据已经添加'));
               }
               if(!M('signIn')->add($dd)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
           }
           $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/signIn/index'));
        }
    }

    /**
     * 编辑文字广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('signIn')->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('签到不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            
            $jf_val = trim(I('post.jf_val', 0,'int'));
            $grow_val = trim(I('post.grow_val', 0,'int'));
            $memo = trim(I('post.memo', '','strip_tags'));
            
            if(!$jf_val) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入签到赠送积分'
                ));
            }
            
            if(!$grow_val) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入签到赠送成长值'
                ));
            }
            
            $dd = array(
                'jf_val'=>$jf_val,
                'grow_val'=>$grow_val,
                'memo'=>$memo,
                'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'update_time' =>time()
            );
            
            if(!M('signIn')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/signIn/index/p/'.$page));
        }
    }
}