<?php
namespace Admin\Controller;

class MessageController extends AdminController{

    protected $_type = array( // 消息类型
        1 => '系统消息',
        2 => '活动消息(单条)',
        3 => '活动消息(多条)',
        4 => '项目消息',
    );

    /**
     * 消息管理
     */
    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数
            $search = urldecode(I('get.s', '', 'strip_tags')); // 搜索关键字
            $type = I('get.type', 0, 'int'); // 消息类型
            $messageGroupObj = M('MessageGroup');
            $messageObj = M('Message');

            $conditions = array();
            $cond[] = "is_delete=0";
            if ($search) $cond[] = "title like '%" . $search . "%'";
            if ($type) $cond[] = "type=" . $type;
            if ($cond) $conditions = implode(' and ', $cond);
            if(!$search){ // 没有搜索关键字
                $counts = $messageGroupObj->where($conditions)->count();
            }else{ // 有搜索关键字(列表展示方式有变)
                $counts = $messageObj->where($conditions)->count();
            }
            $Page = new \Think\Page($counts, $count);
            if ($search) $Page->parameter .= "&s=" . $search;
            $show = $Page->show();
            if(!$search){
                $list = $messageGroupObj->where($conditions)->order('status,add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                foreach($list as $key => $val){
                    $list[$key]['typeStr'] = $this->_type[$val['type']];
                    if($val['type'] == 3){
                        $list[$key]['sub'] = $messageObj->where(array('group_id'=>$val['id']))->order('add_time desc')->select();
                        foreach($list[$key]['sub'] as $k => $v){
                            $list[$key]['sub'][$k]['typeStr'] = $this->_type[$v['type']];
                        }
                    }else{
                        $list[$key]['sub'] = $messageObj->where(array('group_id'=>$val['id']))->find();
                    }
                }
            }else{
                $list = $messageObj->where($conditions)->order('status,add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                foreach($list as $key => $val){
                    $list[$key]['typeStr'] = $this->_type[$val['type']];
                }
            }

