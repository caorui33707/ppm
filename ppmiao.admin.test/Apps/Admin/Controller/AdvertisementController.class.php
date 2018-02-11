<?php
namespace Admin\Controller;

/**
 * 广告管理控制器
 * @package Admin\Controller
 */
class AdvertisementController extends AdminController{

    protected $_action = array(
        0 => '无动作',
        1 => '精品推荐',
        2 => '理财产品',
        3 => '账户',
        4 => '发现',
        5 => 'URL',
        6 => '产品详细',
        7 => '立即购买产品',
        8 => '账户中心',
        9 => '完善银行卡',
        10=> '我的钱包',
        11=>'邀请好友',
        12=>'个人消息',
    );
    protected $_position = array(
        //2 => '启动页',
        3 => 'Banner图',
        //4 => '理财列表Banner图',
        //5 => '更多Banner图',
        //6 => '产品公告',
    );
    protected $_status = array(
        1 => '未上架',
        2 => '已上架',
        3 => '已下架',
    );
    
    protected $tag_list = array(
        0 => '普通标',
        1 => '新人特惠',
        2 => '爆款',
        3 => 'HOT',
        6 => '活动',
        8 => '私人专享',
        9 => '月月加薪',
    );

    protected $img_focus ; //图片存放地址

    protected function _initialize(){
        parent::_initialize();
        $this->img_focus =  C('OSS_STATIC_ROOT')."/Uploads/focus/";

    }

    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            
            $user_type = trim(I('get.userType',0,'int'));
            $user_key = trim(I('get.userKey','','strip_tags'));//关键字
            
            $count = 10; // 每页显示条数
            $advertisementObj = M('Advertisement');
    
            $cond = ' position in(3,4,5) and is_delete=0';
            
            if($user_type>0) {
                
                if($user_type == 1) {
                    $cond .= " and old_user = 1";
                } else{
                    $cond .= " and new_user = 1";
                }
            }

            if($user_key) {
                $cond .= " and title LIKE '$user_key%'";
            }

