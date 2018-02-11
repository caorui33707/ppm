<?php
namespace Admin\Controller;

/**
 * 财务相关控制器
 * @package Admin\Controller
 */
class FinanceController extends AdminController{

    /**
     * 融资人付款明细列表
     */
    public function FinancingPayment(){
        if(!IS_POST){
            $key = urldecode(trim(I('get.key', '', 'strip_tags')));
            $type = I('get.type', 0, 'int');
            $stEnd = I('get.stend', '', 'strip_tags');
            $etEnd = I('get.etend', '', 'strip_tags');
            $stStart = I('get.ststart', '', 'strip_tags');
            $etStart = I('get.etstart', '', 'strip_tags');
            $params = array(
                'key' => $key,
                'type' => $type,
                'st_end' => $stEnd,
                'et_end' => $etEnd,
                'st_start' => $stStart,
                'et_start' => $etStart,
            );
            $this->assign('params', $params);

            $count = 10;
            $projectObj = M("Project");
            $userDueDetailObj = M("UserDueDetail");
            $rechargeLogObj = M("RechargeLog");
            $contractObj = M("Contract");
            $contractProjectObj = M("ContractProject");
            $projectIssuedObj = M("ProjectIssued");

            $cond[] = 'is_delete=0';
            if($key) $cond[] = "title='".$key."'";
            if($type) $cond[] = "type=".$type;
            if($stEnd) $cond[] = "end_time>='".$stEnd." 00:00:00.000000'";
            if($etEnd) $cond[] = "end_time<='".$etEnd." 23:59:59.999000'";
            if($stStart) $cond[] = "start_time>='".$stStart." 00:00:00.000000'";
            if($etStart) $cond[] = "start_time<='".$etStart." 23:59:59.999000'";
            $conditions = implode(' and ', $cond);

            $counts = $projectObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();//$Page->firstRow . ',' . $Page->listRows
            $list = $projectObj->where($conditions)->order('id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $startTime = ''; $endTime = '';
            $html = '';
            $totleUserInterest = 0; // 用户总利息
            $totleInterest = 0; // 当前页借款总利息
            $totleInterestOne = 0; // 当前页借款+1总利息
            $totleSx = 0; // 当前页总手续费
            foreach($list as $key => $val){
                $startTime = $userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->order('add_time asc')->getField('add_time');
                $endTime = $userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->order('add_time desc')->getField('add_time');
                if($startTime){
                    $startTime = date('Y-m-d', strtotime($startTime));
                    $endTime = date('Y-m-d', strtotime($endTime));
                    $pos = 0;
                    $days = count_days($endTime, $startTime);
                    $totlePrice = 0;
                    // 获取产品线下利率
                    $rate = 0;
                    $fee = 0; // 手续费
                    $contractId = $contractProjectObj->where(array('project_name'=>$val['title']))->getField('contract_id');
                    if($contractId){
                        $contractInfo = $contractObj->field('interest,fee')->where(array('id'=>$contractId))->find();
                        $rate = $contractInfo['interest'];
                        $fee = $contractInfo['fee'];
                    }
                    while(true){
                        $dayPrice = 0; // 今日金额
                        $sxPrice = 0; // 手续费
                        $html .= '<tr>';
                        // 标名称
                        if($pos === 0){
                            if($days > 0) $html .= '<td rowspan="'.($days+1).'">';
                            else $html .= '<td>';
                            $html .= $val['title'].'</td>';
//                            if(in_array($val['type'], array(2,3,69,70))){
//                                $title = str_replace('石头', '', $val['title']);
//                                $title = str_replace('号第', '', $title);
//                                $title = str_replace('期', '', $title);
//                                $html .= $title.'</td>';
//                            }
                        }
                        // 到期日
                        $html .= '<td align="center">'.date('Y-m-d', strtotime($val['end_time'])).'</td>';
                        // 上线日
                        $html .= '<td align="center">'.$startTime.'</td>';
                        // 本金
                        if($pos === 0){
                            if($days > 0) $html .= '<td rowspan="'.($days+1).'">';
                            else $html .= '<td>';
                            $html .= number_format($val['amount'], 2).'</td>';
                        }
                        // 期限
                        $qixian = count_days($val['end_time'], $startTime);
                        $html .= '<td>'.$qixian.'</td>';
                        // 日销售量
                        $condition = "project_id=".$val['id']." and status=2 and user_id>0 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'";
                        $dayList = $rechargeLogObj->field('id,type,amount')->where($condition)->select();
                        foreach($dayList as $k => $v){
                            $dayPrice += $v['amount'];
                            // 计算手续费
                            switch($v['type']){
                                case 1: // 连连
                                    $sxPrice += $v['amount']*0.002;
                                    break;
                                case 2: // 易宝
                                    if($v['amount'] <= 1000){
                                        $sxPrice += $v['amount']*0.002;
                                    }else{
                                        $sxPrice += $v['amount']*0.0015;
                                    }
                                    break;
                            }
                        }
                        $totlePrice += $dayPrice;
                        $html .= '<td>'.number_format($dayPrice, 2).'</td>';
                        // 超标金额
                        if($pos === 0){
                            if($days > 0) $html .= '<td rowspan="'.($days+1).'">';
                            else $html .= '<td>';
                            $html .= '#cbje#</td>';
                        }
                        // 线上利率
                        if($pos === 0){
                            if($days > 0) $html .= '<td rowspan="'.($days+1).'">';
                            else $html .= '<td>';
                            $html .= $val['user_interest'].'%</td>';
                        }
                        // 手续费
                        $html .= '<td>'.number_format($sxPrice, 2).'</td>';
                        $totleSx += $sxPrice;
                        // 用户利息
                        $interest = $dayPrice*$qixian*$val['user_interest']/100/365;
                        $totleUserInterest += $interest;
                        $html .= '<td>'.number_format($interest, 2).'</td>';
                        // 线下利率
                        if($pos === 0){
                            if($days > 0) $html .= '<td rowspan="'.($days+1).'">';
                            else $html .= '<td>';
                            $html .= $rate.'%</td>';
                        }
                        // 下发时间
                        $projectIssuedInfp = $projectIssuedObj->where(array('project_id'=>$val['id'],'online_time'=>$startTime))->find();
                        $issuedTime = $projectIssuedInfp['issued_time'];
                        $html .= '<td>'.$issuedTime.'</td>';
                        // 下发金额
                        $issuedPrice = $projectIssuedInfp['issued_price'];
                        $html .= '<td>'.number_format($issuedPrice, 2).'</td>';
                        // 借款利息
                        $jkInterest = 0;
                        if($issuedTime && $issuedPrice){
                            $jkInterest = (($issuedPrice*($rate/100)*(count_days($val['end_time'],$issuedTime))/365)+($issuedPrice*$fee/100));
                            $totleInterest += $jkInterest;
                            $html .= '<td>'.number_format($jkInterest, 2).'</td>';
                        }else{
                            $html .= '<td>0</td>';
                        }

                        // 借款利息+1
                        $jkPrice = 0;
                        if($issuedTime && $issuedPrice){
                            $jkPrice = (($issuedPrice*($rate/100)*(count_days($val['end_time'],$issuedTime)+1)/365)+($issuedPrice*$fee/100));
                            $totleInterestOne += $jkPrice;
                            $html .= '<td>'.number_format($jkPrice, 2).'</td>';
                        }else{
                            $html .= '<td>0</td>';
                        }

                        // 息差收入
                        $html .= '<td>'.number_format($jkInterest-$sxPrice-$interest, 2).'</td>';
                        $html .= '</tr>';
                        $pos += 1;
                        if($startTime != $endTime) {
                            $startTime = date('Y-m-d', strtotime('+1 days', strtotime($startTime)));
                        } else {
                            if($val['amount'] != $totlePrice){
                                $html = str_replace('#cbje#', number_format(abs($totlePrice - $val['amount']), 2), $html);
                            }else{
                                $html = str_replace('#cbje#', 0, $html);
                            }
                            break;
                        }
                    }
                }
            }
            $html .= '<tr><td colspan="8" align="right">合计:</td><td align="right">'.number_format($totleSx,2).'</td><td align="right">'.number_format($totleUserInterest,2).'</td><td colspan="3" align="right">合计:</td><td align="right">'.number_format($totleInterest,2).'</td><td align="right">'.number_format($totleInterestOne,2).'</td><td align="right">'.number_format($totleInterest-$totleSx-$totleUserInterest,2).'</td></tr>';
            $this->assign('show', $show);
            $this->assign('html', $html);
            $this->display();
        }else{
            $key = trim(I('post.key', '', 'strip_tags'));
            $type = I('post.type', 0, 'int');
            $stEnd = I('post.st_end', '', 'strip_tags');
            $etEnd = I('post.et_end', '', 'strip_tags');
            $stStart = I('post.st_start', '', 'strip_tags');
            $etStart = I('post.et_start', '', 'strip_tags');
            $query = '';
            if($key) $query .= '/key/'.urlencode($key);
            if($type) $query .= '/type/'.$type;
            if($stEnd) $query .= '/stend/'.$stEnd;
            if($etEnd) $query .= '/etend/'.$etEnd;
            if($stStart) $query .= '/ststart/'.$stStart;
            if($etStart) $query .= '/etstart/'.$etStart;
            redirect(C('ADMIN_ROOT').'/Finance/FinancingPayment'.$query);
        }
    }

    /**
     * 项目下发金额列表
     */
    public function issued(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 20; // 每页显示条数
            $key = urldecode(I('get.key', '', 'strip_tags')); // 搜索关键字

            $projectIssuedObj = M('ProjectIssued');

            $conditions = array();
            if ($key) $cond[] = "project_title='".$key."'";
            $cond[] = "is_delete=0";
            if ($cond) $conditions = implode(' and ', $cond);
            $counts = $projectIssuedObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $orderby = 'project_id';
            $list = $projectIssuedObj->where($conditions)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $params = array(
                'page' => $page,
                'key' => $key,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }else{
            $key = I('post.key', '', 'strip_tags');
            $query = '';
            if($key) $query .= '/key/'.$key;
            redirect(C('ADMIN_ROOT').'/finance/issued'.$query);
        }
    }

    /**
     * 添加项目下发
     */
    public function issued_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $projectObj = M("Project");
            $projectIssuedObj = M("ProjectIssued");

            $projectTitle = trim(I('post.project_name', '', 'strip_tags'));
            $price = I('post.price', 0);
            $datetime = I('post.datetime', '', 'strip_tags');
            $online_time = I('post.online_time', '', 'strip_tags');

            if(!$projectTitle) $this->ajaxReturn(array('status'=>0,'info'=>'项目名称不能为空'));
            if(!$price) $this->ajaxReturn(array('status'=>0,'info'=>'下发金额必须大于0'));
            if(!$datetime) $this->ajaxReturn(array('status'=>0,'info'=>'下发日期不能为空'));
            if(!$online_time) $this->ajaxReturn(array('status'=>0,'info'=>'上线日期不能为空'));

            $projectId = $projectObj->where(array('title'=>$projectTitle,'is_delete'=>0))->getField('id');
            if(!$projectId) $this->ajaxReturn(array('status'=>0,'info'=>'项目信息不存在或已被删除'));

            $time = date('Y-m-d H:i:s', time());
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'project_id' => $projectId,
                'project_title' => $projectTitle,
                'issued_price' => $price,
                'issued_time' => $datetime,
                'online_time' => $online_time,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if($projectIssuedObj->where(array('project_id'=>$projectId,'issued_time'=>$datetime,'online_time'=>$online_time))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同的项目下发信息'));
            if(!$projectIssuedObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/finance/issued'));
        }
    }

    /**
     * 编辑项目下发
     */
    public function issued_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $key = urldecode(I('get.key', '', 'strip_tags'));
            $params = array(
                'page' => $page,
                'key' => $key,
            );
            $this->assign('params', $params);

            $projectIssuedObj = M("ProjectIssued");

            $detail = $projectIssuedObj->where(array('id'=>$id))->find();
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $projectObj = M("Project");
            $projectIssuedObj = M("ProjectIssued");

            $id = I('post.id', 0, 'int');
            $projectTitle = trim(I('post.project_name', '', 'strip_tags'));
            $price = I('post.price', 0);
            $datetime = I('post.datetime', '', 'strip_tags');
            $online_time = I('post.online_time', '', 'strip_tags');
            $page = I('post.page', 1, 'int');
            $key = urlencode(I('post.key', '', 'strip_tags'));

            if(!$projectTitle) $this->ajaxReturn(array('status'=>0,'info'=>'项目名称不能为空'));
            if(!$price) $this->ajaxReturn(array('status'=>0,'info'=>'下发金额必须大于0'));
            if(!$datetime) $this->ajaxReturn(array('status'=>0,'info'=>'下发日期不能为空'));

            $projectId = $projectObj->where(array('title'=>$projectTitle,'is_delete'=>0))->getField('id');
            if(!$projectId) $this->ajaxReturn(array('status'=>0,'info'=>'项目信息不存在或已被删除'));

            $time = date('Y-m-d H:i:s', time());
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'project_id' => $projectId,
                'project_title' => $projectTitle,
                'issued_price' => $price,
                'issued_time' => $datetime,
                'online_time' => $online_time,
                'add_time' => $time,
                'add_user_id' => $uid,
            );
            if($projectIssuedObj->where("project_id=".$projectId." and issued_time='".$datetime."' and online_time='".$online_time."' and id<>".$id)->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同的项目下发信息'));
            if(!$projectIssuedObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $query = '';
            if($key) $query .= '/key/'.$key;
            if($page) $query .= '/p/'.$page;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/finance/issued'.$query));
        }
    }

