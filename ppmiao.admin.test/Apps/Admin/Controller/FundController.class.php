<?php
namespace Admin\Controller;

/**
 * 基金数据管理控制器
 * @package Admin\Controller
 */
class FundController extends AdminController{

    /**
     * 基金列表
     */
    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $search = urldecode(I('get.s', '', 'strip_tags'));
            $count = 20;

            $fundObj = M('Fund');

            $conditions = array();
            $cond[] = "is_delete=0";
            if ($search) $cond[] = "name like '%" . $search . "%' or code like '%".$search."%'";
            if ($cond) $conditions = implode(' and ', $cond);
            $counts = $fundObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $fundObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $params = array(
                'page' => $page,
                'search' => $search,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }else{
            $key = I('post.key', '', 'strip_tags');
            $quest = '';
            if($key) $quest .= '/s/'.urlencode($key);
            redirect(C('ADMIN_ROOT') . '/fund/index'.$quest);
        }
    }

    /**
     * 添加基金
     */
    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $name = I('post.name', '', 'strip_tags');
            $code = I('post.code', '', 'strip_tags');
            $type = I('post.type', 1, 'int');
            $type2 = I('post.type2', 1, 'int');
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';

            $fundObj = M('Fund');
            if($fundObj->where(array('code'=>$code,'is_delete'=>0))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同代码的基金'));
            $rows = array(
                'name' => $name,
                'code' =>   $code,
                'type' =>   $type,
                'type2' =>   $type2,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$fundObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/fund/index'));
        }
    }

    /**
     * 编辑基金
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $fundObj = M('Fund');
            $detail = $fundObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail) {
                $this->error('数据不存在或已被删除');
                exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $id = I('post.id', 0, 'int');
            $name = I('post.name', '', 'strip_tags');
            $code = I('post.code', '', 'strip_tags');
            $type = I('post.type', 1, 'int');
            $type2 = I('post.type2', 1, 'int');
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';

            $fundObj = M('Fund');
            if($fundObj->where("code='".$code."' and id<>".$id.' and is_delete=0')->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同代码的基金'));
            $rows = array(
                'name' => $name,
                'code' => $code,
                'type' => $type,
                'type2' => $type2,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$fundObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/fund/index'));
        }
    }

    /**
     * 净值界面
     */
    public function net(){
        $time_interval = 30; // 默认显示时间间隔(30天)
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $start_time = I('get.st', '', 'strip_tags');
            $end_time = I('get.et', date('Y-m-d', time()), 'strip_tags');
            if(!$start_time) $start_time = date('Y-m-d', strtotime('-'.$time_interval.' days'));
            $params = array(
                'st' => $start_time,
                'et' => $end_time,
            );
            $this->assign('params', $params);

            $fundObj = M('Fund');
            $detail = $fundObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('基金详细信息不存在或已被删除');exit;
            }

            // 获取某个时间段内基金走势图
            $currentYear = date('Y', time());
            $fundDetailObj = M('FundDetail');
            $conditions = "fund_id=".$detail['id'];
            $conditions .= " and datetime>='".$start_time."'";
            $conditions .= " and datetime<='".$end_time."'";
            $list =  $fundDetailObj->where($conditions)->order('datetime asc')->select();

            $categories = ''; // X轴描述
            $net = ''; // 净值
            foreach($list as $key => $val){
                $categories .= ",'".str_replace($currentYear.'-', '', $val['datetime'])."'";
                $net .= ','.$val['val'];
            }
            if($categories) $categories = substr($categories, 1);
            if($net) $net = substr($net, 1);

            $this->assign('categories', $categories);
            $this->assign('net', $net);
            $this->assign('detail', $detail);
            $this->assign('stime', date('Y-m-d', strtotime('-'.$time_interval.' days')));
            $this->assign('etime', date('Y-m-d', time()));
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $start_time = I('post.st', '', 'strip_tags');
            $end_time = I('post.et', date('Y-m-d', time()), 'strip_tags');
            if(!$start_time) $start_time = date('Y-m-d', strtotime('-'.$time_interval.' days'));

            redirect(C('ADMIN_ROOT').'/fund/net/st/'.$start_time.'/et/'.$end_time.'/id/'.$id);
        }
    }

    /**
     * 从基金网站抓取基金数据
     */
    public function net_grab(){
        if(!IS_POST){
            include_once('include/simple_html_dom.php');
            ini_set("memory_limit", "1000M");
            ini_set("max_execution_time", 0);
            $id = I('get.id', 0, 'int');
            $action = I('get.action', 'search', 'strip_tags');
            $start_time = I('get.st', '', 'strip_tags');
            $end_time = I('get.et', '', 'strip_tags');
            $code = I('get.code', '', 'strip_tags');
            $source = I('get.source', 1, 'int'); // 数据来源(1:和讯基金/2:天天基金网)
            $params = array(
                'st' => $start_time,
                'et' => $end_time,
                'code' => $code,
                'source' => $source,
            );
            $this->assign('params', $params);

            $fundObj = M('Fund');
            $fundDetailObj = M('FundDetail');
            $detail = $fundObj->where(array('id'=>$id,'is_delete'=>0))->find();
            if(!$detail){
                $this->error('基金详细信息不存在或已被删除');exit;
            }

            // 从基金网站抓取数据
            switch($source){
                case 1: // 和讯基金
                    $url = 'http://jingzhi.funds.hexun.com/database/jzzs.aspx?fundcode='.$code.'&startdate='.$start_time.'&enddate='.$end_time;
                    $rows = array();
                    if($code && $start_time && $end_time){ // 开始抓取数据
                        $html = file_get_html($url);
                        $dTable = $html->find(".m_table tbody", 0);
                        foreach($dTable->find('tr') as $element){
                            $tds = $element->find('td');
                            if(count($tds) > 0){
                                $subRows = array(
                                    'datetime' => $tds[0]->plaintext,
                                    'val' => ($detail['type2'] == 1 ? $tds[2]->plaintext : $tds[1]->plaintext),
                                    'day_increment' => str_replace('%', '', $tds[3]->plaintext),
                                );
                                $rows[] = $subRows;
                            }
                        }
                        $html->clear();

                        // 和本地数据做对比
                        $local = $fundDetailObj->field('datetime')->where("fund_id=".$id." and datetime>='".$start_time."' and datetime<='".$end_time."'")->select();
                        $localArr = array();
                        foreach($local as $key => $val) array_push($localArr, $val['datetime']);
                        foreach($rows as $key => $val){
                            if(in_array($val['datetime'], $localArr)) $rows[$key]['exist'] = 1;
                            else $rows[$key]['exist'] = 0;
                        }

                        $this->assign('grab_rows', $rows);
                    }
                    break;
                case 2: // 天天基金网
                    $url = 'http://fund.eastmoney.com/f10/F10DataApi.aspx?type=lsjz&code='.$code.'&page=1&per=1000'.'&sdate='.$start_time.'&edate='.$end_time.'&rt='.rand(0,1);
                    $rows = array();
                    if($code && $start_time && $end_time){ // 开始抓取数据
                        $data = file_get_contents($url);
                        $data = str_replace("var apidata={ content:\"", "", $data);
                        $data = str_replace("\",records:20,pages:1,curpage:1};", "", $data);
                        $html = new \simple_html_dom();
                        //$html = file_get_html($data);
                        $html->load($data);
                        $dTable = $html->find(".lsjz tbody", 0);
                        foreach($dTable->find('tr') as $element){
                            $tds = $element->find('td');
                            if(count($tds) > 0){
                                $subRows = array(
                                    'datetime' => $tds[0]->plaintext,
                                    'val' => ($detail['type2'] == 1 ? $tds[2]->plaintext : $tds[1]->plaintext),
                                    'day_increment' => str_replace('%', '', $tds[3]->plaintext),
                                );
                                $rows[] = $subRows;
                            }
                        }
                        $html->clear();

                        // 和本地数据做对比
                        $local = $fundDetailObj->field('datetime')->where("fund_id=".$id." and datetime>='".$start_time."' and datetime<='".$end_time."'")->select();
                        $localArr = array();
                        foreach($local as $key => $val) array_push($localArr, $val['datetime']);
                        foreach($rows as $key => $val){
                            if(in_array($val['datetime'], $localArr)) $rows[$key]['exist'] = 1;
                            else $rows[$key]['exist'] = 0;
                        }

                        $this->assign('grab_rows', $rows);
                    }
                    break;
            }

            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $action = I('post.action', '', 'strip_tags');
            $start_time = I('post.st', '', 'strip_tags');
            $end_time = I('post.et', '', 'strip_tags');
            $code = I('post.code', '', 'strip_tags');
            $source = I('post.source', 1, 'int');
            switch($action){
                case 'search': // 查询数据
                    $quest = '';
                    if($start_time) $quest .= '/st/'.$start_time;
                    if($end_time) $quest .= '/et/'.$end_time;
                    if($code) $quest .= '/code/'.$code;
                    if($source) $quest .= '/source/'.$source;
                    redirect(C('ADMIN_ROOT').'/fund/net_grab/id/'.$id.'/action/'.$action.$quest);
                    break;
                case 'grab': // 开始抓取数据
                    $fundDetailObj = M('FundDetail');

                    break;
            }
        }
    }

    /**
     * 添加基金净值数据
     */
    public function net_add(){
        if(!IS_POST){
            $fund_id = I('get.id', 0, 'int');
            $this->assign('fund_id', $fund_id);
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $fund_id = I('post.fund_id', 0, 'int');
            $datetime = I('post.datetime', '', 'strip_tags');
            $val = I('post.val', 0);
            $day_increment = I('post.day_increment', 0);

            $fundDetailObj = M('FundDetail');
            if($fundDetailObj->where(array('fund_id'=>$fund_id,'datetime'=>$datetime))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该天净值已存在,请勿重复抓取'));
            $rows = array(
                'fund_id' => $fund_id,
                'datetime' => $datetime,
                'val' => $val,
                'day_increment' => $day_increment,
                'worth_date' => $datetime.' '.date('H:i:s', time()),
            );
            if(!$fundDetailObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加净值失败,请重试'));
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 添加基金净值数据(全部)
     */
    public function net_addall(){
        if(!IS_POST || !IS_AJAX) exit;

        $data = I('post.data');
        if(!$data) $this->ajaxReturn(array('status'=>0,'info'=>'没有可添加的基金净值'));
        $dataArr = array();
        $datetimeArr = '';
        $fund_id = 0;
        foreach($data as $key => $val){
            $tmp = explode(',', $val);
            $dataArr[$key]['fund_id'] = $tmp[0];
            $dataArr[$key]['datetime'] = $tmp[1];
            $dataArr[$key]['val'] = $tmp[2];
            $dataArr[$key]['day_increment'] = $tmp[3];
            $dataArr[$key]['worth_date'] = $tmp[1].' '.date('H:i:s', time());
            $fund_id = $tmp[0];
            $datetimeArr .= ",'".$tmp[1]."'";
        }
        if($datetimeArr) $datetimeArr = substr($datetimeArr, 1);

        $fundDetailObj = M('FundDetail');
        if($fundDetailObj->where("fund_id = ".$fund_id." and datetime in (".$datetimeArr.")")->count() > 0) $this->ajaxReturn(array('status'=>0,'info'=>'本次批量添加含有重复数据,请刷新页面后重新提交'));
        if(!$fundDetailObj->addAll($dataArr)) $this->ajaxReturn(array('status'=>0,'info'=>'批量添加失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 更新净值
     */
    public function net_update(){
        if(!IS_POST || !IS_AJAX) exit;

        $fund_id = I('post.fund_id', 0, 'int');
        $datetime = I('post.datetime', '', 'strip_tags');
        $val = I('post.val', 0);
        $day_increment = I('post.day_increment', 0);

        $fundDetailObj = M('FundDetail');
        if(!$fundDetailObj->where(array('fund_id'=>$fund_id,'datetime'=>$datetime))->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该天净值信息不存在或已被删除'));
        $rows = array(
            'val' => $val,
            'day_increment' => $day_increment,
            'worth_date' => $datetime.' '.date('H:i:s'),
        );
        if(!$fundDetailObj->where(array('fund_id'=>$fund_id,'datetime'=>$datetime))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'更新净值失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 删除基金
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');

        $fundObj = M('Fund');
        $detail = $fundObj->where(array('id'=>$id,'is_delete'=>0))->find();
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'基金数据不存在或已被删除'));
        if(!$fundObj->where(array('id'=>$id))->save(array('is_delete'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));

    }

    /**
     * 基金选择界面
     */
    public function select_page(){
        if(!IS_POST){
            $fundObj = M('Fund');
            $list = $fundObj->order('add_time desc')->select();

            $this->assign('list', $list);
            $this->display();
        }else{

        }
    }

}