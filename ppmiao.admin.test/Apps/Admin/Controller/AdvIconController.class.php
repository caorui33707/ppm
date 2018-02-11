<?php
namespace Admin\Controller;

/**
 * 悬浮ICON
 * @package Admin\Controller
 */
class AdvIconController extends AdminController{
    
    private $pageSize = 10;
    

    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $counts = M('advIcon')->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('advIcon')->where(array('is_delete'=>0))->order('edit_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $name = trim(I('post.name', '', 'strip_tags'));
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $status = I('post.status', 0, 'int'); // 上下架
            
            if($name == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入名称 '));
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

            if($start_time>=$end_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'开始时间必须小于结束时间'));
            }

            if($status == 1 || $status == 2 ){ // 已上架 和 未上架 判断时间段
                $cond = array();

                $cond[] = " ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) ";
                //$cond [] = 'start_time >='.$start_time.' and end_time <= '.$end_time;
                $cond[] = '(status = 1 or status = 2) '; // 新手红包弹窗

                $cond[] = 'is_delete = 0';

                $condWhere = implode(' and ',$cond);

                if(M('advIcon')->where($condWhere)->find()){
                    $this->ajaxReturn(array('status'=>0,'info'=>'导航图标时间重合'));
                }
            }
            
            $icon1 = $icon2 = $icon3 = $icon4  = '';
            
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

                    $iconErrorInfoArr = array('请选择每日签到图','请选择安全保障图','请选择会员中心图','请选择推荐有礼图');

                    $icon1 = $info['icon1']['savepath'].$info['icon1']['savename'];
                    $icon2 = $info['icon2']['savepath'].$info['icon2']['savename'];
                    $icon3 = $info['icon3']['savepath'].$info['icon3']['savename'];
                    $icon4 = $info['icon4']['savepath'].$info['icon4']['savename'];

                    $iconArr =array($icon1,$icon2,$icon3,$icon4);
                    foreach ($iconArr as $k=>$icon){ // 多图上传
                        if(empty($icon)) $this->ajaxReturn(array('status'=>0,'info'=>$iconErrorInfoArr[$k]));

                        $ossPath = 'Uploads/focus/'.$icon;
                        $file = C('localPath').$icon;
                        $res = uploadToOss($ossPath,$file);
                        if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                            $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                        }
                        \Think\Log::write('upload info:'.json_encode($res),'INFO');
                    }
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择导航图'));
            }

            $time = date('Y-m-d H:i:s',time());

            $rows = array(
                'name' => $name,
                'icon1' => $icon1,//每日签到
                'icon2' => $icon2,//安全保障
                'icon3' => $icon3,//会员中心
                'icon4' => $icon4,//推荐有礼
                'status' => $status,
                'add_user_id' => $this->uid,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'add_time' => $time,
                'edit_time' => $time
            );
            if(!M('advIcon')->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advIcon/index'));
        }
    }

    /**
     * 编辑广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('advIcon')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('广告信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            
            $name = trim(I('post.name', '', 'strip_tags'));

            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $status = I('post.status', 0, 'int'); // 上下架
            
            if($name == ""){
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入名称 '));
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

            if($start_time>=$end_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'开始时间必须小于结束时间'));
            }

            if($status == 1 || $status == 2 ){ // 已上架 和 未上架 判断时间段
                $cond = array();

                $cond [] = 'id != '.$id;

                $cond[] = " ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) ";
                //$cond [] = 'start_time >='.$start_time.' and end_time <= '.$end_time;
                $cond[] = '(status = 1 or status = 2 )'; // 新手红包弹窗
                $cond[] = 'is_delete = 0';

                $condWhere = implode(' and ',$cond);

                if(M('advIcon')->where($condWhere)->find()){
                    $this->ajaxReturn(array('status'=>0,'info'=>'导航图标时间重合'));
                }
            }
            
           // $image = I('post.image', '', 'strip_tags');

            $icon1 = I('post.img_icon1', '', 'strip_tags');
            $icon2 = I('post.img_icon2', '', 'strip_tags');
            $icon3 = I('post.img_icon3', '', 'strip_tags');
            $icon4 = I('post.img_icon4', '', 'strip_tags');

                
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
                  //  $iconErrorInfoArr = array('请选择每日签到图','请选择安全保障图','请选择会员中心图','请选择推荐有礼图');

                    $savename_icon1 = $info['icon1']['savepath'].$info['icon1']['savename'];
                    $savename_icon2 = $info['icon2']['savepath'].$info['icon2']['savename'];
                    $savename_icon3 = $info['icon3']['savepath'].$info['icon3']['savename'];
                    $savename_icon4 = $info['icon4']['savepath'].$info['icon4']['savename'];

                    $iconArr =array($savename_icon1,$savename_icon2,$savename_icon3,$savename_icon4);

                    foreach ($iconArr as $k=>$icon) { // 多图上传
                      //  if(empty($icon)) $this->ajaxReturn(array('status'=>0,'info'=>$iconErrorInfoArr[$k]));

                        if(empty($icon)) continue;

                        $ossPath = 'Uploads/focus/' . $icon;
                        $file = C('localPath') . $icon;
                        $res = uploadToOss($ossPath, $file);
                        if ($res['info']['http_code'] != 200 || $res['oss-request-url'] == '') {
                            $this->ajaxReturn(array('status' => 0, 'info' => 'oss图片上传失败'));
                        }
                        \Think\Log::write('upload info:' . json_encode($res), 'INFO');
                    }
                }

                $icon1 = $savename_icon1?$savename_icon1:$icon1;
                $icon2 = $savename_icon2?$savename_icon2:$icon2;
                $icon3 = $savename_icon3?$savename_icon3:$icon3;
                $icon4 = $savename_icon4?$savename_icon4:$icon4;

            }

            $time = date('Y-m-d H:i:s',time());
            $rows = array(
                'name' => $name,
                'status' => $status,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'icon1' => $icon1,//每日签到
                'icon2' => $icon2,//安全保障
                'icon3' => $icon3,//会员中心
                'icon4' => $icon4,//推荐有礼
                'edit_user_id' => $this->uid, // 修改 uid
                'edit_time' => $time
            );
            if(!M('advIcon')->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advIcon/index/p/'.$page));
        }
    }

    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $time = date('Y-m-d H:i:s',time());
        if(!M('advIcon')->where(array('id'=>$id))->save(array('is_delete'=>1,'edit_time'=>$time)))
             $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

}