            $params = array(
                'page' => $page,
                'search' => $search,
                'type' => $type,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->assign('msg_type', $this->_type);
            $this->display();
        }else{
            $key = I('post.key', '', 'strip_tags');
            $type = I('post.type', 0, 'int');
            $quest = '/type/'.$type;
            if($key) $quest .= '/s/'.urlencode($key);
            redirect(C('ADMIN_ROOT') . '/message/index'.$quest);
        }
    }

    /**
     * 消息回收站
     */
    public function recycle(){
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $counts = $messageGroupObj->where(array('is_delete'=>1))->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $messageGroupObj->where(array('is_delete'=>1))->order('status,add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $key => $val){
            $list[$key]['typeStr'] = $this->_type[$val['type']];
            if($val['type'] == 3){
                $list[$key]['sub'] = $messageObj->where(array('group_id'=>$val['id']))->order('add_time desc')->select();
                foreach($list[$key]['sub'] as $k => $v){
                    $list[$key]['sub'][$k]['typeStr'] = $this->_type[$v['type']];
                }
            }else{
                $list[$key]['sub'] = $messageObj->where(array('group_id'=>$val['id']))->find();
            }
        }

        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * 发布消息
     */
    public function add(){
        if(!IS_POST){
            $type = I('get.type', 1, 'int'); // 添加消息类型
            $page = I('get.p', 1, 'int');
            $search = I('get.s', '', 'strip_tags');
            $_type = I('get._type', 0, 'int');

            $params = array(
                'page' => $page,
                'search' => $search,
                'type' => $_type,
            );
            $this->assign('params', $params);

            switch($type){
                case 1:
                    $this->display('add1');
                    break;
                case 2:
                    $this->display('add2');
                    break;
                case 3:
                    $gid = I('get.gid', 0, 'int'); // 消息组ID
                    if($gid){ // 如果有组ID,则获取该组下面已添加的消息
                        $messageGroupObj = M('MessageGroup');
                        $messageObj = M('Message');
                        $groupInfo = $messageGroupObj->where(array('id'=>$gid,'is_delete'=>0))->find();
                        if(!$groupInfo){
                            $this->error('消息组信息不存在或已被删除');exit;
                        }
                        $list = $messageObj->where(array('group_id'=>$gid))->order('top desc,add_time desc')->select();
                    }
                    $this->assign('list', $list);
                    $this->assign('gid', $gid);
                    $this->assign('timestamp', time());
                    $this->display('add3');
                    break;
                case 4:
                    // 债权类型
                    $constant = M('Constant');
                    $typeParentId = $constant->where(array('key' => 'project_type'))->getField('id');
                    $typeArr = $constant->where(array('parent_id' => $typeParentId))->select();
                    $this->assign('project_type', $typeArr);
                    $this->display('add4');
                    break;
            }
        }else{
            $type = I('post.type', 1, 'int'); // 添加消息类型
            $messageGroupObj = M('MessageGroup');
            $messageObj = M('Message');
            $time = Date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';

            switch($type){
                case 1:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
                    if(!$summary) $this->ajaxReturn(array('status'=>0,'info'=>'摘要不能为空'));
                    if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    $messageGroupObj->startTrans();
                    $rows = array(
                        'type' => 1,
                        'status' => 0,
                        'is_delete' => 0,
                        'add_time' => $time,
                        'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    $gid = $messageGroupObj->add($rows);
                    if(!$gid) $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));

                    $rows2 = array(
                        'group_id' => $gid,
                        'title' => $title,
                        'summary' => $summary,
                        'description' => $description,
                        'type' => 1,
                        'status' => 0,
                        'author' => $author,
                        'add_time' => $time,
                        'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->add($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/index'));
                    break;
                case 2:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $url = trim(I('post.url', '', 'url'));
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
                    //if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    $messageGroupObj->startTrans();
                    $rows = array(
                        'type' => 2,
                        'status' => 0,
                        'is_delete' => 0,
                        'add_time' => $time,
                        'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    $gid = $messageGroupObj->add($rows);
                    if(!$gid) $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));

                    $rows2 = array(
                        'group_id' => $gid,
                        'title' => $title,
                        'summary' => $summary,
                        'description' => $description,
                        'type' => 2,
                        'status' => 0,
                        'author' => $author,
                        'url' => $url,
                        'add_time' => $time,
                        'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->add($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/index'));
                    break;
                case 3:  // 添加活动消息(多条)时还是返回当前添加页面
                    $gid = I('post.gid', 0, 'int'); // 消息组ID
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $type2 = I('post.type2', 0, 'int');
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $url = trim(I('post.url', '', 'url'));
                    $icon_img = I('post.hidden_img'); // 新闻图标图片
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    $isPadding = I('post.is_padding') ? 1 : 0;

                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));

                    if($_FILES){
                        $config = array(
                            'maxSize'    =>    3145728,
                            'rootPath'   =>    '../web/Uploads/msgicon/',
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
                            $icon_img = $info['img']['savepath'].$info['img']['savename'];
                        }
                    }
                    //if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    if(!$gid){ // 第一次添加消息
                        $messageGroupObj->startTrans();
                        $rows = array(
                            'type' => 3,
                            'status' => 0,
                            'is_delete' => 0,
                            'add_time' => $time,
                            'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                            'modify_time' => $time,
                            'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        );
                        $gid = $messageGroupObj->add($rows);
                        if(!$gid) $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));
                        $rows2 = array(
                            'group_id' => $gid,
                            'title' => $title,
                            'summary' => $summary,
                            'description' => $description,
                            'type' => 3,
                            'type2' => $type2,
                            'status' => 0,
                            'author' => $author,
                            'url' => $url,
                            'icon_img' => $icon_img,
                            'is_padding' => $isPadding,
                            'add_time' => $time,
                            'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                            'modify_time' => $time,
                            'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        );
                        if(!$messageObj->add($rows2)) {
                            $messageGroupObj->rollback();
                            $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));
                        }
                        $messageGroupObj->commit();
                    }else{
                        $rows2 = array(
                            'group_id' => $gid,
                            'title' => $title,
                            'type2' => $type2,
                            'summary' => $summary,
                            'description' => $description,
                            'type' => 3,
                            'status' => 0,
                            'author' => $author,
                            'url' => $url,
                            'is_padding' => $isPadding,
                            'icon_img' => $icon_img,
                            'add_time' => $time,
                            'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                            'modify_time' => $time,
                            'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        );
                        if(!$messageObj->add($rows2)) $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));
                    }
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/add/type/3/gid/'.$gid));
                    break;
                case 4:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $url = trim(I('post.url', '', 'url'));
                    $project_type = I('post.project_type', 0, 'int');
                    $project_id = I('post.project_id', 0, 'int');
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
                    //if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    $messageGroupObj->startTrans();
                    $rows = array(
                        'type' => 4,
                        'status' => 0,
                        'is_delete' => 0,
                        'add_time' => $time,
                        'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    $gid = $messageGroupObj->add($rows);
                    if(!$gid) $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));

                    $rows2 = array(
                        'group_id' => $gid,
                        'title' => $title,
                        'summary' => $summary,
                        'description' => $description,
                        'type' => 4,
                        'status' => 0,
                        'author' => $author,
                        'url' => $url,
                        'project_type' => $project_type,
                        'project_id' => $project_id,
                        'add_time' => $time,
                        'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->add($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'添加消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/index'));
                    break;
            }
        }
    }

    /**
     * 编辑消息
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $gid = I('get.gid', 0, 'int');
            $page = I('get.p', 1, 'int');
            $search = trim(I('get.s', '', 'strip_tags'));
            $type = I('get.type', 0, 'int');

            $params = array(
                'page' => $page,
                'search' => $search,
                'type' => $type,
            );
            $this->assign('params', $params);

            $messageGroupObj = M('MessageGroup');
            $messageObj = M('Message');

            $groupDetail = $messageGroupObj->where(array('id'=>$gid,'is_delete'=>0))->find();
            if(!$groupDetail){
                $this->error('消息不存在或已被删除');exit;
            }
            if($groupDetail['type'] != 3){
                if($groupDetail['type'] == 4){
                    // 债权类型
                    $constant = M('Constant');
                    $typeParentId = $constant->where(array('key' => 'project_type'))->getField('id');
                    $typeArr = $constant->where(array('parent_id' => $typeParentId))->select();
                    $this->assign('project_type', $typeArr);
                }
                $detail = $messageObj->where(array('id'=>$id,'is_delete'=>0))->find();
                if(!$detail){
                    $this->error('消息不存在或已被删除');exit;
                }
                $this->assign('detail', $detail);
                $this->assign('group_detail', $groupDetail);
                $this->display('edit'.$groupDetail['type']);
            }else{
                $detail = $messageObj->where(array('id'=>$id,'is_delete'=>0))->find();
                if(!$detail){
                    $this->error('消息不存在或已被删除');exit;
                }
                $list = $messageObj->where(array('group_id'=>$groupDetail['id'],'is_delete'=>0))->order('top desc,add_time desc')->select();
                foreach($list as $key => $val){
                    $list[$key]['typeStr'] = $this->_type[$val['type']];
                }
                $this->assign('list', $list);
                $this->assign('detail', $detail);
                $this->assign('group_detail', $groupDetail);
                $this->display('edit3');
            }
        }else{
            $type = I('post.type', 0, 'int');
            $id = I('post.id', 0, 'int');
            $gid = I('post.gid', 0, 'int');
            $page = I('post.p', 1, 'int');
            $search = I('post.s', '', 'strip_tags');
            $_type = I('post._type', 0, 'int');

            $messageGroupObj = M('MessageGroup');
            $messageObj = M('Message');
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
            switch($type){
                case 1:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
                    if(!$summary) $this->ajaxReturn(array('status'=>0,'info'=>'摘要不能为空'));
                    if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    $messageGroupObj->startTrans();
                    $rows = array(
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageGroupObj->where(array('id'=>$gid))->save($rows)) {
                        $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));
                    }
                    $rows2 = array(
                        'title' => $title,
                        'summary' => $summary,
                        'description' => $description,
                        'author' => $author,
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->where(array('id'=>$id))->save($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $quest = '';
                    if($search) $quest .= '/s/' . $search;
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/index/p/'.$page.'/type/'.$_type.$quest));
                    break;
                case 2:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $url = trim(I('post.url', '', 'url'));
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
                    //if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    $messageGroupObj->startTrans();
                    $rows = array(
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageGroupObj->where(array('id'=>$gid))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));

                    $rows2 = array(
                        'title' => $title,
                        'summary' => $summary,
                        'description' => $description,
                        'author' => $author,
                        'url' => $url,
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->where(array('id'=>$id))->save($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $quest = '';
                    if($search) $quest .= '/s/' . $search;
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/index/p/'.$page.'/type/'.$_type.$quest));
                    break;
                case 3:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $type2 = I('post.type2', 0, 'int');
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $icon_img = I('post.hidden_img');
                    $url = trim(I('post.url', '', 'url'));
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    $delimg = I('post.delimg');
                    $isPadding = I('post.is_padding') ? 1 : 0;
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
//                    if(!$type2) $this->ajaxReturn(array('status'=>0,'info'=>'请选择消息类型'));
                    //if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));

                    if($_FILES){
                        $config = array(
                            'maxSize'    =>    3145728,
                            'rootPath'   =>    '../web/Uploads/msgicon/',
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
                            $icon_img = $info['img']['savepath'].$info['img']['savename'];
                        }
                    }else if($delimg){
                        $icon_img = '';
                    }

                    $messageGroupObj->startTrans();
                    $rows = array(
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageGroupObj->where(array('id'=>$gid))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));

                    $rows2 = array(
                        'title' => $title,
                        'type2' => $type2,
                        'summary' => $summary,
                        'description' => $description,
                        'author' => $author,
                        'url' => $url,
                        'icon_img' => $icon_img,
                        'is_padding' => $isPadding,
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->where(array('id'=>$id))->save($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $quest = '';
                    if($search) $quest .= '/s/' . $search;
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/add/type/3/gid/'.$gid.'/p/'.$page.'/_type/'.$_type.$quest));
                    break;
                case 4:
                    $title = trim(I('post.title', '', 'strip_tags'));
                    $author = trim(I('post.author', '', 'strip_tags'));
                    $url = trim(I('post.url', '', 'url'));
                    $project_type = I('post.project_type', 0, 'int');
                    $project_id = I('post.project_id', 0, 'int');
                    $summary = trim(I('post.summary', '', 'strip_tags'));
                    $description = trim($_POST['description']);
                    if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'消息标题不能为空'));
                    //if(!$description) $this->ajaxReturn(array('status'=>0,'info'=>'消息详细不能为空'));
                    $messageGroupObj->startTrans();
                    $rows = array(
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageGroupObj->where(array('id'=>$gid))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));

                    $rows2 = array(
                        'title' => $title,
                        'summary' => $summary,
                        'description' => $description,
                        'author' => $author,
                        'url' => $url,
                        'project_type' => $project_type,
                        'project_id' => $project_id,
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    if(!$messageObj->where(array('id'=>$id))->save($rows2)) {
                        $messageGroupObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'编辑消息失败,请重试'));
                    }
                    $messageGroupObj->commit();
                    $quest = '';
                    if($search) $quest .= '/s/' . $search;
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/index/p/'.$page.'/type/'.$_type.$quest));
                    break;
            }
        }
    }

    /**
     * 删除消息(组)
     */
    public function delete(){
        if (!IS_POST || !IS_AJAX) exit;

        $isMulti = false; // 是否批量删除
        $id = I('post.id');
        if (!is_numeric($id)) {
            $isMulti = true;
        }

        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');
        if (!$isMulti) {
            $info = $messageGroupObj->where(array('id' => $id, 'is_delete' => 0))->find();
        } else {
            $info = $messageGroupObj->where('id in (' . $id . ') and is_delete=0')->select();
        }
        if (!$info) $this->ajaxReturn(array('status' => 0, 'info' => '消息不存在或已被删除'));
        if (!$isMulti) {
            $messageGroupObj->startTrans();
            if (!$messageGroupObj->where(array('id' => $id))->save(array('is_delete' => 1))) $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
            if(!$messageObj->where(array('group_id'=>$id))->save(array('is_delete'=>1))){
                $messageGroupObj->rollback();
                $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
            }
            $messageGroupObj->commit();
        } else {
            if (!$messageGroupObj->where('id in (' . $id . ')')->save(array('is_delete' => 1))) $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
            if(!$messageObj->where('group_id in ('.$id.')')->save(array('is_delete'=>1))){
                $messageGroupObj->rollback();
                $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
            }
            $messageGroupObj->commit();
        }
        $this->ajaxReturn(array('status' => 1));
    }

    /**
     * 删除消息(条目)
     */
    public function delete2(){
        if (!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $messageInfo = $messageObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$messageInfo) $this->ajaxReturn(array('status'=>0,'info'=>'消息不存在或已被删除'));
        if($messageInfo['type'] == 3){ // 多条消息组的
            // 检查该消息是否是组内最后一条消息
            if($messageObj->where(array('group_id'=>$messageInfo['group_id'],'is_delete'=>0))->count() > 0){ // 直接删除
                if(!$messageObj->where(array('id'=>$id))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }else{ // 连同组一起删除
                $messageObj->startTrans();
                if(!$messageObj->where(array('id'=>$id))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
                if(!$messageGroupObj->where(array('id'=>$messageInfo['group_id']))->save(array('is_delete'=>1))){
                    $messageObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
                }
                $messageObj->commit();
                $this->ajaxReturn(array('status'=>1));
            }
        }else{ // 单条消息,同时把组的状态同时改掉
            $messageObj->startTrans();
            if(!$messageObj->where(array('id'=>$id))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
            if(!$messageGroupObj->where(array('id'=>$messageInfo['group_id']))->save(array('is_delete'=>1))){
                $messageObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
            }
            $messageObj->commit();
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 恢复删除消息
     */
    public function recovery(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $detail = $messageGroupObj->where(array('id'=>$id,'is_delete'=>1))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'消息不存在或未被删除'));

        $messageGroupObj->startTrans();
        if(!$messageGroupObj->where(array('id'=>$id))->save(array('is_delete'=>0))) $this->ajaxReturn(array('status'=>0,'info'=>'恢复失败,请重试'));
        if(!$messageObj->where(array('group_id'=>$detail['id']))->save(array('is_delete'=>0))){
            $messageGroupObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'恢复失败,请重试'));
        }
        $messageGroupObj->commit();
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 审核发布消息
     */
    public function pass(){
        
        exit;
        
        
        if(!IS_POST || !IS_AJAX) exit;

        $gid = I('post.gid', 0, 'int');
        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $groupInfo = $messageGroupObj->where(array('id'=>$gid,'is_delete'=>0))->find();
        if(!$groupInfo) $this->ajaxReturn(array('status'=>0,'info'=>'消息不存在或已被删除'));
        if($groupInfo['status'] != 0) $this->ajaxReturn(array('status'=>0,'info'=>'该消息已经是已发布状态,请勿重复发布'));
        $messageGroupObj->startTrans();
        if(!$messageGroupObj->where(array('id'=>$gid))->save(array('status'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'发布消息失败,请重试'));
        if(!$messageObj->where(array('group_id'=>$gid))->save(array('status'=>1))){
            $messageGroupObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'发布消息失败,请重试'));
        }
        $messageGroupObj->commit();
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 置顶/取消置顶
     */
    public function top(){
        if(!IS_POST || !IS_AJAX) exit;

        $gid = I('post.gid', 0, 'int');
        $id = I('post.id', 0, 'int');

        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $groupInfo = $messageGroupObj->where(array('id'=>$gid,'is_delete'=>0))->find();
        if(!$groupInfo) $this->ajaxReturn(array('status'=>0,'info'=>'消息不存在或已被删除'));
        $detail = $messageObj->where(array('id'=>$id,'group_id'=>$gid,'is_delete'=>0))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'消息不存在或已被删除'));
        if($groupInfo['type'] != 3) $this->ajaxReturn(array('status'=>0,'info'=>'该类型消息无法置顶'));

        if($detail['top'] == 1){ // 原先是置顶的消息,取消置顶
            if(!$messageObj->where(array('id'=>$id))->save(array('top'=>0))) $this->ajaxReturn(array('status'=>0,'info'=>'置顶失败,请重试'));
            $this->ajaxReturn(array('status'=>1));
        }else{ // 原先为非置顶消息,置顶,并把之前置顶的消息取消置顶
            if($messageObj->where('group_id='.$gid.' and is_delete=0 and top=1 and id<>'.$id)->count() > 0){ // 之前有置顶的消息
                $messageObj->startTrans();
                if(!$messageObj->where(array('group_id'=>$gid,'is_delete'=>0))->save(array('top'=>0))) $this->ajaxReturn(array('status'=>0,'info'=>'置顶失败,请重试'));
                if(!$messageObj->where(array('id'=>$id))->save(array('top'=>1))) {
                    $messageObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'置顶失败,请重试'));
                }
                $messageObj->commit();
                $this->ajaxReturn(array('status'=>1));
            }else{
                if(!$messageObj->where(array('id'=>$id))->save(array('top'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'置顶失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }
        }
    }

    /**
     * 消息评论
     */
    public function message_comment(){
        $ppage = I('get.page', 1, 'int'); // 上一级页面
        $type = I('get.type', 0, 'int');
        $search = I('get.s', '', 'strip_tags');
        $page = I('get.p', 1, 'int');
        $mid = I('get.mid', 0, 'int');
        $count = 10;

        $params = array(
            'page' => $ppage,
            'type' => $type,
            's' => $search,
            'p' => $page,
        );
        $this->assign('params', $params);

        $messageCommentObj = M('MessageComment');
        $counts = $messageCommentObj->where(array('msg_id'=>$mid))->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $messageCommentObj->where(array('msg_id'=>$mid))->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * 审核评论
     */
    public function comment_verify(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $messageCommentObj = M('MessageComment');
        $detail = $messageCommentObj->field('id,status')->where(array('id'=>$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'评论不存在或已被删除'));
        if($detail['status'] == 2) $this->ajaxReturn(array('status'=>1));
        else {
            if(!$messageCommentObj->where(array('id'=>$id))->save(array('status'=>2))) $this->ajaxReturn(array('status'=>0,'info'=>'审核失败,请重试'));
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 评论删除
     */
    public function comment_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $messageCommentObj = M('MessageComment');
        if(!$messageCommentObj->where(array('id'=>$id))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'评论不存在或已被删除'));
        if(!$messageCommentObj->where(array('id'=>$id))->delete()) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 评论列表
     */
    public function comment(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $count = 10;

            $params = array(
                'p' => $page,
            );
            $this->assign('params', $params);

            $messageCommentObj = M('MessageComment');
            $messageObj = M('Message');

            $counts = $messageCommentObj->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $messageCommentObj->order('status, add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['msg_title'] = $messageObj->where(array('id'=>$val['msg_id']))->getField('title');
            }

            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->display();
        }else{

        }
    }

    /**
     * 消息推送
     */
    public function push(){
        if(!IS_POST){
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $content = I('post.content', '', 'strip_tags');
            $regid = I('post.regid', '', 'strip_tags');
            $platform = I('post.platform', '', 'strip_tags');
            $action = I('post.action', 0, 'int'); // 推送动作
            $key = I('post.key');
            $value = I('post.value');
            $filter_bk = I('post.filter_bk'); // 是否不推送已购买爆款用户
            $extra = array();
            if($action) $extra['action'] = $action;
            foreach($key as $k => $v){
                if($v) $extra[strtolower($v)] = $value[$k];
            }
            if(!$filter_bk){ // 普通推送
                $result = pushMsg($content, $regid, $platform, $extra);
                //if($result) $this->ajaxReturn(array('status'=>0,'info'=>$result));
                $this->ajaxReturn(array('status'=>1));
            }else{ // 过滤爆款用户推送
                $projectObj = M('Project');
                $rechargeLogObj = M('RechargeLog');
                $bkList = $projectObj->field('id')->where("type=107 and end_time>'".date('Y-m-d H:i:s').'.'.getMillisecond()."000' and is_delete=0")->select();
                $bkIds = ''; $userIds = '';
                foreach($bkList as $key => $val){
                    $bkIds .= ','.$val['id'];
                }
                if($bkIds) $bkIds = substr($bkIds, 1);
                if($bkIds) $userIds = $rechargeLogObj->field('user_id')->where("project_id in (".$bkIds.")")->select(); // 已购买过爆款的用户(爆款期内)
                if(!$userIds){ // 没有爆款用户,切换成普通推送
                    $result = pushMsg($content, $regid, $platform, $extra);
                    //if($result) $this->ajaxReturn(array('status'=>0,'info'=>$result));
                    $this->ajaxReturn(array('status'=>1));
                }else{ // 有爆款用户,排除爆款用户推送
                    $userObj = M('User');
                    $extIds = '';
                    foreach($userIds as $key => $val){
                        $extIds .= ','.$val['id'];
                    }
                    if($extIds) $extIds = substr($extIds, 1);
                    $enableIds = $userObj->field('id')->where("id not in (".$extIds.")")->select();
                    $pushIds = '';
                    $pos = 0; // 100个推送注册ID为一组
                    foreach($enableIds as $key => $val){
                        $pos += 1;
                        $pushIds .= ','.$val['id'];

                        if($pos >= 100){
                            $pos = 0;
                            $pushIds = substr($pushIds, 1);
                            $result = pushMsg($content, $pushIds, $platform, $extra);
                            $pushIds = '';
                        }
                    }
                    if($pushIds) {
                        $pushIds = substr($pushIds, 1);
                        $result = pushMsg($content, $pushIds, $platform, $extra);
                        //$this->ajaxReturn(array('status'=>0,'info'=>$extIds));
                    }
                    //$this->ajaxReturn(array('status'=>0,'info'=>$result));
                    $this->ajaxReturn(array('status'=>1));
                }
            }
        }
    }

    /**
     * 意见建议
     */
    public function suggest(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 20; // 每页显示条数
            $suggestObj = M('Suggest');
            $userObj = M('User');

            $counts = $suggestObj->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $suggestObj->order('is_read,add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            //print_r($list);
            
            foreach($list as $key => $val){
                if($val['user_id']){
                    $list[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                }
                
                if ($val['img']){
                    $_imgList=explode('|', $val['img']);
                    $list[$key]['img'] = $_imgList;
                } else{
                    $list[$key]['img'] = "";
                }
                
                if($val['edit_user_id']){
                    $list[$key]['edit_user_id'] = M('Member')->where('id='.$val['edit_user_id'])->getField('username');
                }else{
                    $list[$key]['edit_user_id'] = '';
                }
                
                if($val['edit_time']){
                    $list[$key]['edit_time'] = date("Y-m-d H:i:s",$val['edit_time']);
                }else{
                    $list[$key]['edit_time'] = '';
                }
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
     * 意见建议详细
     */
    public function suggest_detail(){
        $id = I('get.id', 0, 'int');
        $page = I('get.p', 1, 'int');
        $params = array(
            'page' => $page,
        );
        $this->assign('params', $params);

        $suggestObj = M('Suggest');
        $detail = $suggestObj->where(array('id'=>$id))->find();
        if(!$detail){
            $this->error('意见建议不存在或已被删除');exit;
        }
        if(!$detail['is_read']) $suggestObj->where(array('id'=>$id))->save(array('is_read'=>1));
        if($detail['user_id']){ // 如果是登录用户提交,获取用户信息
            $userObj = M('User');
            $detail['user_info'] = $userObj->where(array('id'=>$detail['user_id']))->find();
            if ($detail['img']){
                $detail['img'] = explode('|', $detail['img']);
            } else{
                $detail['img'] = "";
            }
        }
        $this->assign('detail', $detail);
        $this->display();
    }

    /**
     * 删除意见建议
     */
    public function suggest_delete(){
        if(!IS_POST || !IS_AJAX) exit;

        $isMulti = false; // 是否批量删除
        $id = I('post.id');
        if (!is_numeric($id)) {
            $isMulti = true;
        }

        $suggestObj = M('Suggest');
        if (!$isMulti) {
            $info = $suggestObj->where(array('id' => $id))->find();
        } else {
            $info = $suggestObj->where('id in (' . $id . ')')->select();
        }
        if (!$info) $this->ajaxReturn(array('status' => 0, 'info' => '意见建议信息不存在或已被删除'));
        if (!$isMulti) {
            if (!$suggestObj->where(array('id' => $id))->delete()) $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
        } else {
            if (!$suggestObj->where('id in (' . $id . ')')->delete()) $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
        }
        $this->ajaxReturn(array('status' => 1));
    }

    /**
     * 更改意见建议状态
     */
    public function suggest_status_change(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $suggestObj = M('Suggest');
        $eidt_user_id = $_SESSION[ADMIN_SESSION]['uid'];
        $suggestObj->where(array('id'=>$id))->save(array('is_read'=>1,'edit_time'=>time(),'edit_user_id'=>$eidt_user_id));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 回复客户意见建议
     */
    public function suggest_reply(){
        if(!IS_POST){

        }else{
            $suggestObj = M("Suggest");
            $userObj = M("User");
            $mid = I('post.mid', 0, 'int');
            $uid = I('post.uid', 0, 'int');
            $content = trim(I('post.content', '', 'strip_tags'));
            if(!$mid || !$uid || !$content) $this->ajaxReturn(array('status'=>0,'info'=>'数据不完整'));
            $title = $suggestObj->where(array('id'=>$mid))->getField('content');
            $registrationId = $userObj->where(array('id'=>$uid))->getField('registration_id');
            $message = "您提交的意见建议【".$title."】客服已回复:\r\n".$content;
            if(!send_personal_message(0, $uid, $message)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'回复失败,请重试'));
            }
            $eidt_user_id = $_SESSION[ADMIN_SESSION]['uid'];
            $suggestObj->where(array('id'=>$mid))->save(array('is_reply'=>1,'reply_content'=>$content,'edit_time'=>time(),'edit_user_id'=>$eidt_user_id));
            if($registrationId) pushMsg("您提交的意见建议客服已回复【点击查看】", $registrationId, '', array('action'=>4));
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 发送短信
     */
    public function send_sms(){
        if(!IS_POST){
            $this->display();
        }else{
            if(!IS_AJAX) exit;
            $errorMsg = array(
                101 => '无此用户',
                102 => '密码错',
                103 => '提交过快（提交速度超过流速限制）',
                104 => '系统忙（因平台侧原因，暂时无法处理提交的短信）',
                105 => '敏感短信（短信内容包含敏感词）',
                106 => '消息长度错（>536或<=0）',
                107 => '包含错误的手机号码',
                108 => '手机号码个数错（群发>50000或<=0;单发>200或<=0）',
                109 => '无发送额度（该用户可用短信数已使用完）',
                110 => '不在发送时间内',
                111 => '超出该账户当月发送额度限制',
                112 => '无此产品，用户没有订购该产品',
                113 => 'extno格式错（非数字或者长度不对）',
                115 => '自动审核驳回',
                116 => '签名不合法，未带签名（用户必须带签名的前提下）',
                117 => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',
                118 => '用户没有相应的发送权限',
                119 => '用户已过期',
            );

            $phones = trim(I('post.phones', '', 'strip_tags'));
            $content = trim(I('post.content', '', 'strip_tags'));
            $params = 'account='.C('SMS_INTDERFACE.account');
            $params .= '&pswd='.C('SMS_INTDERFACE.pswd');
            $params .= '&mobile='.$phones;
            $params .= '&msg='.$content;
            $params .= '&needstatus=false';
//            $params .= '&product=235659781';
            $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params);
            $arr = explode("\n", $smsData);
            foreach($arr as $key => $val){
                $arr[$key] = explode(',', $val);
            }
            $msgid = trim($arr[1][0]);
            if($arr[0][1] != 0) $this->ajaxReturn(array('status'=>0, 'info'=>$errorMsg[$arr[0][1]]));
            $this->ajaxReturn(array('status'=>1,'info'=>$msgid));
        }
    }

    /**
     * 个人消息
     */
    public function personal(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $count = 10;

            $params = array(
                'p' => $page,
            );
            $this->assign('params', $params);

            $messagePersonalObj = M('MessagePersonal');
            $messagePersonalContentObj = M('MessagePersonalContent');

            $counts = $messagePersonalObj->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $messagePersonalObj->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['content'] = $messagePersonalContentObj->where(array('id'=>$val['message_content_id']))->find();
            }
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->display();
        }else{

        }
    }

    /**
     * 发布消息
     */
    public function personal_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $recipient = trim(I('post.recipient', '', 'strip_tags'));
            $actionType = I('post.action', 0, 'int');
            $url = trim(I('post.url', '', 'strip_tags'));
            $ext = trim(I('post.ext', '', 'strip_tags'));
            $content = trim(I('post.content', '', 'strip_tags'));

            if(!$recipient) $this->ajaxReturn(array('status'=>0,'info'=>'发送对象不能为空'));
            if(!$content) $this->ajaxReturn(array('status'=>0,'info'=>'消息内容不能为空'));

            $messagePersonalContentObj = M('MessagePersonalContent');
            $messagePersonalObj = M('MessagePersonal');
            $userObj = M('User');

            $rowsContent = array(
                'url' => $url,
                'ext' => $ext,
                'content' => $content,
            );
            $rid = $messagePersonalContentObj->add($rowsContent);
            if(!$rid) $this->ajaxReturn(array('status'=>0,'info'=>'添加个人消息失败'));
            $recipient = str_replace(",", "','", $recipient);
            $uids = $userObj->field('id')->where("username in ('".$recipient."')")->select();
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            foreach($uids as $key => $val){
                $dataList[] = array('sender_uid'=>0,'recipient_uid'=>$val['id'],'message_content_id'=>$rid,'action'=>$actionType,'add_time'=>$time);
            }
            $messagePersonalObj->addAll($dataList);
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/message/personal'));
        }
    }

    /**
     * 删除个人消息
     */
    public function personal_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $messagePersonalObj = M('MessagePersonal');
        if(!$messagePersonalObj->where(array('id'=>$id))->delete()) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }
    
    
    
    public function msg_push_index(){
        if(!IS_POST){
            $status = I('get.status', -1, 'int'); // 状态
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数
            if($status>=0){
                $cond = 'is_delete=0 and status = '.$status;
            }else {
                $cond = 'is_delete=0';
            }
            $counts = M('msgPush')->where($cond)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();

            $order = 'ctime desc';
            if($status == 2){ // 推送成功
                $order = 'done_time desc';
            }else if($status === 0){
                $order = 'push_time asc';
            }

            $list = M('msgPush')->where($cond)->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
//            foreach($list as $key => $val){
//                //$android_ver_arr = explode(',',$val['android_ver']);
//                //foreach ($android_ver_arr as $val){
//                //    echo $val.'-';
//                //}
//
//            }
//            foreach ($list as $k=>$l){
//                $list[$k]['content'] = $this->userTextDecode($l['content']);
//            }
            $params = array(
                'page' => $page,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('status', $status);
            $this->assign('params', $params);
            $this->display();
        }
    }
    
    public function msg_push_add(){
        if(!IS_POST){
            $android_list = M('user')->field('app_version')->where("device_type=2 and app_version>='2.0'")->group('app_version')->select();
            $ios_list = M('user')->field('app_version')->where("device_type=1 and app_version>='2.0'")->group('app_version')->select();

            $this->assign('android_list',$android_list);
            $this->assign('ios_list',$ios_list);
            $this->display();
        }else{
            
            $content = trim(I('post.content', '', 'strip_tags'));
            $target = I('post.target', 1, 'int');//1按用户，0按版本
            $regid = trim(I('post.regid', '', 'strip_tags'));
            
            $push_type = I('post.push_type', 0, 'int'); //0关，1开   定时推送
            $push_time = strtotime(I('post.time','','strip_tags')); //指定推送时间
            
            $action = I('post.action', 0, 'int'); // 推送动作
            $key = I('post.key');
            $value = I('post.value');

            $is_login = I('post.is_login', 0, 'int'); // 是否 登录 1 是 0 否
            
            
            $android_ver = I('post.android_ver',0);//全部
            $ios_ver = I('post.ios_ver',0);//全部

            $mobiles = I('post.mobiles','', 'strip_tags');

            $text = json_encode($content); //暴露出unicode
            $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
                return addslashes($str[0]);
            },$text);
            $contentStore =  json_decode($text);

            //判断定时时间
            if($push_type==1){
                if($push_time<time()){
                    $this->ajaxReturn(array('status'=>0,'info'=>'定时推送时间小于当前时间'));
                }
            }else{ // 定时推送 关时，推送时间为当前时间
                $push_time = time();
            }

            if($android_ver == 1){
                $android_ver = implode(',',I('post._android_ver',-1));
                if(!$android_ver) $android_ver = -1; //没选等于 -1
            }
            
            if($ios_ver == 1){
                $ios_ver = implode(',',I('post._ios_ver',0));
                if(!$ios_ver) $ios_ver = -1; // 没选等于 -1
            }
            
            if($action) $extra['action'] = $action;
            foreach($key as $k => $v){
                if($v) $extra[strtolower($v)] = $value[$k];
            }


            if($action == 5){ // URl
                if(!$extra['url']) $this->ajaxReturn(array('status'=>0,'info'=>'请输入推送URl地址'));
            }
            if($action == 6){ // 产品详细
                if(!$extra['id']) $this->ajaxReturn(array('status'=>0,'info'=>'请输入id'));
                if(!$extra['title']) $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题'));
            }

            
            if(!$content) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入推送内容'));
            }
            
            if ($target == 1){
                
                if(!$regid){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入Registration ID'));
                }
                
                $users = M("user")->field('id,channel,last_channel,registration_id,device_type')->where("registration_id='$regid'")->find();
                
                if(!$users){
                    $this->ajaxReturn(array('status'=>0,'info'=>'没有查到 Registration Id: '.$regid.'的用户'));
                }
            }

            //手机号推送
            if ($target == 2) {
                if (!$mobiles) {
                    $this->ajaxReturn(['status' => 0, 'info' => '手机号不能为空']);
                }
                $mobiles = array_filter(explode('#', trim($mobiles)));
                $mobiles = array_unique($mobiles);
                foreach ($mobiles as $v) {
                    if (!preg_match("/^1[345789]\d{9}$/", $v)) {
                        $this->ajaxReturn(['status' => 0, 'info' => '请填写正确的手机号: ' . $v]);
                    }
                }
                $ids       = M("user")->field('registration_id,username')->where("username in(" . implode(',', $mobiles) . ") and registration_id<>''")->select();
                $pushedArr = array_map('current', $ids);
                //$idsArray         = array_map('current', $ids);
                //$mobilesArray     = array_map('end', $ids);
                //$successIdsStr    = implode(',', $idsArray);
                //$pushFailArr      = array_diff($mobiles, $mobilesArray);
                //$pushFailStr      = implode('#', $pushFailArr);
                $extra['islogin'] = $is_login;
                if (!$ids) {
                    $strMsgArr = [];
                    for ($i = 0; $i < 10; $i++) {
                        if ($mobiles[$i]) {
                            array_push($strMsgArr, $mobiles[$i]);
                        }
                    }
                    $strMsg = implode(' 、', $strMsgArr);
                    $this->ajaxReturn(['status' => 0, 'info' => '0个手机号推送成功！' . $strMsg . '累计 ' . count($mobiles) . ' 个手机号无对应极光ID，无法进行推送。']);
                }

                //定时推送
                if ($push_type == 1) {
                    $row = [
                        'content'          => $contentStore,
                        'target'           => 2,
                        'registration_id'  => '',
                        'android_ver'      => $android_ver,
                        'ios_ver'          => $ios_ver,
                        'push_type'        => $push_type,
                        'push_time'        => $push_time,
                        'push_extra'       => json_encode($extra),
                        'ctime'            => time(),
                        'add_user_id'      => $_SESSION[ADMIN_SESSION]['uid'],
                        'mtime'            => 0,
                        'edit_user_id'     => 0,
                        'status'           => 0,
                        'is_delete'        => 0,
                        'is_login'         => $is_login,
                        'mobile_group_ids' => implode('#', $mobiles)
                    ];
                    M('msgPush')->add($row);
                    $this->ajaxReturn(['status' => 1, 'message_info' => '定时推送保存成功', 'info' => C('ADMIN_ROOT') . '/message/msg_push_index']);
                } else {
                    $row = [
                        'content'          => $contentStore,
                        'target'           => 2,
                        'registration_id'  => '',
                        'android_ver'      => $android_ver,
                        'ios_ver'          => $ios_ver,
                        'push_type'        => $push_type,
                        'push_time'        => $push_time,
                        'push_extra'       => json_encode($extra),
                        'ctime'            => time(),
                        'add_user_id'      => $_SESSION[ADMIN_SESSION]['uid'],
                        'mtime'            => 0,
                        'edit_user_id'     => 0,
                        'status'           => 2,
                        'is_delete'        => 0,
                        'is_login'         => $is_login,
                        'mobile_group_ids' => '',
                        'done_time'        => time()
                    ];
                    M('msgPush')->add($row);
                    $emptyIdsArr   = [];
                    $appArr        = [];
                    $pushMobileStr = implode(',', $mobiles);
                    $pushUsers     = M("user")->field('id,registration_id,username,channel,last_channel,device_type,latest_device_type')->where("username in(" . $pushMobileStr . ")")->select();

                    foreach ($pushUsers as $v) {
                        if ($v['registration_id']) {
                            $_app = getAppId(trim($v['last_channel']));
                            if ($v['latest_device_type'] == 2) {
                                //$platform = 'android';
                                $appArr[$_app]['android'][] = $v['registration_id'];
                            } else if ($v['latest_device_type'] == 1) {
                                //$platform = 'ios';
                                $appArr[$_app]['ios'][] = $v['registration_id'];
                            }
                        } else {
                            array_push($emptyIdsArr, $v['username']);
                        }
                    }

                    foreach ($appArr as $key => $val) {
                        if (isset($val['android']) && $val['android']) {
                            $this->pushMessageLimit($val['android'],$content,'android',$extra,$key);
                        }
                        if (isset($val['ios']) && $val['ios']) {
                            $this->pushMessageLimit($val['ios'],$content,'ios',$extra,$key);
                        }
                    }
                    $emptyIdsArr = array_unique($emptyIdsArr);
                    if ($emptyIdsArr) {
                        $pushEmptyArr = [];
                        for ($i = 0; $i < 10; $i++) {
                            if ($emptyIdsArr[$i]) {
                                array_push($pushEmptyArr, $emptyIdsArr[$i]);
                            }
                        }
                        $pushEmptyStr = implode('、', $pushEmptyArr);
                        $this->ajaxReturn(['status' => 1, 'message_info' => count($pushedArr) . ' 个手机号推送成功！' . $pushEmptyStr . ' 累计 ' . count($emptyIdsArr) . ' 个手机号无对应极光ID，无法进行推送。', 'info' => C('ADMIN_ROOT') . '/message/msg_push_index']);
                    } else {
                        $this->ajaxReturn(['status' => 1, 'message_info' => count($mobiles) . ' 个手机号推送成功！', 'info' => C('ADMIN_ROOT') . '/message/msg_push_index']);
                    }
                }
            }


            $row = array(
                'content'=>$contentStore,
                'target' =>$target,
                'registration_id'=>$regid,
                'android_ver'=>$android_ver,
                'ios_ver' =>$ios_ver,
                'push_type'=>$push_type,
                'push_time'=>$push_time,
                'push_extra'=>json_encode($extra),
                'ctime'=>time(),
                'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'mtime'=>0,
                'edit_user_id'=>0,
                'status'=>0,
                'is_delete'=>0,
                'is_login'=>$is_login,
                'mobile_group_ids' => $mobiles,
            );
            if($id = M('msgPush')->add($row)){
                
                //个人推送，并且马上推送的，马上处理掉
                if($target == 1 && $push_type == 0){// channel
                    $_app = getAppId(trim($users['last_channel']));
                    
                    $platform = '';  //device_type

                    if($users['latest_device_type'] == 2) {
                        $platform = 'android';
                    } else if($users['latest_device_type'] == 1) {
                        $platform = 'ios';
                    }

                    $result = pushMsg($content, $regid, $platform, $extra,$_app);

                    if($result->isOk){
                        M('MsgPush')->where('id='.$id)->save(array('status'=>2,'done_time'=>time()));
                    }else{
                        $this->ajaxReturn(array('status'=>0,'info'=>'消息推送保存失败'));
                    }



                    
                    //解析$result的值。判断推送情况
                    
                    //var_dump($result);                    
                    //\Think\Log::write('推送返回:'.json_encode($result),'INFO');
                }
                $this->ajaxReturn(array('status'=>1,'message_info'=>'推送信息保存成功','info'=>C('ADMIN_ROOT').'/message/msg_push_index'));
            } else{
                $this->ajaxReturn(array('status'=>0,'info'=>'消息推送保存失败'));
            }
        }
    }


    //消息推送 大于1000 每次推送 500
    private function pushMessageLimit($data, $content, $device, $extra, $key)
    {
        $count = count($data);
        $length = 500;
        if ($count >= 1000) {
            $step = ceil($count / $length);
            for ($i = 0; $i < $step; $i++) {
                $pushArr = array_slice($data, $i * $length, $length);
                $pushStr = implode(',', $pushArr);
                pushMsg($content, $pushStr, $device, $extra, $key);
            }
        } else {
            $pushStrOnce = implode(',', $data);
            pushMsg($content, $pushStrOnce, $device, $extra, $key);
        }
    }

    
    
    public function msg_push_edit(){
        if(!IS_POST){
            $id = I('id',0);

            if(!$id) exit('请选择一个数据');


            $android_list = M('user')->field('app_version')->where("device_type=2 and app_version>='2.0'")->group('app_version')->select();
            $ios_list = M('user')->field('app_version')->where("device_type=1 and app_version>='2.0'")->group('app_version')->select();

            $push_list = M('msgPush')->where(array('id'=>$id))->find();

            $this->assign('android_list',$android_list);
            $this->assign('ios_list',$ios_list);
            $this->assign('push_list',$push_list);
            $this->display();
        }else{
            $id = I('post.id',0,'int');
            if(!$id){
                $this->ajaxReturn(array('status'=>0,'info'=>'数据不存在'));
            }

            $status = I('post.status',0,'int');
            if($status !=0 ){
                $this->ajaxReturn(array('status'=>0,'info'=>'只有待推送的数据可以修改'));
            }

            $content = trim(I('post.content', '', 'strip_tags'));
            $target = I('post.target', 1, 'int');//1按用户，0按版本
            $regid = trim(I('post.regid', '', 'strip_tags'));

            $push_type = I('post.push_type', 0, 'int'); //0关，1开   定时推送
            $push_time = strtotime(I('post.time','','strip_tags')); //指定推送时间

            $action = I('post.action', 0, 'int'); // 推送动作
            $key = I('post.key');
            $value = I('post.value');

            $is_login = I('post.is_login', 0, 'int'); // 是否 登录 1 是 0 否


            $android_ver = I('post.android_ver',-1);//全部
            $ios_ver = I('post.ios_ver',-1);//全部

            $mobiles = I('post.mobiles','', 'strip_tags');

            //判断定时时间
            if($push_type==1){
                if($push_time<time()){
                    $this->ajaxReturn(array('status'=>0,'info'=>'定时推送时间小于当前时间'));
                }
            }else{ // 定时推送 关时，推送时间为当前时间
                $push_time = time();
            }


            if($android_ver == 1){
                $android_ver = implode(',',I('post._android_ver',-1));
                if(!$android_ver) $android_ver = -1; //没选等于 -1
            }

            if($ios_ver == 1){
                $ios_ver = implode(',',I('post._ios_ver',0));
                if(!$ios_ver) $ios_ver = -1; // 没选等于 -1
            }

            if($action) $extra['action'] = $action;
            foreach($key as $k => $v){
                if($v) $extra[strtolower($v)] = $value[$k];
            }

            if($action == 5){ // URl
                if(!$extra['url']) $this->ajaxReturn(array('status'=>0,'info'=>'请输入推送URl地址'));
            }
            if($action == 6){ // 产品详细
                if(!$extra['id']) $this->ajaxReturn(array('status'=>0,'info'=>'请输入id'));
                if(!$extra['title']) $this->ajaxReturn(array('status'=>0,'info'=>'请输入标题'));
            }

            if(!$content) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入推送内容'));
            }

            $text = json_encode($content); //暴露出unicode
            $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
                return addslashes($str[0]);
            },$text);
            $contentStore =  json_decode($text);

            if ($target == 1){

                if(!$regid){
                    $this->ajaxReturn(array('status'=>0,'info'=>'请输入Registration ID'));
                }

                $users = M("user")->field('id,channel,last_channel,registration_id,device_type')->where("registration_id='$regid'")->find();

                if(!$users){
                    $this->ajaxReturn(array('status'=>0,'info'=>'没有查到 Registration Id: '.$regid.'的用户'));
                }
            }

            //手机号推送
            if ($target == 2) {
                if (!$mobiles) {
                    $this->ajaxReturn(['status' => 0, 'info' => '手机号不能为空']);
                }
                $mobiles = explode('#', trim($mobiles));
                $mobiles = array_unique($mobiles);
                foreach ($mobiles as $v) {
                    if (!preg_match("/^1[34578]\d{9}$/", $v)) {
                        $this->ajaxReturn(['status' => 0, 'info' => '请填写正确的手机号: ' . $v]);
                    }
                }
                $ids              = M("user")->field('registration_id,username')->where("username in(" . implode(',', $mobiles) . ") and registration_id<>''")->select();
                $extra['islogin'] = $is_login;
                if (!$ids) {
                    $strMsgArr = [];
                    for ($i = 0; $i < 10; $i++) {
                        if ($mobiles[$i]) {
                            array_push($strMsgArr, $mobiles[$i]);
                        }
                    }
                    $strMsg = implode(' 、', $strMsgArr);
                    $this->ajaxReturn(['status' => 0, 'info' => '0个手机号推送成功！' . $strMsg . '累计 ' . count($mobiles) . ' 个手机号无对应极光ID，无法进行推送。']);
                }

                //定时推送
                if ($push_type == 1) {
                    $row = [
                        'content'          => $contentStore,
                        'target'           => 2,
                        'registration_id'  => '',
                        'android_ver'      => $android_ver,
                        'ios_ver'          => $ios_ver,
                        'push_type'        => $push_type,
                        'push_time'        => $push_time,
                        'push_extra'       => json_encode($extra),
                        'ctime'            => time(),
                        'add_user_id'      => $_SESSION[ADMIN_SESSION]['uid'],
                        'mtime'            => 0,
                        'edit_user_id'     => 0,
                        'status'           => 0,
                        'is_delete'        => 0,
                        'is_login'         => $is_login,
                        'mobile_group_ids' => implode('#',$mobiles)
                    ];
                    M('MsgPush')->where('id='.$id)->save($row);
                    $this->ajaxReturn(['status' => 1,'message_info'=>'定时推送保存成功','info' => C('ADMIN_ROOT').'/message/msg_push_index']);
                } else {
                    $row = [
                        'content'          => $contentStore,
                        'target'           => 2,
                        'registration_id'  => '',
                        'android_ver'      => $android_ver,
                        'ios_ver'          => $ios_ver,
                        'push_type'        => $push_type,
                        'push_time'        => $push_time,
                        'push_extra'       => json_encode($extra),
                        'ctime'            => time(),
                        'add_user_id'      => $_SESSION[ADMIN_SESSION]['uid'],
                        'mtime'            => 0,
                        'edit_user_id'     => 0,
                        'status'           => 2,
                        'is_delete'        => 0,
                        'is_login'         => $is_login,
                        'mobile_group_ids' => '',
                        'done_time'        => time()
                    ];
                    M('MsgPush')->where('id='.$id)->save($row);

                    $pushedArr   = [];
                    $emptyIdsArr = [];
                    $pushMobileStr = implode(',',$mobiles);
                    $pushUsers =  M('user')->where("username in(".$pushMobileStr.")")->field('id,username,registration_id,channel,last_channel,device_type,latest_device_type')->select();
                    foreach ($pushUsers as $v){
                        if ($v['registration_id']) {
                            $_app     = getAppId(trim($v['last_channel']));
                            $platform = '';
                            if ($v['latest_device_type'] == 2) {
                                $platform = 'android';
                            } else if ($v['latest_device_type'] == 1) {
                                $platform = 'ios';
                            }
                            pushMsg($content, $v['registration_id'], $platform, $extra, $_app);
                            array_push($pushedArr, $v['username']);
                        } else {
                            array_push($emptyIdsArr, $v['username']);
                        }
                    }
                    $emptyIdsArr = array_unique(array_merge($emptyIdsArr,array_diff($mobiles, $pushedArr)));

                    if ($emptyIdsArr) {
                        $pushEmptyArr = [];
                        for ($i = 0; $i < 10; $i++) {
                            if ($emptyIdsArr[$i]) {
                                array_push($pushEmptyArr, $emptyIdsArr[$i]);
                            }
                        }
                        $pushEmptyStr = implode('、', $pushEmptyArr);
                        $this->ajaxReturn(['status' => 1, 'message_info' => count($pushedArr) . ' 个手机号推送成功！' . $pushEmptyStr . ' 累计 ' . count($emptyIdsArr) . ' 个手机号无对应极光ID，无法进行推送。','info'=>C('ADMIN_ROOT').'/message/msg_push_index']);
                    } else {
                        $this->ajaxReturn(['status' => 1, 'message_info' => count($mobiles) . ' 个手机号推送成功！','info'=>C('ADMIN_ROOT').'/message/msg_push_index']);
                    }
                }
            }else{
                $mobiles = '';
            }


            $extra['islogin'] = $is_login; // 是否登录
            $row = array(
                'content'=>$contentStore,
                'target' =>$target,
                'registration_id'=>$regid,
                'android_ver'=>$android_ver,
                'ios_ver' =>$ios_ver,
                'push_type'=>$push_type,
                'push_time'=>$push_time,
                'push_extra'=>json_encode($extra),
               // 'ctime'=>time(),
                //'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'edit_user_id'=>$this->uid,
                'mtime'=>0,
                'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
               // 'status'=>0,
               // 'is_delete'=>0
            );

            if(M('msgPush')->where(array('id'=>$id))->save($row)){

                //个人推送，并且马上推送的，马上处理掉
                if($target == 1 && $push_type == 0){ // channel
                    $_app = getAppId(trim($users['last_channel']));

                    $platform = '';  //device_type

                    if($users['latest_device_type'] == 2) {
                        $platform = 'android';
                    } else if($users['latest_device_type'] == 1) {
                        $platform = 'ios';
                    }
                    $result = pushMsg($content, $regid, $platform, $extra,$_app);

                    if($result->isOk){
                        M('MsgPush')->where('id='.$id)->save(array('status'=>2,'done_time'=>time()));
                    }else{
                        $this->ajaxReturn(array('status'=>0,'info'=>'消息推送保存失败'));
                    }




                    //解析$result的值。判断推送情况

                    //var_dump($result);
                    //\Think\Log::write('推送返回:'.json_encode($result),'INFO');
                }
                $this->ajaxReturn(array('status'=>1,'message_info'=>'消息推送保存成功','info'=>C('ADMIN_ROOT').'/message/msg_push_index'));
            } else{
                $this->ajaxReturn(array('status'=>0,'info'=>'消息推送保存失败'));
            }
        }
    }
    
    public function msg_push_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');

        $msgPushObj = M('msgPush');

        if($msgPushObj->where(array('status'=>1,'id'=>$id))->find()){
            $this->ajaxReturn(array('status'=>0,'info'=>'处理中数据不能删除'));
            exit;
        }

        if($msgPushObj->where(array('status'=>2,'id'=>$id))->find()){
            $this->ajaxReturn(array('status'=>0,'info'=>'已推送数据不能删除'));
            exit;
        }

        if(!$msgPushObj->where(array('id'=>$id))->save(array('is_delete'=>1,'mtime'=>time(),'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid']))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
    
    public function msg_push_content(){
        if(!IS_POST){
            $id = I('id',0);

            if(!$id) exit('请选择一个数据');


            $android_list = M('user')->field('app_version')->where("device_type=2 and app_version>='2.0'")->group('app_version')->select();
            $ios_list = M('user')->field('app_version')->where("device_type=1 and app_version>='2.0'")->group('app_version')->select();

            $push_list = M('msgPush')->where(array('id'=>$id))->find();

            $this->assign('android_list',$android_list);
            $this->assign('ios_list',$ios_list);
            $this->assign('push_list',$push_list);
            $this->display();
        }else{
            //code
        }
    }

}