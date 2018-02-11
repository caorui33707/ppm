<?php
namespace Admin\Controller;

/**
 * 银行
 * @package Admin\Controller
 */
class BankController extends AdminController{
    
    private $pageSize = 10;    

    public function bank_limit(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $key = urldecode(I('get.key', '', 'strip_tags')); // 银行卡名称
            $count = 10; // 每页显示条数

            $bankLimitObj = M('bankLimit');

            $conditions = array();
            if($key){
                $key = urldecode($key);
                $conditions = "bank_name='".$key."'";
            }

            $counts = $bankLimitObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $bankLimitObj->where($conditions)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $params = array(
                'page' => $page,
                'key' => $key,
            );
            $this->assign('params', $params);
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->display();
        }else{
            $key = I('post.key', '', 'strip_tags');
            $quest = '';
            if($key) $quest .= '/key/'.urlencode($key);
            redirect(C('ADMIN_ROOT') . '/bank/bank_limit'.$quest);
        }
    }

    public function bank_limit_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $data['bank_name'] = trim(I('post.bank_name', 0, 'strip_tags'));
            $data['limit_once']  = trim(I('post.limit_once', 0, 'strip_tags'));
            $data['limit_day'] = I('post.limit_day', 0 ,'strip_tags');
            $data['limit_month'] = I('post.limit_month', '', 'strip_tags');
            $data['pay_id'] = I('post.pay_id', 0, 'strip_tags');
            
            if($data['bank_name'] == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入银行名称 '));
            }
            
            if(M('bankLimit')->where('pay_id='.$data['pay_id'] ." and bank_name='".$data['bank_name']."'")->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>$data['bank_name'].'该渠道已经添加 '));
            }
            
            if($data['limit_once'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入单笔限额'));
            }
            if($data['limit_day'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入每日限额'));
            }
            if($data['limit_month'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入每月限额'));
            }            
            
            $data['image'] = '';
            
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
                    $data['image'] = $info['img']['savepath'].$info['img']['savename'];
                    
                    
                    $ossPath = 'Uploads/focus/'.$data['image'];
                    $file = C('localPath').$data['image'];
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                    
                    
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择图标'));
            }
            
            $data['create_time'] = time();
            $data['add_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            
            if(!M('bankLimit')->add($data)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/bank/bank_limit'));
        }
    }

    /**
     * 编辑广告
     */
    public function bank_limit_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $key = I('get.key', '', 'strip_tags');
    
            $params = array(
                'page' => $page,
                'key' => $key,
            );
            $this->assign('params', $params);
    
            $detail = M('bankLimit')->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('银行限额信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');

            $data['bank_name']  = trim(I('post.bank_name', '', 'strip_tags'));
            $data['limit_once']  = trim(I('post.limit_once', '0', 'strip_tags'));
            $data['limit_day'] = I('post.limit_day', '0' ,'strip_tags');
            $data['limit_month'] = I('post.limit_month', '0', 'strip_tags');
            
            if($data['bank_name'] == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入银行名称 '));
            }
            if($data['limit_once'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入单笔限额'));
            }
            if($data['limit_day'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入每日限额'));
            }
            if($data['limit_month'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入每月限额'));
            }
            
            $data['image'] = I('post.image', '', 'strip_tags');
                
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
                    $data['image'] = $info['img']['savepath'].$info['img']['savename'];
                    
                    $ossPath = 'Uploads/focus/'.$data['image'];
                    $file = C('localPath').$data['image'];
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
            }
                
            $data['update_time'] = time();
            $data['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            
            if(!M('bankLimit')->where(array('id'=>$id))->save($data)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $quest = '';
            if($key) $quest .= '/s/' . $key;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/bank/bank_limit/p/'.$page.$quest));
        }
    }
   
    public function bank_limit_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if(!M('bankLimit')->where(array('id'=>$id))->delete())
             $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }
    
    public function bank_pay_way(){
        $count = 20; // 每页显示条数
        $bankPayWayObj = M('bankPayWay');
        $counts = $bankPayWayObj->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $bankPayWayObj->order('id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }


    public function bank_pay_way_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');    
            $detail = M('bankPayWay')->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('银行限额信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
           
    
            $data['per_transaction']  = trim(I('post.per_transaction', '', 'strip_tags'));
            $data['per_day']  = trim(I('post.per_day', '0', 'strip_tags'));
            $data['per_month'] = I('post.per_month', '0' ,'strip_tags');
            $data['per_transaction4'] = I('post.per_transaction4', '0', 'strip_tags');
            $data['per_day4'] = I('post.per_day4', '0' ,'strip_tags');
            $data['per_month4'] = I('post.per_month4', '0', 'strip_tags');
    
            if($data['per_transaction'] == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入连连单笔限额 '));
            }
            if($data['per_day'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入连连单日限额'));
            }
            if($data['per_month'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入连连单月限额'));
            }
            
            if($data['per_transaction4'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入融宝单笔限额'));
            }
            if($data['per_day4'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入融宝单日限额'));
            }
            if($data['per_month4'] == "") {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入融宝单月限额'));
            }
    
            $data['update_time'] = time();
            $data['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
    
            if(!M('bankPayWay')->where(array('id'=>$id))->save($data)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/bank/bank_pay_way'));
        }
    }
    
}