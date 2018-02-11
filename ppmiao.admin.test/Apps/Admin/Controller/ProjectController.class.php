<?php
namespace Admin\Controller;

/**
 * 债权管理
 */
class ProjectController extends AdminController {
    
    protected $pageSize = 15;

    protected $_status = array( // 项目状态
        1 => '平台待审核',
        2 => '销售中',
        3 => '已售罄',
        4 => '还款中',
        5 => '已还款',
        6 => '未提交银行审核',
        7 => '银行审核失败',
        8 => '已废标',
    );
    protected $_statusColor = array( // 项目状态(带颜色)
        1 => '<span style="color:red;">平台待审核</span>',
        2 => '<span style="color:green;">销(待)售中</span>',
        3 => '<span style="color:#6A6AFF;">已售罄</span>',
        4 => '<span style="color:orange;">还款中</span>',
        5 => '<span style="color:gray">已还款</span>',
        6 => '<span style="color:red">未提交银行审核</span>',
        7 => '<span style="color:gray">银行审核失败</span>',
        8 => '<span style="color:gray">已废标</span>',
    );

    protected $refund_notify_url = 'account/balace' ;// 上线地址 http://ip:port/api/account/balace 测试地址 http://114.55.85.42:8082/api/account/balace

    /**
     * 债权列表
     */
    public function index() {

        if (!IS_POST) {
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数
            $search = urldecode(I('get.s', '', 'strip_tags')); // 搜索关键字
            $status = I('get.status', 0, 'int'); // 产品状态
            $tag_id = I('get.tag_id', -1, 'int');
            $draft_type_id = I('get.draft_type_id', -1, 'int'); // 汇票类型
            $p_group = I('get.p_group', -1, 'int');
            
            $projectObj = M('Project');
            $userDueDetailObj = M('UserDueDetail');
            $financingObj = M('financing');


            $conditions = array();
            $cond[] = "is_delete=0";
            if ($search) $cond[] = "title like '%" . $search . "%'";
            if ($status) $cond[] = "status=" . $status;
            
            if($tag_id >=0 ) $cond[] = "new_preferential=" . $tag_id;
       //     if($tag_id >=0 ) $cond[] = "tag_id =" . $tag_id;
            if($p_group >=0 ) $cond[] = "project_group_id=" . $p_group;
            if($draft_type_id>=0) $cond[] = "draft_type=" . $draft_type_id;
            
            if ($cond) $conditions = implode(' and ', $cond);
            $counts = $projectObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            if ($search) $Page->parameter .= "&s=" . $search;
            $show = $Page->show();
            $orderby = 'start_time desc ,status asc';//'status asc,id desc';//id desc,status,start_time desc';
            //if($_SESSION[ADMIN_SESSION]['uid'] == 5){ // 财务特殊排序需求
            //    $orderby = 'status asc';
            //}
            $list = $projectObj->where($conditions)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach ($list as $key => $val) {
                
                //普通 新手
                if($val['status'] == 2){
                    $list[$key]['bgcolor'] = 2;
                    if($val['new_preferential'] <=1 && $val['able']<=100000){
                        $list[$key]['bgcolor'] = 1;
                    } else if(in_array($val['new_preferential'], array(2,6,9)) && $val['able']<=150000){
                        $list[$key]['bgcolor'] = 1;
                    }
                }
                
                $list[$key]['status_str'] = $this->_statusColor[$val['status']];
                // 查看是否有超出部分
                if($val['status'] > 2) $list[$key]['overflow'] = $userDueDetailObj->where(array('project_id'=>$val['id']))->sum('due_capital') - $val['amount'];
                else $list[$key]['overflow'] = 0;
                
                //融资方
                $list[$key]['financing'] = $financingObj->where('id='.$val['fid'])->getField('name');
                
                //分组标签名称
                $list[$key]['group_tag_name'] = M('projectGroup')->where('id ='.$val['project_group_id'])->getField('name');
                $list[$key]['days'] = count_days($val['start_time'], $val['end_time']);
            }

            $params = array(
                'page' => $page,
                'search' => $search,
                'status' => $status,
                'tag_id' =>$tag_id,
                'draft_typ_id'=>$draft_type_id,
                'p_group' => $p_group 
            );

            $tags = array(
                'id'=>0
            );

            $notice_tags = $this->getSpecialTags();

//            dump($notice_tags);exit;
//            foreach($notice_tags as $t){
//                $tag_str .= '<case value="' . $t['id'] . '">' . $t['tag_title'] . '</case>';
//            }

            $this->assign('notice_tags', $notice_tags);//产品公告标签

            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        } else {
            $key = I('post.key', '', 'strip_tags');            
            $status = I('post.status', 0, 'int');
            $tag_id = I('post.tag_id', -1, 'int');
            $p_group = I('post.project_group', -1, 'int');
            $draft_type_id = I('post.draft_type_id', -1, 'int'); // 汇票类型
            $quest = '/status/'.$status.'/tag_id/'.$tag_id.'/p_group/'.$p_group;
            if($draft_type_id>=0) $quest .= '/draft_type_id/'.$draft_type_id;
            if($key) $quest .= '/s/'.urlencode($key);
            redirect(C('ADMIN_ROOT') . '/project/index'.$quest);
        }
    }

    /**
     * 添加债权
     */
    public function add() {
        if (!IS_POST) {
            $financingArr =  M('Financing')->field('id,name,type')->where('is_show = 1')->select();
            $financing_list = $guaranty_list = array();
            foreach ($financingArr as $f){
                if($f['type'] == 1){
                    $financing_list[] = $f;
                }else{
                    $guaranty_list[] = $f;
                }
            }
            //融资方
            $this->assign("financing_list",$financing_list);
            //担保方
            $this->assign("guaranty_list",$guaranty_list);

            //$this->assign("financing_list",M('Financing')->field('id,name')->select());
            //月度加息配置
            $mConfigList = M('monthlyIncreaseInterestConfig')->field('group_id')->where('status=1')->group('group_id')->select(); 
            foreach ($mConfigList as $key=>$val){
                $mConfigList[$key]['name'] = '月月加薪'.$val['group_id'].'.0';
            }
            $this->assign("mConfigList",$mConfigList);
            $this->assign('users', GhostUser());

            $this->assign('special_tags', $this->getSpecialTags());//特殊标签
            $this->assign('notice_tags', $this->getTags());//产品公告标签
            $vip = D('VipConfig')->getResult();
            $this->assign('vip', $vip);
            $this->display();
        } else {
            if (!IS_AJAX) exit;

            $weight = 0;

            $days = I('post.days', 0, 'int'); // 借款天数
            $stage = I('post.stage', 1, 'int'); // 项目期数
            $title = I('post.title', '', 'strip_tags'); // 项目标题
            $basecount = 0;// I('post.basecount', 0, 'int'); // 购买基数
            $term_type = 1;//I('post.term_type', 1, 'int'); // 期限类型
            $new_preferential = I('post.new_preferential', 0, 'int'); // 是否特殊类型(1:新人特惠/2:爆款)

            $money_min = I('post.money_min', 0, 'int'); // 起购金额
            $money_max = I('post.money_max', 0, 'int'); // 封顶金额
            $money_type = I('post.money_type', 0, 'int'); // 购买方式
            $start_time = I('post.start_time', '', 'strip_tags'); // 项目开始时间
            $end_time = I('post.end_time', '', 'strip_tags'); // 项目结束时间
            $count_interest_type = I('post.count_interest_type', 2, 'int'); // 计息方式(默认T+1)
            $repayment_type = I('post.repayment_type', 1, 'int'); // 还款方式(默认一次性还本付息)
            $amount = I('post.amount', 0, 'int'); // 借款金额
            $contract_interest = I('post.contract_interest', 0, 'float'); // 合同上的利息(%)
            $user_interest = I('post.user_interest', 0, 'float'); // 给用户的利息(%)
            $contract_counter_fee = I('post.contract_counter_fee', 0, 'float'); // 合同的手续费(%)
            $user_platform_subsidy = I('post.user_platform_subsidy', 0, 'float'); // 平台补贴的利息(%)
            $invest_direction_title = I('post.invest_direction_title', '', 'strip_tags'); // 投资方向标题
            $invest_direction = $_POST['invest_direction']; // 投资方向
            $invest_direction_descr = $_POST['invest_direction_descr']; // (新)投资方向
            $invest_direction_image = $_POST['invest_direction_image']; // (新)投资方向
            $repayment_source_title = I('post.repayment_source_title', '', 'strip_tags'); // 还款来源标题
            $repayment_source = $_POST['repayment_source']; // 还款来源
            $repayment_source_descr = $_POST['repayment_source_descr']; // (新)还款来源
            //$repayment_source_image = $_POST['repayment_source_image']; // (新)还款来源
            
            //$establish_type = I('post.establish_type', 1, 'int'); //成标类型
            $vip_id = I('post.vip_id', 0, 'int'); // 会员等级id
            // 可使用券包
            $use_redenvelope= I('post.use_redenvelope', 0, 'int'); // 使用红包
            $use_interest_coupon = I('post.use_interest_coupon', 0, 'int'); //使用加息券
            

            $fid = trim(I('post.financing', 0, 'int')); // 融资方名称
            $gid  = trim(I('post.gid', 0, 'int')); // 担保方名称
            
            $contractNo = I('post.contract_no', '', 'strip_tags'); // 合同编号
            
            $accepting_bank = I('post.accepting_bank', '', 'strip_tags'); // 承兑银行
            $ticket_checking = I('post.ticket_checking', '', 'strip_tags'); // 验票托管
            $buy_times = I('post.buy_times', 0, 'int'); // 可购买次数(0为不限制)
            $is_countdown = I('post.countdown', 0, 'int'); // 是否开启倒计时
            $countdown_show_min = I('post.countdown_show_min', '', 'int'); // 开始时间之前几分钟在app中显示
            
            $project_group_id = I('post.project_group_id', 0, 'int'); //产品分组ID
            
            //20170331
            $custom_label = I('post.custom_label', '', 'strip_tags'); //自定义标签            
            $custom_weight = I('post.custom_weight', 0, 'int'); //自定义权重值            
            $duration_type = I('post.duration_type', 0, 'int'); //产品期限
            
            $draft_type = I('post.draft_type', 0, 'int'); //汇票类型
            
            $show_region = I('post.show_region', 0, 'int'); //产品显示分组
            
            
            $is_autobuy = I('post.autobuy', 0, 'int'); // 是否开启幽灵账号自动购买
            $auto_buy_min = I('post.auto_buy_min', '', 'strip_tags'); // 结束前几分钟开始用幽灵账户自动购买

            $guaranty_type = I('post.guaranty_type', 0, 'int'); // 担保类型
            $guaranty_institution = I('post.guaranty_institution', 0, 'strip_tags'); // 担保机构

            
            /*
             2016.12.29 计算开始用幽灵账号自动购买时间戳
             */
            if ($is_autobuy == 1) {
                $start_auto_buy_time = strtotime($auto_buy_min) ;
            } else {
                $start_auto_buy_time = 0;
            }
            
            $get_users = I('post.users');
            if($get_users){
                $auto_buy_users = implode(',',$get_users);
            }else{
                $auto_buy_users = '';
            }
            
            
                        
            if($custom_label) {
                $weight += 10;
            }
            
            //私人700 新人600 月月500 爆款400 活动300 普通100
            
            if($new_preferential == 1) {
                $weight += 600;                
                // 1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)                
                if($duration_type == 1) {
                    $weight += 5;
                } else if($duration_type == 2) {
                    $weight += 1;
                } else if($duration_type == 3) {
                    $weight += 2;
                } else if($duration_type == 4) {
                    $weight += 4;
                } else if($duration_type == 5) {
                    $weight += 3;
                }                
            } else {                
                if($new_preferential == 0){
                    $weight += 100;
                } else if($new_preferential == 2){
                    $weight += 400;
                } else if($new_preferential == 6){
                    $weight += 300;
                } else if($new_preferential == 8){
                    $weight += 700;
                } else if($new_preferential == 9){
                    $weight += 500;
                }
                
                //期限 值
                if($duration_type == 2) {
                    $weight += 4;
                } else if($duration_type == 3) {
                    $weight += 3;
                } else if($duration_type == 4) {
                    $weight += 2;
                } else if($duration_type == 5) {
                    $weight += 1;
                }
            }
            
            /*
                2016.12.28 计算倒计时标在app中显示的时间
            */
            if ($is_countdown == 1) {
                // echo $start_time;
                $countdown_show_time = strtotime($start_time)-($countdown_show_min*60);
                // echo $countdown_show_time;
                $countdown_show_time = date('Y-m-d:H:i:s',$countdown_show_time);
            } else {
                $countdown_show_time = date('Y-m-d:H:i:s');
            }
            /*
            $establish_time = 0;
            $establish_amt = 0;
            if($establish_type == 2){
                $establish_time = I('post.establish_time', '', 'strip_tags');
                if(!$establish_time){
                    $this->ajaxReturn(array('status' => 0, 'info' => '定时成标，请选择成标时间'));
                }
                $establish_time = strtotime($establish_time);
            } else if($establish_type == 3){
                $establish_amt = I('post.establish_amt', 0, 'int');
            }
            */

            // 校验字段数据
            if (!$stage) $this->ajaxReturn(array('status' => 0, 'info' => '项目期数必须大于0'));
            if (!$title) $this->ajaxReturn(array('status' => 0, 'info' => '项目标题不能为空'));
            if ($money_max) {
                if ($money_min > $money_max) $this->ajaxReturn(array('status' => 0, 'info' => '起购金额不能大于封顶金额'));
            }
            if ($start_time && $end_time) {
                if (strtotime($start_time) > strtotime($end_time)) $this->ajaxReturn(array('status' => 0, 'info' => '开始时间不能大于结束时间'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '请选择项目起止日期'));
            }

            if($days <=0) {
                $this->ajaxReturn(array('status' => 0, 'info' => '借款天数不对，请重新操作项目起止日期'));
            }

            if (!$count_interest_type) $this->ajaxReturn(array('status' => 0, 'info' => '请选择一个计息方式'));
            if (!$repayment_type) $this->ajaxReturn(array('status' => 0, 'info' => '请选择一个还款方式'));
                        

            $projectObj = M('Project');
            // 检查标题是否已存在
            if($projectObj->where(array('title' => $title, 'is_delete' => 0))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同标题的产品'));
            // 校验期数是否可以正确添加
            $maxStage = $projectObj->where(array('is_delete' => 0))->getField('stage'); // 获取一个类型下面当前最大期数(实际添加期数为当前最大期数+1)
            if ($maxStage) {
                if ($maxStage >= $stage) $this->ajaxReturn(array('status' => 0, 'info' => '期数不能小于或等于当前该类型下目前最大期数'));
            }
            
            $monthly_increase_group = 0;
            if($new_preferential == 9){
                $monthly_increase_group = I('post.monthly_increase_group', 0, 'int'); //月月加薪组
                if($monthly_increase_group<=0){
                    $this->ajaxReturn(array('status' => 0, 'info' => '月月加薪的标，请选择`梯度加息`'));
                }
            }

            $tag_name = $this->getTagName($new_preferential);

            $time = time();
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'stage' => $stage,
                'duration' => $days,
                'title' => $title,
                'buy_base' => $basecount,
                'term_type' => $term_type,
                'new_preferential' => $new_preferential,
                'tag_name' =>$tag_name,
                'money_min' => $money_min,
                'money_max' => $money_max,
                'money_type' => $money_type,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'count_interest_type' => $count_interest_type,
                'repayment_type' => $repayment_type,
                'amount' => $amount, // 总金额
                'freeze' => 0, // 冻结资金
                'able' => $amount, // 可投金额
                'contract_interest' => $contract_interest,
                'user_interest' => $user_interest,
                'contract_counter_fee' => $contract_counter_fee,
                'user_platform_subsidy' => $user_platform_subsidy,
                'invest_direction_title' => $invest_direction_title,
                'invest_direction' => $invest_direction,
                'invest_direction_descr' => $invest_direction_descr,
                'invest_direction_image' => $invest_direction_image,
                'repayment_source_title' => $repayment_source_title,
                'repayment_source' => $repayment_source,
                'repayment_source_descr' => $repayment_source_descr,
                //'repayment_source_image' => $repayment_source_image,
                'status' => 1, // 默认状态(等待上线)
                'fid' => $fid,
                'gid'=>$gid,
                'contract_no' => $contractNo,
                'buy_times' => $buy_times,
                'is_countdown' => $is_countdown,
                'countdown_show_time' => $countdown_show_time,                
                'add_time' => date('Y-m-d H:i:s', $time),
                'modify_time' => date('Y-m-d H:i:s', $time),
                'add_user_id' => $uid,
                'modify_user_id' => $uid,
                'accepting_bank'=>$accepting_bank,
                'ticket_checking'=>$ticket_checking,
                'project_group_id'=>$project_group_id,
                'custom_weight' => $custom_weight,
                'weight' => $weight,
                'duration_type' => $duration_type,
                'custom_label' => $custom_label,
                'show_region' => $show_region,
                'draft_type'  => $draft_type,
                //'establish_type' =>$establish_type,
                //'establish_time' =>$establish_time,
                //'establish_amt' =>$establish_amt
                'monthly_increase_group' => $monthly_increase_group,
                
                'is_autobuy' => $is_autobuy,
                'start_auto_buy_time' => $start_auto_buy_time,
                'auto_buy_users' => $auto_buy_users,

                'guaranty_type' =>$guaranty_type,
                'guaranty_institution'=>$guaranty_institution,

                'vip_id'=>$vip_id,
                'use_redenvelope'=>$use_redenvelope,
                'use_interest_coupon'=>$use_interest_coupon,
            );


            $is_notice_tag = I('post.is_notice_tag', 0, 'strip_tags'); // 与特殊标签一致
            if($is_notice_tag){
                $rows['tag_id'] = $new_preferential;
                // code
            }else{
                $tag_id = I('post.tag_id',0, 'strip_tags'); // 产品公告标签
                if(!$tag_id){
                    $this->ajaxReturn(array('status' => 0, 'info' => '请选择产品公告标签'));
                }
                $rows['tag_id'] = $tag_id;
            }

            $rid = $projectObj->add($rows);

            /*
             * 添加和合同表相关信息
             */
            $this->addContractInfo($contractNo);

            if (!$rid) $this->ajaxReturn(array('status' => 0, 'info' => '添加失败,请重试'));
            
            $this->ajaxReturn(array('status' => 1));
        }
    }

    
    /**
     * 编辑债权
     */
    public function edit() {
        if (!IS_POST) {
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $search = I('get.s', '', 'strip_tags');
            $type = I('get.type', 0, 'int');
            $status = I('get.status', 0, 'int');

            // 获取债权详细信息
            $project = M('Project');
            $detail = $project->where(array('id' => $id, 'is_delete' => 0))->find();

            if (!$detail) {
                $this->error('债权信息不存在或已被删除');
                exit;
            }
            
            //计算倒计时标App显示时间
            if ($detail['is_countdown'] == 0 || !$detail['countdown_show_time']) {
                $detail['countdown_show_min'] = 0;
            } else {
                $detail['countdown_show_min'] = strtotime($detail['start_time']) - strtotime($detail['countdown_show_time']);
                $detail['countdown_show_min'] = $detail['countdown_show_min']/60;
            }

            // 1229 计算幽灵账号开始自动购买时间
            if ($detail['is_autobuy'] == 0 || !$detail['start_auto_buy_time']) {
                $detail['start_auto_buy_time'] = 0;
            } else {
                $detail['start_auto_buy_time'] = date('Y-m-d H:i:s.000000',$detail['start_auto_buy_time']);
            }
            /*
            if($detail['establish_type'] == 2){
                $detail['establish_time'] = date("Y-m-d H:i:s",$detail['establish_time']);
            }
            */
            
            //产品分组
            $this->assign('project_group_list',M('ProjectGroup')->field('id,name')->select());

            $financingArr = M('Financing')->field('id,name,type')->select();

            $financing_list = $guaranty_list = array();
            foreach ($financingArr as $f){
                if($f['type'] == 1){
                    $financing_list[] = $f;
                }else{
                    $guaranty_list[] = $f;
                }
            }
            //融资方
            $this->assign("financing_list",$financing_list);
            //担保方
            $this->assign("guaranty_list",$guaranty_list);

            
            $mConfigList = M('monthlyIncreaseInterestConfig')->field('group_id')->where('status=1')->group('group_id')->select();
            foreach ($mConfigList as $key=>$val){
                $mConfigList[$key]['name'] = '月月加薪'.$val['group_id'].'.0';
            }
            $this->assign("mConfigList",$mConfigList);
            $this->assign('users', GhostUser());
            $this->assign('auto_buy_users', $detail['auto_buy_users']);
            

            $params = array(
                'page' => $page,
                'search' => $search,
                'type' => $type,
                'status' => $status,
            );
            $this->assign('params', $params);
            $this->assign('detail', $detail);
            $this->assign('special_tags', $this->getSpecialTags());//特殊标签
            $this->assign('notice_tags', $this->getTags());//产品公告标签
            $vip = D('VipConfig')->getResult();
            $this->assign('vip', $vip);
            $this->display();
        } else {
            if (!IS_AJAX) exit;

            $weight = 0;

            $constantObj = M('Constant');
            $projectObj = M('Project');

            $page = I('post.p', 1, 'int');
            $search = I('post.s', '', 'strip_tags');
            $_status = I('post._status', 0, 'int');

            $stage = I('post.stage', 1, 'int'); // 项目期数
            $days = I('post.days', 0, 'int'); // 借款天数
            $status = I('post.status', 0, 'int');
            $id = I('post.id', 0, 'int');
            $title = trim(I('post.title', '', 'strip_tags')); // 项目标题
            $basecount = 0;//I('post.basecount', 0, 'int'); // 购买基数
            $new_preferential = I('post.new_preferential', 0, 'int'); // 是否特殊类型(1:新人特惠/2:爆款)
            $money_min = I('post.money_min', 0, 'int'); // 起购金额
            $money_max = I('post.money_max', 0, 'int'); // 封顶金额
            $money_type = I('post.money_type', 0, 'int'); // 购买方式
            $start_time = I('post.start_time', '', 'strip_tags'); // 项目开始时间
            $end_time = I('post.end_time', '', 'strip_tags'); // 项目结束时间
            $count_interest_type = I('post.count_interest_type', 2, 'int'); // 计息方式(默认T+1)
            $repayment_type = I('post.repayment_type', 1, 'int'); // 还款方式(默认一次性还本付息)
            $amount = trim(I('post.amount', 0, 'int')); // 借款金额
            $contract_interest = I('post.contract_interest', 0, 'float'); // 合同上的利息(%)
            $user_interest = I('post.user_interest', 0, 'float'); // 给用户的利息(%)
            $user_platform_subsidy = I('post.user_platform_subsidy', 0, 'float'); // 平台补贴的利息(%)
            $invest_direction_title = trim(I('post.invest_direction_title', '', 'strip_tags')); // 投资方向标题
            $invest_direction = trim($_POST['invest_direction']); // 投资方向
            $invest_direction_descr = trim($_POST['invest_direction_descr']); // (新)投资方向
            $invest_direction_image = trim($_POST['invest_direction_image']); // (新)投资方向
            $repayment_source_title = trim(I('post.repayment_source_title', '', 'strip_tags')); // 还款来源标题
            $repayment_source = trim($_POST['repayment_source']); // 还款来源
            $repayment_source_descr = trim($_POST['repayment_source_descr']); // (新)还款来源
            //$repayment_source_image = trim($_POST['repayment_source_image']); // (新)还款来源
            
            $fid  = trim(I('post.financing', 0, 'int')); // 融资方名称
           // $gid  = trim(I('post.gid', 0, 'int')); // 担保方名称
            
            $contractNo = trim(I('post.contract_no', '', 'strip_tags')); //  合同编号
            $accepting_bank = trim(I('post.accepting_bank', '', 'strip_tags')); // 承兑银行
            $ticket_checking = trim(I('post.ticket_checking', '', 'strip_tags')); // 验票托管
            $buy_times = I('post.buy_times', 0, 'int'); // 可购买次数(0为不限制)
            
            $is_countdown = I('post.countdown', 0, 'int'); // 是否开启倒计时
            $countdown_show_min = I('post.countdown_show_min', '', 'strip_tags'); // 开始时间之前几分钟在app中显示
            
            
            $is_autobuy = I('post.autobuy', 0, 'int'); // 是否开启幽灵账号自动购买
            $auto_buy_min = I('post.auto_buy_min', '', 'strip_tags'); // 结束前几分钟开始用幽灵账户自动购买

            $guaranty_type = I('post.guaranty_type', 0, 'int'); // 担保类型
            $guaranty_institution = I('post.guaranty_institution', 0, 'strip_tags'); // 担保机构

            $vip_id = I('post.vip_id', 0, 'int'); // 会员等级id
            // 可使用券包
            $use_redenvelope= I('post.use_redenvelope', 0, 'int'); // 使用红包
            $use_interest_coupon = I('post.use_interest_coupon', 0, 'int'); //使用加息券


            
            /*
             2016.12.29 计算开始用幽灵账号自动购买时间戳
             */
            if ($is_autobuy == 1) {
                // echo $start_time;
                $start_auto_buy_time = strtotime($auto_buy_min) ;
                // echo $countdown_show_time;
            } else {
                $start_auto_buy_time = 0;
            }
            
            
            $get_users = I('post.users');
            if($get_users){
                $auto_buy_users = implode(',',$get_users);
            }else{
                $auto_buy_users = '';
            }
            
            
            //$establish_type = I('post.establish_type', 1, 'int'); //成标类型
            
            /*
                2016.12.28 计算倒计时标在app中显示的时间
            */
            if ($is_countdown == 1) {
                // echo $start_time;
                $countdown_show_time = strtotime($start_time)-($countdown_show_min*60);
                // echo $countdown_show_time;
                $countdown_show_time = date('Y-m-d:H:i:s',$countdown_show_time);
            } else {
                $countdown_show_time = date('Y-m-d:H:i:s');
            }

            
                
            //20170331
            
            $custom_label = I('post.custom_label', '', 'strip_tags'); //自定义标签
            $custom_weight = I('post.custom_weight', 0, 'int'); //自定义权重值
            $duration_type = I('post.duration_type', 0, 'int'); //产品期限
            
            //$weight += $custom_weight;
            
            $show_region = I('post.show_region', 0, 'int'); //产品显示分组
            
            if($new_preferential != 1){
                if($duration_type ==1){
                    $this->ajaxReturn(array('status'=>0,'info'=>'非新手标的产品周期必须要大于一周，请重新选择正确的周期'));
                }
            }
            //自定义标签 +10
            if(!empty($custom_label)) {
                $weight += 10;
            }
            //私人700 新人600 月月500 爆款400 活动300 普通100
            if($new_preferential == 1) {
                $weight += 600;
            
                // 1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)
            
                if($duration_type == 1) {
                    $weight += 5;
                } else if($duration_type == 2) {
                    $weight += 1;
                } else if($duration_type == 3) {
                    $weight += 2;
                } else if($duration_type == 4) {
                    $weight += 4;
                } else if($duration_type == 5) {
                    $weight += 3;
                }
            
            } else {
            
                if($new_preferential == 0){
                    $weight += 100;
                } else if($new_preferential == 2){
                    $weight += 400;
                } else if($new_preferential == 6){
                    $weight += 300;
                } else if($new_preferential == 8){
                    $weight += 700;
                } else if($new_preferential == 9){
                    $weight += 500;
                }
            
                //期限 值
                if($duration_type == 2) {
                    $weight += 4;
                } else if($duration_type == 3) {
                    $weight += 3;
                } else if($duration_type == 4) {
                    $weight += 2;
                } else if($duration_type == 5) {
                    $weight += 1;
                }
            }
            
            $project_group_id = I('post.project_group_id', 0, 'int'); //产品分组ID
            
            $draft_type = I('post.draft_type', 0, 'int'); //汇票类型
            
            $monthly_increase_group = 0;
            if($new_preferential == 9){
                $monthly_increase_group = I('post.monthly_increase_group', 0, 'int'); //月月加薪组
                if($monthly_increase_group <= 0){
                    $this->ajaxReturn(array('status' => 0, 'info' => '月月加薪的标，请选择`梯度加息`'));
                }
            }

            $detail = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'项目不存在或已被删除'));
            
            
            if(!$title) $this->ajaxReturn(array('status'=>0,'info'=>'项目标题不能为空'));
            