            $counts = $advertisementObj->where($cond)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
           // $list = $advertisementObj->where($cond)->order('status asc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $list = $advertisementObj->where($cond)->order('status asc,edit_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            foreach($list as $key => $val){
                $list[$key]['statusStr'] = $this->_status[$val['status']];
                $list[$key]['positionStr'] = $this->_position[$val['position']];
                /*
                $r = '';
                $user_type_arr = explode(',', $val['user_group_type']);
                if(in_array(0, $user_type_arr))$r .= '老用户 ';
                if(in_array(1, $user_type_arr))$r .= ',新用户';
                $r = trim($r,',');
                $list[$key]['user_group_type'] = $r;*/
                
            }

            $params = array(
                'page' => $page,
                'userKey'=>$user_key,
            );
            $this->assign('userType',$user_type);
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
            $this->assign('action', $this->_action);
            $this->assign('position', $this->_position);
            $this->display();
        }else{
            $position = I('post.position', 0, 'int');
            $action = I('post.action', 0, 'int');
            $ext = trim(I('post.ext', '', 'strip_tags'));
            $status = I('post.status', 0, 'int');
            $title = trim(I('post.title', '', 'strip_tags'));
            $summary = trim(I('post.summary', '', 'strip_tags'));
            $url = trim(I('post.url', '', 'url'));
            //$tag = trim(I('post.tag',0,'int'));
            
            $share_id = trim(I('post.share_id',0,'int'));
            
            $big_banner = I('post.big_banner', 0, 'int');
            
            if($status == 3){
                $big_banner = 0;
            }

            $is_activity = I('post.is_activity', 0, 'int');
            
            //$rank = I('post.rank', 0, 'int');
            //$user_group_type = I('post.user_group_type', '', 'strip_tags');
            /*
            if(!$user_group_type){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择用户组，必须一个'
                ));
            }
            $user_group_type = implode(',', $user_group_type);
            */
            
            $new_user = I('new_user',0);
            $new_rank = I('new_rank',0,'int');
            
            $old_user = I('old_user',0);
            $old_rank = I('old_rank',0,'int');
            
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动结束时间'
                ));
            }
            
            if(strtotime($start_time) > strtotime($end_time)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '活动的开始时间必须小于结束时间'
                ));
            }
            
            $image = '';

            if(!$position) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个广告位'));
            if(!$status) $this->ajaxReturn(array('status'=>0,'info'=>'请选择广告位状态'));
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
            
            if($share_id>0){
                if($action == 5){
                    $ret = json_decode($ext);
                    if(strstr($ret->url, '?')){
                        $ret->url .="&sid=".$share_id;
                    } else {
                        $ret->url .="?sid=".$share_id;
                    }
                    $ext = str_replace('\\','',json_encode($ret));
                }
            }
            
            $advertisementObj = M('Advertisement');
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'position' => $position,
                'image' => $image,
                'action' => $action,
                'ext' => $ext,
                'status' => $status,
                'url' => $url,
                'title' => $title,
                'summary' => $summary,
                'is_delete' => 0,
                'add_time' => $time,
                'add_user_id' => $uid,
                'edit_time' => $time,
                'edit_user_id' => $uid,
                //'tag'=>$tag,
                'big_banner'=>$big_banner,
                'share_id'=>$share_id,
                //'rank'=>$rank,
                //'user_group_type'=>$user_group_type
                'is_activity'=>$is_activity,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'new_user' =>$new_user,
                'new_rank' =>$new_rank,
                'old_user' =>$old_user,
                'old_rank' =>$old_rank
            );
            
            
            if(!$advertisementObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/index'));
        }
    }

    /**
     * 编辑广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $advertisementObj = M('Advertisement');
            $detail = $advertisementObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('广告信息不存在或已被删除');exit;
            }
            
            $user_type_arr = explode(',', $detail['user_group_type']);
            if(in_array(0, $user_type_arr))$detail['user_type_0'] = 1;
            if(in_array(1, $user_type_arr))$detail['user_type_1'] = 1;
            $this->assign('action', $this->_action);
            $this->assign('position', $this->_position);
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            $action = I('post.action', 0, 'int');
            $ext = I('post.ext', '', 'strip_tags');
            $status = I('post.status', 0, 'int');
            $title = I('post.title', '', 'strip_tags');
            $summary = I('post.summary', '', 'strip_tags');
            $url = I('post.url', '', 'url');
            $image = I('post.image', '', 'strip_tags');
            //$tag = I('post.tag',0,'int');
            $share_id = trim(I('post.share_id',0,'int'));
            
            $big_banner = I('post.big_banner', 0, 'int');
            if($status == 3) {
                $big_banner = 0;
            }
            
            $is_activity = I('post.is_activity', 0, 'int');
            
            //$rank = I('post.rank', 0, 'int');
            //$user_group_type = I('post.user_group_type', '', 'strip_tags');
            
            /*
            if(!$user_group_type){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择用户组，必须一个'
                ));
            }
            
            $user_group_type = implode(',', $user_group_type);
            */
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
        
            $new_user = I('new_user',0);
            $new_rank = I('new_rank',0,'int');
            
            $old_user = I('old_user',0);
            $old_rank = I('old_rank',0,'int');
            
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动结束时间'
                ));
            }
        
            if(strtotime($start_time) > strtotime($end_time)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '活动的开始时间必须小于结束时间'
                ));
            } 
            

            if(!$status) $this->ajaxReturn(array('status'=>0,'info'=>'请选择广告位状态'));
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
            

            if($share_id>0){
                if($action == 5){
                    $ret = json_decode($ext);
                    if(strstr($ret->url, '?sid')){
                        $url_arr = explode('?sid',$ret->url);
                        $ret->url = $url_arr[0] .'?sid='.$share_id;
                    } else if(strstr($ret->url, '&sid')){
                        $url_arr = explode('&sid',$ret->url);
                        $ret->url = $url_arr[0].'&sid='.$share_id;
                    } else {
                        if(strstr($ret->url, '?')){
                            $ret->url .="&sid=".$share_id;
                        } else {
                            $ret->url .="?sid=".$share_id;
                        }
                    }
                    $ext = str_replace('\\','',json_encode($ret));
                }
            } else {
                if($action == 5){
                    $ret = json_decode($ext);
                    if(strstr($ret->url, '?sid')){
                        $url_arr = explode('?sid',$ret->url);
                        $ret->url = $url_arr[0];
                        $ext = str_replace('\\','',json_encode($ret));
                    } else if(strstr($ret->url, '&sid')){
                        $url_arr = explode('&sid',$ret->url);
                        $ret->url = $url_arr[0];
                        $ext = str_replace('\\','',json_encode($ret));
                    }
                }
            }
            

            $advertisementObj = M('Advertisement');
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'image' => $image,
                'status' => $status,
                'action' => $action,
                'ext' => $ext,
                'url' => $url,
                'title' => $title,
                'summary' => $summary,
                'edit_time' => $time,
                'edit_user_id' => $uid,
                //'tag'=>$tag,
                'big_banner'=>$big_banner,
                'is_activity'=>$is_activity,
                'share_id'=>$share_id,
                //'rank'=>$rank,
                //'user_group_type'=>$user_group_type
                
                'is_activity'=>$is_activity,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'new_user' =>$new_user,
                'new_rank' =>$new_rank,
                'old_user' =>$old_user,
                'old_rank' =>$old_rank
            );
            
           
            if(!$advertisementObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/index/p/'.$page));
        }
    }

    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $advertisementObj = M('Advertisement');
        if(!$advertisementObj->where(array('id'=>$id))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }
    
    //产品公告列表
    public function project_notic_index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $tag = I('tag','-1');

            $tag_type = I('tag_type','0');
        
            $count = 10; // 每页显示条数
            $advertisementObj = M('Advertisement');
        
            $cond = 'position=6 and is_delete=0';
