<?php
namespace Admin\Controller;

/**
 * APP版本管理控制器
 * @package Admin\Controller
 */
class VersionController extends AdminController{

    /**
     * 安卓历史版本
     */
    public function android(){
        if(!IS_POST){
            $this->display();
        }else{

        }
    }

    /**
     * IOS历史版本
     */
    public function ios(){
        if(!IS_POST){
            $this->display();
        }else{

        }
    }

    /**
     * 版本升级
     */
    public function upgrade(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数
            $appVersionReleaseObj = M('appVersionRelease');
            $counts = $appVersionReleaseObj->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $appVersionReleaseObj->where('is_delete=0')->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
     * 批量强制升级
     * @return [type] [description]
     */
    public function upgrade_force() {
        if (!IS_POST || !IS_AJAX) exit;

        $isMulti = false; // 是否批量删除
        $id = I('post.id');

        if (!is_numeric($id)) {
            $isMulti = true;
        }

        $versionObj = M('appVersionRelease');
        if (!$isMulti) {
            $info = $versionObj->where(array('id' => $id, 'is_delete' => 0))->find();
        } else {
            $info = $versionObj->where('id in (' . $id . ') and is_delete=0')->select();
        }
        if (!$info) $this->ajaxReturn(array('status' => 0, 'info' => '项目信息不存在或已被删除'));
        if (!$isMulti) {
            if (!$versionObj->where(array('id' => $id))->save(array('upgrade_type' => 1,'update_time'=>time()))) $this->ajaxReturn(array('status' => 0, 'info' => '修改失败,请重试'));
        } else {
            if (!$versionObj->where('id in (' . $id . ')')->save(array('upgrade_type' => 1,'update_time'=>time()))) $this->ajaxReturn(array('status' => 0, 'info' => '修改失败,请重试'));
        }
        $this->ajaxReturn(array('status' => 1));
    }
    
    /**
    * 渠道对应版本列表
    * @date: 2017-5-4 上午11:00:59
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function get_upgrade_list(){
        if(IS_AJAX){
            $id = I('post.id',0,'int');
            if($id) {
                $list = M('appVersionRelease')->field('id,version')->where(array('channel_id'=>$id))->order('id desc')->select();
                $this->ajaxReturn(array('status'=>1,'info'=>$list));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>''));
            }
        }
    }
    
    
    

    /**
     * Android版本添加
     */
    public function upgrade_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);

            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            $this->assign('channel_list', $channelList);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $version = trim(I('post.version', '', 'strip_tags'));
            $version_desc = trim(I('post.version_desc', '', 'strip_tags'));
            //$upgrade_type = I('post.upgrade_type', 1, 'int');
            $channel_desc = trim(I('post.channel_desc', '', 'strip_tags'));
            $app_url = trim(I('post.app_url', '', 'strip_tags'));
            
            $device_type = I('post.device_type', 1, 'int');
            
            //$upgrade_type =  I('post.upgrade_type',0,'int');
            
            if(!$version) $this->ajaxReturn(array('status'=>0,'info'=>'版本名不能为空'));
            if(!$version_desc) $this->ajaxReturn(array('status'=>0,'info'=>'版本描述不能为空'));
            
            if($channel_desc == '-1'){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择渠道'));
            }
            
