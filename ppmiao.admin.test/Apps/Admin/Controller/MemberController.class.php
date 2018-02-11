<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:35
 */
namespace Admin\Controller;

/**
 * 会员中心体系
 */
class MemberController extends AdminController {
    private $pageSize = 20;

    private $months = [1,2,3,4,5,6,7,8,9,10,11,12];




    /**
     * Banner管理
     */
    public function banners(){
        list($banners,$show,$params) = D('MemberBanners')->getResult();
        $this->assign('banners', $banners);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
//        var_dump($thrones);
    }
    public function banner_store(){
        $create = D('MemberBanners')->store();
        $this->ajaxReturn($create);
    }
    public function banner_edit(){
        $id = I('get.id', 0, 'int');
        $detail = D('MemberBanners')->getOne($id);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function banner_update(){
        $update = D('MemberBanners')->update();
        $this->ajaxReturn($update);
    }
    public function banner_move(){
        $id = I('post.id');
        $direction = I('post.direction');
        $update = D('MemberBanners')->move($direction,$id);
        $this->ajaxReturn($update);
    }


    /**
     * 生日礼包
     */
    public function birthday(){
        $title = I('get.title','');
        
        list($commodities,$show,$params)  = D('MemberBirthday')->getResult($title);
        $this->assign('commodities', $commodities);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }

    /**
     * 新增生日礼包
     * @return [type] [description]
     */
    public function birthday_add(){

        $years = [date('Y'),date('Y')+1];
        $months = $this->months;
        $commodities = D('MemberCommodities')->search();

        // 获取vip配置项
        $vip_config = D('VipConfig')->getResult();
        $this->assign('vip_config', $vip_config);

        $this->assign('commodities', $commodities);
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->display();
    }

    public function birthday_store(){
        $create = D('MemberBirthday')->store();
        $this->ajaxReturn($create);
    }

    public function birthday_edit(){
        $id = I('get.id', 0, 'int');
        $years = [date('Y'),date('Y')+1];
        $months = $this->months;
        $detail = D('MemberBirthday')->getOne($id);
        $commodities = D('MemberCommodities')->search();

        // 获取vip配置项
        $vip_config = D('VipConfig')->getResult();
        $this->assign('vip_config', $vip_config);

        $this->assign('commodities', $commodities);
        $this->assign('detail', $detail);
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->display();
    }
    public function birthday_update(){
        $update = D('MemberBirthday')->update();
        $this->ajaxReturn($update);
    }



    /**
     * 会员体系中商品管理
     */
    public function commodities(){
        $title = I('get.title','');
        list($commodities,$show,$params)  = D('MemberCommodities')->getResult($title);
        $this->assign('commodities', $commodities);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->assign('title', $title);
        $this->display();
    }
    public function commodities_store(){
        $create = D('MemberCommodities')->store();
        $this->ajaxReturn($create);
    }

    public function commodities_select(){
        $type = I("get.type");
        $commodities = D('MemberCommodities')->search($type);
        $this->assign('commodities', $commodities);
        $this->display();
    }

    public function commodities_find(){
        $id = I("get.id");
        $commodity = D('MemberCommodities')->find($id);
        $commodity['project_type'] = D('MemberCommodities')->type_to_name($commodity['project_type']);
        $this->assign('commodity', $commodity);
        $this->display();
    }

    public function commodities_edit(){
        $id = I('get.id', 0, 'int');
        $detail = D('MemberCommodities')->getOne($id);
        $this->assign('detail', $detail);
        $this->display();

    }
    public function commodities_update(){
        $update = D('MemberCommodities')->update();
        $this->ajaxReturn($update);
    }


    public function commodities_use_show(){
        $title = urldecode(I("get.title")) ;
        $model = I("get.model");
        $source = I("get.source");
        list($records,$show,$params)  = D('MemberCommodities')->getUse($source,$model);
        $this->assign('title', $title);
        $this->assign('records', $records);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }



    //每月福利
    public function monthly(){
        $vip_id = I('get.vip_id',0);
        list($commodities,$show,$params)  = D('MemberMonthly')->getResult($vip_id);
        $this->assign('commodities', $commodities);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }
    public function monthly_add(){
        $years = [date('Y'),date('Y')+1];
        $months = $this->months;
        $commodities = D('MemberCommodities')->search();
        $this->assign('commodities', $commodities);
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->display();
    }
    public function monthly_edit(){
        $id = I('get.id', 0, 'int');
        $detail = D('MemberMonthly')->getOne($id);
        $years = [date('Y'),date('Y')+1];
        $months = $this->months;
        $commodities = D('MemberCommodities')->search();
        $this->assign('id', $id);
        $this->assign('commodities', $commodities);
        $this->assign('detail', $detail);
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->display();
    }
    public function monthly_store(){
        $create = D('MemberMonthly')->store();
        $this->ajaxReturn($create);
    }
    public function monthly_update(){
        $update = D('MemberMonthly')->update();
        $this->ajaxReturn($update);
    }

    /**
     * VIP配置与积分
     */
    public function index(){
        $vip = D('VipConfig')->getResult();
        $this->assign('vip', $vip);
        $this->display();
    }

    public function points(){
        $vip = D('VipPoint')->getResult();
        $this->assign('vip', $vip);
        $this->display();
    }
    public function points_update(){
        $update = D("VipPoint")->updateMultiple();
    }


    /**
     * 票票商城商品管理
     */
    public function store(){
        $type = I('get.type',0,'int');
        $name = I('get.name',0);
        list($commodities,$show,$params)  = D("MemberStore")->getResult($type,$name);
        if($name === 0){
            $name = '';
        }
        $params['name'] = $name;
        $this->assign('commodities', $commodities);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }
    public function store_add(){
        $commodities = D('MemberCommodities')->search();
        $this->assign('commodities', $commodities);
        $this->display();
    }

    public function store_store(){
        $create = D('MemberStore')->store();
        $this->ajaxReturn($create);
    }

    public function store_edit(){

        $id = I('get.id', 0, 'int');
        $detail = D('MemberStore')->getOne($id);
        $commodities = D('MemberCommodities')->search();
        $this->assign('commodities', $commodities);
        $this->assign('detail', $detail);
        $this->display();
    }

    public function store_update(){

        $update = D('MemberStore')->update();
        $this->ajaxReturn($update);
    }

    public function store_move(){
        $id = I('post.id');
        $direction = I('post.direction');
        $update = D('MemberStore')->move($direction,$id);
        $this->ajaxReturn($update);
    }


    //任务相关

    public function tasks(){
        $category = I('post.category',0);
        list($tasks,$show,$params) = D('MemberTasks')->getResult($category);
        $this->assign('tasks', $tasks);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }

    public function task_edit(){

        $id = I('get.id', 0, 'int');
        $task = D('MemberTasks')->getOne($id);
        $sub_category = [
            'REGISTER' =>'注册',
            'RECHARGE' =>'充值',
            'BUY' =>'投资',
            'INVITE' =>'邀请',
            'INVITE_TOTAL' =>'邀请总数',
            'WECHAT' =>'微信',
            'SIGN' =>'签到',
        ];
        $redirect = [
            0=>'无', 1=>'登陆', 2=>'钱包详情页',  4=>'产品列表', 5=>'邀请活动页', 6=>'签到页'
        ];

        $ext = (array)json_decode($task['ext']);
        $this->assign('sub_category', $sub_category);
        $this->assign('redirect', $redirect);
        $this->assign('ext', $ext);
        $this->assign('task', $task);
        $this->display();
    }
    public function task_add(){
        $sub_category = [
            'REGISTER' =>'注册',
            'RECHARGE' =>'充值',
            'BUY' =>'投资',
            'INVITE' =>'邀请',
            'INVITE_TOTAL' =>'邀请总数',
            'WECHAT' =>'微信',
            'SIGN' =>'签到',
        ];
        $redirect = [
            0=>'无', 1=>'登陆', 2=>'钱包详情页',  4=>'产品列表', 5=>'邀请活动页', 6=>'签到页'
        ];

        $this->assign('sub_category', $sub_category);
        $this->assign('redirect', $redirect);
        $this->display();
    }
    public function task_store(){
        $create = D('MemberTasks')->store();
        $this->ajaxReturn($create);
    }

    public function task_update(){
        $update = D('MemberTasks')->update();
        $this->ajaxReturn($update);
    }







    /**
     * 特权管理
     */
    public function thrones(){
        $thrones = D('MemberThrones')->getResult();
//        var_dump($thrones);
        $this->assign('thrones', $thrones);
        $this->display();
    }
    public function throne_add(){
        $vips = D('VipConfig')->getResult();
        $this->assign('vips', $vips);
        $this->display();
    }
    public function throne_edit(){

        $id = I('get.id', 0, 'int');
        $vips = D('VipConfig')->getResult();
        $detail = D('MemberThrones')->getOne($id);
        $this->assign('vips', $vips);
        $this->assign('detail', $detail);
        $this->display();
    }

    public function throne_store(){
        $create = D('MemberThrones')->store();
        $this->ajaxReturn($create);
    }
    public function throne_update(){
        $update = D('MemberThrones')->update();
        $this->ajaxReturn($update);
    }

    public function thrones_move(){
        $id = I('post.id');
        $direction = I('post.direction');
        $update = D('MemberThrones')->move($direction,$id);
        $this->ajaxReturn($update);
    }




    /**
     * 删除模型
     */

    public function delete(){

        if(IS_AJAX) {

            $id = I('post.id', 0, 'int');

            $model = I('post.model');

            if(!$id) {
                $this->ajaxReturn(array('status' => 1, 'info' => "参数错误"));
            }
            if($model == 'MemberCommodities'){
                if(!D($model)->deleteAble($id)){
                    $this->ajaxReturn(array('status' => 1, 'info' => "商品已经被使用，请在「每月福利」，「生日礼包」，「票票商城」中删除后再操作"));
                }
            }

            $status = D($model)->where(array('id ='.$id))->getField('status');

            if($status == 1) {
                $this->ajaxReturn(array('status' => 1, 'info' => "请先下架"));
            }

            $dd['is_delete'] = 1;

            if(! D($model)->where("id = $id")->save($dd)) {
                $this->ajaxReturn(array('status' => 1, 'info' => "删除失败"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "删除成功"));
        } else {
            $this->ajaxReturn(array('status' => 1, 'info' => "非法访问"));
        }
    }

    /**
     * 更新模型
     */
    public function update(){

        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $model = I('post.model');





        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        $Obj = D($model);

        // if($model == 'MemberBirthday'){
        //     if(!$Obj->checkChangeStatus($id)) $this->ajaxReturn(array('status'=>0,'info'=>'无法同时上架两种生日礼包'));
        // }


        $detail = $Obj->where(array('id ='.$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'任务信息不存在或已被删除'));
        if($detail['status'] == 1){
            $status = 0;
        }else{
            $status = 1;
        }
        if(!$Obj->where('id ='.$id)->save(array('status'=>$status))) $this->ajaxReturn(array('status'=>0,'info'=>'更新状态失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 获取redis实例
     * @return Redis
     */
    private function newRedis()
    {
        $redis = new \Redis();
        $redis->connect(C("REDIS_HOST"), C("REDIS_PORT"));
        $redis->auth(C("REDIS_AUTH"));
        return $redis;
    }

    /**
     * 获取某个等级的用户id
     * @param  [type] $level_id [description]
     * @return [type]           [description]
     */
    private function getUserIds($level_id) {
        // $result = $this->newRedis()->get('20171226_vip_user_ids');

        $result = S('20171226_vip_users_id_'.$level_id);
        if (!$result) {
            // 数据为空
            $user_ids = D('UserVipLevel')->getVipUserIds($level_id);
            if (empty($user_ids)) {
                return null;
            }

            $users = array();

            foreach ($user_ids as $value) {
                $users[] = $value['uid'];
                unset($value['uid']);
                unset($value);
            }
            unset($user_ids);
            $user_ids = $users;
            unset($users);

            $where['id'] = array('in',$user_ids);
            unset($user_ids);
            $user_ids = M('User')->field('id')->where($where)->order('id asc')->select();

            S('20171226_vip_users_id_'.$level_id, $user_ids, 1800);
            return $user_ids;
        }
        return $result;
    }

    /**
     * 查看对应等级的用户列表
     * @return [type] [description]
     */
    public function user_index() {
        // ini_set("memory_limit", "1000M");
        $level_id= trim(I('get.level_id',0,'int'));
        $page = I('get.p', 1, 'int'); // 页码

        // 获取该等级的所有用户id
        $user_ids = $this->getUserIds($level_id);

        $Page = new \Think\Page(count($user_ids), $this->pageSize);
        $show = $Page->show();

        for ($i = $page * 20 - 20; $i < $page * 20; $i++) { 

            if ($i >= count($user_ids)) {
                break;
            }

            $user_msg = M('user')->alias('t0')
                            ->join('LEFT JOIN s_user_due_detail t1 ON t0.id = t1.user_id')
                            ->group('t0.id')
                            ->where("t0.id = ".$user_ids[$i]['id'])
                            ->field('t0.real_name, t0.mobile, t0.username, t0.card_no, sum(t1.due_capital) as due_capital')
                            ->order('t0.id asc')
                            ->select();

            if (empty($user_msg)) {
                continue;
            }

            $users[] = array(
                'id' => $user_ids[$i]['id'],
                'real_name' => $user_msg[0]['real_name'],
                'mobile' => empty($user_msg[0]['mobile']) ? $user_msg[0]['username'] : $user_msg[0]['mobile'],
                'sex' => get_sex($user_msg[0]['card_no']),
                'age' => get_age($user_msg[0]['card_no']),
                'amount' => empty($user_msg[0]['due_capital']) ? '0.00' : $user_msg[0]['due_capital']
                );
        }

        $vip_name = D('VipConfig')->getVipName($level_id);
        $params = array(
            'page' => $page,
            'level_name' => $vip_name['name'],
            'level_id' => $level_id
        );

        $this->assign('users', $users);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }

    /**
     * 导出某个level的vip用户
     * @return [type] [description]
     */
    public function export_vip_users() {
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);

        $level_id= trim(I('get.level_id',0,'int'));

        // 获取该等级的所有用户id
        $user_ids = $this->getUserIds($level_id);
        if (empty($user_ids)) {
            exit('没有数据可以导出。');
        }
        $user_ids2 = array();
        // 处理数据为数组
        foreach ($user_ids as $value) {
            $user_ids2[] = $value['id'];
            unset($value['id']);
            unset($value);
        }
        unset($user_ids);
        $where['t0.id'] = array('in',$user_ids2);

        $users = M('user')->alias('t0')
                    ->join('LEFT JOIN s_user_due_detail t1 ON t0.id = t1.user_id')
                    ->group('t0.id')
                    ->where($where)
                    ->field('t0.real_name, t0.mobile, t0.username, t0.card_no, sum(t1.due_capital) as amount')
                    ->order('t0.id asc')
                    ->select();

        $vip_name = D('VipConfig')->getVipName($level_id);
        $level_name = $vip_name['name'];


        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)->setTitle($level_name)->setCellValue("A1", "序号")->setCellValue("B1", "姓名")->setCellValue("C1", "手机号码")->setCellValue("D1", "等级")->setCellValue("E1", "性别")->setCellValue("F1", "年龄")->setCellValue("G1", "累计投资金额");
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);


        // 设置列表值
        $pos = 2;
        $count_id = 1;
        foreach ($users as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $count_id++);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $value['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $value['mobile']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $level_name);
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, get_sex($value['card_no']));
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, get_age($value['card_no']));
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $value['amount']);
            $pos ++;
        }

        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$level_name.'用户列表('.date('Y-m-d').').csv"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        
        exit;
    }

}