//            if($tag_type==1) {
//                $cond .= ' and tag_type=' . $tag_type;
//                if ($tag >= 0) {
//                    $cond .= ' and tag=' . $tag;
//                }
//            }else if($tag_type==2){
//                $cond .= ' and tag_type=' . $tag_type;
//                if ($tag > 0) {
//                    $cond .= ' and tag_id=' . $tag;
//                }
//            }else{
//                //code
//            }

            if($tag>0){
                $cond .= ' and tag_id=' . $tag;
            }else if(!$tag){
                $cond .= ' and tag=' . $tag .' and tag_id= ' . $tag;
            }
            
            $counts = $advertisementObj->where($cond)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $advertisementObj->where($cond)->order('status asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            foreach($list as $key => $val){
                //判断时间设置 状态
                $status = $val['status'];// $this->editStatus($val);

                $list[$key]['statusStr'] = $this->_status[$status];
               // $list[$key]['status'] = $status;
                $list[$key]['positionStr'] = $this->_position[$val['position']];
            }

            $advertisementTagObj = new \Admin\Model\AdvertisementTagModel(); // 公告标签
            $tags = $advertisementTagObj->getAdvertisementTagAll();


            $params = array(
                'page' => $page,
                'tag_list'=>$this->tag_list,
                'tag'=>$tag,
                'tag_type'=>$tag_type
            );


            $this->assign('list', $list);
            $this->assign('tags',$tags);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }
    }
    
    public function project_notic_add(){
        if(!IS_POST){
            $advertisementTagObj = new \Admin\Model\AdvertisementTagModel(); // 公告标签
            $tags = $advertisementTagObj->getAdvertisementTagWhere($f=1); //所有

            $this->assign('tags', $tags);
            $this->assign('action', $this->_action);
            $this->assign('position', $this->_position);
            $this->display();
        }else{
            $position = 6;//产品公告
            $action = I('post.action', 0, 'int');
            $ext = trim(I('post.ext', '', 'strip_tags'));
            $status = I('post.status', 0, 'int');
            $summary = trim(I('post.summary', '', 'strip_tags'));
            $tag_type = I('post.tag_type',0,'int'); // 选择产品公告标签
            $tag = trim(I('post.tag',0,'int'));
            $tag_id = trim(I('post.tag_id',0,'int'));
            
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            if(!$summary){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '摘要内容不能为空'
                ));
            }
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动结束时间'
                ));
            }
        
            if(strtotime($start_time) >= strtotime($end_time)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '活动的开始时间必须小于结束时间'
                ));
            }