    /**
     * 删除项目下发
     */
    public function issued_delete(){
        if(!IS_POST && !IS_AJAX) exit;

        $id = I("post.id", 0, 'int');

        $projectIssuedObj = M("ProjectIssued");
        $time = date('Y-m-d H:i:s', time());
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        $rows = array(
            'is_delete' => 1,
            'modify_time' => $time,
            'modify_user_id' => $uid,
        );
        if(!$projectIssuedObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 结构化公募基金数据统计
     */
    public function fund(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 10; // 每页显示条数

            $projectObj = M("Project");
            $projectModelFundObj = M("ProjectModelFund");
            $fundObj = M("Fund");
            $fundDetailObj = M("FundDetail");

            $fundList = $fundObj->where(array('type'=>1,'is_delete'=>0))->select();
            foreach($fundList as $key => $val){ // 获取最新净值
                $fundList[$key]['last_value'] = $fundDetailObj->where(array('fund_id'=>$val['id']))->order('datetime desc')->getField('val');
            }

            $conditions = "type in (109,110)";
            $counts = $projectObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $projectObj->field('id,title,amount,end_time,advance_end_time,type')->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['ext'] = $projectModelFundObj->where(array('project_id'=>$val['id']))->find();
                foreach($fundList as $k => $v){
                    if($list[$key]['ext']['fund_id'] == $v['id']){
                        $list[$key]['fund_code'] = $v['code'];
                        $list[$key]['fund_last_val'] = $v['last_value'];
                        if($val['advance_end_time']){ // 提前结束
                            // 结束净值
                            $list[$key]['finish_code'] = $fundDetailObj->where("fund_id=".$v['id']." and datetime<='".$val['advance_end_time']."'")->order('datetime desc')->getField('val');
                        }
                    }
                }
                // 整体盈利
                $list[$key]['profit'] = ($list[$key]['fund_last_val']-$list[$key]['ext']['start_net'])*$list[$key]['ext']['check_money'];
                // 当前收益
                if($val['type'] == 109){ // B基金
                    if($list[$key]['profit'] > 0){
                        $list[$key]['income'] = (2*$list[$key]['profit']*365)/(15*$val['amount']*count_days(date('Y-m-d',time()), date('Y-m-d',strtotime($list[$key]['ext']['enter_time']))));
                    }else{
                        $list[$key]['income'] = 0;
                    }
                    // 平台获利分成
                    if($list[$key]['income'] > 0.2) {
                        $list[$key]['divided_into'] = ($list[$key]['income'] - 0.2)*0.5*($list[$key]['profit']*1/7.5)/$list[$key]['income'];
                    }else{
                        $list[$key]['divided_into'] = 0;
                    }
                }else if($val['type'] == 110){ // A基金
                    if($list[$key]['profit'] > 0){
                        $list[$key]['income'] = (13*$list[$key]['profit']*365)/(15*$val['amount']*count_days(date('Y-m-d',time()), date('Y-m-d',strtotime($list[$key]['ext']['enter_time']))));
                    }else{
                        $list[$key]['income'] = $list[$key]['profit']/$val['amount'];
                    }
                    // 平台获利分成
                    if($list[$key]['income'] > 1) {
                        $list[$key]['divided_into'] = ($list[$key]['income'] - 1)*0.5*($list[$key]['profit']*6.5/7.5)/$list[$key]['income'];
                    }else{
                        $list[$key]['divided_into'] = 0;
                    }
                }
                // 平台收益
                $list[$key]['platform_revenue'] = $list[$key]['divided_into'] - $val['amount']*$list[$key]['ext']['purchase_fee'] - $list[$key]['ext']['redemption_fee'];
            }

            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{

        }
    }

    /**
     * 编辑基金类产品额外信息
     */
    public function fund_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int'); // 产品ID

            $projectObj = M("Project");
            $projectModelFundObj = M("ProjectModelFund");
            $fundObj = M("Fund");

            $detail = $projectObj->field('id,title')->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                echo '项目信息不存在或已被删除';exit;
            }
            $detail['ext'] = $projectModelFundObj->where(array('project_id'=>$id))->find();
            $detail['fund'] = $fundObj->where(array('id'=>$detail['ext']['fund_id']))->find();

            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $check_money = I('post.check_money', 0); // 确认份额
            $purchase_fee = I('post.purchase_fee', 0); // 申购费率
            $redemption_fee = I('post.redemption_fee', 0); // 赎回费

            $projectModelFundObj = M("ProjectModelFund");

            $rows = array(
                'check_money' => $check_money,
                'purchase_fee' => $purchase_fee,
                'redemption_fee' => $redemption_fee,
            );
            if(!$projectModelFundObj->where(array('project_id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'.$projectModelFundObj->getLastSql()));
            $this->ajaxReturn(array('status'=>1));
        }
    }

}