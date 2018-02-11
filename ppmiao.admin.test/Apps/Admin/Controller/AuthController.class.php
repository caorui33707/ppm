<?php
namespace Admin\Controller;

/**
 * 权限分配
 * @package Admin\Controller
 */
class AuthController extends AdminController{

    /**
     * 后台管理员列表
     */
    public function member(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $uname = urldecode(I('get.uname', '', 'strip_tags'));
            $count = 10; // 每页显示条数

            $memberObj = M('Member');
            $authGroupObj = M('AuthGroup');
            $authGroupAccessObj = M('AuthGroupAccess');

            if($uname) $cond[] = "username like '%".$uname."%'";
            $cond[] = "id<>1";
            $conditions = implode(' and ', $cond);

            $counts = $memberObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $memberObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $gid = $authGroupAccessObj->where(array('uid'=>$val['id']))->getField('group_id');
                $list[$key]['groupName'] = $authGroupObj->where(array('id'=>$gid))->getField('title');
            }

            $params = array(
                'page' => $page,
                'uname' => $uname,
            );
            $this->assign('params', $params);
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->display();
        }else{
            $uname = I('post.uname', '', 'strip_tags');
            $quest = '';
            if($uname) $quest .= '/uname/'.urlencode($uname);
            redirect(C('ADMIN_ROOT') . '/auth/member'.$quest);
        }
    }

    /**
     * 新增管理员
     */
    public function member_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $uname = I('get.uname', '', 'strip_tags');

            $params = array(
                'page' => $page,
                'uname' => $uname,
            );
            $this->assign('params', $params);

            $authGroupObj = M('AuthGroup');
            $authGroupList = $authGroupObj->where('id<>1')->select();
            $this->assign('auth_group', $authGroupList);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $uname = I('post.uname', '', '');

            $username = trim(I('post.username', '', 'strip_tags'));
            $password = trim(I('post.password', '', 'strip_tags'));
            $group = I('post.group', 0, 'int');

            if(!$username) $this->ajaxReturn(array('status'=>0,'info'=>'用户名不能为空'));
            if(!$password) $this->ajaxReturn(array('status'=>0,'info'=>'密码不能为空'));
            if(!$group) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个权限组'));

            $memberObj = M('Member');
            $authGroupAccessObj = M('AuthGroupAccess');

            if($memberObj->where(array('username'=>$username))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'用户名已存在'));

            $rows = array(
                'username' => $username,
                'password' => md5($password),
                'status' => 1,
                'add_time' => date('Y-m-d H:i:s').'.'.getMillisecond(),
            );

            $memberObj->startTrans();
            $rid = $memberObj->add($rows);
            if(!$rid) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $userGroup = $authGroupAccessObj->where(array('uid'=>$rid))->find();
            if(!$userGroup){
                if(!$authGroupAccessObj->add(array('uid'=>$rid,'group_id'=>$group))){
                    $memberObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
                }
            }else{
                if($userGroup['group_id'] != $group){
                    if(!$authGroupAccessObj->where(array('uid'=>$rid))->save(array('group_id'=>$group))){
                        $memberObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
                    }
                }
            }
            $memberObj->commit();
            $quest = '';
            if($uname) $quest .= '/uname/'.$uname;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/auth/member/p/'.$page.$quest));
        }
    }

    /**
     * 编辑管理员
     */
    public function member_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $uname = I('get.uname', '', 'strip_tags');

            $params = array(
                'page' => $page,
                'uname' => $uname,
            );
            $this->assign('params', $params);

            $authGroupObj = M('AuthGroup');
            $authGroupList = $authGroupObj->where('id<>1')->select();
            $this->assign('auth_group', $authGroupList);

            $memberObj = M('Member');
            $authGroupAccess = M('AuthGroupAccess');

            $detail = $memberObj->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('管理员信息不存在或已被删除');exit;
            }
            $detail['group_id'] = $authGroupAccess->where(array('uid'=>$detail['id']))->getField('group_id');
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            $uname = I('post.uname', '', '');

            $password = trim(I('post.password', '', 'strip_tags'));
            $group = I('post.group', 0, 'int');
            $status = I('post.status', 1, 'int');

            if(!$group) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个权限组'));

            $memberObj = M('Member');
            $authGroupAccessObj = M('AuthGroupAccess');
            $rows = array(
                'status' => $status,
                'modify_time' => date('Y-m-d H:i:s').'.'.getMillisecond(),
            );
            if($password) $rows['password'] = md5($password);

            $memberObj->startTrans();
            if(!$memberObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $userGroup = $authGroupAccessObj->where(array('uid'=>$id))->find();
            if(!$userGroup){
                if(!$authGroupAccessObj->add(array('uid'=>$id,'group_id'=>$group))){
                    $memberObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
                }
            }else{
                if($userGroup['group_id'] != $group){
                    if(!$authGroupAccessObj->where(array('uid'=>$id))->save(array('group_id'=>$group))){
                        $memberObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
                    }
                }
            }
            $memberObj->commit();
            $quest = '';
            if($uname) $quest .= '/uname/'.$uname;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/auth/member/p/'.$page.$quest));
        }
    }

    /**
     * 权限组管理
     */
    public function group(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数

            $authGroupObj = M('AuthGroup');

            $cond[] = "id<>1";
            $conditions = implode(' and ', $cond);

            $counts = $authGroupObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $authGroupObj->where($conditions)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->display();
        }else{

        }
    }

    /**
     * 添加权限组
     */
    public function group_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $title = trim(I('post.title', '', 'strip_tags'));
            $description = trim(I('post.description', '', 'strip_tags'));
            $rules = I('post.rules');

            $rulesStr = '';
            if($rules) $rulesStr = implode(',', $rules);

            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'权限组名称不能为空'));

            $authGroupObj = M('AuthGroup');
            if($authGroupObj->where(array('title'=>$title))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的权限组'));

            $rows = array(
                'title' => $title,
                'description' => $description,
                'module' => 'admin',
                'type' => 1,
                'status' => 1,
                'rules' => $rulesStr,
            );
            if(!$authGroupObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/auth/group/p/'.$page));
        }
    }

    /**
     * 编辑权限组
     */
    public function group_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);

            $authGroupObj = M('AuthGroup');
            $detail = $authGroupObj->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('权限组信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
            $title = trim(I('post.title', '', 'strip_tags'));
            $description = trim(I('post.description', '', 'strip_tags'));
            $rules = I('post.rules');
            $status = I('post.status', 1, 'int');

            $rulesStr = '';
            if($rules) $rulesStr = implode(',', $rules);

            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'权限组名称不能为空'));

            $authGroupObj = M('AuthGroup');

            $rows = array(
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'rules' => $rulesStr,
            );
            if(!$authGroupObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/auth/group/p/'.$page));
        }
    }

}