//            $cond = "((start_time >= '$start_time' AND start_time <= '$end_time') OR
//            (start_time <= '$start_time' AND end_time >='$end_time') OR
//            (end_time >= '$start_time' AND end_time <= '$end_time'))";



            $whereTag = ' tag_id = '.$tag_id ;

            $cond = " ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) ";

            $advertisementObj = M('Advertisement');

            $count = $advertisementObj->where("position = 6 and status <=2 and is_delete=0 and $whereTag and ".$cond)->count();

            if($count>0){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '同标签下公告时间重叠，请进行确认'
                ));
            }
            
           // $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $time = date('Y-m-d H:i:s');
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            
            $rows = array(
                'position' => $position,
                'action' => $action,
                'ext' => $ext,
                'status' => $status,
                'summary' => $summary,
                'is_delete' => 0,
                'add_time' => $time,
                'add_user_id' => $uid,
                'edit_time' => $time,
                'edit_user_id' => $uid,
                'tag'=>$tag,
                'tag_id'=>$tag_id,
               // 'tag_type'=>$tag_type
            );

            $rows['is_activity'] = 1;
            $rows['start_time'] = $start_time;
            $rows['end_time'] = $end_time;
            
            if(!$advertisementObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/project_notic_index'));
        }
    }
    
    public function project_notic_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $advertisementObj = M('Advertisement');
            $detail = $advertisementObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('产品公告信息不存在或已被删除');exit;
            }

            $advertisementTagObj = new \Admin\Model\AdvertisementTagModel(); // 公告标签
            $tags = $advertisementTagObj->getAdvertisementTagWhere($f=1);  //所有

            $this->assign('tags', $tags);
            $this->assign('action', $this->_action);
            $this->assign('position', $this->_position);
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            
            $action = I('post.action', 0, 'int');
            $ext = trim(I('post.ext', '', 'strip_tags'));
            $status = I('post.status', 0, 'int');
            $summary = trim(I('post.summary', '', 'strip_tags'));
            $tag_type = I('post.tag_type',0,'int');//
            $tag = trim(I('post.tag',0,'int'));
            $tag_id = trim(I('post.tag_id',0,'int'));
            
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
            if(!$summary){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '摘要内容不能为空'
                ));
            }
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动结束时间'
                ));
            }
            
            if(strtotime($start_time) >= strtotime($end_time)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '活动的开始时间必须小于结束时间'
                ));
            }


            
            
