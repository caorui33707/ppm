<?php
namespace Admin\Controller;

/**
 * 弹窗列表\悬浮ICON
 * @package Admin\Controller
 */
class AdvPopupController extends AdminController{
    
    private $pageSize = 10;
    

    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码

            $counts = M('advPopup')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('advPopup')->where(array('is_delete'=>0))->order('status,create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['statusStr'] = $this->_status[$val['status']];
                $list[$key]['positionStr'] = $this->_position[$val['position']];
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
     * 添加广告
     */
    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            $type = I('post.type', 0, 'int');
            $pos = I('post.pos', 0, 'int');
            $act = I('post.act', 0, 'int');
            
            $ext = '';
            if($act == 5 || $act == 7){
                $ext = I('post.ext', '', 'strip_tags');
            }
            
            $pop_type = I('post.pop_type', 0, 'int');//弹窗方式
            $status = I('post.status', 0, 'int'); // 上下架
            
            $user_type = I('post.user_type', 0 ,'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            
            $memo = trim(I('post.memo', '', 'strip_tags'));
            
            if($pos == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择弹窗位置！'));
            }
            
            if($act == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择动作！'));
            }
            
            if($user_type == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择用户类型！'));
            }
            
            if($pop_type == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择弹窗方式！'));
            }
            
            if($status == -1) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择状态！'));
            }
            
            if(!$start_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择开始时间'));
            }
            if(!$end_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择结束时间'));
            }
            
            if(strtotime($start_time) >= strtotime($end_time)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'弹窗的结束日期必须大于开始时间'));
            }


            if($status == 0 && $type == 2){ // 已上架 判断时间段
                $cond = array();

                $cond [] = 'type = 2'; // 新手红包弹窗
                $cond [] = 'status = 0'; // 上架
                $cond[] = 'is_delete = 0';

                $condWhere = implode(' and ',$cond);

                if(M('advPopup')->where($condWhere)->find()){
                    $this->ajaxReturn(array('status'=>0,'info'=>'新手红包弹窗已有上架状态'));
                }
                /*
                $cond [] = " ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) ";
                //$cond [] = 'start_time >='.$start_time.' and end_time <= '.$end_time;
                $cond [] = 'type = 2'; // 新手红包弹窗
                $cond [] = 'status = 0'; // 上架
                $cond[] = 'is_delete = 0';

                $condWhere = implode(' and ',$cond);

                if(M('advPopup')->where($condWhere)->find()){
                    $this->ajaxReturn(array('status'=>0,'info'=>'新手红包弹窗上架状态时间重合'));
                }
                */
            }

             
            $title = '';
            $content = '';

            $title = trim(I('post.title', '', 'strip_tags'));
            $content = trim(I('post.content', '', 'strip_tags'));
            if($title == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题 '));
            }

            if($type != 2) {
                if ($content == "") {
                    $this->ajaxReturn(array('status' => 0, 'info' => '请输入内容 '));
                }
            }
            
            $image = '';
            if($type != 1) { // 不是 公告弹窗
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
                        $ossPath = 'Uploads/focus/'.$image;
                        $file = C('localPath').$image;
                        $res = uploadToOss($ossPath,$file);
                        if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                            $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                        }
                        \Think\Log::write('upload info:'.json_encode($res),'INFO');
                    }
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>'请选择广告图'));
                }
            } else {
                $image = '';
            }

            $rows = array(
                'type' => $type,
                'pos' => $pos,
                'act' => $act,
                'ext' => $ext,
                'user_type' => $user_type,
                'title' => $title,
                'content' => $content,
                'img' => $image,
                'pop_type' => $pop_type,
                'memo' => $memo,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status,
                'user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time()
            );
            if(!M('advPopup')->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advPopup/index'));
        }
    }

    /**
     * 编辑广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('advPopup')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('广告信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            
            $type = I('post.type', 0, 'int');
            $pos = I('post.pos', 0, 'int');
            $act = I('post.act', 0, 'int');
            
            $ext = '';
            if($act == 5 || $act == 7){
                $ext = I('post.ext', '', 'strip_tags');
            }
            
            $pop_type = I('post.pop_type', 0, 'int');//弹窗方式
            $status = I('post.status', 0, 'int'); // 上下架
            
            $user_type = I('post.user_type', 0 ,'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            $memo = trim(I('post.memo', '', 'strip_tags'));
            
            if($act == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择动作！'));
            }
            
            if($user_type == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择用户类型！'));
            }
            
            if($pop_type == -1){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择弹窗方式！'));
            }
            
            if($status == -1) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择状态！'));
            }
            
            if(!$start_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择开始时间'));
            }
            if(!$end_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择结束时间'));
            }

            if(strtotime($start_time) >= strtotime($end_time)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'弹窗的结束日期必须大于开始时间'));
            }

            if($status == 0 && $type == 2){ // 已上架 判断时间段
                $cond = array();
                $cond [] = 'id != '.$id;

                //$cond [] = " ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) ";
                //$cond [] = 'start_time >='.$start_time.' and end_time <= '.$end_time;
                $cond [] = 'type = 2'; // 新手红包弹窗
                $cond [] = 'status = 0'; // 上架
                $cond[] = 'is_delete = 0';

                $condWhere = implode(' and ',$cond);

                if(M('advPopup')->where($condWhere)->find()){
                    //$this->ajaxReturn(array('status'=>0,'info'=>'新手红包弹窗上架状态时间重合'));
                    $this->ajaxReturn(array('status'=>0,'info'=>'新手红包弹窗已有上架状态'));
                }
            }

            $title = '';
            $content = '';
            
            $title = trim(I('post.title', '', 'strip_tags'));
            $content = trim(I('post.content', '', 'strip_tags'));
            if($title == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题 '));
            }

            if($type != 2) {
                if ($content == "") {
                    $this->ajaxReturn(array('status' => 0, 'info' => '请输入内容 '));
                }
            }
            
            $image = I('post.image', '', 'strip_tags');
            if($type != 1) { // 不是 公告弹窗
                
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
                        
                        $ossPath = 'Uploads/focus/'.$image;
                        $file = C('localPath').$image;
                        $res = uploadToOss($ossPath,$file);
                        if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                            $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                        }
                        \Think\Log::write('upload info:'.json_encode($res),'INFO');
                    }
                }
                
            } else {
                $image = '/f';
            }
            
           
            
            $rows = array(
                'pos' => $pos,
                'act' => $act,
                'ext' => $ext,
                'user_type' => $user_type,
                'title' => $title,
                'content' => $content,
                'img' => $image,
                'pop_type' => $pop_type,
                'memo' => $memo,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => $status,
                'user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'create_time' => time()
            );

            if(!M('advPopup')->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advPopup/index/p/'.$page));
        }
    }

    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if(!M('advPopup')->where(array('id'=>$id))->save(array('is_delete'=>1,'update_time'=>time())))
             $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

}