            if($app_url == ''){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入下载地址'
                ));
            
            } else {
                if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$app_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的下载地址'
                    ));
                }
            }
            $channel_arr = explode('#', $channel_desc);
            $version_code = str_replace('.','',$version);
            if(M('appVersionRelease')->where('channel_id='.$channel_arr[0].' and version_code='.$version_code .' and is_delete=0')->count()>0){
                $this->ajaxReturn(array('status'=>0,'info'=>$channel_arr[1].'的'.$version.'版本已经有配置'));
            }
            $time = date('Y-m-d H:i:s', time());


            $dd = array(
                'version'=>$version,
                'version_code'=>$version_code,
                'channel'=>$channel_arr[1],
                'app_url'=>$app_url,
                'version_desc'=>$version_desc,
                'channel_id'=>$channel_arr[0],
                'add_time'=>$time,
                'device_type'=>$device_type,
                //'upgrade_type'=>$upgrade_type,
                'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'create_time'=>time()
            );
            $rid = M('appVersionRelease')->add($dd);
            if($rid) {
                
                
                $data = array(
                    'pushType'=>1,
                    //'registrationId'=>$registration_id,
                    'position'=>'0000',
                    'page'=>'0000',
                    //'lastDeviceType'=>$base_user['latest_device_type'],
                    //'lastChannel'=>$base_user['last_channel']
                );
                updatePushMsg($data);
                
                $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/version/upgrade/p/'.$page));
            } else {
                $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            }
        }
    }

    /**
     * Android版本编辑
     */
    public function upgrade_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');

            $params = array(
                'page' => $page,
            );
            
            $detail = M('appVersionRelease')->where(array('id'=>$id))->find();
            
            if(!$detail){
                $this->error('版本信息不存在或已被删除');exit;
            }
            
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            
            $this->assign('params', $params);
            $this->assign('channel_list', $channelList);
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $id = I('post.id', 0, 'int');
            
            $version_desc  = trim(I('post.version_desc', '', 'strip_tags'));
            $app_url = trim(I('post.app_url', '', 'strip_tags'));
            $upgrade_type =  I('post.upgrade_type',0,'int');
            
            if(!$version_desc) $this->ajaxReturn(array('status'=>0,'info'=>'版本描述不能为空'));
            
            if($app_url == ''){
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入下载地址'
                ));
            
            } else {
                if(!preg_match("/^(http:\/\/|https:\/\/).*$/",$app_url,$match)){
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请输入正确的下载地址'
                    ));
                }
            }

            $rows = array(
                'version_desc' => $version_desc,
                'app_url' => $app_url,
                'upgrade_type'=>$upgrade_type,
                'update_time' => time(),
                'edit_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            
            if(!M('appVersionRelease')->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            
            $data = array(
                'pushType'=>1,
                'position'=>'0000',
                'page'=>'0000',
            );
            updatePushMsg($data);
            
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/version/upgrade/p/'.$page));
        }
    }

    /**
     * Android版本删除
     */
    public function upgrade_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if($id) {
            $dd = array(
                'update_time'=>time(),
                'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'is_delete'=>1
            );
            if(M("appVersionRelease")->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>1));
            } else {
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
            }
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
    }

    /**
     * 版本渠道管理
     */
    public function upgrade_detail(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int'); // 版本ID
            $page = I('get.p', 1, 'int'); // 页码

            $androidVersionObj = M('AndroidVersion');
            $androidVersionDetailObj = M('AndroidVersionDetail');
            $constantObj = M('Constant');

            $detail = $androidVersionObj->where(array('id'=>$id))->find();
            if(!$detail) {
                $this->error('版本信息不存在或已被删除');exit;
            }

            $list = $androidVersionDetailObj->where(array('ver_id'=>$id))->order('add_time desc')->select();
            foreach($list as $key => $val){
                $list[$key]['channel_name'] = $constantObj->where(array('id'=>$val['channel_id']))->getField('cons_value');
            }

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);
            $this->assign('list', $list);
            $this->assign('detail', $detail);
            $this->display();
        }else{

        }
    }

    /**
     * 添加版本渠道
     */
    public function upgrade_detail_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $id = I('get.id', 0, 'int');

            $params = array(
                'page' => $page,
                'verid' => $id,
            );
            $this->assign('params', $params);

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            $this->assign('channel_list', $channelList);

            $androidVersionObj = M('AndroidVersion');
            $detail = $androidVersionObj->where(array('id'=>$id))->find();
            if(!$detail) {
                $this->error('版本信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $verid = trim(I('post.verid', 0, 'int'));
            $channel_id = trim(I('post.channel', 0, 'int'));
            $url = trim(I('post.url', '', 'strip_tags'));
            $md5 = trim(I('post.md5', '', 'strip_tags'));

            if(!$channel_id) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个渠道'));

            $androidVersionObj = M('AndroidVersion');
            $androidVersionDetailObj = M('AndroidVersionDetail');
            if(!$androidVersionObj->where(array('id'=>$verid))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'版本信息不存在或已被删除'));
            if($androidVersionDetailObj->where(array('ver_id'=>$verid,'channel_id'=>$channel_id))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该渠道已存在'));

            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'ver_id' => $verid,
                'url' => $url,
                'channel_id' => $channel_id,
                'md5' => $md5,
                'add_time' => $time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            $rid = $androidVersionDetailObj->add($rows);
            if(!$rid) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/version/upgrade_detail_add/id/'.$verid.'/p/'.$page));
        }
    }

    /**
     * 编辑版本渠道
     */
    public function upgrade_detail_edit(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $verid = I('get.verid', 0, 'int');
            $id = I('get.id', 0, 'int');

            $params = array(
                'page' => $page,
                'verid' => $verid,
            );
            $this->assign('params', $params);

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            $this->assign('channel_list', $channelList);

            $androidVersionObj = M('AndroidVersion');
            $androidVersionDetailObj = M('AndroidVersionDetail');
            $versionDetail = $androidVersionObj->where(array('id'=>$verid))->find();
            if(!$versionDetail){
                $this->error('版本信息不存在或已被删除');exit;
            }
            $detail = $androidVersionDetailObj->where(array('id'=>$id))->find();
            if(!$detail) {
                $this->error('版本渠道信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->assign('version_detail', $versionDetail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $verid = I('post.verid', 0, 'int');
            $id = I('post.id', 0, 'int');
            $channel_id = trim(I('post.channel', 0, 'int'));
            $url = trim(I('post.url', '', 'strip_tags'));
            $md5 = trim(I('post.md5', '', 'strip_tags'));

            if(!$channel_id) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个渠道'));

            $androidVersionObj = M('AndroidVersion');
            $androidVersionDetailObj = M('AndroidVersionDetail');
            if(!$androidVersionObj->where(array('id'=>$verid))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'版本信息不存在或已被删除'));
            if($androidVersionDetailObj->where("ver_id=".$verid." and channel_id=".$channel_id." and id<>".$id)->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该渠道已存在'));

            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'ver_id' => $verid,
                'url' => $url,
                'channel_id' => $channel_id,
                'md5' => $md5,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!$androidVersionDetailObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/version/upgrade_detail/id/'.$verid.'/p/'.$page));
        }
    }

    /**
     * 删除版本渠道
     */
    public function upgrade_detail_delete(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $androidVersionDetailObj = M('AndroidVersionDetail');
        $detail = $androidVersionDetailObj->where(array('id'=>$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'版本渠道信息不存在或已被删除'));
        if(!$androidVersionDetailObj->where(array('id'=>$id))->delete()) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }
    
    
    public function stat(){
        //android
        $sql = "SELECT  IFNULL(`app_version`,'未知' ) as app_version ,COUNT(*)as cnt  from `s_user` WHERE `device_type` =2 GROUP BY  app_version ";
        $android_list = M()->query($sql);
        //iso
        $sql = "SELECT  IFNULL(`app_version`,'未知' ) as app_version ,COUNT(*)as cnt  from `s_user` WHERE `device_type` =1 GROUP BY  app_version ";
        $ios_list= M()->query($sql);
        
        $this->assign('android_list', $android_list);
        $this->assign('ios_list', $ios_list);
        $this->display();
    }
    
    /**
     * ios 上线版本配置
     */
    public function ios_upgrade(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数
            $iosVerifyConfigObj = M('iosVerifyConfig');
            $counts = $iosVerifyConfigObj->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $iosVerifyConfigObj->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
    

    public function ios_upgrade_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $ios_channel = trim(I('post.ios_channel', '', 'strip_tags'));
            $config_value = trim(I('post.config_value', '0', 'int'));
            $version_name = trim(I('post.version_name', '', 'strip_tags'));
            $app_key = trim(I('post.app_key', '', 'strip_tags'));
            $master_secret = trim(I('post.master_secret', '', 'strip_tags'));   
    
            if(!$ios_channel) $this->ajaxReturn(array('status'=>0,'info'=>'渠道不能为空'));
            if(!$version_name) $this->ajaxReturn(array('status'=>0,'info'=>'版本不能为空'));            
            if(!$app_key) $this->ajaxReturn(array('status'=>0,'info'=>'app_key不能为空'));
            if(!$master_secret) $this->ajaxReturn(array('status'=>0,'info'=>'master_secret不能为空'));  
    
            $dd = array(
                'ios_channel'=>$ios_channel,
                'config_value'=>$config_value,
                'version_name'=>$version_name,
                'app_key'=>$app_key,
                'master_secret'=>$master_secret,
                'create_time'=>time()
            );
            $rid = M('iosVerifyConfig')->add($dd);
            if($rid) {   
                $data = [];
                updatePushMsg($data,'/flush');    
                $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/version/ios_upgrade/p/'.$page));
            } else {
                $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            }
        }
    }
    
    /**
     * Android版本编辑
     */
    public function ios_upgrade_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
    
            $params = array(
                'page' => $page,
            );
    
            $detail = M('iosVerifyConfig')->where(array('id'=>$id))->find();
    
            if(!$detail){
                $this->error('版本信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $id = I('post.id', 0, 'int');
    
            $ios_channel = trim(I('post.ios_channel', '', 'strip_tags'));
            $config_value = trim(I('post.config_value', '0', 'int'));
            $version_name = trim(I('post.version_name', '', 'strip_tags'));
            $app_key = trim(I('post.app_key', '', 'strip_tags'));
            $master_secret = trim(I('post.master_secret', '', 'strip_tags'));
            
            if(!$ios_channel) $this->ajaxReturn(array('status'=>0,'info'=>'渠道不能为空'));
            if(!$version_name) $this->ajaxReturn(array('status'=>0,'info'=>'版本不能为空'));
            if(!$app_key) $this->ajaxReturn(array('status'=>0,'info'=>'app_key不能为空'));
            if(!$master_secret) $this->ajaxReturn(array('status'=>0,'info'=>'master_secret不能为空'));
            
            $dd = array(
                'ios_channel'=>$ios_channel,
                'config_value'=>$config_value,
                'version_name'=>$version_name,
                'app_key'=>$app_key,
                'master_secret'=>$master_secret,
                'create_time'=>time()
            );
    
           
            if(!M('iosVerifyConfig')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $data = [];
            updatePushMsg($data,'/flush');  
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/version/ios_upgrade/p/'.$page));
        }
    }
    
    /**
     * Android版本删除
     */
    public function ios_upgrade_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if($id) {
            if(M("iosVerifyConfig")->where(array('id'=>$id))->delete()) {
                $this->ajaxReturn(array('status'=>1));
            } else {
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
            }
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
    }
}