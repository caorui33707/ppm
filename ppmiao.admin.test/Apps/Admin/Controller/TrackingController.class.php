<?php
namespace Admin\Controller;

/**
 * 用户行为跟踪控制器
 * @package Admin\Controller
 */
class TrackingController extends AdminController{

    protected $_actionType = array(
        0 => '无',
        1 => '单击',
        2 => '长按',
        3 => '下拉刷新',
        4 => '上拉刷新',
        5 => '向左滑动',
        6 => '向右滑动',
        7 => '开',
        8 => '关',
        9 => 'true',
        10 => 'false',
    );

    public function index(){
        $this->display();
    }

    /**
     * 单用户
     */
    public function singleuser(){
        if(!IS_POST){
            $st = I('get.st', '', 'strip_tags');
            $et = I('get.et', '', 'strip_tags');
            $key = I('get.key', '', 'strip_tags');
            $count = 10; // 每页显示条数
            $st = str_replace('|', ' ', $st);
            $et = str_replace('|', ' ', $et);
            $params = array(
                'start_time' => $st,
                'end_time' => $et,
                'key' => urldecode($key),
            );
            $this->assign('params', $params);
            if($key) {
                $userObj = M('User');
                $behaviorTrackingIdentObj = M('BehaviorTrackingIdent');
                $sid = $userObj->where(array('username'=>$key))->getField('device_serial_id');
                if($sid){
                    $behaviorTrackingObj = M('BehaviorTracking');
                    $cond[] = "device_serial_id='".$sid."'";
                    if($st) $cond[] = "add_time>=".strtotime($st);
                    if($et) $cond[] = "add_time<=".strtotime($et);
                    $conditions = implode(' and ', $cond);
                    $counts = $behaviorTrackingObj->where($conditions)->count();
                    $Page = new \Think\Page($counts, $count);
                    $show = $Page->show();
                    $list = $behaviorTrackingObj->where($conditions)->order("add_time,id")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                    $trackingIdentData = F('tracking_ident_cache');
                    if(!$trackingIdentData){
                        $trackingList = $behaviorTrackingIdentObj->select();
                        foreach($trackingList as $key => $val){
                            $trackingIdentData[$val['id']] = $val['name'];
                        }
                        F('tracking_ident_cache', $trackingIdentData);
                    }
                    foreach($list as $key => $val){
                        $list[$key]['pageName'] = $trackingIdentData[$val['ident_id']];
                        $list[$key]['actionTypeStr'] = $this->_actionType[$val['action_type']];
                    }

                    $this->assign('show', $show);
                    $this->assign('list', $list);
                }
            }
            $this->display();
        }else{
            $st = I('post.st', '', 'strip_tags');
            $et = I('post.et', '', 'strip_tags');
            $key = I('post.key', '', 'strip_tags');

            $params = '';
            if($st) $params .= '/st/'.str_replace(' ', '|', $st);
            if($et) $params .= '/et/'.str_replace(' ', '|', $et);
            if($key) $params .= '/key/'.urlencode($key);
            redirect(C('ADMIN_ROOT').'/tracking/singleuser'.$params);
        }
    }
    /**
     * 录入用户跟踪类型
     */
    public function add(){
        $userTrackTypeObj = M('UserTrackType');
        if(IS_POST){
            $type_id = I("post.parent_id",0,'int');//类型id
            $title   = I("post.title",'','strip_tags');//分类内容
            if(empty($title)){
                $this->ajaxReturn(array('status'=>0,'info'=>'分类标题不能为空'));
            }
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $data=array(
                'parent_id'=>$type_id,
                'title'=>$title,
                'add_time'=>$time,
                'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'modify_time'=>$time,
                'modify_user_id'=>$_SESSION[ADMIN_SESSION]['uid']
            );
            $add_id = $userTrackTypeObj->add($data);
            if($add_id){
                $url = C('ADMIN_ROOT').'/tracking/add';
                $this->ajaxReturn(array('status'=>1,'info'=>'添加成功','url'=>$url));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'添加失败'));
            }
        }else{
            $list_type = $userTrackTypeObj->select();
            $this->assign("list",$list_type);
            $this->display();
        }

    }
    /**
     * 查询用户跟踪内容
     */
    public function search(){
        $userTrackTypeObj = M('UserTrackType');//用户跟踪一级类型列表
        $userTrackContentObj = M('UserTrackContent');//用户跟踪内容表
        if(!IS_POST){
            $user_id = I("get.user_id",0,'int');//用户ID
            $start_time = I("get.start_time",date("Y-m-d",time()-24*3600),'strip_tags');//开始时间
            $end_time = I("get.end_time",date("Y-m-d",time()),'strip_tags');//结束时间
            $index_type = I("get.index_type",'',"int");//跟踪类型
            $sql = "SELECT m.`id`,n.`title`,m.`content`,m.`add_time`,k.`username`,u.`real_name`,u.`username` AS phone FROM `s_user_track_content` AS m,`s_user_track_type` AS n,`s_member` AS k,`s_user` AS u WHERE  m.`type_id` = n.`id` AND k.`id` = m.`add_user_id` AND m.`track_user` = u.`id` and m.`is_delete`=0" ;
            $sql_num="";
            if($user_id){
                 $sql.=" and m.`add_user_id`=".$user_id;
                $sql_num.=" and m.`add_user_id`=".$user_id;

            }
            if($start_time && $end_time){
                $sql.=" and m.`add_time`>='".$start_time." 00:00:00.000000' and m.`add_time`<='".$end_time." 23:59:59.999000'";
                $sql_num.=" and m.`add_time`>='".$start_time." 00:00:00.000000' and m.`add_time`<='".$end_time." 23:59:59.999000'";
            }
            if($index_type){
                //判断类型下面有没有子分类
                $secondTypeList = $userTrackTypeObj->where(array('parent_id'=>$index_type))->select();
                if($secondTypeList){//走跟踪类型表
                    $secondTypeStr = '';
                    foreach($secondTypeList as $k=>$v){
                        $secondTypeStr.=",".$v['id'];
                    }
                   $secondTypeStr = substr($secondTypeStr,1);
                   $sql.=" and m.`type_id` in(".$secondTypeStr.")";
                   $sql_num.=" and m.`type_id` in(".$secondTypeStr.")";
                }else{// 走跟踪内容表
                    $sql.=" and m.`type_id`=".$index_type;
                    $sql_num.=" and m.`type_id`=".$index_type;
                }

            }
            $sql .=" order by m.`add_time` desc";
            //总记录条数
            $sql_num = "SELECT count(*) as total_record FROM `s_user_track_content` AS m,`s_user_track_type` AS n,`s_member` AS k,`s_user` AS u WHERE  m.`type_id` = n.`id` AND k.`id` = m.`add_user_id` AND m.`track_user` = u.`id` and m.`is_delete`=0 ".$sql_num;

            $track_user_total_arr = M()->query($sql_num);
            //记录列表
            $track_user_list = M()->query($sql);
            foreach($track_user_list as $k => $v){
                $track_user_list[$k]['add_time'] = date("Y-m-d H:i:s",strtotime($v['add_time']));
            }
            //获取负责人
            $admin_sql = "SELECT k.`username`,m.`add_user_id` FROM `s_user_track_content` AS m,`s_member` AS k WHERE  k.`id` = m.`add_user_id` group by  m.`add_user_id`";
            $admin_user_list = M()->query($admin_sql);
            //获取跟踪类型列表
            $userTrackTypeList = $userTrackTypeObj->where(array('parent_id'=>0))->select();
            $params=array(
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
            $this->assign("params",$params);
            $this->assign("trackTypeList",$userTrackTypeList);
            $this->assign("track_user_list",$track_user_list);
            $this->assign("admin_user_list",$admin_user_list);
            $this->assign("user_id",$user_id);
            $this->assign("track_user_total",$track_user_total_arr[0]['total_record']);
            $this->display();
        }else{
            $admin_user_id = I("post.admin_user_id",0,'int');//用户ID
            $start_time = I("post.start_time",date("Y-m-d",time()-24*3600),'strip_tags');//开始时间
            $end_time = I("post.end_time",date("Y-m-d",time()),'strip_tags');//结束时间
            $index_type = I("post.index_type",'',"int");//跟踪类型
            $redirect_url = C('ADMIN_ROOT').'/tracking/search';
            if($admin_user_id){
                $redirect_url .='/user_id/'.$admin_user_id;
            }
            if($index_type){
                $redirect_url.='/index_type/'.$index_type;
            }
            $redirect_url.='/start_time/'.$start_time.'/end_time/'.$end_time;
            redirect($redirect_url);

        }

    }
    /**
     * 查询对应负责人跟踪的问题
     */
    public function queryuser(){
        $admin_user = $_SESSION[ADMIN_SESSION]['uid'];
        $sql = "SELECT m.`id`,n.`title`,m.`content`,m.`add_time`,k.`username`,u.`real_name`,u.`username` AS phone FROM `s_user_track_content` AS m,`s_user_track_type` AS n,`s_member` AS k,`s_user` AS u WHERE m.`add_user_id` = ".$admin_user." AND m.`type_id` = n.`id` AND k.`id` = m.`add_user_id` AND m.`track_user` = u.`id` and m.`is_delete`=0";
        $track_user_list = M()->query($sql);
        foreach($track_user_list as $k => $v){
            $track_user_list[$k]['add_time'] = date("Y-m-d H:i:s",strtotime($v['add_time']));
        }
        $this->assign("track_user_list",$track_user_list);
        $this->display();
    }
    /**
     * 删除指定条列
     */
    public function delete(){
        if (!IS_POST || !IS_AJAX) exit;
        $id = I('post.id');
        if (!$id) {
            $this->ajaxReturn(array('status' =>0,'info'=>"参数有误"));
        }
        $userTrackContentObj = M('UserTrackContent');
        $del_status = $userTrackContentObj->where(array('id' => $id))->save(array('is_delete' => 1));
        if($del_status){
            $this->ajaxReturn(array('status' => 1,'info'=>"删除成功"));
        }else{
            $this->ajaxReturn(array('status' =>0,'info'=>"删除失败"));
        }
    }
    /**
     * 编辑指定跟踪内容
     */
    public function editTrackText(){
        $userTrackTypeObj = M('UserTrackType');//用户跟踪一级类型列表
        $userTrackContentObj = M('UserTrackContent');//用户跟踪内容表
        if(IS_POST){//edit
            $user_track_content = I('post.user_track_content','','strip_tags');//跟踪内容
            $detail_id = I("post.detail_id",'','int');//跟踪内容标识
            $index_type = I("post.index_type",'','int');//类型
            if(!$user_track_content || !$detail_id || !$index_type){
                $this->ajaxReturn(array('status'=>0,'info'=>'参数有误'));
            }
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $data=array(
                'type_id'=>$index_type,
                'content'=>$user_track_content,
                'modify_time'=>$time,
                'modify_user_id'=>$_SESSION[ADMIN_SESSION]['uid']
            );

            $save_id = $userTrackContentObj->where(array("id"=>$detail_id))->save($data);
            if($save_id !== false){
                $url = C('ADMIN_ROOT').'/Tracking/queryuser';
                $this->ajaxReturn(array('status'=>1,'link'=>$url));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败'));
            }
        }else{//display
            $detail_id = I("get.id",0,'int');//内容ID
            if(!$detail_id){
                $this->error("ID参数有误,请联系系统管理员");
            }

            //获取跟踪的具体内容
            $trackContent = $userTrackContentObj->where(array('id'=>$detail_id))->find();
            if(!$trackContent){
                $this->error("指定的跟踪内容不存在,请联系系统管理员");
            }
            //获取跟踪类型列表
            $userTrackTypeList = $userTrackTypeObj->where(array('parent_id'=>0))->select();
            $this->assign("trackTypeList",$userTrackTypeList);
            $this->assign("content",$trackContent);
            $this->assign("detail_id",$detail_id);
            $this->display();
        }

    }

}