            //标状态 1：等待审核上线 2：销售中 3：已售完 4：还款中 5：已还款' ,6平台已审核，7银行存管审核失败
            
            $tag_name = $this->getTagName($new_preferential);
            
            $time = time();
            $uid = $_SESSION[ADMIN_SESSION]['uid'];


            $is_notice_tag = I('post.is_notice_tag', 0, 'strip_tags'); // 与特殊标签一致
            if($is_notice_tag){
                $tag_id = $new_preferential;
                // code
            }else{
                $tag_id = I('post.tag_id', 0, 'strip_tags'); // 产品公告标签
                if(!$tag_id){
                    $this->ajaxReturn(array('status' => 0, 'info' => '请选择产品公告标签'));
                }
               // $rows['tag_id'] = $tag_id;
            }

            /*
             * 添加和合同表相关信息
             */
            $this->addContractInfo($contractNo);
            
            //在售的标的可以修改数据
            if(!in_array($detail['status'], [1,6])){
                //标题、标签、产品分组、承兑机构(承兑银行)、担保机构、梯度加息、相关协议、预售倒计时、幽灵账户自动购买
                $data['title'] = $title;
                //$data['tag_name'] = $tag_name;
                $data['project_group_id'] = $project_group_id;
                $data['custom_label'] = $custom_label;
                $data['accepting_bank'] = $accepting_bank;
                $data['guaranty_institution'] = $guaranty_institution;
              //  $data['monthly_increase_group'] = $monthly_increase_group;
                $data['countdown'] = $is_countdown;
                $data['countdown_show_time'] = $countdown_show_time;
                $data['is_autobuy'] = $is_autobuy;
                $data['start_auto_buy_time'] = $start_auto_buy_time;
                $data['auto_buy_users'] = $auto_buy_users;
                $data['invest_direction_image'] = $invest_direction_image;
                $data['modify_time'] = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
                $data['modify_user_id'] = $uid;
                $data['repayment_source_descr'] = $repayment_source_descr;

                //$data['tag_id'] = $tag_id;   // 产品公告标签
                
                if (!$projectObj->where(array('id' => $id))->save($data)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '编辑失败,请重试'));
                }
                                
                $quest = '';
                if($search) $quest .= '/s/' . $search;
                $this->ajaxReturn(array('status' => 1, 'info' => C('ADMIN_ROOT') . '/project/index/p/' . $page . '/status/' . $_status . $quest));
            }            
            

            // 校验字段数据
            if($detail['status'] == 1 || $detail['status'] == 6 ){ // 项目在审核阶段可以修改(验证)的信息
                if(!$stage) $this->ajaxReturn(array('status'=>0,'info'=>'项目期数必须大于0'));
                
                if($money_max){
                    if($money_min > $money_max) $this->ajaxReturn(array('status'=>0,'info'=>'起购金额不能大于封顶金额'));
                }
                if($start_time && $end_time){
                    if(strtotime($start_time) > strtotime($end_time)) $this->ajaxReturn(array('status'=>0,'info'=>'开始时间不能大于结束时间'));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>'请选择项目起止日期'));
                }

                if($days <=0) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '借款天数不对，请重新操作项目起止日期'));
                }

                if(!$count_interest_type) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个计息方式'));
                if(!$repayment_type) $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个还款方式'));
                
                if($projectObj->where("id<>".$id." and title='".$title."' and is_delete=0")->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称产品'));
                // 校验期数是否可以正确添加
                $maxStage = $projectObj->where('is_delete=0 and id<>'.$id)->getField('stage'); // 获取一个类型下面当前最大期数(实际添加期数为当前最大期数+1)
                if($maxStage){
                    if($maxStage == $stage) $this->ajaxReturn(array('status'=>0,'info'=>'其数'.$stage.'已经存在,编辑失败'));
                }
            }

            
            $rows = array(
                'title' => $title,
                'buy_base' => $basecount,
                'money_max' => $money_max,
                'money_type' => $money_type,
                'fid' => $fid,
               // 'gid'=>$gid,
                'new_preferential' => $new_preferential,
				'tag_name' =>$tag_name,
                'contract_no' => $contractNo,
                'buy_times' => $buy_times,                
                'is_countdown' => $is_countdown,
                'countdown_show_time' => $countdown_show_time,
                'invest_direction_title' => $invest_direction_title,
                'invest_direction' => $invest_direction,
                'invest_direction_descr' => $invest_direction_descr,
                'invest_direction_image' => $invest_direction_image,
                'repayment_source_title' => $repayment_source_title,
                'repayment_source' => $repayment_source,
                'repayment_source_descr' => $repayment_source_descr,
                'modify_time' => date('Y-m-d H:i:s', $time).'.'.getMillisecond().'000',
                'modify_user_id' => $uid,
                'accepting_bank'=>$accepting_bank,
                'ticket_checking'=>$ticket_checking,
                'project_group_id'=>$project_group_id,                
                'custom_weight' => $custom_weight,
                'weight' => $weight,
                'duration_type' => $duration_type,
                'custom_label' => $custom_label,
                'show_region' => $show_region,
                'draft_type' => $draft_type,
                'is_autobuy' => $is_autobuy,
                'start_auto_buy_time' => $start_auto_buy_time,
                'auto_buy_users' => $auto_buy_users,
                'monthly_increase_group' => $monthly_increase_group,

                'guaranty_type'=>$guaranty_type,
                'guaranty_institution'=>$guaranty_institution,

                'vip_id'=>$vip_id,
                'use_redenvelope'=>$use_redenvelope,
                'use_interest_coupon'=>$use_interest_coupon,

                'tag_id' => $tag_id   // 产品公告标签
            );
            
            if(in_array($detail['status'], [1,6])){
                $rows['duration'] = $days;
                $rows['stage'] = $stage;
                $rows['money_min'] = $money_min;
                $rows['start_time'] = $start_time.'.000000';
                $rows['end_time'] = $end_time.'.000000';
                $rows['count_interest_type'] = $count_interest_type;
                $rows['repayment_type'] = $repayment_type;
                $rows['amount'] = $amount;
                $rows['able'] = $amount;
                $rows['contract_interest'] = $contract_interest;
                $rows['user_interest'] = $user_interest;
                $rows['user_platform_subsidy'] = $user_platform_subsidy;
            }


            if (!$projectObj->where(array('id' => $id))->save($rows)) $this->ajaxReturn(array('status' => 0, 'info' => '编辑失败,请重试'));
            
            $quest = '';
            if($search) $quest .= '/s/' . $search;
            $this->ajaxReturn(array('status' => 1, 'info' => C('ADMIN_ROOT') . '/project/index/p/' . $page . '/status/' . $_status . $quest));
        }
    }

    /**
     * 债权详细
     */
    public function detail(){
        $id = I('get.id', 0, 'int');
        $page = I('get.p', 1, 'int');
        $search = I('get.s', '', 'strip_tags');
        $type = I('get.type', 0, 'int');
        $status = I('get.status', 0, 'int');

        // 获取债权详细信息
        $project = M('Project');
        $detail = $project->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('债权信息不存在或已被删除');
            exit;
        }
        $params = array(
            'page' => $page,
            'search' => $search,
            'status' => $status,
        );
        
        $detail['financing'] = M('financing')->where('id='.$detail['fid'])->getField('name');
        
        if(0 == $detail['monthly_increase_group']){
            $detail['monthly_increase_group'] = '无';
        } else{
            $detail['monthly_increase_group'] = '月月加薪'.$detail['monthly_increase_group'].'.0';
        }
        $this->assign('params', $params);
        $this->assign('detail', $detail);
        $this->assign('special_tags', $this->getSpecialTags());//特殊标签
        $this->display();
    }

    /**
     * 删除债权
     */
    public function delete() {
        if (!IS_POST || !IS_AJAX) exit;

        $isMulti = false; // 是否批量删除
        $id = I('post.id');
        if (!is_numeric($id)) {
            $isMulti = true;
        }

        $projectObj = M('Project');
        if (!$isMulti) {
            $info = $projectObj->field('id,status')->where(array('id' => $id, 'is_delete' => 0))->find();
        } else {
            $info = $projectObj->field('id,status')->where('id in (' . $id . ') and is_delete=0')->select();
        }
        if (!$info) $this->ajaxReturn(array('status' => 0, 'info' => '项目信息不存在或已被删除'));

        if (!$isMulti) {
            
            if(in_array($info['status'],[1,6])){
                if (!$projectObj->where(array('id' => $id))->save(array('is_delete' => 1,'modify_time'=>date('Y-m-d H:i:s')))) $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '删除失败，只有未审核和平台已审核的标可以删除'));
            }
        
        } else {
            foreach ($info as $val){
                if(in_array($val['status'],[1,6])){
                    if (!$projectObj->where('id in (' . $val['id'] . ')')->save(array('is_delete' => 1,'modify_time'=>date('Y-m-d H:i:s')))){ 
                        $this->ajaxReturn(array('status' => 0, 'info' => '删除失败,请重试'));
                    }
                }
            }
        }
        $this->ajaxReturn(array('status' => 1));
    }

    /**
     * 审核产品到销售状态
     */
    public function verify() {
        if (!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $projectObj = M('Project');
        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) $this->ajaxReturn(array('status' => 0, 'info' => '项目不存在或已被删除'));

        if (!in_array($detail['status'], [1,6])){
            $this->ajaxReturn(array('status' => 0, 'info' => '项目当前状态属于非平台审核状态，审核失败'.$detail['status']));
        }

        //标状态 1：等待审核上线 2：销售中 3：已售完 4：还款中 5：已还款' ,6平台已审核，7银行存管审核失败，8废标
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $datetime = date('Y-m-d H:i:s');        
        
        if (!$projectObj->where(array('id' => $id))
                ->save(array('status' => 6,'modify_user_id'=>$uid,'modify_time'=>$datetime))) {
                    
            $this->ajaxReturn(array('status' => 0, 'info' => '审核失败,请重试'));
        }
        
        $this->ajaxReturn(array('status' => 1,'info'=>'平台已审核通过~!'));
    }
    
    /**
    * 产品银行审核
    * @date: 2017-6-27 下午5:15:58
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function bankVerify(){
        if (!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int'); 
        $projectObj = M('Project');
        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) $this->ajaxReturn(array('status' => 0, 'info' => '项目不存在或已被删除'));
        if ($detail['status'] != 6){
            $this->ajaxReturn(array('status' => 0, 'info' =>'项目当前状态：平台还没有审核，请选平台审核')); 
        }
        
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $time = time();
        $datetime = date('Y-m-d H:i:s', $time).'.'.getMillisecond().'000';
        
        $projectObj->startTrans();
        
        //取合同信息
        $contractInfo = M('Contract')->where(array('name'=>$detail['contract_no']))->find();
        
        if(!$contractInfo) {
            $this->ajaxReturn(array('status'=>0,'info'=>'该产品录入的合同编号：'.$detail['contract_no'].' ,在合同表里没有找到，审核失败！'));
        }
        
        if($contractInfo['price']>1000000){
            $contractInfo['price'] = 1000000;
        }
        //到计时标
        $amount = $detail['amount'];
        $start_time = $detail['start_time'];
        $days = $detail['duration'];
        
        if($detail['is_countdown'] == 0){
            $start_time = date("Y-m-d ").' '.date("H:i:s",strtotime($detail['start_time']));////$datetime;
            $_endTime = date("Y-m-d H:i:s",strtotime($detail['end_time']));
            $days = count_days($start_time, $_endTime);
            $amount = substr(intval($contractInfo['price'] / ($detail['user_interest']/100/365 * $days + 1)),0,-2).'00';
        }
        
        
        $contractProjectInfo = M('contractProject')->where(array('project_id'=>$detail['id']))->find();
       
        //生成合同里的标信息
        if(!$contractProjectInfo){
            $rows = array(
                'contract_id' => $contractInfo['id'],
                'project_id' => $detail['id'],
                'project_name' => $detail['title'],
                'price' => $amount,
                'remark' => '程序处理',
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            
            $update_stauts = M('ContractProject')->add($rows);

        } else {
            $rows = array(
                'contract_id' => $contractInfo['id'],
                'project_id' => $detail['id'],
                'project_name' => $detail['title'],
                'price' => $amount,
                'remark' => '程序处理更新',
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            $update_status = M('ContractProject')->where('id='.$contractProjectInfo['id'])->save($rows);
        }
        
        $update_project = $projectObj->where(array('id'=>$detail['id']))->save(array('start_time'=>$start_time,'duration'=>$days,'amount'=>$amount,'able'=>$amount));
        
        if($update_status !== false && $update_project !== false) { 
            $projectObj->commit();
        } else {
            $projectObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'产品关联合同 更新失败，请手动关联'));
        }
        
        
        // 检查一下是否有生成还本付息表,没有则生成
        $repaymentDetailObj = M('RepaymentDetail');
        $count = $repaymentDetailObj->where(array('project_id' => $id))->count(); // 项目还本付息表条目列表
        
        if (!$count) { // 还未生成还本付息表,生成
            if ($detail['repayment_type'] == 1) { // 一次性还本付息
                $rows['project_id'] = $detail['id'];
                $rows['repayment_time'] = $detail['end_time'];
                $rows['period'] = 1;
                $rows['status'] = 1;
                $rows['add_time'] = $datetime;
                $rows['add_user_id'] = $uid;
                $rows['modify_time'] = $datetime;
                $rows['modify_user_id'] = $uid;
                if(!$repaymentDetailObj->add($rows)){
                    $this->ajaxReturn(array('status'=>0,'info'=>'还款记录生成失败;project_id:'.$detail['id']));
                }
            }
        } else {
            if ($detail['repayment_type'] == 1) {
                $rows['repayment_time'] = $detail['end_time'];
                $rows['period'] = 1;
                $rows['status'] = 1;
                $rows['modify_time'] = $rows['add_time'] = $datetime;
                $rows['modify_user_id'] = $rows['add_user_id'] = $uid;
                $update_status = $repaymentDetailObj->where('project_id='.$detail['id'])->save($rows);
                if($update_status === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>'更新还款记录失败;project_id:'.$detail['id']));
                }
            }
        }
        $platcust = M('financing')->where(array('type'=>2,'id'=>$detail['gid']))->getField('platform_account');

        $ret = publish($detail['id'],$platcust);//$this->publish($detail['id']);
        
        if($ret) {
            
            if($ret['code'] == 0){
                $projectObj->where(array('id' => $id))->save(array('status' =>2,'modify_user_id'=>$uid,'modify_time' => $datetime));
                $this->ajaxReturn(array('status' =>1,'info'=>'银行审核已通过~!，可以开卖了'));
            } else {
                $projectObj->where(array('id' => $id))->save(array('status' =>7,'modify_user_id'=>$uid,'modify_time' => $datetime));
                $repaymentDetailObj->where('project_id='.$id)->limit(1)->delete();
                M('ContractProject')->where('project_id='.$id)->limit(1)->delete();
                $this->ajaxReturn(array('status' =>0,'info'=>'银行审核失败~!'.$ret['errorMsg']));
            }
        } else {
            
            $this->ajaxReturn(array('status' =>0,'info'=>'提交银行银行审核未响应，请联系技术'));
        }
    }
    
    
    public function pub($id){
        return $this->publish($id);
    }
    
    /**
    * 上报标的信息
    * @date: 2017-7-3 上午11:16:12
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    /*
    private function publish($id){
        
        $projectObj = M('Project');
        $financingObj = M('Financing');
        $contractObj = M('Contract');
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $projectInfo = M('Project')->field('id,title,amount,start_time,end_time,fid,contract_no,user_interest,duration_type')
                                ->where('id='.$id)->find();
        
        $req['prod_id'] = $projectInfo['id'];
        $req['prod_name'] = $projectInfo['title'];
        $req['total_limit'] = $projectInfo['amount'];
        $req['sell_date'] = date('Y-m-d H:i:s',strtotime($projectInfo['start_time']));
        $req['expire_date'] = date('Y-m-d H:i:s',strtotime($projectInfo['end_time']));
        
        //标的期限类型 1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)
        $dd['cycle'] = $projectInfo['duration_type'];
        
        if($projectInfo['duration_type'] == 1) {
            $cycle_unit = 2;
        } else {
            $cycle_unit = 3;
        }
        //cycle_unit	是	String	周期单位  1日 2周 3月 4季 5年
        $req['cycle_unit'] = $cycle_unit;//$projectInfo['id'];
        $req['ist_year'] = $projectInfo['user_interest']/100;
        
        $contract = $contractObj->field('add_time,fee')->where("name='".$projectInfo['contract_no']."'")->find();
        
        $financing = M('Financing')->field('bank_id,bank_card_no,platform_account,acct_name,bank_code')
                                ->where('id='.$projectInfo['fid'])->find();
        
        $dd['cust_no'] = $financing['platform_account'];
        $dd['reg_date'] = date('Y-m-d',strtotime($contract['add_time']));
        $dd['reg_time'] = date('h:i:s',strtotime($contract['add_time']));
        $dd['financ_int'] = '0.15';
        $dd['fee_int'] = $contract['fee']/100;
        $dd['financ_purpose'] = '融资';
        $dd['use_date'] = date('Y-m-d',strtotime($projectInfo['end_time']));
        $dd['open_branch'] = $financing['bank_code'];
        $dd['withdraw_account'] = $financing['bank_card_no']; //收款银行卡号
        $dd['account_type'] = '2'."";//2企业，1个人
        $dd['payee_name'] = $financing['acct_name'];
        $dd['financ_amt'] = $projectInfo['amount'];
        
        $arr[] = $dd;
        
        $req['financing_info_list'] = json_encode($arr);
        
        $plainText =  \SignUtil::params2PlainText($req);
        
        $sign =  \SignUtil::sign($plainText);
        
        $req['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        $ret = $fd->post('/project/publish',$req);
        
        
        \Think\Log::write('提交银行返回：'.$ret,'INFO');
        
        return json_decode($ret,true);
    }
    */
    
    /**
    * 成标
    * @date: 2017-7-6 下午3:44:10
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function establish(){
        if (!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        $projectObj = M('Project');
        $detail = $projectObj->field('id,status,end_time')->where(array('id' => $id))->find(); 
        
        if ($detail['status'] !=2) {
            $this->ajaxReturn(array('status'=>0,'info'=>'成标失败，只有在销售中的标才能成标操作'));
        }
        
        $req['prod_id'] = $detail['id'];
        $req['flag'] = 2;
        $req['funddata'] = "{\"payout_plat_type\":\"01\",\"payout_amt\":\"0\"}";
        
        $repay_amt = M('userDueDetail')->where('project_id='.$detail['id'].' and user_id>0')->sum('due_amount');
        
        $list[] = array('repay_amt'=>$repay_amt,
                        'repay_fee'=>'0',
                        'repay_num'=>'1',
                        'repay_date'=>date('Y-m-d',strtotime($detail['end_time']))
                    );
        
        $req['repay_plan_list'] = json_encode($list);
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $plainText =  \SignUtil::params2PlainText($req);
        
        $sign =  \SignUtil::sign($plainText);
        
        $req['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        $res_str = $fd->post('/project/establishorabandon',$req);
        
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $data['project_id'] = $detail['id'];
        $data['amt'] = $repay_amt;
        $data['repay_date'] = $detail['end_time'];
        $data['memo'] = $res_str;
        $data['user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
        $data['create_time'] = date("Y-m-d H:i:s");
        M('projectEstablishLog')->add($data);
        
        $res = json_decode($res_str,true);
        if($res['code'] == '0'){
            $projectObj->where(array('id' => $id))
                        ->save(array('status' =>3,'modify_user_id'=>$uid,'modify_time' =>date("Y-m-d H:i:s")));
            $this->ajaxReturn(array('status' =>1,'info'=>'成标操作成功'));
        } else {
            $this->ajaxReturn(array('status' =>0,'info'=>$res['errorMsg']));
        }
    }
    
    /**
    * 产品出账
    * @date: 2017-7-10 下午5:43:23
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function project_chargeoff(){
        if (!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        $projectObj = M('Project');
        $detail = $projectObj->field('id,status,end_time,fid')->where(array('id' => $id))->find();
        
        if ($detail['status'] !=2 && $detail['status'] !=3 ) {
            $this->ajaxReturn(array('status'=>0,'info'=>'标的状态只有在售和已售完的可以出账操作！'.$detail['status']));
        }
        
        $financingInfo = M('Financing')->where('id='.$detail['fid'])->find();
        
        if(!$financingInfo){
            $this->ajaxReturn(array('status'=>0,'info'=>'没有查到融资方'));
        }
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $balace['prod_id'] = $detail['id'];
        $balace['type'] = '01';
        
        $plainText =  \SignUtil::params2PlainText($balace);
        
        $sign =  \SignUtil::sign($plainText);
        
        $balace['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        $res = json_decode($fd->post('/project/balace',$balace),true);
        
        
        if($res['code'] == 0){
            
            if($res['result'] > 0 ) {
                
                $req['prod_id'] = $id;
                              
                $list[] = array('platcust'=>$financingInfo['platform_account'],
                    'out_amt'=>$res['result'],
                    'open_branch'=>$financingInfo['bank_code'],
                    'withdraw_account'=>$financingInfo['bank_card_no'],
                    'payee_name'=>$financingInfo['acct_name'],
                    'is_advance'=>'1',
                    'client_property'=>'0',
                    'bank_id'=>$financingInfo['bank_id']
                );
                
                $req['charge_off_list'] = json_encode($list);
                
                $plainText =  \SignUtil::params2PlainText($req);
                
                $sign =  \SignUtil::sign($plainText);
                
                $req['signdata'] = \SignUtil::sign($plainText);
                
                $res_str = $fd->post('/project/chargeoff',$req);
                
                
                \Think\Log::write('出账返回：'.$res_str,'INFO');
                
                $uid = $_SESSION[ADMIN_SESSION]['uid'];
                $data['project_id'] = $detail['id'];
                $data['memo'] = $res_str;
                $data['user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
                $data['create_time'] = date("Y-m-d H:i:s");
                $data['fid'] = $financingInfo['id'];
                $res2 = json_decode($res_str,true);
                
                $data['order_no'] = $res2['result']['order_no'];
                
                M('projectChargeoffLog')->add($data);
                
                if($res2['code'] == '0'){
                    $this->ajaxReturn(array('status' =>1,'info'=>'出账成功，金额：'.$res['result']));
                } else {
                    $this->ajaxReturn(array('status' =>0,'info'=>'出账错误：'.$res2['errorMsg']));
                }
                
            } else {
                $this->ajaxReturn(array('status' =>0,'info'=>'标的当前可出账金额是 0 ，出账失败'));
            }
            
        } else {
            $this->ajaxReturn(array('status' =>0,'info'=>'标的金额查询失败，错误信息：'.$res['errorMsg']));
        }
        
    }
    
    /**
    * 标的金额
    * @date: 2017-7-10 下午6:18:59
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function project_balace(){
        
        $id = 15;//I('post.id', 0, 'int');
        $projectObj = M('Project');
        $detail = $projectObj->field('id,status')->where(array('id' => $id))->find();
        
        if(!$detail){
            $this->ajaxReturn(array('status'=>0,'info'=>'该标不存在或者已被删除！'));
        }
        
        if (!in_array($detail['status'], [2,3])) {
            $this->ajaxReturn(array('status'=>0,'info'=>'标的状态只有在售和已售完的可以金额操作！'));
        }
        
        $req['prod_id'] = $detail['id'];
        $req['type'] = '1';
        
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $plainText =  \SignUtil::params2PlainText($req);
        
        $sign =  \SignUtil::sign($plainText);
        
        $req['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
                
        $res = json_decode($fd->post('/project/balace',$req),true);
                
        if($res['code'] == '0'){
            $this->ajaxReturn(array('status' =>1,'info'=>'成标操作成功'.$req['result']));
        } else {
            $this->ajaxReturn(array('status' =>0,'info'=>$res['errorMsg']));
        }
    }
    
    /**
    * 克隆标
    * @date: 2017-7-1 下午10:17:05
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function project_copy(){
        
        if (!IS_POST || !IS_AJAX) exit;
        
        $id = I('post.id', 0, 'int');
        $projectObj = M('Project');
        $detail = $projectObj->where(array('id' => $id))->find();
        if($detail) {            
            unset($detail['id']);
            $detail['stage'] = $projectObj->max('stage') + 1;            
            $num = preg_replace('/\D/s', '', $detail['title']);            
            $detail['title'] = str_replace($num,$detail['stage'],$detail['title']);
            $detail['status'] = 1;
            //$detail['contract_no'] = 'ppm'.$detail['stage'];
            $projectObj->add($detail);
            $this->ajaxReturn(array('status'=>1,'info'=>'复制标成功','jump'=>C('ADMIN_ROOT') . '/project/index/'));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'复制标不存在'));
        }
    }

    /**
     * 还本付息   28.27.3
     */
    public function repay() {
        
        if(IS_AJAX){            
            $id = I('id');             
            $projectObj = M('Project');            
            $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
            if (!$detail) {
                $this->ajaxReturn(array('status'=>0,'info'=>'项目不存在或已被删除'));
            }
            
            if($detail['status'] != 3){
                $this->ajaxReturn(array('status'=>0,'info'=>'只有已售完的项目才能 支付操 '));
            }
            
            if($detail['repay_review'] == 0){
                $this->ajaxReturn(array('status'=>0,'info'=>'融资方还款财务还没有审核，操作失败。'));
            }
            
            if($detail['repay_review'] == 1) {
                $this->ajaxReturn(array('status'=>0,'info'=>'融资方还款已审核，融资方还没有把资金转入标的账户，操作失败。'));
            }
            
            if($detail['repay_review'] == 3) {
                $this->ajaxReturn(array('status'=>0,'info'=>'已经支付过了，'));
            }
            
            if($detail['repay_review'] == 4) {
                $this->ajaxReturn(array('status'=>0,'info'=>'融资方还款财务审核失败'));
            }
            
            $list = M('UserDueDetail')->field('id,project_id,red_amount,due_interest,due_capital,due_amount,user_id,interest_coupon,duration_day')->where('project_id='.$id .' and user_id>0 and status=1')->select();
            
            if (!$list) {
                $this->ajaxReturn(array('status'=>0,'info'=>'没有购买记录'));
            }
            
            $userObj = M('User');
            $trans_amt = 0;
            $rows = [];
            foreach ($list as $val) {
                $trans_amt += $val['due_amount'];
                $v['real_repay_amt'] = $val['due_amount'];
                $v['real_repay_amount'] = $val['due_capital'];
                $v['cust_no'] = $userObj->where('id='.$val['user_id'])->getField('platcust');
                $v['real_repay_val'] = $val['due_interest'];
                $v['repay_num'] = "1";
                $v['repay_date'] = $v['real_repay_date'] = date("Y-m-d",strtotime($detail['end_time']));
                $v['experience_amt'] = "0";
                $v['rates_amt'] = "0";
                $v['repay_fee'] = "0";
                $rows[] = $v;
            }
            
            vendor('Fund.FD');
            vendor('Fund.sign');
            
            $req['prod_id'] = $detail['id'];
            $req['trans_amt'] = $trans_amt;
            $req['funddata'] = json_encode(['custRepayList'=>$rows]);
            
            \Think\Log::write('交易列表:'.$req['funddata'],'INFO');
            
            $plainText =  \SignUtil::params2PlainText($req);
            
            $sign =  \SignUtil::sign($plainText);
            
            $req['signdata'] = \SignUtil::sign($plainText);
            
            $fd  = new \FD();
            
            $res_str = $fd->post('/project/repay',$req);
            
            \Think\Log::write('交易列表返回参数:'.$res_str,'INFO');
            
            if(!$res_str)$res_str ='';
            
            $data = [
                'project_id'=>$detail['id'],
                'amt'=>$trans_amt,
                'memo'=>$res_str,
                'user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'create_time'=>date('Y-m-d H:i:s')
            ];
            
            M('projectRepayLog')->add($data);
            
            $res = json_decode($res_str,true);
            
            if($res['code'] == '0'){
                
                $time = date('Y-m-d H:i:s').'.'.getMillisecond();
                
                $projectObj->where('id='.$detail['id'])->save(['repay_review'=>3,'repayment_time'=>$time]);
                
                $rows = array(
                    'status' => 3,
                    'status_new' => 3,
                    'real_time' => $time,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );

                M('RepaymentDetail')->where(array('project_id'=>$detail['id']))->save($rows);
                
                $this->ajaxReturn(array('status' =>1,'info'=>'支付成功'));
                
            } else {
                $this->ajaxReturn(array('status' =>0,'info'=>'支付错误：'.$res['errorMsg']));
            }
            
        } else {
            exit('非法操作');
        }
        
        
    }

    /**
     * 撤销支付动作
     */
    public function revoke(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int'); // 产品ID
        $rid = I('post.rid', 0, 'int'); // 付息ID

        $userAccountObj = M('UserAccount');
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userAccountObj = M('UserAccount');
        $repaymentDetail = $repaymentDetailObj->where(array('id'=>$rid,'project_id'=>$id))->find();
        if(!$repaymentDetail) $this->ajaxReturn(array('status'=>0,'info'=>'付息信息不存在或已被删除'));
        // 非已支付状态
        if($repaymentDetail['status'] != 2) $this->ajaxReturn(array(''=>0,'info'=>'不在已支付状态,无法撤销'));
        $repaymentDetailObj->startTrans();
        $time = date('Y-m-d H:i:s').'.'.getMillisecond();
        $rows = array(
            'status' => 1,
            'real_time' => '',
            'modify_time' => $time,
            'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
        );
        if(!$repaymentDetailObj->where(array('id'=>$rid))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        // 检查是否有用户购买列表
        $userList = $userDueDetailObj->where(array('repay_id'=>$repaymentDetail['id'],'status'=>2))->select();
        if($repaymentDetail['status2'] != 0){ // 逾期、坏账
            $rowsSub = array(
                'status' => 1,
                'real_time' => null,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!$userDueDetailObj->where(array('repay_id'=>$repaymentDetail['id']))->save($rowsSub)){
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
        }else{ // 正常支付流程(需要把用户账户数据同时返回)
            if(count($userList) > 0){
                foreach($userList as $key => $val){
                    $sql = "update s_user_account set ";
                    $sql .= "total_invest_capital=total_invest_capital-" . $val['due_capital'];
                    $sql .= ",total_invest_interest=total_invest_interest-" . $val['due_interest'];
                    $sql .= ",wait_amount=wait_amount+" . $val['due_amount'];
                    $sql .= ",wait_capital=wait_capital+" . $val['due_capital'];
                    $sql .= ",wait_interest=wait_interest+" . $val['due_interest'];
                    $sql .= " where user_id=".$val['user_id'];
                    $userAccountObj->execute($sql);
                }
                $rowsSub = array(
                    'status' => 1,
                    'real_time' => null,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );
                if(!$userDueDetailObj->where(array('repay_id'=>$repaymentDetail['id']))->save($rowsSub)){
                    $repaymentDetailObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                }
            }
        }
        $repaymentDetailObj->commit();
        // 检查该是否已全部支付完成
        if($projectObj->where(array('status'=>5,'id'=>$id))->getField('id')){ // 产品状态为已支付状态
            if(!$projectObj->where(array('id'=>$id))->save(array('status'=>3))){
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
        }
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 付款用户列表(去掉到期转入钱包的部分订单)
     */
    public function paylist() {
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }

        $count = 15;
        $conditions = "user_id > 0 and project_id=".$id." and repay_id=".$repay_id;
        $counts = $userDueDetailObj->where($conditions)->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $page_totle_capital = 0;
        $page_totle_interest = 0;
        foreach ($list as $key => $val) {
            $list[$key]['real_name'] = $userObj->where(array('id' => $val['user_id']))->getField('real_name');
            $list[$key]['bank_name'] = $userBankObj->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->getField('bank_name');
            $list[$key]['to_wallet'] = $userAccountObj->where(array('user_id' => $val['user_id']))->getField('to_wallet');
            $page_totle_capital += $val['due_capital'];
            $page_totle_interest += $val['due_interest'];
        }

        $this->assign('totle_interest', $userDueDetailObj->where($conditions)->sum('due_interest'));
        $this->assign('totle_capital', $userDueDetailObj->where($conditions)->sum('due_capital'));

        $this->assign('page_totle_interest', $page_totle_interest);
        $this->assign('page_totle_capital', $page_totle_capital);

        $this->assign('detail', $detail);
        $this->assign('repay_detail', $repayDetail);
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('id', $id);
        $this->assign('rid', $repay_id);
        $this->assign('count', $counts);
        $this->display();
    }

    /**
     * 转入钱包的支付记录
     */
    public function paylisttowallet(){
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }

        $count = 15;
        $cond[] = 'user_id > 0';
        $cond[] = "project_id=".$id;
        $cond[] = "repay_id=".$repay_id;
    
        $conditions = implode(' and ', $cond);
        $counts = $userDueDetailObj->where($conditions)->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $page_totle_capital = 0;
        $page_totle_interest = 0;

        foreach ($list as $key => $val) {
            if($val['user_id'] > 0){
                $list[$key]['user_info'] = $userObj->where(array('id' => $val['user_id']))->field('username,real_name,platcust')->find();
                $list[$key]['bank_name'] = $userBankObj->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->getField('bank_name');
            }else{
                $list[$key]['real_name'] = '幽灵账户';
            }
            
            
            if($detail['status'] == 5) {
                $page_totle_capital += $val['due_capital'];
                $page_totle_interest += $val['due_interest'];
            } else {
                if($val['status'] !=2){
                    $page_totle_capital += $val['due_capital'];
                    $page_totle_interest += $val['due_interest'];
                }
            }

            //$page_totle_capital += $val['due_capital'];
            //$page_totle_interest += $val['due_interest'];
        }
        
        if($detail['status'] == 5) {
            $this->assign('totle_interest', $userDueDetailObj->where($conditions)->sum('due_interest'));
            $this->assign('totle_capital', $userDueDetailObj->where($conditions)->sum('due_capital'));
        } else {
            $this->assign('totle_interest', $userDueDetailObj->where($conditions .' and status=1')->sum('due_interest'));
            $this->assign('totle_capital', $userDueDetailObj->where($conditions .'  and status=1')->sum('due_capital'));
        }
        

        $this->assign('page_totle_interest', $page_totle_interest);
        $this->assign('page_totle_capital', $page_totle_capital);
        $this->assign('detail', $detail);
        $this->assign('repay_detail', $repayDetail);
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('id', $id);
        $this->assign('rid', $repay_id);
        $this->assign('count', $counts);
        $this->display();
    }

    /**
     * 导出Excel(宝付支付)
     */
    public function exporttoexcel(){
        vendor('PHPExcel.PHPExcel');
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID
        $act = I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }
        $num = preg_replace('/\D/s', '', $detail['title']);
        $time = strtotime($repayDetail['repayment_time']);
        if($act == 2){
            $file_name =date('md',$time).'ppm'.$num.'钱包';
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=1 or from_wallet=1)")->order('add_time desc')->select();
        }else{
            $file_name =date('md').'ppm'.$num.'银行卡'.date('md',$time).'到期';
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=0 and from_wallet=0")->order('add_time desc')->select();
        }
        $totle_capital = 0;
        $totle_interest = 0;
        $totle_count = count($list); // 总笔数

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
        ->setCreator("票票喵票据")
        ->setLastModifiedBy("票票喵票据")
        ->setTitle("title")
        ->setSubject("subject")
        ->setDescription("description")
        ->setKeywords("keywords")
        ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
            ->setCellValue("A1", "*收款方姓名")
            ->setCellValue("B1", "*收款方银行账号")
            ->setCellValue("C1", "*开户行所在省")
            ->setCellValue("D1", "*开户行所在市")
            ->setCellValue("E1", "*开户行名称")
            ->setCellValue("F1", "*收款方银行名称")
            ->setCellValue("G1", "*金额")
            ->setCellValue("H1", "商户订单号")
            ->setCellValue("I1", "商户备注");

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            $totle_interest += $val['due_interest'];

            $bankInfo = $userBankObj->field('acct_name,area,bank_code,bank_address,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();

            if($bankInfo['bank_name'] == '邮政储蓄') {
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            }

            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['card_no']); // 收款银行账号

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,''); // 开户行所在省
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,''); // 开户行所在市
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''); // 开户行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$bankInfo['bank_name']); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,($val['due_capital']+$val['due_interest']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,str_replace('RP', '', $val['repayment_no'])); // 商户订单号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,''); // 商户备注
            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }




    /**
     * 导出Excel(连连支付)
     * User: ChenJunJie
     */
    public function exporttoexcelLL(){
        vendor('PHPExcel.PHPExcel');
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID
        $act = I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }
        $num = preg_replace('/\D/s', '', $detail['title']);
        $time = strtotime($repayDetail['repayment_time']);
        if($act == 2){
            $file_name =date('md',$time).'ppm'.$num.'钱包';
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=1 or from_wallet=1)")->order('add_time desc')->select();
        }else{
            $file_name =date('md').'ppm'.$num.'银行卡'.date('md',$time).'到期';
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=0 and from_wallet=0")->order('add_time desc')->select();
        }
        $totle_capital = 0;
        $totle_interest = 0;
        $totle_count = count($list); // 总笔数

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
        ->setCreator("票票喵票据")
        ->setLastModifiedBy("票票喵票据")
        ->setTitle("title")
        ->setSubject("subject")
        ->setDescription("description")
        ->setKeywords("keywords")
        ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
            ->setCellValue("A3", "*商户付款流水号")
            ->setCellValue("B3", "*1-对公/0-对私")
            ->setCellValue("C3", "*收款方开户名")
            ->setCellValue("D3", "*收款银行账号")
            ->setCellValue("E3", "*金额(单位元：精确到分)")
            ->setCellValue("F3", "银行编号")
            ->setCellValue("G3", "收款备注")
            ->setCellValue("H3", "付款用途");

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A1", "*日期")
            ->setCellValue("B1", "*总金额")
            ->setCellValue("C1", "*总笔数");

        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        // 设置列表值
        $pos = 4;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            $totle_interest += $val['due_interest'];

            $money = $val['due_capital']+$val['due_interest'];

            if($money >= 50000){
                $bak = "付款到银行卡";
            }else{
                $bak = "";
            }

            $bankInfo = $userBankObj->field('acct_name,area,bank_code,bank_address,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("A".$pos,str_replace('RP', '', $val['repayment_no'])); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,0); 

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$bankInfo['acct_name']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,trim($val['card_no']));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,($val['due_capital']+$val['due_interest']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,''); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$bak); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,'付款到银行卡'); // 商户订单号
            $pos += 1;
        }


        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A2", date("Ymd"))
            ->setCellValue("B2", $totle_capital+$totle_interest)
            ->setCellValue("C2", count($list));

        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * @author hui.xu
     * @date 2016\04\14
     * 导出Excel(融宝支付)
     */
    public function exportToExcelRb(){

        vendor('PHPExcel.PHPExcel');

        $id = I('get.id', 0, 'int'); // 项目ID

        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

        $act = I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }
        if($act == 2){
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=1 or from_wallet=1)")->order('add_time desc')->select();
        }elseif($act == 3){
            //新增导出存管账号数据
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=2 or from_wallet=2)")->order('add_time desc')->select();
        }else{
            //不是钱包购买的，也没有转入钱包
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=0 and from_wallet=0")->order('add_time desc')->select();
        }


        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()
                    ->setCreator("票票喵票据")
                    ->setLastModifiedBy("票票喵票据")
                    ->setTitle("title")
                    ->setSubject("subject")
                    ->setDescription("description")
                    ->setKeywords("keywords")
                    ->setCategory("Category");

        $objPHPExcel->setActiveSheetIndex(0)->setTitle('融宝..')->setCellValue("A1", "序号")
                    ->setCellValue("B1", "银行账号")
                    ->setCellValue("C1", "开户名")
                    ->setCellValue("D1", "开户行")
                    ->setCellValue("E1", "分行")
                    ->setCellValue("F1", "支行")
                    ->setCellValue("G1", "账户类型")
                    ->setCellValue("H1", "金额")
                    ->setCellValue("I1", "币种")
                    ->setCellValue("J1", "省")
                    ->setCellValue("K1", "市")
                    ->setCellValue("L1", "手机号码")
                    ->setCellValue("M1", "证件类型")
                    ->setCellValue("N1", "证件号")
                    ->setCellValue("O1", "用户协议号")
                    ->setCellValue("P1", "商户订单号")
                    ->setCellValue("Q1", "备注");

        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);

        // 设置列表值
        $pos = 2;

        $n = 1;

        foreach ($list as $key => $val) {


            $bankInfo = $userBankObj->field('acct_name,area,bank_code,bank_address,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();

            if($bankInfo['bank_name'] == '邮政储蓄'){
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            } else if($bankInfo['bank_name'] == '建设银行'){
                $bankInfo['bank_name'] = '中国建设银行';
            } else if($bankInfo['bank_name'] == '农业银行'){
                $bankInfo['bank_name'] = '中国农业银行';
            } else if($bankInfo['bank_name'] == '民生银行'){
                $bankInfo['bank_name'] = '中国民生银行';
            } else if($bankInfo['bank_name'] == '广发银行'){
                $bankInfo['bank_name'] = '广东发展银行';
            } else if($bankInfo['bank_name'] == '光大银行'){
                $bankInfo['bank_name'] = '中国光大银行';
            } else if($bankInfo['bank_name'] == '浦发银行'){
                $bankInfo['bank_name'] = '上海浦东发展银行';
            } else if($bankInfo['bank_name'] == '工商银行'){
                $bankInfo['bank_name'] = '中国工商银行';
            }


            $userInfo = M('User')->field("username,card_no")->where(array("id"=>$val['user_id']))->find();

            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['card_no']); // 收款银行账号

            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $bankInfo['acct_name']); // 收款方开户姓名

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$bankInfo['bank_name']); // 收款方银行名称

            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos,'分行');

            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,'支行');

            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,'私');

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,($val['due_capital']+$val['due_interest']), \PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额

            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, 'CNY');

            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, '省');

            $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, '市');

            $objPHPExcel->getActiveSheet()->setCellValue("L".$pos, '13888001111',\PHPExcel_Cell_DataType::TYPE_STRING);//$userInfo['username']  //手机号码

            $objPHPExcel->getActiveSheet()->setCellValue("M".$pos, '身份证');//身分证   证件类型

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("N".$pos, '430101199001010011',\PHPExcel_Cell_DataType::TYPE_STRING);// $userInfo['card_no'],\PHPExcel_Cell_DataType::TYPE_STRING; 身分证号

            $objPHPExcel->getActiveSheet()->setCellValue("O".$pos, ''); //$val['repayment_no']

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("P".$pos, str_replace('TX', '', $val['repayment_no']));//商户订单号

            $objPHPExcel->getActiveSheet()->setCellValue("Q".$pos, '还款至银行卡');//备注

            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户付息表 - 融宝('.date("Y-m-d H:i:s").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    /**
     * 导出Excel(盛付通)
     */
    public function exporttoexcel_sft(){
        vendor('PHPExcel.PHPExcel');
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID
        $act = I('get.act', 1, 'int');

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }

        if($detail['type'] == 104 || $detail['type'] == 109 || $detail['type'] == 110){ // 基金类产品
            $projectModelFundObj = M("ProjectModelFund");
            $fundDetailObj = M("FundDetail");
            $detailExt = $projectModelFundObj->where(array('project_id'=>$detail['id']))->find();
            $percent = 0; // 基金类收益率
            if($detailExt){
                if(!$detailExt['enter_time']){
                    $timeStart = date('Y-m-d', strtotime($detail['start_time'])); // 产品净值进入时间点
                }else{
                    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                }
                if(!$detail['advance_end_time']){
                    $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }else{
                    $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }
                $today = date('Y-m-d', time()); // 当前时间点
                if($today < $timeEnd) $timeEnd = $today;
                $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=".$detailExt['fund_id']." and datetime>='".$timeStart."' and datetime<='".$timeEnd."'")->order('datetime asc')->select(); // 关联基金净值列表
                if(count($fundList) > 1){ // 两个净值点以上
                    $fundStart = $fundList[0]['val']; // 起始净值
                    $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                    switch($detail['type']){
                        case 104: // 打新股,收益超过18%分成
                            if($fundEnd - $fundStart > 0){
                                if(($fundEnd - $fundStart)/$fundStart > 0.18){ // 分成
                                    $percent = 0.18 + (($fundEnd - $fundStart)/$fundStart - 0.18)/2;
                                }else{
                                    $percent = ($fundEnd - $fundStart)/$fundStart;
                                }
                            }
                            break;
                        case 109: // B类基金,杠杆0.2
                            if($fundEnd - $fundStart > 0){
                                $fundEndB = ($fundEnd - $fundStart)*0.2 + $fundStart;
                                $percent = ($fundEndB - $fundStart)/$fundStart;
                            }
                            break;
                        case 110: // A类基金,杠杆2.6
                            if($fundEnd - $fundStart > 0){
                                $fundEndA = ($fundEnd - $fundStart)*2.6 + $fundStart;
                                $percent = ($fundEndA - $fundStart)/$fundStart;
                            }else if($fundEnd - $fundStart < 0){
                                $fundEndA = ($fundEnd - $fundStart)*3 + $fundStart;
                                $percent = ($fundEndA - $fundStart)/$fundStart;
                            }
                            break;
                    }
                }
            }
        }else if($detail['type'] == 148){ // 搏息宝
            $percent = cal_fund_percent($id);
        }

        if($act == 2){
            $list = $userDueDetailObj->where('project_id='.$id.' and repay_id='.$repay_id.' and user_id>0 and (to_wallet=1 or from_wallet=1)')->order('add_time desc')->select();
        }else{
            $list = $userDueDetailObj->where('project_id='.$id.' and repay_id='.$repay_id.' and user_id>0 and to_wallet=0 and from_wallet=0')->order('add_time desc')->select();
        }
        $totle_capital = 0;
        $totle_interest = 0;
        $totle_count = count($list); // 总笔数

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')->setCellValue("A1", "批次号")->setCellValue("A2", "商户流水号")
            ->setCellValue("B2", "省份")->setCellValue("C2", "城市")->setCellValue("D2", "开户支行名称")
            ->setCellValue("E2", "银行名称")->setCellValue("F2", "收款人账户类型（C为个人B为企业）")->setCellValue("G2", "收款人户名")
            ->setCellValue("H2", "收款方银行账号")->setCellValue("I2", "付款金额（元）")->setCellValue("J2", "付款理由");
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A2:J2')->getFont()->setName('宋体')->setSize(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(23);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(21);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(19);

        // 设置列表值
        $pos = 3;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            $totle_interest += $val['due_interest'];

            $bankInfo = $userBankObj->field('acct_name,area,bank_name,bank_address')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("A".$pos, str_replace('RP', '', $val['repayment_no']), \PHPExcel_Cell_DataType::TYPE_STRING); // 商户流水号
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $bankInfo['area']); // 省份
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, ''); // 城市
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $bankInfo['bank_address']); // 开户支行名称
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $bankInfo['bank_name']); // 银行名称
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, 'C');
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos, $val['card_no'], \PHPExcel_Cell_DataType::TYPE_STRING); // 收款银行账号
            $objPHPExcel->getActiveSheet()->getStyle('I'.$pos)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            if($detail['type'] == 104 || $detail['type'] == 109 || $detail['type'] == 110) { // 基金类产品
                $objPHPExcel->getActiveSheet()->setCellValue("I" . $pos, number_format($val['due_capital'] + $val['due_capital'] * $percent, 2)); // 金额
            }else if($detail['type'] == 148){ // 搏息宝
                $objPHPExcel->getActiveSheet()->setCellValue("I" . $pos, number_format($val['due_capital'] + ($val['due_capital']*(count_days(date('Y-m-d',strtotime($val['due_time'])).' 08:00:00', date('Y-m-d',strtotime($val['start_time'])).' 08:00:00')+1)*$percent/365), 2)); // 金额
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, number_format(($val['due_capital']+$val['due_interest']), 2)); // 金额
            }
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, '还本付息');
            $pos += 1;
        }

        // 设置日期
        //$objPHPExcel->getActiveSheet()->setCellValue("A2", date('Ymd', time()));
        // 设置总金额
        //$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        //$objPHPExcel->getActiveSheet()->setCellValue("B2", number_format(($totle_capital + $totle_interest), 2));
        // 设置总笔数
        //$objPHPExcel->getActiveSheet()->setCellValue("C2", $totle_count);

        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="盛付通用户付息表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 项目详情页面预览
     */
    public function preview(){
        $action = I('get.act', 'detail', 'strip_tags'); // 动作(detail:详细页面,descr1:描述页面1,descr2:页面描述2)
        $id = I('get.id', 0, 'int');

        $projectObj = M('Project');
        $detail = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$detail) {
            $this->error('项目详细信息不存在或已被删除');exit;
        }
        if($action == 'detail'){
            if($detail['user_platform_subsidy']>0){
                $detail['user_interest'] = $detail['user_interest'] - $detail['user_platform_subsidy'];
            }
            if($detail['status'] == 1) {
                $detail['percent'] = 0;   
            } else {
                $detail['buy_count'] = M('userDueDetail')->where('user_id>0 and project_id='.$detail['id'])->group('user_id')->count();
            }            
            
            if($detail['invest_direction_image']) {
                $imgarr = explode("\r\n", trim($detail['invest_direction_image']));
                if($imgarr[2]){
                    $detail['invest_direction_image'] =$imgarr[2];
                }
            }
            if($detail['repayment_source_descr']) {
                $detail['repayment_source_descr'] = explode("\r\n", trim($detail['repayment_source_descr']));
            }
            $this->assign('detail', $detail);
            $this->display();
        }else if($action == 'descr1'){
            $this->assign('title', $detail['invest_direction_title']);
            $this->assign('content', $detail['invest_direction']);
            if($detail['invest_direction_descr']){
                if($detail['invest_direction_descr']) $this->assign('descr', format_project_descr($detail['invest_direction_descr']));
                if($detail['invest_direction_image']) $this->assign('image', format_project_image($detail['invest_direction_image']));
                $this->display('preview1');exit;
            }
            $this->display('preview2');
        }else if($action == 'descr2'){
            $this->assign('title', $detail['repayment_source_title']);
            $this->assign('content', $detail['repayment_source']);
            if($detail['repayment_source_descr']){
                if($detail['repayment_source_descr']) $this->assign('descr', format_project_descr($detail['repayment_source_descr']));
                //if($detail['repayment_source_image']) $this->assign('image', format_project_image($detail['repayment_source_image']));
                $this->display('preview1');exit;
            }
            $this->display('preview2');
        }
    }

    /**
     * 回收站
     */
    public function recycle(){
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        $projectObj = M('Project');

        $counts = $projectObj->where(array('is_delete'=>1))->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = $projectObj->where(array('is_delete'=>1))->order('modify_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $key => $val) {
            $list[$key]['status_str'] = $this->_statusColor[$val['status']];
        }

        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * 恢复已删除内容
     */
    public function recovery(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $projectObj = M('Project');
        $detail = $projectObj->where(array('id'=>$id,'is_delete'=>1))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'项目信息不存在或未被删除'));

        if(!$projectObj->where(array('id'=>$id))->save(array('is_delete'=>0,'status'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'恢复失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }


    /**
     * 产品日销售列表
     */
    public function daysales(){
        if(!IS_POST){
            $datetime = I('get.dt', '', 'strip_tags');
            $updatecache = I('get.uc', 0, 'int'); // 更新缓存
            if(!$datetime) $datetime = date('Y-m-d', strtotime('-1 days'));
            $start_time = $datetime.' 00:00:00.0000';
            $end_time = $datetime.' 23:59:59.999000';

            $projectObj = M('Project');
            $rechargeLogObj = M('RechargeLog');
            $userDueDetailObj = M("UserDueDetail");
            $userWalletRecordsObj = M("UserWalletRecords");
            $contractObj = M("Contract");
            $contractProjectObj = M("ContractProject");

            if($datetime < date('Y-m-d', time())) {
                $cacheData = F('project_daysales_'.str_replace('-', '_', $datetime));
            }

            if($updatecache) $cacheData = null;


            if(!$cacheData){

                $list = $rechargeLogObj->field('recharge_no,user_id,project_id,sum(amount) totlecapital')->where("user_id > 0 and status = 2 and modify_time>='".$start_time."' and modify_time<='".$end_time."'")->group('project_id')->order('project_id')->select();

                $totleMoney = 0;//募集总金额
                $totleMoneyMore = 0;
                $totleGhostMoney = 0; // 幽灵账户购买金额
                $totleWalletMoney = 0; // 使用钱包购买金额
                $totleFee = 0; // 总手续费

                $totleRedAmount=0; // 总红包

                foreach($list as $key => $val){

                    $list[$key]['project'] = $projectObj->field('id,title,amount,user_interest,fid,start_time,end_time,remark,fid,new_preferential,project_group_id')->where(array('id'=>$val['project_id']))->find();
                    
                    $list[$key]['project']['financing'] = M('financing')->where('id='.$list[$key]['project']['fid'])->getField('name');
                    
                    $list[$key]['group_name'] = M('projectGroup')->where('id='.$list[$key]['project']['project_group_id'])->getField('name');

                    $list[$key]['fid'] = $list[$key]['project']['fid'];
                    
                    $list[$key]['project_title_id'] = preg_replace('/\D/s', '', $list[$key]['project']['title']);
                    
                    $list[$key]['project']['days'] = count_days($list[$key]['project']['end_time'], $list[$key]['project']['start_time']);

                    //超额部份
                    $more_money = $userDueDetailObj->where("project_id=".$val['project_id']." and add_time<='".$end_time."'")->sum('due_capital') - $list[$key]['project']['amount'];

                    if($more_money < 0) $more_money = 0;

                    $list[$key]['money_more'] = $more_money;

                    $totleMoneyMore += $more_money;

                    //	幽灵账户
                    $ghost_money = $userDueDetailObj->where("user_id>=-130 and user_id<=0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('due_capital');
                    $list[$key]['ghost_money'] = $ghost_money;
                    $totleGhostMoney += $ghost_money;
                   

                    //钱包
                    $wallet_money = $rechargeLogObj->where("type=3 and status=2 and user_id>0 and project_id=".$val['project_id']." and modify_time>='".$start_time."' and modify_time<='".$end_time."'")->sum('amount');

                    //取红包金额
                    $where = array(
                        'project_id' => $val['project_id'],
                       // 'user_id'=> $val['user_id'],
                      //  'status' => 1,
                        'add_time'=> array('between',array($start_time,$end_time))
                       // 'add_time'=> array('elt',$end_time),
                    );
//                    $redAmount = M('userDueDetail')->where($where)->getField(' sum(red_amount) as amount_sum ');
                    $redAmount = M('userDueDetail')->where($where)->sum('red_amount');

                    $redAmount = isset($redAmount)?$redAmount:0;

                    $list[$key]['red_amount'] = $redAmount;
                    $totleRedAmount += $redAmount;

                    //募集金额
                    $totleMoney += $val['totlecapital'];

                    $list[$key]['wallet_money'] = $wallet_money;

                    $totleWalletMoney += $wallet_money;
                   
                    // 获取产品对应合同相关数据
                    $contractId = $contractProjectObj->where(array('project_id'=>$list[$key]['project']['id']))->getField('contract_id');
                    if($contractId){
                        $list[$key]['contract_info'] = $contractObj->where(array('id'=>$contractId))->find();
                    }
                }

                //end 零钱包
                if($datetime < date('Y-m-d', time())){
                    $rows = array(
                        'list' => $list,
                        'totle_money' => $totleMoney,
                        'totle_money_more' => $totleMoneyMore,
                        'totle_ghost_money' => $totleGhostMoney,
                        'totle_wallet_money' => $totleWalletMoney,
                        'totle_red_amount'=>$totleRedAmount,
                        'totle_fee' => $totleFee,
                    );
                    F('project_daysales_'.str_replace('-', '_', $datetime), $rows);
                }
            } else {
                $list = $cacheData['list'];
                foreach($list as $key => $val){
                    $list[$key]['remark'] = $projectObj->where(array('id'=>$val['project_id']))->getField('remark');
                }
                $totleMoney = $cacheData['totle_money'];
                $totleMoneyMore = $cacheData['totle_money_more'];
                $totleGhostMoney = $cacheData['totle_ghost_money'];
                $totleWalletMoney = $cacheData['totle_wallet_money'];
                $totleRedAmount =   $cacheData['totle_red_amount'];
                $totleFee = $cacheData['totle_fee'];
            }
            $this->assign('totle_money', $totleMoney);
            $this->assign('totle_money_more', $totleMoneyMore);
            $this->assign('totle_ghost_money', $totleGhostMoney);
            $this->assign('totle_wallet_money', $totleWalletMoney);
            $this->assign('totle_fee', $totleFee);
            $this->assign('totle_red_amount', $totleRedAmount);

            $this->assign('list', $list);
            $this->assign('datetime', $datetime);
            $this->display();
        }else{
            $datetime = I('post.dt');
            $flushcache = I('post.flushcache');
            $quest = '';
            if($datetime) $quest .= '/dt/'.$datetime;
            if($flushcache) $quest .= '/uc/1';
            redirect(C('ADMIN_ROOT') . '/project/daysales'.$quest);
        }
    }

    /*
     * 日销售明细
     */
    public function daysaledetails(){

        if(!IS_POST){
            $starttime = I('get.start_time', '', 'strip_tags');
            $endtime = I('get.end_time', '', 'strip_tags');

            if(!$starttime) {
                $starttime = date('Y-m-d');
            }

            $start_time = $starttime.' 00:00:00.0000';

            $condition = "user_id > 0 and add_time>'$start_time'";

            if($endtime) {
                $end_time = $endtime.' 23:59:59.999000';
                $condition .= " AND add_time<='$end_time'";
            }

            $totalCnt =  M("UserDueDetail")->where($condition)->count();

            $Page = new \Think\Page($totalCnt, $this->pageSize);

            $list = M("UserDueDetail")->field('id,user_id,project_id,invest_detail_id,due_capital,due_interest,add_time,due_time')
                                    ->where($condition)->order("add_time DESC")->limit($Page->firstRow . ',' . $Page->listRows)
                                    ->select();

            $res = array();

            $total_money = M("UserDueDetail")->where($condition)->sum('due_capital');
            $total_due_interest = M("UserDueDetail")->where($condition)->sum('due_interest');

            if(!empty($list)) {

                foreach($list as $val){

                    $rows['id'] = $val['id'];

                    $userinfo = M("User")->field('username,real_name,card_no')->where(array('id'=>$val['user_id']))->find();
                    $rows['username'] = $userinfo['username'];
                    $rows['real_name'] = $userinfo['real_name'];

                    $year = substr($userinfo['card_no'], 6, 4);
                    $nowYear = date('Y', time());
                    $rows['age'] = $nowYear - $year;
                    $sex = substr($userinfo['card_no'], strlen($userinfo['card_no']) - 2, 1);
                    if($sex % 2 != 0) {
                        $rows['sex'] = '男';
                    } else {
                        $rows['sex'] = '女';
                    }

                    $_t = strtotime($val['add_time']);

                    $rows['date'] = date("Y-m-d",$_t);
                    $rows['time'] = date("H:i:s",$_t);
                    
                    
                    

                    $rows['project'] = M("Project")->field('title,fid,new_preferential,project_group_id')->where(array('id'=>$val['project_id']))->find();
                    
                    $rows['project']['financing'] = M('financing')->where('id='.$rows['project']['fid'])->getField('name');
                    
                    $rows['group_name'] = M('projectGroup')->where('id='.$rows['project']['project_group_id'])->getField('name');
                    
                    $recharge_no = M('InvestmentDetail')->where(array('id'=>$val['invest_detail_id']))->getField('recharge_no');

                    //$amount = M("UserRedenvelope")->where(array("recharge_no"=>$recharge_no,"status"=>1))->getField('amount');
                    
                    //回款时间
                    $rows['due_time'] = date("Y-m-d",strtotime($val['due_time']));

                    $pay_type = M('rechargeLog')->where("recharge_no = '$recharge_no'")->getField('type');

                    if($pay_type == 1) {
                        $rows['des'] = "<b>宝付</b>";
                    }else if($pay_type == 3) {
                        $rows['des'] = "<b>钱包</b>";
                    }else if($pay_type == 4){
                        $rows['des'] = "<b>融宝</b>";
                    }else if($pay_type == 6){
                        $rows['des'] = "<b>连连支付</b>";
                    }else {
                        $rows['des'] = "<b>宝付API</b>";
                    }

                    $rows['amount'] = $val['due_capital'];
                    $rows['due_interest'] = $val['due_interest'];
                    //$rows['red_amount'] = $amount;

                    $res[] = $rows;
                }
                unset($list);
            }
            $param = array(
                "start_time" => $starttime,
                "end_time" => $endtime,
                'totalCnt' => $Page->totalRows,
                'total_money'=>$total_money,
                'total_due_interest'=>$total_due_interest
            );
            $this->assign("params",$param);
            $this->assign("list",$res);
            $this->assign('show', $Page->show());
            $this->display('daysaledetails');
        }
    }


    
    
    /**
     * 导出销售明细
     * @param
     */
    public function saledetailsexportExcel() {
    
        ini_set("memory_limit", "512M");
        set_time_limit(0);
    
        $starttime = I('get.start_time', '', 'strip_tags');
        $endtime = I('get.end_time', '', 'strip_tags');
    
        if(!$starttime) {
            $starttime = date('Y-m-d');
        }
    
        $start_time = $starttime.' 00:00:00.0000';
    
        $condition = "udd.user_id >0 and udd.add_time>'$start_time'";
    
        if($endtime) {
            $end_time = $endtime.' 23:59:59.999000';
            $condition .= " AND udd.add_time<='$end_time'";
        }
        
        $sql = "SELECT udd.`id`,udd.`user_id`,udd.`project_id`,udd.`invest_detail_id`,udd.duration_day,udd.`due_capital`,udd.`due_interest`,udd.`from_wallet`,udd.`add_time`,udd.`due_time`,udd.`red_amount`, udd.interest_coupon,u.username,u.real_name,u.card_no,pj.title,ind.recharge_no
                ,pj.new_preferential,fin.name as financing,pj.project_group_id
                FROM `s_user_due_detail` AS udd LEFT JOIN s_user as u ON udd.user_id = u.id LEFT JOIN s_project AS pj ON udd.project_id = pj.id LEFT JOIN s_investment_detail as ind ON udd.invest_detail_id = ind.id LEFT JOIN s_financing as fin ON fin.id = pj.fid
                WHERE $condition ORDER BY udd.id ASC";

        $list = M()->query($sql);
    
        if(!empty($list)) {
    
            vendor('PHPExcel.PHPExcel');    
            $objPHPExcel = new \PHPExcel();    
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")    
                        ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");    
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('产品销售明细')
                        ->setCellValue("A1", "编号")
                        ->setCellValue("B1", "账号")
                        ->setCellValue("C1", "姓名")
                        ->setCellValue("D1", "年龄")
                        ->setCellValue("E1", "期数")
                        ->setCellValue("F1", "金额")
                        ->setCellValue("G1", "收益")
                        ->setCellValue("H1", "红包")
                        ->setCellValue("I1", "券包收益")
                        ->setCellValue("J1", "购买日期")
                        ->setCellValue("K1", "购买时刻")
                        ->setCellValue("L1", "还款时间")
                        ->setCellValue("M1", "描述")
                        ->setCellValue("N1", "产品标签")
                        ->setCellValue("O1", "融资方")
                        ->setCellValue("P1", "分组名称");
    
    
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);            
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    
            // 设置列表值
            $pos = 2;
            $n = 1;
            $nowYear = date('Y', time());
    
            foreach($list as $val){
                $year = substr($val['card_no'], 6, 4);    
                $_t = strtotime($val['add_time']);    
                if($val['from_wallet'] == 0) {
                    $des = "银行卡购买";
                } else {
                    $des = "零钱包购买";
                }
                
                $tag_name = $this->getTagName($val['new_preferential']);                
                
                $group_name = M('projectGroup')->where('id='.$val['project_group_id'])->getField('name');                
                
                $red_amount = 0;
                $coupon_income = 0;
                if($val['interest_coupon'] > 0 ){                    
                    $coupon_income = $val['due_capital'] * $val['duration_day'] * ($val['interest_coupon'])/ 100 / 365;
                } else {
                    //2017.04.21 版本上线。所以红包会单独记录在 red_amount 字段，在该时间之前的红包数据需要在s_user_redenvelope表里查询
                    if($val['add_time']>='2017-04-21') {
                        $red_amount = $val['red_amount'];
                    } else {
                        $red_amount = M("userRedenvelope")->where(array('user_id'=>$val['user_id'],'recharge_no'=>$val['recharge_no']))->getField('amount');
                    }
                }
                
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $nowYear - $year);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['title']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['due_capital']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, number_format($val['due_interest'], 2));
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $red_amount);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $coupon_income);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, date("Y-m-d",$_t));
                $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, date("H:i:s",$_t));
                $objPHPExcel->getActiveSheet()->setCellValue("L".$pos, date("Y-m-d",strtotime($val['due_time'])));
                $objPHPExcel->getActiveSheet()->setCellValue("M".$pos, $des);                
                $objPHPExcel->getActiveSheet()->setCellValue("N".$pos, $tag_name);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$pos, $val['financing']);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$pos, $group_name);
                
                $pos += 1;
                $n++;
            }    
            unset($list);    
            ob_end_clean();//清除缓冲区,避免乱码    
            header("Content-Type: application/vnd.ms-excel");    
            header('Content-Disposition: attachment;filename="销售明细('.date("Y-m-d").').xls"');    
            header('Cache-Control: max-age=0');    
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');    
            $objWriter->save('php://output');    
            exit;    
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => "没有记录"));
        }
    }





    /**
     * 每日销售列表导出Excel
     */
    public function daysales_export(){
        vendor('PHPExcel.PHPExcel');

        $datetime = I('get.dt', '', 'strip_tags');
        
        if(!$datetime) $datetime = date('Y-m-d', strtotime('-1 days'));
        
        $start_time = $datetime.' 00:00:00.0000';

        $end_time = $datetime.' 23:59:59.999000';

        $projectObj = M('Project');
        $rechargeLogObj = M('RechargeLog');
        $userDueDetailObj = M("UserDueDetail");
        $userWalletRecordsObj = M("UserWalletRecords");

        if($datetime < date('Y-m-d', time())) {
            $cacheData = F('project_daysales_'.str_replace('-', '_', $datetime));
        }
        
        if(!$cacheData){

            $list = $rechargeLogObj->field('recharge_no,user_id,project_id,sum(amount) totlecapital')->where("status = 2 and modify_time>='".$start_time."' and modify_time<='".$end_time."'")->group('project_id')->order('project_id')->select();

            $totleMoney = 0;
            $totleMoneyMore = 0;
            $totleGhostMoney = 0; // 幽灵账户购买金额
            $totalRedAmount = 0; // 总红包
            foreach($list as $key => $val){

                $list[$key]['project'] = $projectObj->field('id,title,amount,fid,remark,fid,new_preferential,project_group_id')->where(array('id'=>$val['project_id']))->find();
                
                $list[$key]['project']['financing'] = M('financing')->where('id='.$list[$key]['project']['fid'])->getField('name');
                
                $list[$key]['group_name'] = M('projectGroup')->where('id='.$list[$key]['project']['project_group_id'])->getField('name');
                
                $list[$key]['fid'] = $list[$key]['project']['fid'];
                
                $list[$key]['project_title_id'] = preg_replace('/\D/s', '', $list[$key]['project']['title']);
                
                $more_money = $userDueDetailObj->where("project_id=".$val['project_id']." and add_time<='".$end_time."'")->sum('due_capital') - $list[$key]['project']['amount'];

                if($more_money < 0) $more_money = 0;

                $list[$key]['money_more'] = $more_money;

                $totleMoney += $val['totlecapital'];

                $totleMoneyMore += $more_money;

                $ghost_money = $userDueDetailObj->where("user_id=0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('due_capital');

                $list[$key]['ghost_money'] = $ghost_money;

                $totleGhostMoney += $ghost_money;

                //取红包金额
                $where = array(
                    'project_id' => $val['project_id'],
                    // 'user_id'=> $val['user_id'],
                    //  'status' => 1,
                    'add_time'=> array('between',array($start_time,$end_time))
                    // 'add_time'=> array('elt',$end_time),
                );
//                    $redAmount = M('userDueDetail')->where($where)->getField(' sum(red_amount) as amount_sum ');
                $redAmount = M('userDueDetail')->where($where)->sum('red_amount');
                $redAmount = isset($redAmount)?$redAmount:0;

                $list[$key]['red_amount'] = $redAmount;

                $totalRedAmount +=$redAmount;

                $wallet_money = $rechargeLogObj->where("type=3 and status=2 and user_id>0 and project_id=".$val['project_id']." and modify_time>='".$start_time."' and modify_time<='".$end_time."'")->sum('amount');

                $list[$key]['wallet_money'] = $wallet_money;

                $totleWalletMoney += $wallet_money;
            }
            
            if($datetime < date('Y-m-d', time())){
                $rows = array(
                    'list' => $list,
                    'totle_money' => $totleMoney,
                    'totle_money_more' => $totleMoneyMore,
                    'totle_ghost_money' => $totleGhostMoney,
                    'totle_red_amount'=>$totalRedAmount,
                );
                F('project_daysales_'.str_replace('-', '_', $datetime), $rows);
            }
        }else{
            $list = $cacheData['list'];
            foreach($list as $key => $val){
                $list[$key]['remark'] = $projectObj->where(array('id'=>$val['project_id']))->getField('remark');
            }
            $totleMoney = $cacheData['totle_money'];
            $totleMoneyMore = $cacheData['totle_money_more'];
            $totleGhostMoney = $cacheData['totle_ghost_money'];
            $totalRedAmount = $cacheData['totle_red_amount'];
        }

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('日销售额')
            ->setCellValue("A1", "产品名称")
            ->setCellValue("B1", "募集款数(元)")
            ->setCellValue("C1", "超过部分(元)")
            ->setCellValue("D1", "幽灵账户(元)")
            ->setCellValue("E1", "融资人")
            ->setCellValue("F1", "日期")
            ->setCellValue('G1','期数')
            ->setCellValue('H1','标签')
            ->setCellValue('I1','分组名称')
            ->setCellValue('J1','红包金额');//bag_amount
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

        // 设置列表值
        $pos = 2;
        
        $list = $this->multi_array_sort($list,'fid');
        
        foreach ($list as $key => $val) {
            /*
            if($val['project']['fid'] == 1) {
                $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'I'.$pos)->getFont()->getColor()->setARGB('FF00FF00');
            } else if($val['project']['fid'] == 2) {
                $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'I'.$pos)->getFont()->getColor()->setARGB('FFFF0000');
            } else if($val['project']['fid'] == 3) {
                $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'I'.$pos)->getFont()->getColor()->setARGB('FF0000FF');
            } else if($val['project']['fid'] == 4){
                $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'I'.$pos)->getFont()->getColor()->setARGB('FF000000');
            }          
            */
            
            $tag_name = $this->getTagName($val['project']['new_preferential']);
                        
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['project']['title']);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($val['totlecapital'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($val['money_more'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['ghost_money'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['project']['financing']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $datetime);//$val['project']['remark']
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['project_title_id']);
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $tag_name);//$val['project']['remark']
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $val['group_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $val['red_amount']);
            $pos += 1;
        }
        $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, '合计');
        $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($totleMoney, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($totleMoneyMore, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($totleGhostMoney, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, number_format($totalRedAmount, 2));

        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="日销售额('.$datetime.').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    //比较数组值
    private function multi_array_sort($multi_array,$sort_key,$sort=SORT_DESC){
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort($key_array,$sort,$multi_array);
        return $multi_array;
    }
    
    /**
     * 每日销售连连对账导出Excel-(投资+充值)
     */
    public function daysales_lianlian_export(){ //pay_type
        vendor('PHPExcel.PHPExcel');
        $datetime = I('get.dt', '', 'strip_tags');
        if(!$datetime) $datetime = date('Y-m-d', strtotime('-1 days'));
        $start_time = $datetime.' 00:00:00.0000';
        $end_time = $datetime.' 23:59:59.999000';

        if($datetime < date('Y-m-d', time())) $cacheData =''; //F('project_daysales_lianlian_'.str_replace('-', '_', $datetime));
        if(!$cacheData){
            //钱包充值记录
            $wallet_sql = "SELECT b.`recharge_no`,b.`value`,b.`add_time`,a.`real_name`,a.`username`FROM `s_user_wallet_records`  b,`s_user` a WHERE b.`add_time`>='".$start_time."' AND b.`add_time`<='".$end_time."' AND b.`type` = 1 AND b.`pay_status` = 2 AND b.`user_id` = a. id";//AND b.`pay_type` = 1
            $wallet_recharge = M()->query($wallet_sql);
            //投资记录
            $recharge_sql = "SELECT k.`recharge_no`,k.`amount`,k.`add_time`, m.`real_name`,m.`username`, n.`title` FROM `s_recharge_log` k,`s_user` m,`s_project` n WHERE k.`add_time`>='".$start_time."' AND k.`add_time`<='".$end_time."' AND k.`status` = 2 AND k.`type` = 1 AND k.`user_id` = m.`id` AND k.`project_id` = n.`id`";

			$invest_list = M()->query($recharge_sql);
            //组合
            $trade_list = array(
                'wallet'=>$wallet_recharge,
                'invest'=>$invest_list,
            );

            if($datetime < date('Y-m-d', time())){
                $rows = array(
                    'list' => $trade_list
                );
                F('project_daysales_lianlian_'.str_replace('-', '_', $datetime), $rows);
            }
        }else{
            $trade_list = $cacheData['list'];
        }

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('日销售额连连对账')->setCellValue("A1", "商品名称")->setCellValue("B1", "商家订单号")
            ->setCellValue("C1", "交易金额(元)")->setCellValue("D1", "用户姓名")->setCellValue("E1", "用户账号")
            ->setCellValue("F1", "交易时间");
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

		$wallet_lianlian_total = 0;//钱包充值
		$invest_lianlian_total = 0;//投资
        // 设置列表值 - 钱包充值
        $pos = 2;
        foreach ($trade_list['wallet'] as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, '钱包充值');
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['recharge_no']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($val['value'],2));
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['username']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
            $pos += 1;
			$wallet_lianlian_total+=$val['value'];
        }
        // 设置列表值 - 投资
        foreach ($trade_list['invest'] as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['title']);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['recharge_no']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($val['amount'],2));
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['username']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
            $pos += 1;
			$invest_lianlian_total+=$val['amount'];
        }
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, '合计');
        $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($wallet_lianlian_total+$invest_lianlian_total,2));
        $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, '');
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="日销售额连连对账('.$datetime.').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    /**
     * 银行卡管理
     */
    public function bank(){

        //var_dump(getRedis()->get('bank_list'));
        
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $key = urldecode(I('get.key', '', 'strip_tags')); // 银行卡名称
            $count = 10; // 每页显示条数

            $bankObj = M('Bank');

            $conditions = array();
            if($key){
                $key = urldecode($key);
                $conditions = "bank_code='".$key."' or bank_name='".$key."'";
            }

            $counts = $bankObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $bankObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

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
            redirect(C('ADMIN_ROOT') . '/project/bank'.$quest);
        }
    }

    /**
     * 添加银行卡信息
     */
    public function bank_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $key = I('get.key', '', 'strip_tags');

            $params = array(
                'page' => $page,
                'key' => $key,
            );
            $this->assign('params', $params);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $key = I('post.key', '', 'strip_tags');
            $bank_code = trim(I('post.bank_code', '', 'strip_tags'));
            $bank_name = trim(I('post.bank_name', '', 'strip_tags'));
            $support_area = trim(I('post.support_area', '', 'strip_tags'));
            $support_card_type = trim(I('post.support_card_type', '', 'strip_tags'));
            $limit_times = I('post.limit_times', 0, 'strip_tags');
            $limit_day = I('post.limit_day', 0, 'strip_tags');
            $limit_month = I('post.limit_month', 0, 'strip_tags');
            $bank_status = I('post.bank_status', 1, 'int');

            if(!$bank_code) $this->ajaxReturn(array('status'=>0,'info'=>'银行编号不能为空'));
            if(!$bank_name) $this->ajaxReturn(array('status'=>0,'info'=>'银行名称不能为空'));
            if(!$support_card_type) $this->ajaxReturn(array('status'=>0,'info'=>'银行卡类型不能为空'));

            $bankObj = M('Bank');
            if($bankObj->where(array('bank_code'=>$bank_code,'bank_name'=>$bank_name,'support_card_type'=>$support_card_type))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同银行卡信息'));

            
            $_icon = '';
            $_small_icon = '';
            
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
                    
                    $_icon = $info['icon']['savepath'].$info['icon']['savename'];
                    
                    \Think\Log::write($_icon,'INFO');
                    
                    $ossPath = 'Uploads/focus/'.$_icon;
                    $file = C('localPath').$_icon;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'大图片oss图片上传失败'));
                    }
                    
                    $_small_icon = $info['small_icon']['savepath'].$info['small_icon']['savename'];
                    $ossPath = 'Uploads/focus/'.$_small_icon;
                    $file = C('localPath').$_small_icon;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'大图片oss图片上传失败'));
                    }
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择图标'));
            }
            
            
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'bank_code' => $bank_code,
                'bank_name' => $bank_name,
                'support_area' => $support_area,
                'support_card_type' => $support_card_type,
                'limit_times' => $limit_times,
                'limit_day' => $limit_day,
                'limit_month' => $limit_month,
                'add_time' => $time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'icon'=>$_icon,
                'small_icon'=>$_small_icon,
                'status' => $bank_status
            );
            $rid = $bankObj->add($rows);
            if(!$rid) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            
            
            //getRedis()->del('bank_list');
            
            $quest = '';
            if($key) $quest .= '/s/' . $key;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/bank/p/'.$page.$quest));
        }
    }

    /**
     * 编辑银行卡信息
     */
    public function bank_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $key = I('get.key', '', 'strip_tags');

            $params = array(
                'page' => $page,
                'key' => $key,
            );
            $this->assign('params', $params);

            $bankObj = M('Bank');
            $detail = $bankObj->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('银行卡信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $key = I('post.key', '', 'strip_tags');
            $id = I('post.id', 0, 'int');
            $bank_code = trim(I('post.bank_code', '', 'strip_tags'));
            $bank_name = trim(I('post.bank_name', '', 'strip_tags'));
            $support_area = trim(I('post.support_area', '', 'strip_tags'));
            $support_card_type = I('post.support_card_type', '', 'strip_tags');
            $limit_times = I('post.limit_times', 0, 'strip_tags');
            $limit_day = I('post.limit_day', 0, 'strip_tags');
            $limit_month = I('post.limit_month', 0, 'strip_tags');
            $bank_status = I('post.bank_status', 1, 'int');
            
            
            $_icon = I('post.icon', '', 'strip_tags');
            $_small_icon = I('post.small_icon', '', 'strip_tags');
            

            if(!$bank_code) $this->ajaxReturn(array('status'=>0,'info'=>'银行编号不能为空'));
            if(!$bank_name) $this->ajaxReturn(array('status'=>0,'info'=>'银行名称不能为空'));
            if(!$support_card_type) $this->ajaxReturn(array('status'=>0,'info'=>'银行卡类型不能为空'));

            $bankObj = M('Bank');
            // 检查版本号是否存在
            if($bankObj->where("bank_code='".$bank_code."' and bank_name='".$bank_name."' and support_card_type='".$support_card_type."' and id<>".$id)->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同银行卡信息'));

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
            
                    $_icon = $info['new_icon']['savepath'].$info['new_icon']['savename'];
                    
                    \Think\Log::write($_icon,'INFO');
            
                    $ossPath = 'Uploads/focus/'.$_icon;
                    $file = C('localPath').$_icon;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'大图片oss图片上传失败'));
                    }
            
                    $_small_icon = $info['new_small_icon']['savepath'].$info['new_small_icon']['savename'];
                    
                    \Think\Log::write($_small_icon,'INFO');
                    
                    $ossPath = 'Uploads/focus/'.$_small_icon;
                    $file = C('localPath').$_small_icon;
                    $res = uploadToOss($ossPath,$file);
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $this->ajaxReturn(array('status'=>0,'info'=>'大图片oss图片上传失败'));
                    }
                }
            }
            
            
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'bank_code' => $bank_code,
                'bank_name' => $bank_name,
                'support_area' => $support_area,
                'support_card_type' => $support_card_type,
                'limit_times' => $limit_times,
                'limit_day' => $limit_day,
                'limit_month' => $limit_month,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'small_icon' => $_small_icon,
                'icon' => $_icon,
                'status' => $bank_status
            );
            if(!$bankObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            //getRedis()->del('bank_list');
            $quest = '';
            if($key) $quest .= '/s/' . $key;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/bank/p/'.$page.$quest));
        }
    }

    /**
     * 删除银行卡信息
     */
    public function bank_delete(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $bankObj = M('Bank');
        $detail = $bankObj->where(array('id'=>$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'银行卡信息不存在或已被删除'));
        if(!$bankObj->where(array('id'=>$id))->delete()) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        //getRedis()->del('bank_list');
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 产品类型管理
     */
    public function project_type(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数

            $constantObj = M('Constant');
            $parentId = $constantObj->where(array('cons_key'=>'project_type'))->getField('id');
            if(!$parentId) {
                $this->error('产品分类信息不存在');exit;
            }
            $counts = $constantObj->where(array('parent_id'=>$parentId))->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $constantObj->where(array('parent_id'=>$parentId))->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

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
     * 添加产品类型
     */
    public function project_type_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $cons_key = trim(I('post.cons_key', '', 'strip_tags'));
            $cons_value = trim(I('post.cons_value', '', 'strip_tags'));
            $cons_desc = trim(I('post.cons_desc', '', 'strip_tags'));

            if(!$cons_key) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类标识不能为空'));
            if(!$cons_value) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类名称不能为空'));

            $constantObj = M('Constant');
            $parentId = $constantObj->where(array('cons_key'=>'project_type'))->getField('id');
            if(!$parentId) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类信息不存在'));
            if($constantObj->where(array('cons_value'=>$cons_value,'parent_id'=>$parentId))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的产品分类信息'));

            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'parent_id' => $parentId,
                'cons_key' => $cons_key,
                'cons_value' => $cons_value,
                'cons_desc' => $cons_desc,
                'add_time' => $time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            $rid = $constantObj->add($rows);
            if(!$rid) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/project_type/p/'.$page));
        }
    }

    /**
     * 编辑产品类型
     */
    public function project_type_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');

            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);

            $constantObj = M('Constant');
            $parentId = $constantObj->where(array('cons_key'=>'project_type'))->getField('id');
            $detail = $constantObj->where(array('id'=>$id,'parent_id'=>$parentId))->find();
            if(!$detail){
                $this->error('产品分类信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $id = I('post.id', 0, 'int');
            $cons_key = trim(I('post.cons_key', '', 'strip_tags'));
            $cons_value = trim(I('post.cons_value', '', 'strip_tags'));
            $cons_desc = trim(I('post.cons_desc', '', 'strip_tags'));

            if(!$cons_key) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类标识不能为空'));
            if(!$cons_value) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类名称不能为空'));

            $constantObj = M('Constant');
            $parentId = $constantObj->where(array('cons_key'=>'project_type'))->getField('id');
            if(!$parentId) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类信息不存在'));
            $parentId = $constantObj->where(array('cons_key'=>'project_type'))->getField('id');
            // 检查版本号是否存在
            if($constantObj->where("id<>".$id." and cons_value='".$cons_value."' and parent_id=".$parentId)->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的产品分类信息'));

            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'cons_key' => $cons_key,
                'cons_value' => $cons_value,
                'cons_desc' => $cons_desc,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!$constantObj->where(array('id'=>$id,'parent_id'=>$parentId))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/project_type/p/'.$page));
        }
    }

    /**
     * 删除产品类型
     */
    public function project_type_delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $this->ajaxReturn(array('status'=>0,'info'=>'当前不开放删除功能'));
        exit; // 暂时不开放删除产品分类
        $id = I('post.id', 0, 'int');

        $constantObj = M('Constant');
        $projectObj = M('Project');
        $parentId = $constantObj->where(array('cons_key'=>'project_type'))->getField('id');
        if(!$parentId) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类信息不存在'));
        $detail = $constantObj->where(array('id'=>$id,'parent_id'=>$parentId))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'产品分类信息不存在或已被删除'));
        // 查看是否有关联的产品信息
        if($projectObj->where(array('type'=>$id))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该分类下还有未彻底删除的产品,请删除该分类下所有产品后再次删除'));
        if(!$constantObj->where(array('id'=>$id))->delete()) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 产品内容模板列表
     */
    public function project_template(){
        if(!IS_POST){
            $type = I('get.type', 1, 'int');
            $projectTemplateObj = M('ProjectTemplate');
            $list = $projectTemplateObj->where(array('type'=>$type))->select();
            $this->assign('list', $list);
            $this->display();
        }else{

        }
    }

    /**
     * 产品模板内容详细
     */
    public function project_template_detail(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $projectTemplateObj = M('ProjectTemplate');
        $detail = $projectTemplateObj->where(array('id'=>$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'模板内容不存在或已被删除'));
        $this->ajaxReturn(array('status'=>1, 'data'=>$detail));
    }

    /**
     * 添加产品内容模板
     */
    public function project_template_add(){
        if(!IS_POST || !IS_AJAX) exit;

        $name = trim(I('post.name', '', 'strip_tags'));
        $content = $_POST['content'];
        $type = I('post.type', 1, 'int');

        $projectTemplateObj = M('ProjectTemplate');
        $time = date('Y-m-d H:i:s').'.'.getMillisecond();
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $rows = array(
            'title' => $name,
            'content' => $content,
            'type' => $type,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        if(!$projectTemplateObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 删除产品内容模板
     */
    public function project_template_delete(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $projectTemplateObj = M('ProjectTemplate');
        if(!$projectTemplateObj->where(array('id'=>$id))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'模板信息不存在或已被删除'));
        if(!$projectTemplateObj->where(array('id'=>$id))->delete()) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 标一键推送通知和发布系统消息
     */
	public function forecast_push(){
		if(IS_POST){
			$projectObj = M('Project');
			$messageGroupObj = M('MessageGroup');
			$messageObj = M('Message');
			//标的ID
			$id = I("post.id",0,'int');
			if(!$id){
				$this->ajaxReturn(array('status'=>0,'info'=>'系统参数有误，请联系系统管理员'));
			}
			$projectInfo = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();

			if(!$projectInfo) {

			    $this->ajaxReturn(array('status'=>0,'info'=>'产品信息不存在或已被删除'));
			}
			if($projectInfo['status'] > 2) {
			    $this->ajaxReturn(array('status'=>0,'info'=>'已经结束的标无法推送预告'));
			}
			//系统消息标题
			$title = I("post.title",'','htmlspecialchars');
			if(empty($title)){
				$this->ajaxReturn(array('status'=>0,'info'=>'系统消息标题必填'));
			}
			// 检查该标是否已发过预告
			$already_push_status = $messageObj->where(array('title'=>$title,'type'=>1,'is_delete'=>0))->getField('id');
			if($already_push_status) {
			    $this->ajaxReturn(array('status'=>0,'info'=>'该标已发过预告,请勿重复发送'));
			}
			//系统消息摘要
			$summary = I("post.summary",'','htmlspecialchars');
			//系统消息详细内容
			$description = I("post.description",'','htmlspecialchars');
			if(empty($description)){
				$this->ajaxReturn(array('status'=>0,'info'=>'系统消息详细内容必填'));
			}
			//客户端推送信息
			$push_msg  = I("post.push_msg",'','htmlspecialchars');
			if(empty($push_msg)){
				$this->ajaxReturn(array('status'=>0,'info'=>'客户端推送信息必填'));
			}
			//发送信息逻辑处理
			//1.0 添加产品预告的系统消息
			$time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
			$uid = $_SESSION[ADMIN_SESSION]['uid'];
			$username = $_SESSION[ADMIN_SESSION]['username'];
			$messageGroupObj->startTrans();
			$rows = array(
				'type' => 1,
				'status' => 1,
				'is_delete' => 0,
				'add_time' => $time,
				'add_user_id' => $uid,
				'modify_time' => $time,
				'modify_user_id' => $uid,
			);
			$g_rid = $messageGroupObj->add($rows);
			if(!$g_rid) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
			$rows2 = array(
				'group_id' => $g_rid,
				'title' => $title,
				'summary' => $summary,
				'description' => $description,
				'type' => 1,
				'status' => 1,
				'author' => $username,
				'is_delete' => 0,
				'add_time' => $time,
				'add_user_id' => $uid,
				'modify_time' => $time,
				'modify_user_id' => $uid,
			);
			if(!$messageObj->add($rows2)) {
				$messageGroupObj->rollback();
				$this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
			}
			$messageGroupObj->commit();
			//2.0 发送推送信息
			$pushTitle = $push_msg;
			$extra['action'] = 3;
			pushMsg($pushTitle, '', '', $extra,0);
			pushMsg($pushTitle, '', '', $extra,1);
			pushMsg($pushTitle, '', '', $extra,2);
			pushMsg($pushTitle, '', '', $extra,3);
			pushMsg($pushTitle, '', '', $extra,4);
			$this->ajaxReturn(array('status'=>1,'link'=>'/project/index'));

		}else{
			$id = I("get.id",0,"int");//标ID
			if(!$id){
				$this->error("参数不对,请联系系统管理员!!");
			}
			$this->assign("id",$id);
			$this->display("forecast_push");
		}

	}
    public function onenotice(){
        if(!IS_POST || !IS_AJAX) exit;

        $projectObj = M('Project');
        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $id = I('post.id', 0, 'int');
        $projectInfo = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$projectInfo) $this->ajaxReturn(array('status'=>0,'info'=>'产品信息不存在或已被删除'));
        if($projectInfo['status'] > 2) $this->ajaxReturn(array('status'=>0,'info'=>'已经结束的标无法推送预告'));

        // 检查该标是否已发过预告
        $title = $projectInfo['title'].'上线预告';
        $summary = '期限'.$projectInfo['duration'].'天，金额'.number_format($projectInfo['amount']).'元，年化率'.$projectInfo['user_interest'].'％';
        $description = date('m月d日H点i分', strtotime($projectInfo['start_time'])).'，'.$projectInfo['title'].'上线预告，年化率'.$projectInfo['user_interest'].'％，期限'.$projectInfo['duration'].'天，'.$projectInfo['money_min'].'元起购，小伙伴们即刻申购吧！！';
        if($messageObj->where(array('title'=>$title,'type'=>1,'is_delete'=>0))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该标已发过预告,请勿重复发送'));
        // 添加产品预告的系统消息
        $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $username = $_SESSION[ADMIN_SESSION]['username'];
        $messageGroupObj->startTrans();
        $rows = array(
            'type' => 1,
            'status' => 1,
            'is_delete' => 0,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        $g_rid = $messageGroupObj->add($rows);
        if(!$g_rid) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        $rows2 = array(
            'group_id' => $g_rid,
            'title' => $title,
            'summary' => $summary,
            'description' => $description,
            'type' => 1,
            'status' => 1,
            'author' => $username,
            'is_delete' => 0,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        if(!$messageObj->add($rows2)) {
            $messageGroupObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }
        $messageGroupObj->commit();

        // 发送推送信息
        $pushTitle = date('H点i分', strtotime($projectInfo['start_time'])).$projectInfo['title'].'上线预告，金额'.number_format($projectInfo['amount']).'元！！';
        $extra['action'] = 3;
        pushMsg($pushTitle, '', '', $extra,0);
        pushMsg($pushTitle, '', '', $extra,1);
        pushMsg($pushTitle, '', '', $extra,2);
        pushMsg($pushTitle, '', '', $extra,3);
        pushMsg($pushTitle, '', '', $extra,4);

        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 标一键发布系统消息
     */
    public function onesystem(){
        if(!IS_POST || !IS_AJAX) exit;

        $projectObj = M('Project');
        $messageGroupObj = M('MessageGroup');
        $messageObj = M('Message');

        $id = I('post.id', 0, 'int');
        $projectInfo = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$projectInfo) $this->ajaxReturn(array('status'=>0,'info'=>'产品信息不存在或已被删除'));
        if($projectInfo['status'] > 2) $this->ajaxReturn(array('status'=>0,'info'=>'已经结束的标无法发送预告'));

        // 检查该标是否已发过预告
        $title = $projectInfo['title'].'上线预告';
        $summary = '期限'.$projectInfo['duration'].'天，金额'.number_format($projectInfo['amount']).'元，年化率'.$projectInfo['user_interest'].'％';
        $description = date('m月d日H点i分', strtotime($projectInfo['start_time'])).'，'.$projectInfo['title'].'上线预告，年化率'.$projectInfo['user_interest'].'％，期限'.$projectInfo['duration'].'天，'.$projectInfo['money_min'].'元起购，小伙伴们即刻申购吧！！';
        if($messageObj->where(array('title'=>$title,'type'=>1,'is_delete'=>0))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该标已发过预告,请勿重复发送'));
        // 添加产品预告的系统消息
        $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $username = $_SESSION[ADMIN_SESSION]['username'];
        $messageGroupObj->startTrans();
        $rows = array(
            'type' => 1,
            'status' => 1,
            'is_delete' => 0,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        $g_rid = $messageGroupObj->add($rows);
        if(!$g_rid) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        $rows2 = array(
            'group_id' => $g_rid,
            'title' => $title,
            'summary' => $summary,
            'description' => $description,
            'type' => 1,
            'status' => 1,
            'author' => $username,
            'is_delete' => 0,
            'add_time' => $time,
            'add_user_id' => $uid,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        if(!$messageObj->add($rows2)) {
            $messageGroupObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }
        $messageGroupObj->commit();

        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 还本付息短信通知
     */
    public function paysms(){
        if(!IS_POST || !IS_AJAX) exit;
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
            114 => '可用参数组个数错误（小于最小设定值或者大于1000）',
            115 => '自动审核驳回',
            116 => '签名不合法，未带签名（用户必须带签名的前提下）',
            117 => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',
            118 => '用户没有相应的发送权限',
            119 => '用户已过期',
        );

        $rid = I('post.id', 0, 'int');
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userBankObj = M('UserBank');

        $repayDetail = $repaymentDetailObj->where(array('id'=>$rid,'is_sms'=>0))->find();
        if(!$repayDetail) $this->ajaxReturn(array('status'=>0,'info'=>'请勿重复短信通知'));
        $projectInfo = $projectObj->field('id,title,type,start_time,end_time,advance_end_time')->where(array('id'=>$repayDetail['project_id']))->find();
        $project_name = $projectInfo['title'];
        $msg = '尊敬的用户，您申购的'.$project_name.'本息{$var}元正在往您尾号为{$var}的{$var}卡狂奔而去，会在1~2个工作日内到账，请注意查收！';
        if($projectInfo['type'] == 104 || $projectInfo['type'] == 109 || $projectInfo['type'] == 110){ // 基金类产品,获取净值数据
            $projectModelFundObj = M("ProjectModelFund");
            $fundDetailObj = M("FundDetail");
            $detailExt = $projectModelFundObj->where(array('project_id'=>$projectInfo['id']))->find();
            $percent = 0; // 基金类收益率
            if($detailExt){
                if(!$detailExt['enter_time']){
                    $timeStart = date('Y-m-d', strtotime($projectInfo['start_time'])); // 产品净值进入时间点
                }else{
                    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                }
                if(!$projectInfo['advance_end_time']){
                    $timeEnd = date('Y-m-d', strtotime($projectInfo['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }else{
                    $timeEnd = date('Y-m-d', strtotime($projectInfo['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }
                $today = date('Y-m-d', time()); // 当前时间点
                if($today < $timeEnd) $timeEnd = $today;
                $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=".$detailExt['fund_id']." and datetime>='".$timeStart."' and datetime<='".$timeEnd."'")->order('datetime asc')->select(); // 关联基金净值列表
                if(count($fundList) > 1){ // 两个净值点以上
                    $fundStart = $fundList[0]['val']; // 起始净值
                    $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                    switch($projectInfo['type']){
                        case 104: // 打新股,收益超过18%分成
                            if($fundEnd - $fundStart > 0){
                                if(($fundEnd - $fundStart)/$fundStart > 0.18){ // 分成
                                    $percent = 0.18 + (($fundEnd - $fundStart)/$fundStart - 0.18)/2;
                                }else{
                                    $percent = ($fundEnd - $fundStart)/$fundStart;
                                }
                            }
                            break;
                        case 109: // B类基金,杠杆0.2
                            if($fundEnd - $fundStart > 0){
                                $fundEndB = ($fundEnd - $fundStart)*0.2 + $fundStart;
                                $percent = ($fundEndB - $fundStart)/$fundStart;
                            }
                            break;
                        case 110: // A类基金,杠杆2.6
                            if($fundEnd - $fundStart > 0){
                                $fundEndA = ($fundEnd - $fundStart)*2.6 + $fundStart;
                                $percent = ($fundEndA - $fundStart)/$fundStart;
                            }else if($fundEnd - $fundStart < 0){
                                $fundEndA = ($fundEnd - $fundStart)*3 + $fundStart;
                                $percent = ($fundEndA - $fundStart)/$fundStart;
                            }
                            break;
                    }
                }
            }
        }else if($projectInfo['type'] == 148){ // 搏息宝
            $percent = cal_fund_percent($projectInfo['id']);
        }
        $list = $userDueDetailObj->where("repay_id=".$rid." and from_wallet=0 and to_wallet=0")->select();
        $params = '';
        if($projectInfo['type'] == 104 || $projectInfo['type'] == 109 || $projectInfo['type'] == 110) { // 基金类产品,获取净值数据
            foreach ($list as $key => $val) {
                if ($val['user_id'] > 0) {
                    $userBankInfo = $userBankObj->where(array('bank_card_no' => $val['card_no'], 'has_pay_success' => 2))->find();
                    $params .= ';' . $userBankInfo['mobile'] . ',' . round($val['due_capital'] + $val['due_capital'] * $percent, 2) . ',' . substr($val['card_no'], strlen($val['card_no']) - 4) . ',' . $userBankInfo['bank_name'];
                }
            }
        }else if($projectInfo['type'] == 148){ // 搏息宝
            foreach ($list as $key => $val) {
                if ($val['user_id'] > 0) {
                    $userBankInfo = $userBankObj->where(array('bank_card_no' => $val['card_no'], 'has_pay_success' => 2))->find();
                    $params .= ';' . $userBankInfo['mobile'] . ',' . round($val['due_capital'] + $val['due_capital']*$percent*(count_days(date('Y-m-d', strtotime($val['due_time'])).' 08:00:00', date('Y-m-d', strtotime($val['start_time'])).' 08:00:00')+1)/365, 2) . ',' . substr($val['card_no'], strlen($val['card_no']) - 4) . ',' . $userBankInfo['bank_name'];
                }
            }
        }else{
            foreach($list as $key => $val){
                if($val['user_id'] > 0){
                    $userBankInfo = $userBankObj->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->find();
                    $params .= ';'.$userBankInfo['mobile'].','.$val['due_amount'].','.substr($val['card_no'],strlen($val['card_no'])-4).','.$userBankInfo['bank_name'];
                }
            }
        }
        if($params){
            $params = mb_substr($params, 1, mb_strlen($params) - 1, 'utf-8');

            $smsUrl = 'http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpVarSM?account='.C('SMS_INTDERFACE.account').'&pswd='.C('SMS_INTDERFACE.pswd'); // 变量短信发送
            $smsUrl .= '&msg='.$msg;
            $smsUrl .= '&params='.$params;
            $smsUrl .= '&needstatus=true';
            //$this->ajaxReturn(array('status'=>0,'info'=>$params));

            $smsData = file_get_contents($smsUrl);

            $arr = explode("\n", $smsData);
            if($arr) $arr[0] = explode(',', $arr[0]);
            if($arr[0][1] != 0) $this->ajaxReturn(array('status'=>0, 'info'=>$arr[0][1].':'.$errorMsg[$arr[0][1]]));

            $sms_msgid = trim($arr[1]);
			if($sms_msgid){
				$repaymentDetailObj->where(array('id'=>$rid))->save(array('sms_msgid'=>$sms_msgid,'is_sms'=>1));
				//$this->ajaxReturn(array('status'=>0,'info'=>$arr[1].'|'.$sms_msgid.'|'.$repaymentDetailObj->getLastSql()));
				$this->ajaxReturn(array('status'=>1,'info'=>'『短信通知』操作成功~短信回执编号:'.$sms_msgid));
			}else{
				$this->ajaxReturn(array('status'=>0,'info'=>'『短信通知』操作失败~短信回执编号无法获取'));
			}

        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'没有可发送的信息'));
        }
    }

    /**
     * 检查短信到达情况
     */
    public function checksms(){
        if(!IS_POST){
            $smsStatus = array(
                'DELIVRD' => '短消息转发成功',
                'EXPIRED' => '短消息超过有效期',
                'UNDELIV' => '短消息是不可达的',
                'UNKNOWN' => '未知短消息状态',
                'REJECTD' => '短消息被短信中心拒绝',
                'DTBLACK' => '目的号码是黑名单号码',
                'ERR:104' => '系统忙',
                'REJECT'  => '审核驳回',
                'MBBLACK' => '本地黑名单',
                'DBBLACK' => '网关黑名单',
                'TIMEOUT' => '超时',
                'NOROUTER' => '无通道',
                'ROUTEERR' => '网络超时',
                'MK:0000' => '广西屏蔽',
                'MK:0001' => '不存在的用户',
                'MN:0001' => '广西屏蔽',
                'MK:0002' => '移动内部网关问题',
                'MK:0005' => '停机',
                'MB:1041' => '河北屏蔽',
                'MK:0015' => '河南屏蔽',
                'MK:0012' => '云南屏蔽',
                'MK:0075' => '账号不存在',
                'MK:0036' => '四川屏蔽',
                'NP:1243' => '用户转网至联通',
                'MK:0013' => '停机',
                'HTTPERR:500' => '异常',
                'HTTPERROR' => '异常',
                'HTTPException' => '异常',
                'HTTPResp:102' => '密码错',
                'HTTPResp:105' => '屏蔽词',
                'HTTPResp:108' => '手机号超过长度',
                'HTTPResp:109' => '无发送条数',
                'HTTPResp:Faild' => '异常',
                'DB:0108' => '业务暂停服务',
                'DB:0142' => '超过日最大发送 MT 数量',
                'IB:0008' => '网关流速控制错',
                'IB:0011' => '外省网关数据不同步',
                'DB:0144' => '网关黑名单',
                'ID:0011' => '参数错误/数据未同步',
                'MK:0014' => '信息安全鉴权消息内容错误',
                'MK:0115' => '垃圾短信被拦截',
                'MK:0010' => '发送失败 因用户关机或不在服务区里，超出短信最大保存时间',
                'MK:0023' => '用户关机',
                'MK:1041' => '主叫用户提交的短消息数超过此用户的最大提交数',
                'SGIP:12' => '序列号错误',
                '其他'    => '网关内部状态',
            );
            $id = I('get.id', 0, 'int');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M("UserDueDetail");
            $projectObj = M('Project');
            $userObj = M('User');
            $smsStatusObj = M('SmsStatus');
            $detail = $repaymentDetailObj->field('id,project_id,sms_msgid')->where(array('id'=>$id))->find();
            if(!$detail['sms_msgid']){
                header("Content-type: text/html; charset=utf-8");
                echo '该还款记录没有短信回执记录';exit;
            }
            // 获取购买该标的用户列表
            $count = 10;
            $counts = $userDueDetailObj->where(array('repay_id'=>$id))->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $userDueDetailObj->field('id,user_id,sms_msgid')->where("repay_id=".$id." and to_wallet=0 and from_wallet=0")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $userInfo = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                $list[$key]['username'] = $userInfo['username'];
                $list[$key]['realname'] = $userInfo['real_name'];
                if(!$val['sms_msgid']){
                    $list[$key]['status'] = $smsStatusObj->where(array('msgid'=>$detail['sms_msgid'],'mobile'=>$userInfo['username']))->getField('status');
                }else{
                    $list[$key]['status'] = $smsStatusObj->where(array('msgid'=>$val['sms_msgid'],'mobile'=>$userInfo['username']))->getField('status');
                }
                if($list[$key]['status']) {
                    if($list[$key]['status'] != 'DELIVRD'){
                        $list[$key]['status'] = '<span style="color:red;">'.$list[$key]['status'].':'.$smsStatus[$list[$key]['status']].'&nbsp;<a href="javascript:;" onclick="resendsms('.$val['id'].')">补发</a></span>';
                    }else{
                        $list[$key]['status'] = '<span style="color:green;">'.$smsStatus[$list[$key]['status']].'</span>';
                    }
                } else {
                    $list[$key]['status'] = '<span style="color:red;">无状态&nbsp;<a href="javascript:;" onclick="resendsms('.$val['id'].')">补发</a></span>';
                }
            }
            $this->assign('list', $list);
            $this->assign('title', $projectObj->where(array('id'=>$detail['project_id']))->getField('title'));
            $this->assign('show', $show);
            $this->display();
        }else{

        }
    }

    /**
     * 还款补发短信
     */
    public function repaysms_simple(){
        if(!IS_POST || !IS_AJAX) exit;
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

        $id = I('post.id', 0, 'int');

        $userDueDetailObj = M("UserDueDetail");
        $userBankObj = M('UserBank');
        $projectObj = M('Project');

        $detail = $userDueDetailObj->where(array('id'=>$id))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'用户投资记录不存在'));

        $projectInfo = $projectObj->field('id,title,type,start_time,end_time,advance_end_time')->where(array('id'=>$detail['project_id']))->find();
        $project_name = $projectInfo['title'];
        $userBankInfo = $userBankObj->where(array('bank_card_no'=>$detail['card_no'],'has_pay_success'=>2))->find();
        if($projectInfo['type'] == 104 || $projectInfo['type'] == 109 || $projectInfo['type'] == 110) {
            $projectModelFundObj = M("ProjectModelFund");
            $fundDetailObj = M("FundDetail");
            $detailExt = $projectModelFundObj->where(array('project_id' => $projectInfo['id']))->find();
            $percent = 0; // 基金类收益率
            if ($detailExt) {
                if (!$detailExt['enter_time']) {
                    $timeStart = date('Y-m-d', strtotime($projectInfo['start_time'])); // 产品净值进入时间点
                } else {
                    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                }
                if (!$projectInfo['advance_end_time']) {
                    $timeEnd = date('Y-m-d', strtotime($projectInfo['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                } else {
                    $timeEnd = date('Y-m-d', strtotime($projectInfo['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                }
                $today = date('Y-m-d', time()); // 当前时间点
                if ($today < $timeEnd) $timeEnd = $today;
                $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
                if (count($fundList) > 1) { // 两个净值点以上
                    $fundStart = $fundList[0]['val']; // 起始净值
                    $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                    switch ($projectInfo['type']) {
                        case 104: // 打新股,收益超过18%分成
                            if ($fundEnd - $fundStart > 0) {
                                if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
                                    $percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
                                } else {
                                    $percent = ($fundEnd - $fundStart) / $fundStart;
                                }
                            }
                            break;
                        case 109: // B类基金,杠杆0.2
                            if ($fundEnd - $fundStart > 0) {
                                $fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
                                $percent = ($fundEndB - $fundStart) / $fundStart;
                            }
                            break;
                        case 110: // A类基金,杠杆2.6
                            if ($fundEnd - $fundStart > 0) {
                                $fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
                                $percent = ($fundEndA - $fundStart) / $fundStart;
                            } else if ($fundEnd - $fundStart < 0) {
                                $fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
                                $percent = ($fundEndA - $fundStart) / $fundStart;
                            }
                            break;
                    }
                }
            }
            $content = '尊敬的用户，您申购的' . $project_name . '本息' . round(($detail['due_capital'] + $detail['due_capital'] * $percent), 2) . '元正在往您尾号为' . substr($detail['card_no'], strlen($detail['card_no']) - 4) . '的' . $userBankInfo['bank_name'] . '卡狂奔而去，会在1~2个工作日内到账，请注意查收！';
        }else if($projectInfo['type'] == 148){ // 搏息宝
            $percent = cal_fund_percent($projectInfo['id']);
            $content = '尊敬的用户，您申购的'.$project_name.'本息'.round($detail['due_capital'] + $detail['due_capital']*$percent*(count_days(date('Y-m-d', strtotime($detail['due_time'])).' 08:00:00', date('Y-m-d', strtotime($detail['start_time'])).' 08:00:00')+1)/365, 2).'元正在往您尾号为'.substr($detail['card_no'],strlen($detail['card_no'])-4).'的'.$userBankInfo['bank_name'].'卡狂奔而去，会在1~2个工作日内到账，请注意查收！';
        }else{
            // 尊敬的用户，您申购的票票喵本息正10000.99元在往您尾号为xxxx的XX银行卡狂奔而去，会在1~2个工作日内到账，请注意查收！
            $content = '尊敬的用户，您申购的'.$project_name.'本息'.$detail['due_amount'].'元正在往您尾号为'.substr($detail['card_no'],strlen($detail['card_no'])-4).'的'.$userBankInfo['bank_name'].'卡狂奔而去，会在1~2个工作日内到账，请注意查收！';
        }
        $params = 'account='.C('SMS_INTDERFACE.account');
        $params .= '&pswd='.C('SMS_INTDERFACE.pswd');
        $params .= '&mobile='.$userBankInfo['mobile'];
        $params .= '&msg='.$content;
        $params .= '&needstatus=true';
        $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params);
        $arr = explode("\n", $smsData);
        foreach($arr as $key => $val){
            $arr[$key] = explode(',', $val);
        }
        $msgid = trim($arr[1][0]);
        if($arr[0][1] != 0) $this->ajaxReturn(array('status'=>0, 'info'=>$arr[0][1].':'.$errorMsg[$arr[0][1]]));
        // 把回执编号更新的用户购买记录
        $userDueDetailObj->where(array('id'=>$id))->save(array('sms_msgid'=>$msgid));
        $this->ajaxReturn(array('status'=>1,'info'=>'补发短信成功~短信回执编号:'.$msgid));
    }

    /**
     * 爆款/打新股订阅推送
     */
    public function push(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        $projectObj = M('Project');
        $projectPushObj = M('ProjectPush');
        $list = $projectPushObj->where(array('project_id'=>$id,'status'=>1))->select();
        $projectInfo = $projectObj->field('id,title,type')->where(array('id'=>$id))->find();
        $title = $projectInfo['title'];
        $projectType = $projectInfo['type']; // 产品类型
        //$title = $projectObj->where(array('id'=>$id))->getField('title');
        $device_id = '';
        $pos = 0; // 100个推送注册id一组
        $success = false;
        foreach($list as $key => $val){
            $pos += 1;
            if($val['registration_id']) $device_id .= ','.$val['registration_id'];
            if($pos >= 100){
                $pos = 0;
                $success = true;

                $device_id = substr($device_id, 1);
                if($projectType == 104){ // 打新股详细页面推送
                    $ret = pushMsg('您订阅的'.$title.'即将开售~', $device_id, '', array('action'=>5,'title'=>$title,'url'=>'http://wap.stlc.cn/AppBlock/pdetail/id/'.$id));
                }else if($projectType == 109 || $projectType == 110 || $projectType == 148){ // 基金类产品(搏息类)
                    $ret = pushMsg('您订阅的'.$title.'即将开售~', $device_id, '', array('action'=>5,'title'=>$title,'url'=>'http://wap.stlc.cn/AppBlock/pdetail/id/'.$id));
                }else{
                    $ret = pushMsg('您订阅的'.$title.'即将开售~', $device_id, '', array('action'=>6,'title'=>$title,'id'=>$id));
                }
//                if($ret) $this->ajaxReturn(array('status'=>0,'info'=>$ret));
                $datetime = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
                $device_id = str_replace(",", "','", $device_id);
                $projectPushObj->where("project_id=".$id." and registration_id in ('".$device_id."')")->save(array('status'=>2,'push_time'=>$datetime));

                $device_id = '';
            }
        }
        if($device_id) {
            $device_id = substr($device_id, 1);
            if($projectType == 104){ // 打新股详细页面推送
                $ret = pushMsg('您订阅的'.$title.'即将开售~', $device_id, '', array('action'=>5,'title'=>$title,'url'=>'http://wap.stlc.cn/AppBlock/pdetail/id/'.$id));
            }else if($projectType == 109 || $projectType == 110 || $projectType == 148) { // 基金类产品(搏息类)
                $ret = pushMsg('您订阅的' . $title . '即将开售~', $device_id, '', array('action' => 5, 'title' => $title, 'url' => 'http://wap.stlc.cn/AppBlock/pdetail/id/' . $id));
            }else{
                $ret = pushMsg('您订阅的'.$title.'即将开售~', $device_id, '', array('action'=>6,'title'=>$title,'id'=>$id));
            }
//            if($ret) $this->ajaxReturn(array('status'=>0,'info'=>$ret));
            $datetime = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
            $device_id = str_replace(",", "','", $device_id);
            $projectPushObj->where("project_id=".$id." and registration_id in ('".$device_id."')")->save(array('status'=>2,'push_time'=>$datetime));
            $this->ajaxReturn(array('status'=>1,'info'=>'推送成功~'));
        }else{
            if(!$success) {
                $this->ajaxReturn(array('status'=>0,'info'=>'没有可推送的用户'));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>'推送成功~'));
            }
        }
    }

    /**
     * 购买记录
     * 最后修改日期：2016.04.22
     */
    public function buylist(){

        if(!IS_POST){

            $id = I('get.id', 0, 'int'); // 项目ID

            $detail = M('Project')->field('id,title')->where(array('id' => $id, 'is_delete' => 0))->find();
            if (!$detail) {
                $this->error('项目不存在或已被删除');
                exit;
            }

            $userDueDetailObj = M('UserDueDetail');

            //$conditions = array('project_id' => $id);
            
            //$conditions = "project_id = ".$id;
            
            if(C('GHOST_ACCOUNT')){
                $conditions = "user_id >= -130 and project_id = ".$id;
            } else {
                $conditions = "user_id >0 and project_id = ".$id;
            }

            //$conditions = "user_id >= 0 and project_id = ".$id;

            $totalCnt = $userDueDetailObj->where($conditions)->count();

            $Page = new \Think\Page($totalCnt, $this->pageSize);

            $show = $Page->show();

            $list = $userDueDetailObj->field('user_id,card_no,due_capital,due_interest,add_time,status')
                ->where($conditions)
                ->order('add_time desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();

            $page_totle_capital = 0;

            $page_totle_interest = 0;

            foreach ($list as $key => $val) {

                $tmp_date = explode(".", $list[$key]['add_time']);

                $list[$key]['add_time'] = $tmp_date[0];

                if($val['user_id'] > 0){
                    $list[$key]['real_name'] = M('User')->where(array('id' => $val['user_id']))->getField('real_name');
                    $list[$key]['bank_name'] = M('UserBank')->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->getField('bank_name');
                    $list[$key]['to_wallet'] = M('UserAccount')->where(array('user_id' => $val['user_id']))->getField('to_wallet');
                    
                    if($detail['status'] == 5) {
                        $page_totle_capital += $val['due_capital'];
                        $page_totle_interest += $val['due_interest'];
                    } else {
                        if($val['status'] !=2){
                            $page_totle_capital += $val['due_capital'];
                            $page_totle_interest += $val['due_interest'];
                        }
                    }
                } else {
                   
                    if($val['user_id']<0){
                        $u = GhostUser(abs($val['user_id']));
                        if($u){
                            $list[$key]['real_name'] = $u[1];//'幽灵账户';
                        } else{
                            $list[$key]['real_name'] = '幽灵账户';
                        }
                    }else{
                        $list[$key]['real_name'] = '幽灵账户';
                    }
                }
            }

            $this->assign('rid', M('RepaymentDetail')->where(array('project_id'=>$detail['id']))->getField('id'));
            $this->assign('page_totle_interest', $page_totle_interest);
            $this->assign('page_totle_capital', $page_totle_capital);
            
            
            if($detail['status'] == 5) {      
                $this->assign('totle_interest', $userDueDetailObj->where('project_id = '.$id .' and user_id>0')->sum('due_interest'));
                $this->assign('totle_capital', $userDueDetailObj->where('project_id = '.$id .' and user_id>0')->sum('due_capital'));
            } else {
                $this->assign('totle_interest', $userDueDetailObj->where('project_id = '.$id .' and user_id>0 and status=1')->sum('due_interest'));
                $this->assign('totle_capital', $userDueDetailObj->where('project_id = '.$id .' and user_id>0 and status=1')->sum('due_capital'));
            }
            
            //幽灵总计
            $this->assign('robot_total_amount', $userDueDetailObj->where('project_id = '.$id .' and user_id<=0')->sum('due_capital'));

            $this->assign('title', $detail['title']);
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('id', $id);
            $this->assign('count', $totalCnt);
            $this->display();
        }else{

        }
    }

    /**
     * 幽灵账户
     */
    public function ghostaccount(){
        $projectObj = M('Project');//status<=2 and
        $list = $projectObj->field('id,title,amount,status,able,start_time,end_time,new_preferential')->where('status=2 and is_delete=0')->select();
        foreach($list as $key => $val){
            $list[$key]['status_str'] = $this->_statusColor[$val['status']];
        }
        $this->assign('list', $list);
        $this->display();
    }

    
    /**
     * 幽灵账户购买
     */
    public function ghostaccount_buy(){
        
        if(!IS_POST){
    
        }else{
            if(!IS_AJAX) exit;
            $id = I('post.id', 0, 'int');
            $val = I('post.val', '', 'strip_tags');
    
            if(!$val) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入金额'));
            }
    
            $user_id = 0;
            // 给幽灵账户生成随机手机号码
            $randPhone = auto_create_phone_for_ghost();
    
            $isGhostUser = false;
    
            $tmp = explode("#", $val);
    
            $val = $tmp[0];
    
            if(count($tmp)>1){
    
                if ($tmp[1] >= 101 && $tmp[1] <=130) {
                    $user_id = -$tmp[1];
    
                    $_randPhone = $this->getGhostAccount($user_id);
                    $randPhone = $_randPhone[0];
    
                    $isGhostUser = true;
                }
            }
    
            if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'产品信息不存在'));
    
            if(!is_numeric($val)) $this->ajaxReturn(array('status'=>0,'info'=>'金额必须为数字'));
    
            $projectObj = M('Project');
    
            $detail = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'产品信息不存在或已被删除'));
    
            if($detail['new_preferential'] == 1) {
                if($val >10000) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'购买新手标的金额不能超过1w'));
                }
            }
    
            $rechargeLogObj = M('RechargeLog');
            $investmentDetailObj = M('InvestmentDetail');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M('UserDueDetail');
    
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $recharge_no = 'ghost'.date('YmdHis', time()).getMillisecond().rand(100,999);
    
    
            if($val > $detail['able']) $this->ajaxReturn(array('status'=>0,'info'=>'剩余可购买金额不足'));
    
            $rechargeLogObj->startTrans();
            $rowsRechargeLog = array(
                'user_id' => $user_id,
                'project_id' => $id,
                'recharge_no' => $recharge_no,
                'trade_no' => '',
                'type' => 0,
                'amount' => $val,
                'status' => 2,
                'input_params' => '',
                'notify_params' => '',
                'card_no' => '',
                'ghost_phone' => $randPhone,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$rechargeLogObj->add($rowsRechargeLog)) $this->ajaxReturn(array('status'=>0,'info'=>'购买失败'));
            if(!$projectObj->where(array('id'=>$id))->setDec('able', $val)){
                $rechargeLogObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'购买失败'));
            }
    
            $percent = number_format((1-(($detail['able'] - $val)/$detail['amount']))*100, 2);
            $projectObj->where(array('id'=>$id))->save(array('percent'=>$percent));
            
            $rowsInvestmentDetail = array(
                'user_id' => $user_id,
                'project_id' => $id,
                'inv_total' => $val,
                'inv_succ' => $val,
                'device_type' => 5,
                'auto_inv' => 1,
                'recharge_no' => $recharge_no,
                'status' => 2,
                'bow_type' => '173',
                'card_no' => '',
                'ghost_phone' => $randPhone,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            $invest_detail_id = $investmentDetailObj->add($rowsInvestmentDetail);
            if(!$invest_detail_id){
                $rechargeLogObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'购买失败'));
            }
    
            $repay_id = $repaymentDetailObj->where(array('project_id'=>$id))->getField('id');
            if(!$repay_id){
                $rechargeLogObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'购买失败'));
            }
    
            $rowsUserDueDetail = array(
                'user_id' => $user_id,
                'project_id' => $id,
                'repay_id' => $repay_id,
                'invest_detail_id' => $invest_detail_id,
                'due_amount' => $val,
                'due_capital' => $val,
                'due_interest' => 0,
                'period' => 1,
                'duration_day' => count_days(date("Y-m-d",strtotime($detail['end_time'])), date('Y-m-d')),
                'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')).'.'.getMillisecond().'000',
                'due_time' => $detail['end_time'],
                'status' => 1,
                'bow_type' => '173',
                'card_no' => '',
                'ghost_phone' => $randPhone,
                'repayment_no' => '',
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
    
    
            if(!$userDueDetailObj->add($rowsUserDueDetail)){
                $rechargeLogObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'购买失败'));
            }
    
            if($val == $detail['able']){ // 已全部买完,更改标的状态
                $update_status = $projectObj->where(array('id'=>$id))->save(array('status'=>3,'soldout_time'=>$time));
                if($update_status===false){
                    $rechargeLogObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'购买失败'));
                }
                
                //成标
                
                $repay_amt = M('userDueDetail')->where('project_id='.$detail['id'].' and user_id>0')->sum('due_amount');
                $req['flag'] = 2;
                if(!$repay_amt) {
                    $repay_amt = 0;
                    $req['flag'] = 3;
                }
                
                $req['prod_id'] = $detail['id'];
                $req['funddata'] = "{\"payout_plat_type\":\"01\",\"payout_amt\":\"0\"}";
                
                $list[] = array('repay_amt'=>$repay_amt,
                    'repay_fee'=>'0',
                    'repay_num'=>'1',
                    'repay_date'=>date('Y-m-d',strtotime($detail['end_time']))
                );
                
                $req['repay_plan_list'] = json_encode($list);
                
                vendor('Fund.FD');
                vendor('Fund.sign');
                
                $plainText =  \SignUtil::params2PlainText($req);
                
                $sign =  \SignUtil::sign($plainText);
                
                $req['signdata'] = \SignUtil::sign($plainText);
                $fd  = new \FD();
                
                $res_str = $fd->post('/project/establishorabandon',$req);
                
                $uid = $_SESSION[ADMIN_SESSION]['uid'];
                $data['project_id'] = $detail['id'];
                $data['flag'] = $req['flag'];
                $data['amt'] = $repay_amt;
                $data['repay_date'] = $detail['end_time'];
                $data['memo'] = $res_str;
                $data['user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
                $data['create_time'] = date("Y-m-d H:i:s");
                if(M('projectEstablishLog')->add($data)) {
                    if($req['flag'] == 3){
                        $projectObj->where(array('id'=>$id))->save(array('status'=>8));
                    }
                }
            }
            
            $rechargeLogObj->commit();
            
            $money = $projectObj->where(array('id'=>$id,'is_delete'=>0))->getField('able');
            
            if($money<30000){
                try {
                    $api = A('Api2');
                    $upId = $api->getUpProject($id); 
                    if($upId>0){                        
                        $ret = publish($upId);   
                        $api->update_project_some_data($uid, $upId, $id, $ret);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 到期金额转入钱包-单条处理
     */
    public function project_to_wallet(){

        if(!IS_POST || !IS_AJAX) {
            exit;
        }

        $id = I('post.id', 0, 'int'); //UserDueDetail ID

        if(!$id) {
            $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        }

        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where("id = $id and user_id > 0")->find();//array('id'=>$id)

        if(!$userDueDetailInfo) {
            $this->ajaxReturn(array('status'=>0,'info'=>'用户订单信息不存在'));
        }
        if($userDueDetailInfo['status'] != 1) {
            $this->ajaxReturn(array('status'=>0,'info'=>'订单不是未支付状态'));
        }

        $projectInfo = M("Project")->field('id,title,repay_review')->where(array('id'=>$userDueDetailInfo['project_id']))->find();

        if(!$projectInfo) {
            $this->ajaxReturn(array('status'=>0,'info'=>'期数不存在'));
        }
        
        if($projectInfo['repay_review'] != 3){
            if($projectInfo['repay_review'] == 0){
                $info='融资方还款还没有审核，操作失败。';
            } else if($projectInfo['repay_review'] == 1) {
                $info='融资方还款已审核，融资方还没有把资金转入标的账户，操作失败。';
            } else if($projectInfo['repay_review'] == 2) {
                $info='标的账户的资金还没有转打投资用户的账户，请先操作 ·支付·。';
            } else {
                $info='融资方还款审核失败';
            }
            $this->ajaxReturn(array('status'=>0,'info'=>$info));
        }
        
        $userWalletRecordsObj = M("UserWalletRecords");
        $investmentDetailObj = M("InvestmentDetail");
        $userAccountObj = M("UserAccount");
        $userObj = M('User');
        $userBankObj = M("UserBank");

        $rows = array(
            'user_id' => $userDueDetailInfo['user_id'],
            'value' => $userDueDetailInfo['due_amount'],
            'type' => 4,
            'pay_status' => 2,
            'status' => 1,
            'user_due_detail_id' => $userDueDetailInfo['id'],
            'add_time' => date('Y-m-d H:i:s'),
            'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
            'modify_time'=>date('Y-m-d H:i:s'),
            'remark'=> $projectInfo['title'].'标的还款'
        );
        
        
        $userWalletRecordsObj->startTrans();

        //添加记录明细
        if(!$userWalletRecordsObj->add($rows)) {
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        //更新 s_user_due_detail 设为已还款。
        if(!$userDueDetailObj->where(array('id'=>$userDueDetailInfo['id']))->save(array('status'=>2,'status_new'=>2))){
            $userWalletRecordsObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        //设为已还款
        if(!$investmentDetailObj->where(array('id'=>$userDueDetailInfo['invest_detail_id']))->save(array('status'=>4,'status_new'=>4))){
            $userWalletRecordsObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }
        
        $sql = "update s_user_account set wallet_totle=wallet_totle+".$userDueDetailInfo['due_amount'];
        $sql.= ",total_invest_capital=total_invest_capital+".$userDueDetailInfo['due_capital'];
        $sql.= ",total_invest_interest=total_invest_interest+".$userDueDetailInfo['due_interest'];
        $sql.= ",account_total=account_total-".$userDueDetailInfo['due_amount'];
        $sql.= ",wait_amount=wait_amount-".$userDueDetailInfo['due_amount'];
        $sql.= ",wait_capital=wait_capital-".$userDueDetailInfo['due_capital'];
        $sql.= ",wait_interest=wait_interest-".$userDueDetailInfo['due_interest'];
        $sql.= ",wallet_product_interest=wallet_product_interest+".$userDueDetailInfo['due_interest'];
        
        //判断订单是否使用红包;解决客户端提现问题
        $red_amt = $userDueDetailInfo['red_amount'];
        if($red_amt == 0){
            $recharge_no = M('InvestmentDetail')->where(array('id'=>$userDueDetailInfo['invest_detail_id'],'user_id'=>$userDueDetailInfo['user_id']))->getField('recharge_no');
            $red_amt = $this->getBagAmount($userDueDetailInfo['user_id'], $recharge_no);
        }
        
        if($red_amt>0){
            $sql.= ",red_coupon_total = red_coupon_total+".$red_amt;
        }
        
        $sql.= " where user_id=".$userDueDetailInfo['user_id'] .' limit 1';
        

        if(!$userAccountObj->execute($sql)){
            $userWalletRecordsObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        $userWalletRecordsObj->commit();
        
        $this->update_status($projectInfo['id']);

        $tips = '';
        if($this->isUseBag($userDueDetailInfo['user_id'])) {
            $tips = '记得使用您的红包喔！';
        }

        // 发送个人消息
        send_personal_message(0, $userDueDetailInfo['user_id'], '您购买的产品['.$projectInfo['title'].']本息已转入平台账户，请注意查收。'.$tips);

        // 转入钱包完成后发送推送给用户,处理到账通知推送(极光)
        $base_user = $userObj->field('registration_id,channel,last_channel,latest_device_type')->where(array('id'=>$userDueDetailInfo['user_id']))->find();

        $registration_id = trim($base_user['registration_id']);

        $channel_name = trim($base_user['channel']);

        if($registration_id && !empty($registration_id)) {
            
            $_app = getAppId($channel_name);
            
            /*
            JPushLog('您购买的产品['.$projectInfo['title'].']本息已转入钱包，请注意查收。'.$tips.',
                registration_id:'.$registration_id.',user_id:'.$userDueDetailInfo['user_id'].',action:12,app:'.$_app);
            */
            pushMsg('您购买的产品['.$projectInfo['title'].']本息已转入平台账户，请注意查收。'.$tips, $registration_id, '', array('action'=>12),$_app);
            
            $data = array(
                'pushType'=>2,
                'registrationId'=>$registration_id,
                'position'=>3,
                'page'=>3,
                'lastDeviceType'=>$base_user['latest_device_type'],
                'lastChannel'=>$base_user['last_channel']
            );
            updatePushMsg($data);
        }

        $this->ajaxReturn(array('status'=>1));
    }
    
    //更新标地还款状态
    private function update_status($id){
        $repay_count = M('UserDueDetail')->where("project_id= $id and status = 1 and user_id>0")->count();
        if($repay_count == 0){
            M("Project")->where("id=$id")->save(['status'=>5]);
            $time = date('Y-m-d H:i:s').'.'.getMillisecond();
            M("repaymentDetail")->where("project_id=$id")->save(['status'=>2,'status_new'=>2,'real_time' => $time,'modify_time' => $time,'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid']]);
        }
    }

	 /**
     * 到期金额转入钱包-批量处理  (外层)
     */
    public function project_batchto_wallet()
    {

       // header("Content-type:text/html;charset=utf-8");
        set_time_limit(0);
		//第一步查出待处理的转入钱包的支付记录
		
        /*
        $this->ajaxReturn(array('status'=>1,'info'=>'批量还款成功'));
        
        exit;
        */

        $id = I('id', 0, 'int'); // 项目ID
        $repay_id = I('rid', 0, 'int'); // 还本付息表条目ID
        

        $detail = M('Project')->field('id,title,repay_review')->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->ajaxReturn(array('status'=>0,'info'=>'项目不存在或已被删除'));
        }
        
        if($detail['repay_review'] != 3){
            if($detail['repay_review'] == 0){
                $info='融资方还款还没有审核，操作失败。';
            } else if($detail['repay_review'] == 1) {
                $info='融资方还款已审核，融资方还没有把资金转入标的账户，操作失败。';
            } else if($detail['repay_review'] == 2) {
                $info='标的账户的资金还没有转打投资用户的账户，请先操作 ·支付·。';
            } else {
                $info='融资方还款审核失败';
            }
            $this->ajaxReturn(array('status'=>0,'info'=>$info));
        }
        
        $repayDetail = M('RepaymentDetail')->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
			$this->ajaxReturn(array('status'=>0,'info'=>'还本付息条目不存在或已被删除'));
        }

        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');
        $userWalletRecordsObj = M("UserWalletRecords");
        $investmentDetailObj = M("InvestmentDetail");
        
        $cond[] = 'user_id > 0';
        $cond[] = "project_id=".$id;
        $cond[] = "repay_id=".$repay_id;
        $cond[] = 'status = 1';

        $conditions = implode(' and ', $cond);

        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->select();

		if(empty($list)){
			//exit("没有待处理的数据<br/>");
			$this->ajaxReturn(array('status'=>0,'info'=>'没有待处理的数据'));
		}

		//第二步批量处理数据
		//echo "正在处理中...,请耐心等待处理结果<br/>";

        foreach($list as $i => $v) {
			
			$rows = array(
			    'user_id' => $v['user_id'],
			    'value' => $v['due_amount'],
			    'type' => 4,
			    'pay_status' => 2,
			    'status' => 1,
			    'user_due_detail_id' => $v['id'],
			    'add_time' => date('Y-m-d H:i:s'),
			    'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
			    'modify_time'=>date('Y-m-d H:i:s'),
			    'remark'=>$detail['title'].'标的还款'
			);
				
			$userWalletRecordsObj->startTrans();

			if(!$userWalletRecordsObj->add($rows)) {
			   // exit("操作失败,请重试<br/>");
			    
			    $this->ajaxReturn(array('status'=>0,'info'=>'生成还款记录失败,请重试;联系技术排查'));
			}

			if(!$userDueDetailObj->where(array('id'=>$v['id']))->save(array('status'=>2,'status_new'=>2))){
				$userWalletRecordsObj->rollback();
				//exit("操作失败,请重试<br/>");
				
				$this->ajaxReturn(array('status'=>0,'info'=>'更新userDueDetail记录失败。id:'.$v['id']));
			}

			if(!$investmentDetailObj->where(array('id'=>$v['invest_detail_id']))->save(array('status'=>4,'status_new'=>4))){
				$userWalletRecordsObj->rollback();
				//exit("操作失败,请重试<br/>");
				
				$this->ajaxReturn(array('status'=>0,'info'=>'更新investmentDetail记录失败。id:'.$v['invest_detail_id']));
			}
                            
            $sql = "update s_user_account set wallet_totle=wallet_totle+".$v['due_amount'];
            $sql.= ",total_invest_capital=total_invest_capital+".$v['due_capital'];
            $sql.= ",total_invest_interest=total_invest_interest+".$v['due_interest'];
            $sql.= ",account_total=account_total-".$v['due_amount'];
            $sql.= ",wait_amount=wait_amount-".$v['due_amount'];
            $sql.= ",wait_capital=wait_capital-".$v['due_capital'];
            $sql.= ",wait_interest=wait_interest-".$v['due_interest'];
            $sql.= ",wallet_product_interest=wallet_product_interest+".$v['due_interest'];
            
            $red_amt = $v['red_amount'];
            if($red_amt <=0) {
                $recharge_no = M('InvestmentDetail')->where(array('id'=>$v['invest_detail_id'],'user_id'=>$v['user_id']))->getField('recharge_no');
                $red_amt = $this->getBagAmount($v['user_id'], $recharge_no);
            }
            if($red_amt>0){
                $sql.= ",red_coupon_total = red_coupon_total+".$red_amt;
            }
            
            $sql.= " where user_id=".$v['user_id'].' limit 1';

            if(!$userAccountObj->execute($sql)){
                $userWalletRecordsObj->rollback();
                //exit('操作失败,请重试<br/>');
                
                $this->ajaxReturn(array('status'=>0,'info'=>'更新userAccount记录失败。user_id:'.$v['user_id']));
            }

            $userWalletRecordsObj->commit();


            $tips = '';
            if($this->isUseBag($v['user_id'])) {
                $tips = '记得使用您的红包喔！';
            }
            // 发送个人消息
            send_personal_message(0, $v['user_id'], '您购买的产品['.$detail['title'].']本息已转入平台账户，请注意查收。'.$tips);

            // 转入钱包完成后发送推送给用户,处理到账通知推送(极光)
            $base_user = $userObj->field('registration_id,channel,latest_device_type,last_channel')->where(array('id'=>$v['user_id']))->find();

            $registration_id = trim($base_user['registration_id']);

            $channel_name = trim($base_user['channel']);

            if($registration_id && !empty($registration_id)) {
                
                $_app = getAppId($channel_name);
                /*
                JPushLog('您购买的产品['.$detail['title'].']本息已转入钱包，请注意查收。'.$tips.',
                            registration_id:'.$registration_id.',user_id:'.$v['user_id'].',action:12,app:'.$_app);
                */
                pushMsg('您购买的产品['.$detail['title'].']本息已转入平台账户，请注意查收。'.$tips, $registration_id, '', array('action'=>12),$_app);
                
                $data = array('pushType'=>2,
                            'registrationId'=>$registration_id,
                            'position'=>3,
                            'page'=>3,
                            'lastDeviceType'=>$base_user['latest_device_type'],
                            'lastChannel'=>$base_user['last_channel']
                );
                updatePushMsg($data);
                
            }
        }
        $this->update_status($detail['id']);
        $this->ajaxReturn(array('status'=>1,'info'=>'批量还款成功'));
    }

    
    /**
     * 第三方代付
     */
    public function project_pay(){
        if(!IS_AJAX || !IS_POST) exit;
        $id = I('post.id', 0, 'int'); //UserDueDetail ID

        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$id))->find();
        if(!$userDueDetailInfo) $this->ajaxReturn(array('status'=>0,'info'=>'用户订单信息不存在'));
        if($userDueDetailInfo['status'] != 1) $this->ajaxReturn(array('status'=>0,'info'=>'订单不是未支付状态'));

        $userBankObj = M('UserBank');
        $userBankInfo = $userBankObj->where(array('bank_card_no'=>$userDueDetailInfo['card_no']))->find();
        $llPayCardCode = array('01000000', '03090000', '03040000');
        if(!in_array($userBankInfo['bank_code'], $llPayCardCode)) { // 使用盛付通代付

        }else{ // 使用连连支付代付

        }
    }

    /**
     * 检查第三方支付订单状态
     */
    public function project_pay_status(){
        if(!IS_AJAX || !IS_POST) exit;
        $id = I('post.id', 0, 'int'); //UserDueDetail ID
        $order = I('post.order', '', 'strip_tags'); // 订单号

        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where(array('id'=>$id))->find();
        if(!$userDueDetailInfo) $this->ajaxReturn(array('status'=>0,'info'=>'用户订单信息不存在'));

        $userBankObj = M('UserBank');
        $userBankInfo = $userBankObj->where(array('bank_card_no'=>$userDueDetailInfo['card_no']))->find();
        $llPayCardCode = array('01000000', '03090000', '03040000');
        if(!in_array($userBankInfo['bank_code'], $llPayCardCode)) $this->ajaxReturn(array('status'=>0,'info'=>'盛付通暂时为对接完成'));
        $ret = check_order_pay_status_by_ll($order);
        $ret = json_decode($ret);
        if($ret->ret_code != '0000') $this->ajaxReturn(array('status'=>0,'info'=>$ret->ret_msg));
        $result = $ret->result_pay;
        //SUCCESS:代付成功  PROCESSING:银行代付处理中
        $this->ajaxReturn(array('status'=>1,'info'=>$result));
    }

    /**
     * 更新产品备注信息
     */
    public function update_project_remark(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $dt = I('post.dt', '', 'strip_tags');
        $remark = I('post.remark', '', 'strip_tags');

        $projectObj = M('Project');
        $detail = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'产品信息不存在或已被删除'));

        if(!$projectObj->where(array('id'=>$id))->save(array('remark'=>$remark))) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        // 清空该天缓存
        F('project_daysales_'.str_replace('-', '_', $dt), null);
        $this->ajaxReturn(array('status'=>1));
    }


    /**
     * 导出指定时间段之内的产品还本付息列表
     */
    public function repayment(){
        $st = I("get.st",'','strip_tags');//开始时间
        $et = I('get.et','',"strip_tags");//结束时间
        $term_type = I("get.term_type",0,'int');//产品期限类型
        $financing = urldecode(I("get.financing",'','strip_tags'));//融资人
        $projectObj = M('project');
        if($st && $et){
            ini_set("memory_limit", "2000M");
            ini_set("max_execution_time", 0);
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('还本付息的标')->setCellValue("A1", "标名称")->setCellValue("B1", "还本到钱包本金")->setCellValue("C1", "还本到钱包利息")->setCellValue("D1", "还本到银行卡本金")->setCellValue("E1", "还本到银行卡利息")->setCellValue("F1", "到期日期")->setCellValue("G1", "融资人");
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);

            $begin_time = $st;//开始时间
            $end_time   = $et;//结束时间
            $sql = "";
            if($term_type == 1){//稳一稳
                $sql.=" AND m.`repayment_time`>='".$begin_time." 00:00:00.000000' AND m.`repayment_time`<='".$end_time." 23:59:59.999000'";
            }else if($term_type == 2){ //搏一搏
                $sql.=" AND m.`real_time`>='".$begin_time." 00:00:00.000000' AND m.`real_time`<='".$end_time." 23:59:59.999000'";
            }else{
                $sql.=" AND m.`repayment_time`>='".$begin_time." 00:00:00.000000' AND m.`repayment_time`<='".$end_time." 23:59:59.999000'";
            }
            $repayment_sql = "SELECT m.`project_id`,m.`id`,m.`repayment_time` FROM stone.`s_repayment_detail` AS m WHERE  m.`status` = 2 AND m.`status_new` = 2 ".$sql;

            $repayment_list_arr = M()->query($repayment_sql);
            $pos = 2;
            $finance_flag = false;
            $term_flag = false;
            foreach($repayment_list_arr as $k=>$v){
                //标的名称
                $project_one_sql = "SELECT `title`,`term_type`,`financing` FROM stone.`s_project` AS m WHERE m.`id`=".$v['project_id']."";
                $project_one_amount_arr = M()->query($project_one_sql);
                $project_title = $project_one_amount_arr[0]['title'];
                $project_type  = $project_one_amount_arr[0]['term_type'];
                $project_financing = $project_one_amount_arr[0]['financing'];
                if($financing){
                    if($project_financing == $financing){
                        $finance_flag = true;
                    }else{
                        $finance_flag = false;
                    }

                }else{
                    $finance_flag = true;
                }
                if($term_type){
                    if($project_type == $term_type){
                        $term_flag = true;
                    }else{
                        $term_flag = false;
                    }

                }else{
                    $term_flag = true;
                }
                if($finance_flag && $term_flag){
                    // 还本到钱包
                    $person_one_sql = "SELECT SUM(m.`due_capital`) as due_total,SUM(m.`due_interest`) as due_interest FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`project_id`=".$v['project_id']." AND m.`repay_id`= ".$v['id']." AND m.`to_wallet` =1";
                    $person_one_amount_arr = M()->query($person_one_sql);
                    $person_one_amount = $person_one_amount_arr[0]['due_total'];
                    $person_two_amount = $person_one_amount_arr[0]['due_interest'];
                    //还本到银行卡
                    $person_two_sql = "SELECT SUM(m.`due_capital`) as due_total,SUM(m.`due_interest`) as due_interest FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`project_id`=".$v['project_id']." AND m.`repay_id`= ".$v['id']." AND m.`to_wallet` =0";
                    $person_two_amount_arr = M()->query($person_two_sql);
                    $person_three_amount = $person_two_amount_arr[0]['due_total'];
                    $person_four_amount = $person_two_amount_arr[0]['due_interest'];
                    //日期
                    if($term_type == 1){//稳一稳
                        $date_time = date("Y-m-d",strtotime($v['repayment_time']));
                    }else if($term_type == 2){ //搏一搏
                        $date_time = date("Y-m-d",strtotime($v['real_time']));
                    }else{
                        $date_time = date("Y-m-d",strtotime($v['repayment_time']));
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $project_title);
                    $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $person_one_amount);
                    $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $person_two_amount);
                    $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $person_three_amount);
                    $objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $person_four_amount);
                    $objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $date_time);
                    $objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $project_financing);
                    $pos++;
                }

            }
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')还本付息的标.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }else {
            //查出所有融资人
            $term_type_list = $projectObj->field("financing")->group('financing')->select();
            $this->assign("term_type_list", $term_type_list);
            $this->display("export_repayment");
        }
    }

    /*
     *
     */
    private function getBagAmount($userId,$orderId) {
        $amount = 0;
        if($userId && $orderId) {
            $amount = M('UserRedenvelope')->where(array('user_id'=>$userId,'recharge_no'=>$orderId,'status'=>1))->getField('amount');
        }
        return $amount;
    }

    /*
     *
     */
    private function isUseBag($uid) {
        $cnt = M('UserRedenvelope')->where(array('user_id'=>$uid,'status'=>1))->count();
        if($cnt > 0) {
            return false;
        }
        $cnt = M('UserRedenvelope')->where(array('user_id'=>$uid,'status'=>0))->count();
        if($cnt > 0) {
            return true;
        }
        return false;
    }

    /**
     * 产品分组管理
     * create_time 2016/08/02
     */
    public function project_group(){

        if(!IS_POST){
            $this->assign('list', M('ProjectGroup')->order('id asc')->select());
            $this->display();
        }
    }

    /**
     * 添加产品分组
     * create_time 2016/08/02
     */
    public function project_group_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $name = trim(I('post.name', '', 'strip_tags'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            $tag = trim(I('post.tag', 0, 'int'));

            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'产品分组名称不能为空'));
            if(!$memo) $this->ajaxReturn(array('status'=>0,'info'=>'产品分组描述不能为空'));

            if(M('ProjectGroup')->where(array('name'=>$name))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的产品分组信息'));

            $time = date('Y-m-d H:i:s', time());
            $rows = array(
                'tag'  => $tag,
                'name' => $name,
                'memo' => $memo,
                'add_time' => $time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            $rid = M('ProjectGroup')->add($rows);
            if(!$rid) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/project_group'));
        }
    }


     /**
     * 编辑产品分组
     * create_time 2016/08/02
     */
    public function project_group_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('ProjectGroup')->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('产品分组信息不存在或已被删除');
                exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            $memo = trim(I('post.memo', '', 'strip_tags'));
            $tag = trim(I('post.tag', 0, 'int'));

            if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'id 不能为0 请返回重新操作'));

            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'产品分组名称不能为空'));
            if(!$memo) $this->ajaxReturn(array('status'=>0,'info'=>'产品分组描述不能为空'));


            if(M('ProjectGroup')->where("name='$name' and id !=$id")->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的产品分组信息'));
            }

            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $rows = array(
                'tag'  =>$tag,
                'name' => $name,
                'memo' => $memo,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!M('ProjectGroup')->where(array('id'=>$id))->save($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/project_group'));
        }
    }

    /**
     * 删除产品分组
     * create_time 2016/08/02
     */
    public function project_group_delete(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        if(!$id) $this->ajaxReturn(array('status'=>0,'info'=>'是删除的记录不存在'));

        if( M('Project')->where(array('project_group_id'=>$id))->count()) {
            $this->ajaxReturn(array('status'=>0,'info'=>'该分组已经在产品中有使用，无法删除，只能修改'));
        }

        if(!M('ProjectGroup')->where(array('id'=>$id))->delete()){
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
    /**
     * 融资方协议利率
     */
    public function project_protocol_rate(){
        if(IS_POST){
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $quest="";
            if($start_time) $quest .= '/st/'.str_replace(' ', '|', $start_time);
            if($end_time) $quest .= '/et/'.str_replace(' ', '|', $end_time);
            redirect(C('ADMIN_ROOT') . '/project/project_protocol_rate'.$quest);
        }else{
            $projectProtocolRateObj = M('ProjectProtocolRate');
            $start_time = I("get.st",date("Y-m-d",time()-(10*24*3600)),'strip_tags');//开始时间
            $end_time = I('get.et', date('Y-m-d', time()+(10*34*3600)), 'strip_tags');//结束时间
            if($start_time) $cond[] = "add_time>='".$start_time."'";
            if($end_time) $cond[] = "add_time<='".$end_time."'";
            $condition = implode(" and ",$cond);
            $params=array(
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
            $counts = $projectProtocolRateObj->where($condition)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $projectProtocolRateList = $projectProtocolRateObj->where($condition)->order("id desc")->limit($Page->firstRow.",".$Page->listRows)->select();
            foreach($projectProtocolRateList as $k => $v){
                $projectProtocolRateList[$k]['week'] =getWeek(strtotime($v['add_time']));
                $projectProtocolRateList[$k]['financing'] = M('financing')->where('id='.$v['fid'])->getField('name');
            }
            $now_date = date("Y-m-d",time());
            $this->assign("params",$params);
            $this->assign("now_date",$now_date);
            $this->assign('show',$show);
            $this->assign("rate_list",$projectProtocolRateList);
            $this->display();
        }
    }


    /**
     * 融资方协议利率
     */
    public function project_protocol_rate_edit(){
        $rate_obj = M('UserWalletAnnualizedRate');
        if(IS_POST){//添加/编辑
            $id = I('post.id',0,'int');//年化利率ID
            $rate_date = I('post.add_time',date("Y-m-d",time()-24*3600),'strip_tags');//年化利率日期
            $rate_num = I('post.rate',0,'strip_tags');//年化利率
            $fid = I('post.financing','0','int');//融资方

            if(!$rate_date){
                $this->ajaxReturn(array('status'=>0,'info'=>'日期未设置'));
            }
            if(!$rate_num){
                $this->ajaxReturn(array('status'=>0,'info'=>'年化利率未设置'));
            }

            if(!$fid) {
                $this->ajaxReturn(array('status'=>0,'info'=>'融资方未设置'));
            }

            if($id){//编辑
                $row = array(
                    'add_time'=>$rate_date,
                    'rate'=>$rate_num,
                    'fid'=>$fid
                );
                $update_status = M('ProjectProtocolRate')->where(array('id'=>$id))->save($row);
                if($update_status!==false){
                    $this->ajaxReturn(array('status'=>2,'info'=>C('ADMIN_ROOT').'/project/project_protocol_rate'));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>'更新该日融资方协议利率失败,重新更新'));
                }
            }else{//添加
                //判断该日期是否已经录入了
                $rate_exist = M('ProjectProtocolRate')->where(array('add_time'=>$rate_date,'fid'=>$fid))->find();
                if($rate_exist){
                    $this->error("该日期已经录入了");
                }
                $row = array(
                    'add_time'=>$rate_date,
                    'rate'=>$rate_num,
                    'fid'=>$fid
                );
                $add_status = M('ProjectProtocolRate')->add($row);
                if($add_status){
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/project_protocol_rate'));
                }else{
                    $this->ajaxReturn(array('status'=>1,'info'=>'录入融资方协议利率失败'));
                }
            }
        } else {
            $id = I('get.id',0,'int');//年化利率ID
            if($id){
                $rate_info = M('ProjectProtocolRate')->where(array('id'=>$id))->find();
                $this->assign("id",$id);
                $this->assign("add_time",$rate_info['add_time']);
                $this->assign("rate",$rate_info['rate']);
                $this->assign("financing",$rate_info['financing']);
            }
            $this->assign("financing_list",M('Financing')->field('id,name')->select());
            $this->display();
        }
    }

    /*
     * 融资方管理
     */
    public function financing(){
        $counts = M('Financing')->count();
        $Page = new \Think\Page($counts, $this->pageSize);
        $list = M('Financing')->where(['type'=>1])->order("id desc")->limit($Page->firstRow.",".$Page->listRows)->select();
        $res = array();
        foreach ($list as $val) {
            $val['project_status_2'] = M('Project')->where(array('status'=>2,'fid'=>$val['id'],'is_delete'=>0))->count();//销售中
            $val['project_status_5'] = M('Project')->where(array('status'=>5,'fid'=>$val['id'],'is_delete'=>0))->count();;//已还款
            $res[] = $val;
        }
        $this->assign('show',$Page->show());
        $this->assign("list",$res);
        $this->display();
    }

    /*
     * 融资方管理
     * 20170203
     */
    public function financing_edit(){
        $financingObj = M('Financing');
        if(IS_POST){//添加/编辑
            $id = I('post.id',0,'int');
            $name = trim(I('post.name','','strip_tags'));
            $intro = trim(I("post.intro",'','strip_tags'));
            $legal_person = trim(I("post.legal_person",'','strip_tags'));
            $license = trim(I("post.license",'','strip_tags'));
            $address = trim(I("post.address",'','strip_tags'));
            
            $platform_account = trim(I("post.platform_account",'','strip_tags'));
            $bank_id = trim(I("post.bank_id",'0','strip_tags'));
            $bank_card_no = trim(I("post.bank_card_no",'','strip_tags'));
            $acct_name = trim(I("post.acct_name",'','strip_tags'));
            $bank_code = trim(I("post.bank_code",'','strip_tags'));
            
            if(!$name) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入融资方'));
            }            
            if(!$intro) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写融资方简称'));
            }            
            if(!$legal_person) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写法人'));
            }            
            if(!$license) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写营业执照'));
            }            
            if(!$address) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写融资方所在地'));
            }
            if(!$platform_account) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写平台客户号'));
            }
            if(!$bank_id) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择收款开户行'));
            }
            if(!$bank_code) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写银行编号(对公出账)'));
            }
            if(!$bank_card_no) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写银行卡号'));
            }
            if(!$acct_name) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写收款人姓名'));
            }
            
            $time = date("Y-m-d H:i:s");

            if($id){//编辑

                $old_financing = $financingObj->where(array('id'=>$id))->find();
                if(!$old_financing) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'更新融资方失败,重新更新 id,不存在'));
                }

                if($financingObj->where("name =  '$name' and id !=".$id)->count()) {
                    $this->ajaxReturn(array('status'=>0,'info'=>"融资方 `$name` 已经存在"));
                }
                
                $row = array(
                    'edit_time'=>$time,
                    'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                    'name'=>$name,
                    'intro'=>$intro,
                    'legal_person'=>$legal_person,
                    'license'=>$license,
                    'address'=>$address,
                    'platform_account'=>$platform_account,
                    'bank_card_no'=>$bank_card_no,
                    'bank_id'=>$bank_id,
                    'acct_name'=>$acct_name,
                    'bank_code'=>$bank_code
                );
               
                $update_status = $financingObj->where(array('id'=>$id))->save($row);

                if ($update_status) {
                   $this->ajaxReturn(array('status'=>2,'info'=>C('ADMIN_ROOT').'/project/financing'));
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'更新融资方失败,重新更新'));
                }

            } else {//添加

                $financing = $financingObj->where(array('name'=>$name))->find();
                if($financing){
                    $this->ajaxReturn(array('status'=>0,'info'=>'该融资方已经录入了'));
                }
                $row = array(
                    'name'=>$name,
                    'intro'=>$intro,
                    'legal_person'=>$legal_person,
                    'license'=>$license,
                    'address'=>$address,
                    'add_time'=>$time,
                    'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                    'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                    'edit_time'=>$time,
                    'platform_account'=>$platform_account,
                    'bank_card_no'=>$bank_card_no,
                    'bank_id'=>$bank_id,
                    'acct_name'=>$acct_name,
                    'bank_code'=>$bank_code
                );
                $add_status = $financingObj->add($row);
                if($add_status){
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/project/financing'));
                }else{
                    $this->ajaxReturn(array('status'=>1,'info'=>'录入融资方失败'));
                }
            }
        } else {
            $id = I('get.id',0,'int');
            if($id){
                $info = $financingObj->where(array('id'=>$id))->find();
            }
            $bank_list = M('baseBanks')->field('bank_no,bank_name')->select();
            $this->assign("id",$id);
            $this->assign("detail",$info);
            $this->assign("bank_list",$bank_list);
            $this->display();
        }
    }
    /*
     * 融资方管理
     * 20160818
     */
    public function financing_del(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if(M('Financing')->where(array('id'=>$id))->count()) {
            if(M('Project')->where(array('fid'=>$id))->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败，该融资方已经有产品在卖了'));
            }
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败，该记录不存在'));
        }

        if(!M('Financing')->where(array('id'=>$id))->delete()) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
        }
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 产品复息
     */
    public function project_repay() {

        $starttime = I('get.start_time', '', 'strip_tags');

        $endtime = I('get.end_time', '', 'strip_tags');

        $financing_id = I("get.financing_id",0,"int");

        $condition = "1 = 1";

        if($starttime) {
            $condition .= " AND `stat_date` >= '$starttime'";
        }

        if($endtime) {
            $condition .= " AND `stat_date` <= '$endtime'";
        }

        if($financing_id > 0 ) {
            $condition .= " AND financing_id =".$financing_id;
        }

        $totalCnt =  M("StatisticsDailyProjectEarnings")->where($condition)->count();

        $Page = new \Think\Page($totalCnt, $this->pageSize);

        $list = M("StatisticsDailyProjectEarnings")->where($condition)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $res = array();

        $total_earnings = 0;

        if(!empty($list)) {
            foreach ($list as $val) {
                $val['financing_name'] = M("Financing")->field("name")->where("id=".$val['financing_id'])->getField('name');
                $treaty_rate = M('ProjectProtocolRate')->where(array('financing'=>$val['financing_name'],'add_time'=>$val['stat_date']))->getField('rate');
                if($treaty_rate) {
                    $val['treaty_rate'] = $treaty_rate;
                }
                //$val['earnings'] = ($val['treaty_rate'] - $val['avg_rate']) / 100 * $val['amount'] / 365;
                //$total_earnings += $val['earnings'];
                $res[] = $val;
            }
        }

        $this->assign("params", array(
            "start_time" => $starttime,
            "end_time" => $endtime,
            'financing_id'=>$financing_id,
            'total_earnings'=> M("StatisticsDailyProjectEarnings")->where($condition)->sum('earnings')
        ));

        $this->assign("financing_list",M('Financing')->field('id,name')->select());
        $this->assign("list",$res);
        $this->assign('show', $Page->show());
        $this->display();

    }



    public function project_repay_export_to_excel() {

        $starttime = I('get.start_time', '', 'strip_tags');

        $endtime = I('get.end_time', '', 'strip_tags');

        $financing_id = I("get.financing_id",0,"int");

        $condition = "1 = 1";

        if($starttime) {
            $condition .= " AND `stat_date` >= '$starttime'";
        }

        if($endtime) {
            $condition .= " AND `stat_date` <= '$endtime'";
        }

        if($financing_id > 0 ) {
            $condition .= " AND financing_id =".$financing_id;
        }

        $list = M("StatisticsDailyProjectEarnings")->where($condition)->order("id desc")->select();

        $res = array();

        if(!empty($list)) {
            foreach ($list as $val) {
                $val['financing_name'] = M("Financing")->field("name")->where("id=".$val['financing_id'])->getField('name');
                $treaty_rate = M('ProjectProtocolRate')->where(array('financing'=>$val['financing_name'],'add_time'=>$val['stat_date']))->getField('rate');
                if($treaty_rate) {
                    $val['treaty_rate'] = $treaty_rate;
                }
                $res[] = $val;
            }
        }

        vendor('PHPExcel.PHPExcel');

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                    ->setCreator("每日还本付息")
                    ->setLastModifiedBy("每日还本付息")
                    ->setTitle("title")
                    ->setSubject("subject")
                    ->setDescription("description")
                    ->setKeywords("keywords")
                    ->setCategory("Category");

        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
                    ->setCellValue("A1", "融资方")
                    ->setCellValue("B1", "截止日期")
                    ->setCellValue("C1", "金额")
                    ->setCellValue("D1", "平均利率")
                    ->setCellValue("E1", "协议利率")
                    ->setCellValue("F1", "平台收益");

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(38);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        // 设置列表值
        $pos = 2;
        foreach ($res as $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['financing_name']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['stat_date']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['amount']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['avg_rate']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['treaty_rate']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['earnings']);
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户付息表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }



    public function ghost_account_buy_list(){
        $projectId = I('get.projectId', 0, 'int');
        if($projectId) {
            $condition = "project_id = $projectId and user_id <= -101 and user_id >=-130 ORDER BY add_time DESC";
            $sql = "SELECT due_capital as total_amount,user_id,add_time from s_user_due_detail WHERE $condition";
        } else { // and add_time <'2017-06-16'
            $condition = "add_time >= '2017-05-24'  and user_id <= -101 and user_id >=-130 and duration_day >= 30 GROUP BY user_id ORDER BY user_id DESC";
            $sql = "SELECT SUM(due_capital) as total_amount,user_id from s_user_due_detail WHERE $condition";
        }
        $list = M()->query($sql);
        $total_amount = 0;

        foreach ($list as $key =>$val) {
            $userInfo = $this->getGhostAccount($val['user_id']);

            $list[$key]['user_name'] = $userInfo[0];
            $list[$key]['real_name'] = $userInfo[1];

            if($val['add_time']) {
                $list[$key]['add_time'] = date("Y-m-d H:i:s",strtotime($val['add_time']));
            }
            $total_amount +=$val['total_amount'];
        }
        $this->assign("list",$list);
        $this->assign("total_amount",$total_amount);
        $this->display();
    }
    
    /**
    * 平台实时存量信息
    * @date: 2017-5-27 上午11:20:36
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function platform_stock(){
        $res['projectTotalMoney'] = 0;
        $res['walletTotalMoney'] = 0;
        $sql = "SELECT SUM(amount) as amount from s_recharge_log WHERE user_id >0 and status=2 and project_id in(SELECT id FROM s_project WHERE status in(2,3,4) and is_delete=0)";
        $ret = M()->query($sql);
        if($ret) {
            $res['projectTotalMoney'] = $ret[0]['amount'];
        }
        $res['walletTotalMoney'] = M("UserAccount")->sum('wallet_totle');
        
        //申请提现金额，未打款
        $res['walletTotalMoney'] += 0;//abs(M('userWalletRecords')->where('user_id>0 and type=2 and user_bank_id>0 and status=3')->sum('value'));
        
        $res['totalMoney'] = $res['walletTotalMoney'] + $res['projectTotalMoney'];
        $this->assign('res',$res);
        $this->display();
    }
    
    //标的出账记录
    public function chargeoff_log(){
        
        if (!IS_POST) {
            $fid = urldecode(I('fid', '', 'strip_tags'));            
            $startTime = trim(urldecode(I("get.startTime",'','strip_tags')));
            $endTime = trim(urldecode(I("get.endTime",'','strip_tags')));
            $title = trim(urldecode(I("get.title",'','strip_tags')));
        
            $this->assign('financing',M('financing')->field('id,name')->select());
        
            $cond[] = '1=1';
            
            if($fid>0){
                $cond[] = 'fid ='.$fid;
            }
            if($startTime) {
                $cond[] = "create_time >= '$startTime'";
            }
            
            if($endTime) {
                $cond[] = "create_time < '$endTime'";
            }
            
            $projectObj = M('Project');
            $financingObj = M('Financing');
            
            $projectChargeoffLogObj = M('projectChargeoffLog');
            
            if($title) {                
                $idArr = $projectObj->field('id')->where("is_delete=0 and title like '%$title%'")->select();
                
                $idStr = '';
                if($idArr){
                    foreach ($idArr as $val){
                        $idStr .= $val['id'].',';
                    }
                    $idStr = trim($idStr,',');
                }
                if($idStr){
                    $cond[] = "project_id in($idStr)";
                }
            }
            $cond = implode(' and ', $cond);
            $counts = $projectChargeoffLogObj->where($cond)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            
            $list = M('projectChargeoffLog')->field('id,project_id,out_amt,fid,order_no,order_status,create_time,pay_finish_date,pay_finish_time')->where($cond)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
            $total_out_amt = M('projectChargeoffLog')->where($cond .' and order_status =1')->sum('out_amt');
            foreach ($list as $key => $val){
                
                $list[$key]['title'] = $projectObj->where('id='.$val['project_id'])->getField('title');
                $list[$key]['f_name'] = $financingObj->where('id='.$val['fid'])->getField('name');
  
            }
            $this->assign('params', array(
                'fid' => $fid,
                'total_out_amt' => $total_out_amt,
                'title' => $title,
                'startTime'=>$startTime,
                'endTime' => $endTime,
                'counts'=>$counts
            ));
            $this->assign('list',$list);
            $this->assign('show',$show);
            $this->display();
        
        } else {
            $fid = I('post.financing', '', 'strip_tags');
            $startTime = I('post.startTime', '', 'strip_tags');
            $endTime = I('post.endTime', '', 'strip_tags');
            $title = I('post.title', '', 'strip_tags');
            $quest = '';
            if($fid) $quest .= '/fid/'.urlencode($fid);
            if($startTime)$quest .= '/startTime/'.urlencode($startTime);
            if($endTime)$quest .= '/endTime/'.urlencode($endTime);
            if($title) $quest .= '/title/'.$title;
            redirect(C('ADMIN_ROOT') . '/project/chargeoff_log'.$quest);
        }
        
    }
    
    
    //标的成费日志
    public function establish_log(){
        $projectObj = M('Project');
        $financingObj = M('Financing');
        $establishLogObj = M('projectEstablishLog');
        $conds = '1=1';
        $counts = $establishLogObj->where($conds)->count();
        $Page = new \Think\Page($counts, $this->pageSize);
        $show = $Page->show();
        $list = $establishLogObj->where($conds)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
         
        foreach ($list as $key => $val){
            $list[$key]['mess'] = '';
            $list[$key]['code'] = '';
            if($val['memo']) {
                $str = json_decode($val['memo'],true);
                $list[$key]['code'] = $str['code'];
                $list[$key]['mess'] = $str['errorMsg'];
            }
            $prod = $projectObj->field('fid,title')->where('id='.$val['project_id'])->find();
            $list[$key]['f_name'] = $financingObj->where('id='.$prod['fid'])->getField('name');
            $list[$key]['p_title'] = $prod['title'];
        }
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }

    /*
    * 退汇
    */
    public function refund_notify_index(){
        if(!IS_POST) {
            $datetime = I('get.dt', '', 'strip_tags');

            $updatecache = I('get.uc', 0, 'int'); // 更新缓存
            if (!$datetime) $datetime = date('Y-m-d', strtotime('-1 days'));

            $daytime = $datetime;
            $datetime = strtotime($datetime);
            $start_time = $datetime;
            $end_time = $datetime + (24 * 3600 - 1);

            $where = array(
                's_refund_notify.update_time' => array('between', array($start_time, $end_time))
            );

       //     $where = array();

            $refundNotifyObj = M('refund_notify');//退汇表
            $list = $refundNotifyObj->field('s_refund_notify.id as id,s_refund_notify.status as refund_status,u.id as user_id,u.real_name,u.mobile,
            s_refund_notify.order_no,s_refund_notify.plat_no,s_refund_notify.platcust,s_refund_notify.amt,s_refund_notify.update_time')
                ->join('s_user as u ON s_refund_notify.platcust = u.platcust', 'LEFT')
                ->where($where)->order('s_refund_notify.id asc')
                ->select();//  select * from s_user p where p.platcust='2017072118403082310815220'

            $this->assign('list', $list);
            $this->assign('datetime', $daytime);
            $this->display();
        }else{
            $datetime = I('post.dt');
            $flushcache = I('post.flushcache');
            $quest = '';
            if($datetime) $quest .= '/dt/'.$datetime;
            if($flushcache) $quest .= '/uc/1';
            redirect(C('ADMIN_ROOT') . '/project/refund_notify_index'.$quest);
        }
    }

    /*
    * 退汇操作
    */
    public function refund_notify_save(){
        $idArr = I('post.idArr',array());

        if(!$idArr){
            $this->ajaxReturn(array('status'=>0,'info'=>'请选在一个数据'));
        }


        $where = array(
            's_refund_notify.id'=>array('in',$idArr),
            's_refund_notify.status'=>1,//等待退汇
        );
        $model = M();
        $model->startTrans(); // 开启事务

        $depositoryLogObj = M('depository_log');
        $userWalletRecordsObj = M('user_wallet_records');
        $userObj = M('user');


        $userAccountObj  = M('user_account');
        $refundNotifyObj = M('refund_notify');

        $list = M('refund_notify')->field('s_refund_notify.id as id,u.id as user_id,u.real_name,u.mobile,
            s_refund_notify.order_no,s_refund_notify.plat_no,s_refund_notify.platcust,s_refund_notify.amt,s_refund_notify.update_time')
            ->join('s_user as u ON s_refund_notify.platcust = u.platcust', 'LEFT')
            ->where($where)->order('s_refund_notify.id asc')
            ->select();

        if(!$list){
            $this->ajaxReturn(array('status' => 0, 'info' => '没有退汇的数据！'));
        }

        $is_commit = true; //  是否commit
        foreach ($list as $l) {

            $order_no = isset($l['order_no'])?trim($l['order_no']):'';//I('get.order_no','','strip_tags');
            $user_id  = isset($l['user_id'])?intval($l['user_id']):0;//I('get.user_id',0,'int');
            $platcust = isset($l['platcust'])?trim($l['platcust']):'';//I('get.platcust','','strip_tags');
            $amt = isset($l['amt'])?trim($l['amt']):'';//I('get.amt','','string');//退汇金额

            $id = isset($l['id'])?intval($l['id']):0;

            if(empty($user_id)){
                $this->ajaxReturn(array('status' => 0, 'info' => '订单号为：'.$order_no.',用户不存在！'));
            }

            if (!$depositoryLogRow = $depositoryLogObj->where(array('order_no' => $order_no))->find()) { // depository_log 是否有订单
                $is_commit = false;
                $this->ajaxReturn(array('status' => 0, 'info' => '退汇失败,记录订单不存在！'));
            } else if (!$userWalletRecordsObj->where(array('recharge_no' => $order_no))->find()) { // user_wallet_records 是否有订单
                $is_commit = false;
                $this->ajaxReturn(array('status' => 0, 'info' => '退汇失败,钱包记录订单不存在！'));
            } else if (!$userObj->where(array('platcust' => $platcust))->find()) { // user_wallet_records 是否有电子账户
                $is_commit = false;
                $this->ajaxReturn(array('status' => 0, 'info' => '退汇失败,银行存管客户编号不存在！'));
            }
            else if (!$userAccountObj->where(array('user_id' => $user_id))->find()) { // user_account 是否有这个用户
                $is_commit = false;
                $this->ajaxReturn(array('status' => 0, 'info' => '退汇失败,账单用户不存在！'));
            }
            else {
                $accountWhere = array(
                    'user_id' => array('gt', 0),
                    'user_id' => $user_id,
                );

                if (!$userAccountObj->where($accountWhere)->setInc('wallet_totle', $amt)) {//加$amt
                    $is_commit = false;
                    $model->rollback();
                    $this->ajaxReturn(array('status' => 0, 'info' => '退汇失败,累加退汇金额失败'));
                } else {
                    $dataRefund = array('account' => $platcust, 'acct_type' => 12, 'fund_type' => '01', 'signdata' => '账户退汇');
                    vendor('Fund.FD');
                    $fd  = new \FD();
                    if ($jsonArr = $fd->post($this->refund_notify_url, $dataRefund)) { // 厦门接口账户余额明细查询

                        $jsonData = json_decode($jsonArr->resText, true);

                        $success = $jsonData['success'];

                        if ($success) {
                            $result = $jsonData['result'];

                            $balance = isset($result['balance']) ? trim($result['balance']) : 0;

                            $balance = sprintf('%.2f', $balance);
                            $amt = sprintf('%.2f', $amt);


                            if ($balance == $amt) {
                                $dataSave = array('update_time'=>time(),'edit_user_id'=>$this->uid,'status'=>2);
                                if(!$refundNotifyObj->where(array('id'=>$id,'status'=>1))->save($dataSave)){
                                    $is_commit = false;
                                    $model->rollback();
                                    $this->ajaxReturn(array('status' => 0, 'info' => '修改退汇状态失败！'));
                                };
                            }else{
                                $is_commit = false;
                                $this->ajaxReturn(array('status' => 0, 'info' => '退汇失败,退汇金额不对！'));
                            }
                        } else {
                            $is_commit = false;
                            $errorMsg = $jsonData['errorMsg'];
                            $this->ajaxReturn(array('status' => 0, 'info' => $errorMsg));
                        }
                    }
                }
            }

        }

        if($is_commit) { // 是否 都正确
            $model->commit(); // 所有 验证正确 验证
            $this->ajaxReturn(array('status' => 1));
        }else{
            $model->rollback();
        }

    }
    
    //出账导出
    public function chargeoff_log_export(){
        
        vendor('PHPExcel.PHPExcel');
        
        $fid = urldecode(I('fid', '', 'strip_tags'));
        $startTime = trim(urldecode(I("get.startTime",'','strip_tags')));
        $endTime = trim(urldecode(I("get.endTime",'','strip_tags')));
        $title = trim(urldecode(I("get.title",'','strip_tags')));
        
        echo 'startTime:'.$startTime;
        
        $cond[] = '1=1';
        
        if($fid>0){
            $cond[] = 'fid ='.$fid;
        }
        if($startTime) {
            $cond[] = "create_time >= '$startTime'";
        }
        
        if($endTime) {
            $cond[] = "create_time < '$endTime'";
        }
        
        $projectObj = M('Project');
        $financingObj = M('Financing');
        
        $projectChargeoffLogObj = M('projectChargeoffLog');
        
        if($title) {
            $idArr = $projectObj->field('id')->where("is_delete=0 and title like '%$title%'")->select();
        
            $idStr = '';
            if($idArr){
                foreach ($idArr as $val){
                    $idStr .= $val['id'].',';
                }
                $idStr = trim($idStr,',');
            }
            if($idStr){
                $cond[] = "project_id in($idStr)";
            }
        }
        $cond = implode(' and ', $cond);

        $counts = $projectChargeoffLogObj->where($cond)->count();
        
        if($counts <=0) {
            exit('没有记录');
        }
        
        $list = M('projectChargeoffLog')->field('id,project_id,out_amt,fid,order_no,order_status,create_time,pay_finish_date,pay_finish_time')->where($cond)->order('id desc')->select();
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("票票喵票据")
            ->setLastModifiedBy("票票喵票据")
            ->setTitle("title")
            ->setSubject("subject")
            ->setDescription("description")
            ->setKeywords("keywords")
            ->setCategory("Category");
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
            ->setCellValue("A1", "产品名称")
            ->setCellValue("B1", "出账时间")
            ->setCellValue("C1", "完成时间")
            ->setCellValue("D1", "出账金额")
            ->setCellValue("E1", "订单号")
            ->setCellValue("F1", "状态")
            ->setCellValue("G1", "融资方名称");

        
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
    
        $pos = 2;
        $n = 1;
        foreach ($list as $key => $val){

            $title = $projectObj->where('id='.$val['project_id'])->getField('title');
            $f_name = $financingObj->where('id='.$val['fid'])->getField('name');
            
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$title);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos,$val['create_time']);             
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['pay_finish_date'] .' '.$val['pay_finish_time']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['out_amt']); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos,$val['order_no']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$val['order_status']=='0'?'初始':($val['order_status'] == '1' ? '成功' : '失败'));
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,$f_name);           
            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="出账日志('.date("Y-m-d H:i:s").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    //幽林账号购买
    private function getGhostAccount($k){
        $arr = array(
            '-101'=>array('13600009252','赵丽坤'),
            '-102'=>array('13300006969','芮宇浩'),
            '-103'=>array('13300001518','余栋韬'),
            '-104'=>array('17800001844','杨岚芝'),
            '-105'=>array('15200005715','赵量'),
            '-106'=>array('13000006566','沈立军'),
            '-107'=>array('13400003922','李娜'),
            '-108'=>array('18600008078','陈大鹏'),
            '-109'=>array('18800000708','林轩'),
            '-110'=>array('15800009477','王艳 '),
            '-111'=>array('18700000140','钱东'),
            '-112'=>array('13500006064','李天涯'),
            '-113'=>array('18200004205','王勇'),
            '-114'=>array('15100009049','朱健康'),
            '-115'=>array('15300002022','郑浩'),
            '-116'=>array('17800001844','廖建东'),
            '-117'=>array('15900003419','陆剑波'),
            '-118'=>array('15200004790','方辉'),
            '-119'=>array('18800003520','方勇'),
            '-120'=>array('18200009291','吴建杰'),            
            '-121'=>array('13807228569','饶小梦'),
            '-122'=>array('15874859755','徐晓慧'),
            '-123'=>array('18682000030','沈文'),
            '-124'=>array('13654663321','章立彪'),
            '-125'=>array('13822556877','石磊'),
            '-126'=>array('13847772158','史家聪'),            
            '-127'=>array('15162248801','刘宜德 '),            
            '-128'=>array('15177417650','赵嘉琪'),
            '-129'=>array('18200006005','俞晓佳'),
            '-130'=>array('13300001171','韦俐芬'),
        );
        if ($k>0) $k = -$k;
        return $arr[$k];
    }


    private function getTagName($tagId) {
        $s = '';
        if($tagId == 0) {
            $s = '普通';
        }else if($tagId == 1) {
            $s = '新人特惠';
        }else if($tagId == 2) {
            $s = '爆款';
        }else if($tagId == 3) {
            $s = 'HOT';
        }else if($tagId == 6) {
            $s = '活动';
        }else if($tagId == 8) {
            $s = '私人专享';
        }else if($tagId == 9){
            $s = '月月加薪';
        }
        return $s;
    }

    /*
     * 添加和合同表相关信息
     */
    private function addContractInfo($contract_no){
        return;
        // 不同步
        $row = array();
       // $draft_type = I('post.draft_type', 0, 'int'); // 汇票类型
        $accepting_bank = I('post.accepting_bank', '', 'strip_tags');//承兑机构
        //$guaranty_type = I('post.guaranty_type', 0, 'int'); //担保类型
        $guaranty_institution = I('post.guaranty_institution', '', 'strip_tags');//承兑机构
        if($accepting_bank){
            $row['accepting_institution'] = $accepting_bank;
        }
        if($guaranty_institution){
            $row['guaranty_institution'] = $guaranty_institution;
        }

        if($row) {
            M('Contract')->where(array('name' => $contract_no))->save($row);
        }
        return;
    }


    private function getTags(){
        $advertisementTagObj = new \Admin\Model\AdvertisementTagModel(); // 公告标签
        return $advertisementTagObj->getAdvertisementTagWhere();
    }

    private function getSpecialTags(){
        $advertisementTagObj = new \Admin\Model\AdvertisementTagModel(); // 特殊标签
        return $advertisementTagObj->getAdvertisementTagType($type=1);
    }

    private function getTagsAll(){
        $advertisementTagObj = new \Admin\Model\AdvertisementTagModel(); // 公告标签
        return $advertisementTagObj->getAdvertisementTagWhere($f=1);
    }

}