//            $cond = "((start_time >= '$start_time' AND start_time <= '$end_time') OR
//            (start_time <= '$start_time' AND end_time >='$end_time') OR
//            (end_time >= '$start_time' AND end_time <= '$end_time'))";

            $whereTag = ' tag_id = '.$tag_id  ;

            $advertisementObj = M('Advertisement');


            $cond = " ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) ";

            $count =  $advertisementObj->where("id != $id and  position = 6 and status <=2 and is_delete=0 and $whereTag and ".$cond)->count();


            if($count>0){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '同标签下公告时间重叠，请进行确认'
                ));
            }
            
            //$time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';

            $time = date('Y-m-d H:i:s');
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            
            $rows = array(
                'action' => $action,
                'ext' => $ext,
                'status' => $status,
                'summary' => $summary,
                'edit_time' => $time,
                'edit_user_id' => $uid,
                'tag'=>$tag,
                'tag_id'=>$tag_id,
                //'tag_type'=>$tag_type,
                'start_time' => $start_time,
                'end_time'=>$end_time
            );

            if(!$advertisementObj->where(array('id'=>$id))->save($rows))$this->ajaxReturn(array('status'=>0,'info'=>'修改失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/project_notic_index'));
        }
    }
    
    //启动页
    public function boot_page_index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
        
            $count = 15; // 每页显示条数
            $advertisementObj = M('Advertisement');
        
            $cond = 'position=2 and is_delete=0';
           
            $counts = $advertisementObj->where($cond)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $advertisementObj->where($cond)->order('status asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                //判断时间设置 状态
                $status =  $val['status'];//$this->editStatus($val);

                $list[$key]['statusStr'] = $this->_status[$status];
             //   $list[$key]['status'] = $status;
                $list[$key]['positionStr'] = $this->_position[$val['position']];
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
    
    public function boot_page_add(){
        if(!IS_POST){
            $this->assign('action', $this->_action);
            $this->assign('position', $this->_position);
            $this->display();
        }else{
            $position = 2;//产品公告
            $status = I('post.status', 0, 'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            
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
            
                    $ossPath = 'Uploads/focus/'.$image;
                    $file = C('localPath').$image;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择启动页图片'));
            }
            
            
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动结束时间'
                ));
            }
        
            if(strtotime($start_time) >= strtotime($end_time)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '活动的开始时间必须小于结束时间'
                ));
            }
            $advertisementObj = M('Advertisement');
            // "position = 2 and status <=2 and is_delete=0 and end_time>='$start_time'"
            $advertisementWhere = " position = 2 and status <=2 and is_delete=0 and ( (start_time BETWEEN '$start_time'  AND   '$end_time') OR (end_time BETWEEN '$start_time'  AND   '$end_time') OR (start_time <= '$start_time'  AND  end_time >= '$end_time') ) " ;
            $count = $advertisementObj->where($advertisementWhere)->count();
            if($count>0){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '启动页时间重叠，请进行确认'
                ));
            }
        
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
        
            $rows = array(
                'position' => $position,
                'image'=>$image,
                'status' => $status,
                'is_delete' => 0,
                'add_time' => $time,
                'add_user_id' => $uid,
                'edit_time' => $time,
                'edit_user_id' => $uid,
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
        
            if(!$advertisementObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/boot_page_index'));
        }
    }
    
    public function boot_page_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $advertisementObj = M('Advertisement');
            $detail = $advertisementObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('启动页不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');            
            $status = I('post.status', 0, 'int');            
            $image = I('post.image', '', 'strip_tags');        
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
    
            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择活动结束时间'
                ));
            }
    
            if(strtotime($start_time) > strtotime($end_time)) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '活动的开始时间必须小于结束时间'
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
        
                    $ossPath = 'Uploads/focus/'.$image;
                    $file = C('localPath').$image;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
            }
        
            $advertisementObj = M('Advertisement');
            
            
            $cond = "((start_time >= '$start_time' AND start_time <= '$end_time') OR
            (start_time <= '$start_time' AND end_time >='$end_time') OR
            (end_time >= '$start_time' AND end_time <= '$end_time'))";
            
            $count = $advertisementObj->where("id != $id and position = 2 and status <=2 and is_delete=0 and $cond")->count();
            if($count>0){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '启动页时间重叠，请进行确认'
                ));
            }
            
            
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'image' => $image,
                'status' => $status,
                'edit_time' => $time,
                'edit_user_id' => $uid,
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
            if(!$advertisementObj->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/boot_page_index/p/'.$page));
        }
    }

    //公告标签管理
    public function project_notic_tag_index(){
        $list = M('AdvertisementTag')->field('id,tag_title,description,start_time,end_time,tag_type')->where('is_delete=0')->order('tag_type desc,id desc')->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function project_notic_tag_add(){
        $time = time();
        if(!IS_POST) {
            $this->display();
        }else{
            $advertisementTagObj = M('AdvertisementTag');

            $action = I('post.action', 0, 'int');
            $tag_title = trim(I('post.tag_title', '', 'strip_tags'));

            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $description = trim(I('post.description', '', 'strip_tags'));

            $tag_type = I('post.tag_type', 2, 'int');
//            if(!$description){
//                $this->ajaxReturn(array(
//                    'status' => 0,
//                    'info' => '备注不能为空'
//                ));
//            }

            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);

            $tag_title = trim($tag_title);
            if(!$tag_title){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '公告标签不能为空'
                ));
            }

            if($advertisementTagObj->where('tag_title = \''.$tag_title .'\' and is_delete = 0')->find()){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '公告标签已存在'
                ));
            }

            if( $tag_type == 2) {

                if (!$start_time) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请选择标签开始时间'
                    ));
                }
                if (!$end_time) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请选择标签结束时间'
                    ));
                }

                if ($start_time > $end_time) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '标签的开始时间必须小于结束时间'
                    ));
                }

            }



            $rows = array(
                'add_user_id'=>$this->uid,
                'tag_title' => $tag_title,
                'description' => $description,
                'start_time' => $start_time,
                'end_time'=>$end_time,
                'tag_type'=>$tag_type ,// 标签类型
                'ctime'=>$time
            );

            if(!$advertisementTagObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/project_notic_tag_index'));
        }
    }

    public function project_notic_tag_edit(){
        $advertisementTagObj = M('AdvertisementTag');
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = $advertisementTagObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('产品公告信息不存在或已被删除');exit;
            }

            $this->assign('detail', $detail);
            $this->display();
        }else {

            $action = I('post.action', 0, 'int');
            $id = I('post.id', 0, 'int');
            $tag_title = trim(I('post.tag_title', '', 'strip_tags'));

            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $description = trim(I('post.description', '', 'strip_tags'));

            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);

            $tag_title = trim($tag_title);
            if(!$tag_title){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '公告标签不能为空'
                ));
            }

            if($advertisementTagObj->where('tag_title = \''.$tag_title .'\' and is_delete = 0 and id != '.$id)->find()){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '公告标签已存在'
                ));
            }

            if (! $start_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择标签开始时间'
                ));
            }
            if (! $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请选择标签结束时间'
                ));
            }

            if($start_time > $end_time) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '标签的开始时间必须小于结束时间'
                ));
            }

            $rows = array(
                'add_user_id'=>$this->uid,
                'tag_title' => $tag_title,
                'description' => $description,
                'start_time' => $start_time,
                'end_time'=>$end_time,
            );

            if(!$advertisementTagObj->where('id = '.$id)->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'修改失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/project_notic_tag_index'));

        }
    }

    public function project_notic_tag_del(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        $this->ajaxReturn(array('status'=>0,'info'=>'不允许删除'));
        //if($id<=9) $this->ajaxReturn(array('status'=>0,'info'=>'不允许删除'));

        $advertisementTagObj = M('AdvertisementTag');
        if(!$advertisementTagObj->where(array('id'=>$id))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }


    //提现说明
    public function withdrawals_explain_index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码

            $count = 15; // 每页显示条数
            $advertisementObj = M('Advertisement');

            $cond = 'position=9 and is_delete=0';

            $counts = $advertisementObj->where($cond)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $advertisementObj->where($cond)->order('status asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                //判断时间设置 状态
                $status =  $val['status'];//$this->editStatus($val);

                $list[$key]['statusStr'] = $this->_status[$status];
                //   $list[$key]['status'] = $status;
                $list[$key]['positionStr'] = $this->_position[$val['position']];
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

    public function withdrawals_explain_add(){
        if(!IS_POST){
            $this->assign('action', $this->_action);
            $this->assign('position', $this->_position);
            $this->display();
        }else{
            $position = 9;//提现说明
            $status = I('post.status', 0, 'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');

            $advertisementObj = M('Advertisement');
            $constantObj = M('Constant');

            if($status == 2 ){ // 已上架判断
                $positionCount = $advertisementObj->where('is_delete=0 and status=2 and position = ' . $position)->count();

                if($positionCount>0){
                    $this->ajaxReturn(array('status'=>0,'info'=>'已有上架的提现说明图片'));
                }
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

                    $ossPath = 'Uploads/focus/'.$image;
                    $file = C('localPath').$image;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                    }
                    \Think\Log::write('upload info:'.json_encode($res),'INFO');
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择图片'));
            }

            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];

            $rows = array(
                'position' => $position,
                'image'=>$image,
                'status' => $status,
                'is_delete' => 0,
                'add_time' => $time,
                'add_user_id' => $uid,
                'edit_time' => $time,
                'edit_user_id' => $uid,
//                'start_time'=>$start_time,
//                'end_time'=>$end_time
            );

            if(!$advertisementObj->add($rows)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '添加失败,请重试'));
            }

            if($status==2) { // 状态上架 修改 constant 图片  //  withdral_desc
                $this->img_focus = $this->img_focus . $image;
                $data = array(
                    'cons_value'=>$this->img_focus
                );
                $constantObj->where(array('cons_key'=>'withdral_desc') )->save($data);
            }

            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/withdrawals_explain_index'));
        }
    }

    public function withdrawals_explain_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $advertisementObj = M('Advertisement');
            $detail = $advertisementObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('提现说明页不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $position = 9;//提现说明
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            $status = I('post.status', 0, 'int');
            $image = I('post.image', '', 'strip_tags');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');

            $advertisementObj = M('Advertisement');
            $constantObj = M('Constant');

            if($status == 2 ) { // 已上架判断
                $cond = array(
                    'is_delete' => 0,
                    'status' => 2,
                    'position' => $position,
                );

                $cond['id'] = array('neq', $id);

                $positionCount = $advertisementObj->where($cond)->count();

                if ($positionCount > 0) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '已有上架的提现说明图片'));
                }
            }

        /* 时间设置
                if (! $start_time) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请选择活动开始时间'
                    ));
                }
                if (! $end_time) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请选择活动结束时间'
                    ));
                }

                if(strtotime($start_time) > strtotime($end_time)) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '活动的开始时间必须小于结束时间'
                    ));
                }

                 $cond = "((start_time >= '$start_time' AND start_time <= '$end_time') OR
                (start_time <= '$start_time' AND end_time >='$end_time') OR
                (end_time >= '$start_time' AND end_time <= '$end_time'))";

                $count = $advertisementObj->where("id != $id and position = 9 and status <=2 and is_delete=0 and $cond")->count();
                if($count>0){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '提现说明页时间重叠，请进行确认'
                    ));
                }
            */


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


            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'image' => $image,
                'status' => $status,
                'edit_time' => $time,
                'edit_user_id' => $uid,
//                'start_time'=>$start_time,
//                'end_time'=>$end_time
            );
            if(!$advertisementObj->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }

            if($status==2) { // 状态上架 修改 constant 图片  //  withdral_desc
                $this->img_focus = $this->img_focus . $image;

                $data = array(
                    'cons_value'=>$this->img_focus
                );
                $constantObj->where(array('cons_key'=>'withdral_desc') )->save($data);
            }

            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/advertisement/withdrawals_explain_index/p/'.$page));
        }
    }

    /**
     * 提现说明图片(软删除)
     */
    public function withdrawals_explain_del(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $advertisementObj = M('Advertisement');
        if(!$advertisementObj->where(array('id'=>$id,'position'=>9))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /// 通过时间修改状态
    protected function editStatus($val){
        if($val['status'] < 3 ) {
            $time = time();
            $status_start_time = strtotime($val['start_time']);
            $status_end_time = strtotime($val['end_time']);
            if ($status_start_time > $time) {
                $status  = 1;
            } else if ($time > $status_start_time && $time < $status_end_time) {
                $status  = 2;
            } else {
                $status  = 3;
            }
        }else{
            $status =  $val['status'];
        }

        return $status;
    }
}