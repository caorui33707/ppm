<?php

/**
 * 抽奖活动
 */
namespace Home\Controller;

use Think\Controller;

class LotteryController extends BaseController {

    private $userId = 0;


    public function __construct(){
        $token = trim(I('post.mobile'));
        $user = M('User')->where(['username'=>$token])->find();
        $this->userId = $user['id'];
    }


    public function event20161115(){
        $lottery_id = 1;
        $user_name = trim(I('get.mobile','','strip_tags'));

        $user_id = 0;
        if($user_name) {
            $user_id = M('User')->where(array('username'=>$user_name))->getField('id');
        }

        $lottery_info = $this->get_lottery_base($lottery_id);

        $lottery_log_list = M('LotteryLog')
                            ->where("lottery_id=" . $lottery_id . ' and create_time >=' . $lottery_info['start_time'])
                            ->order('create_time desc')
                            ->limit(50)
                            ->select();

        foreach($lottery_log_list as $key => $val){
            $lottery_log_list[$key]['prize_name'] = $this->get_lottery_award($val['lottery_id'],$val['lottery_award_id'])['name'];
            $lottery_log_list[$key]['user_name'] =  substr_replace($lottery_log_list[$key]['user_name'],'****',3,4);
        }

        $cnt = 0;
        if($user_id > 0 && $lottery_info) {
            $cnt = $this->get_lottery_count($user_id, $lottery_id) - $this->get_use_lottery_count($user_id,$lottery_id);
            if($cnt<0){
                $cnt = 0;
            }
        }

        $this->assign('params', array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'lottery_id'=>$lottery_id,
            'lottery_log'=>$lottery_log_list,
            'cnt'=>$cnt
        ));
        $this->display('event20161115');
    }

    /**
    * 圣诞节活动,默认送一次抽奖机会
    * @date: 2016-12-22 上午11:37:38
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20161222() {

        $lottery_id = 2;//抽奖id

        $give_cnt = 1;//默认送1次

        $user_name = trim(I('get.mobile','','strip_tags'));

        $user_id = 0;

        if($user_name) {
            $user_id = M('User')->where(array('username'=>$user_name))->getField('id');
        }

        $lottery_info = $this->get_lottery_base($lottery_id);

        $cnt = 0;

        if($user_id > 0 && $lottery_info['start_time'] < time() && $lottery_info['end_time'] > time()) {

            $cnt = $give_cnt + ($this->get_lottery_count($user_id, $lottery_id) - $this->get_use_lottery_count($user_id,$lottery_id));

            //echo $give_cnt.'-'.$this->get_lottery_count($user_id, $lottery_id).'-'.$this->get_use_lottery_count($user_id,$lottery_id);

            if($cnt<0)$cnt = 0;
        }

        $this->assign('params', array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'lottery_id'=>$lottery_id,
            'cnt'=>$cnt
        ));

        $this->display();
    }

    public function event20170916() {

        $mock = [
            '3' => [
                [
                    'id'=>'42507',
                    'username'=>'13656687393',
                    'avatar'=>'http://image.ppmiao.com/Uploads/avatar/20170717/13656687393_20170717180540.jpg',
                    'open_id'=>'oeC5Ls08IpcOFSN7WBEZnk8yiUOs'
                ]
            ],
        ];




        $times = I("post.times");
        $count = I("post.count");

        \Think\Log::write("第".$times."次抽奖,人数：".$count);

        if($mock[$times]){
            $count = $count-count($mock[$times]);
        }
        \Think\Log::write("第".$times."次抽奖,人数：".$count);


        $awards = ['体验卷','保温杯','BMW钱包','BMW拉杆箱','体验卷','保温杯','BMW钱包','MINI拉杆箱'];
        $sql = "select id,username,avatar,open_id,real_name from s_user where channel = 'mba'";
        $users = M()->query($sql);

        $a = [];
        foreach($users as $user){
            $a[] = $user['id'];
        }
        $random_keys=array_rand($a,$count);

        $b = [];
        foreach( $users as $key=> $user){
            if(in_array($key,$random_keys)){
                $user['avatar'] = $this->showAvatar($user['avatar']);
                $b[] = $user;
            }
        }
        foreach($mock[$times] as $v){
            $b[] = $v;
        }


        $postData['users'] = json_encode($b);
        $postData['award'] = $times;

        $send = $this->curlPost($postData);


//如果需要设置允许所有域名发起的跨域请求，可以使用通配符 *
        header("Access-Control-Allow-Origin: *");
        header('Content-Type:application/json; charset=utf-8');
        $data['users'] = $b;
        echo json_encode($data);




    }


    public function event20170916_all(){
        $sql = "select id,username,avatar,open_id,real_name from s_user where channel = 'mba' order by id DESC limit 100";
        $users = M()->query($sql);
        $sql = "select count(*) as count from s_user where channel = 'mba'";
        $count = M()->query($sql);

        $redis = new \Redis();
        $conn = $redis->connect('r-uf678e1e6a9deeb4.redis.rds.aliyuncs.com', 6379);
        $auth = $redis->auth('Aa311512'); //设置密码
        $times = $redis->get('ppmiao_mba_actTims');

        if(!$times)$times = 0;

        $re['users'] = $users;
        $re['count'] = $count['0']['count'];
        $re['times'] = $times;
//如果需要设置允许所有域名发起的跨域请求，可以使用通配符 *
        header("Access-Control-Allow-Origin: *");
        header('Content-Type:application/json; charset=utf-8');

        echo json_encode($re);
    }

    public function event20170916_clear(){
        $redis = new \Redis();
        $conn = $redis->connect('r-uf678e1e6a9deeb4.redis.rds.aliyuncs.com', 6379);
        $auth = $redis->auth('Aa311512'); //设置密码
        $redis->set('ppmiao_mba_actTims',0);
        echo ' 次数重置成功';
    }

    public function showAvatar($string){
        $f = substr( $string, 0, 1 );
        $f = strtolower($f);
        if ($f == 'h'){
            return $string;
        } elseif ($f == 'a' || $f == '/') {
            return 'http://image.ppmiao.com/Uploads/'.$string;
        }
    }

    public function event20170916_list(){
        $redis = new \Redis();
        $conn = $redis->connect('r-uf678e1e6a9deeb4.redis.rds.aliyuncs.com', 6379);
        $auth = $redis->auth('Aa311512'); //设置密码
        $award = I("post.award");

        if($award == 'all'){
            $types = [];
            $awards = ['体验卷','保温杯','BMW钱包','帝格爱心手镯','BMW拉杆箱','体验卷','保温杯','BMW钱包','帝格爱心手镯','MINI拉杆箱'];
            foreach($awards as $k => $val){

                $key = "ppmiao_mba_activity_" . $k;

                $users = $redis->hget($key, 'users');
//                if($users){
                    $types[(string)$k]=(array)json_decode($users);
//                }
            }

            header("Access-Control-Allow-Origin: *");
            header('Content-Type:application/json; charset=utf-8');


            $list['list'] = $types;
            echo json_encode($list);

        }else{
            $key = "ppmiao_mba_activity_" . $award;

            $users = $redis->hget($key, 'users');
//        var_dump($users);
//        var_dump((array)json_decode($users));
//        var_dump(['award'=>0,'users'=>(array)json_decode($users)]);

//如果需要设置允许所有域名发起的跨域请求，可以使用通配符 *
            header("Access-Control-Allow-Origin: *");
            header('Content-Type:application/json; charset=utf-8');

            $list['list'] = [$award =>(array)json_decode($users)];
            echo json_encode($list);
        }




    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    public function curlPost($postFields)
    {
        $url = "http://testwechat.ppmiao.com/wechat/sendNotice";
        $postFields = http_build_query($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
//        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }



    /**
    * 2017 幸运刮刮乐
    * @date: 2017-1-7 下午1:08:57
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170107() {
        $lottery_id = 3; // 抽奖id
        $give_cnt = 1; // 默认送1次
        $user_name = trim(I('get.mobile', '', 'strip_tags'));
        $user_id = 0;
        if ($user_name) {
            $user_id = M('User')->where(array('username' => $user_name))->getField('id');
        }

        $lottery_info = $this->get_lottery_base($lottery_id);

        $currentCnt = 0;

        $history_log = '';

        $g = $h = $x = $c = 0;

        //活动开始，显示最近抽中记录
        if(time() > $lottery_info['start_time']) {
            $history_log = M('lotteryLog')->field('user_name,lottery_award_id')->where('lottery_id='.$lottery_id)->order('create_time desc')->limit(100)->select();
            foreach ($history_log as $key=>$val){
                $history_log[$key]['user_name'] = substr_replace($val['user_name'],'****',3,4);
                $history_log[$key]['lottery_award_name'] = M('lotteryAward')->where('id='.$val['lottery_award_id'])->getField('name');

                if($val['lottery_award_id'] >=30 && $val['lottery_award_id'] <=33) {
                    $history_log[$key]['lottery_award_name'] = '`'.$history_log[$key]['lottery_award_name'].'` 字';
                }
            }
        }

        if($lottery_info['start_time'] < time() /*&& $lottery_info['end_time'] > time()*/) {
            if($user_id>0){
                if($lottery_info['end_time'] > time()) {
                    $currentCnt = $give_cnt + ($this->get_lottery_count($user_id, $lottery_id) - $this->get_use_lottery_count($user_id, $lottery_id));
                    if ($currentCnt < 0){
                        $currentCnt = 0;
                    }
                }
                $g = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=30')->count();
                $h = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=31')->count();
                $x = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=32')->count();
                $c = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=33')->count();
            }
        }

        $this->assign('params', array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'lottery_id' => $lottery_id,
            'cnt' => $currentCnt,
            'history_log'=> $history_log,
            'g'=>$g,
            'h'=>$h,
            'x'=>$x,
            'c'=>$c
        ));
        $this->display();
    }

    /**
    * 福袋活动
    * @date: 2017-1-20 下午5:17:58
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170120() {
        //抽奖id
        $lottery_id = 4;
        $user_name = trim(I('get.mobile', '', 'strip_tags'));
        $user_id = 0;
        $lottery_cnt = 0;

        if ($user_name) {
            $user_id = M('User')->where(array('username' => $user_name))->getField('id');
        }

        $lottery_info = $this->get_lottery_base($lottery_id);

        if(!$lottery_info) {
            exit('活动没有配置');
        }

        if($user_id > 0 && time() >=$lottery_info['start_time']
            && time() <= $lottery_info['end_time']) {

            $login_cnt = 0;
            $login_cnt = M('evt20170120')->where('user_id='.$user_id)->count();

            $lottery_cnt = ($this->get_lottery_count($user_id, $lottery_id) + $login_cnt)
                                 -$this->get_use_lottery_count($user_id, $lottery_id);

            if(!M('evt20170120')->where(array('user_id'=>$user_id,'date'=>date('Y-m-d')))->count()) {
                $lottery_cnt += 1;
            }

            if ($lottery_cnt < 0){
                $lottery_cnt = 0;
            }
        }

        $this->assign('params', array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'lottery_id'=>$lottery_id,
            'lottery_cnt'=>$lottery_cnt
        ));
        $this->display();
    }

    /**
    * 元宵节活动
    * @date: 2017-2-6 下午7:47:23
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170206() {
        //抽奖id
        $lottery_id = 5;
        $user_name = trim(I('get.mobile', '', 'strip_tags'));
        $user_id = 0;
        $lottery_cnt = 0;
        $hd_cnt = 0;
        $free_chance = 1;

        if ($user_name) {
            $user_id = M('User')->where(array('username' => $user_name))->getField('id');
        }

        $lottery_info = $this->get_lottery_base($lottery_id);

        if(!$lottery_info) {
            exit('活动没有配置');
        }

        if($user_id > 0 && time() >=$lottery_info['start_time']) {

            $hd_cnt =  $this->get_lottery_count($user_id, $lottery_id) + $free_chance;

            if(time() <= $lottery_info['end_time']) {
                $lottery_cnt = $hd_cnt - $this->get_use_lottery_count($user_id, $lottery_id);
                if ($lottery_cnt < 0){
                    $lottery_cnt = 0;
                }
            }
        }

        $this->assign('params', array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'lottery_id'=>$lottery_id,
            'lottery_cnt'=>$lottery_cnt,
            'hd_cnt' =>$hd_cnt
        ));
        $this->display();
    }




    /**
    * 砸蛋活动
    * @date: 2017-4-6 上午9:20:24
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function event20170406() {
        //抽奖id
        $lottery_id = 6;
        $user_name = trim(I('get.mobile', '', 'strip_tags'));
        $user_id = 0;
        $lottery_cnt = 0;

        if ($user_name) {
            $user_id = M('User')->where(array('username' => $user_name))->getField('id');
        }
        $lottery_info = $this->get_lottery_base($lottery_id);

        if(!$lottery_info) exit('活动没有配置');

        if($user_id > 0 && time() >=$lottery_info['start_time'] && time() <= $lottery_info['end_time']) {

            $lottery_cnt = $this->get_lottery_count($user_id, $lottery_id)
                                - $this->get_use_lottery_count($user_id,$lottery_id);
            if ($lottery_cnt < 0){
                $lottery_cnt = 0;
            }
        }

        $this->assign('params', array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'lottery_id'=>$lottery_id,
            'lottery_cnt'=>$lottery_cnt,
        ));
        $this->display();
    }

    public function get_user_lottery_cnt(){
        $lottery_id = trim(I('post.lotteryId', '', 'strip_tags'));
        $user_id = trim(I('post.userId', '', 'strip_tags'));

        if($lottery_id && $user_id) {

            $lottery_info = $this->get_lottery_base($lottery_id);

            if($user_id > 0 && time() >=$lottery_info['start_time'] && time() <= $lottery_info['end_time']) {

                $lottery_cnt = $this->get_lottery_count($user_id, $lottery_id)
                    - $this->get_use_lottery_count($user_id,$lottery_id);
                if ($lottery_cnt < 0){
                    $lottery_cnt = 0;
                }
                $this->ajaxReturn(array('status'=>'0','info'=>$lottery_cnt));
            } else{
                $this->ajaxReturn(array('status'=>'0','info'=>0));
            }
        } else {
            $this->ajaxReturn(array('status'=>'0','info'=>0));
        }

    }


    /**
     * 抽奖次数
     * @param unknown $user_id
     * @param int $lottery_id
     */
    private function get_lottery_count($user_id,$lottery_id){

        $count = 0;

        $lottery_base_info = $this->get_lottery_base($lottery_id);

        $start_time = date("Y-m-d H:i:s",$lottery_base_info['start_time']);
        $end_time = date("Y-m-d H:i:s",$lottery_base_info['end_time']);

        $list = M('LotteryCond')->where(array('lottery_id'=>$lottery_id,'is_delete'=>0))->select();

        foreach ($list as $val){

            // == 0 不限制tag 标签
            if($lottery_base_info['tag'] == 0) {

                if ($lottery_base_info['key_name'] == 'flop'){

                    $sql = "SELECT due_capital FROM ".C('DB_PREFIX')."user_due_detail AS d,".C('DB_PREFIX')."project AS p
                          WHERE p.is_delete =0 AND d.project_id = p.id AND
                          p.new_preferential <> 1 AND d.due_capital>=".$val['min_amount']. " AND
                          d.due_capital<=".$val['max_amount'] .' AND d.user_id='.$user_id ." AND d.add_time>='$start_time' AND d.add_time<='$end_time'";
                } else {
                    $sql = "SELECT user_id,due_capital FROM ".C('DB_PREFIX')."user_due_detail
                        WHERE user_id = $user_id AND duration_day>=".$val['invest_days']." AND due_capital>=".$val['min_amount']. " AND
                          due_capital<=".$val['max_amount']." AND add_time>='$start_time' AND add_time<='$end_time'";
                }
            } else {

                if($lottery_id == 2) { //带投资期限的

                    $sql = "SELECT * FROM ".C('DB_PREFIX')."user_due_detail AS d,".C('DB_PREFIX')."project AS p
                          WHERE p.is_delete =0 AND d.project_id = p.id AND
                          p.new_preferential=".$lottery_base_info['tag']." AND
                          d.duration_day>=".$val['invest_days']." AND d.due_capital>=".$val['min_amount']. " AND
                          d.due_capital<=".$val['max_amount'] .' AND d.user_id='.$user_id ." AND d.add_time>='$start_time' AND d.add_time<='$end_time'";

                } else if(in_array($lottery_id, array(3,4,5,6))) { //不带投资期限的

                    $sql = "SELECT * FROM ".C('DB_PREFIX')."user_due_detail AS d,".C('DB_PREFIX')."project AS p
                          WHERE p.is_delete =0 AND d.project_id = p.id AND
                          p.new_preferential=".$lottery_base_info['tag']." AND d.due_capital>=".$val['min_amount']. " AND
                          d.due_capital<=".$val['max_amount'] .' AND d.user_id='.$user_id ." AND d.add_time>='$start_time' AND d.add_time<='$end_time'";
                }
            }

            $data = M()->query($sql);

            //圣诞节活动 5000有一次机会，   floor(金额/5000) 的机会

            if(in_array($lottery_id, array(2,4,5))) {
                foreach ($data as $v) {
                    $count += floor($v['due_capital'] / 5000) * $val['count'];
                }
            } else if($lottery_base_info['key_name'] == 'flop') {
                foreach ($data as $v) {
                    $count += floor($v['due_capital'] / 5000) * $val['count'];
                }
            } else if(in_array($lottery_id, array(3,6))){
                foreach ($data as $v) {
                    $count += floor($v['due_capital'] / 3000) * $val['count'];
                }
            }else {
                $count += count($data) * $val['count'];
            }
        }
        return $count;
    }

    /**
     * 已用抽奖次数
     * @param int $user_id
     * @param int $lottery_id
     */
    private function get_use_lottery_count($user_id,$lottery_id){

        //2017 幸运刮刮乐 默认送一次抽奖机会  id;34 的不能算
        if($lottery_id == 3) {
            return M('LotteryLog')->where('user_id='.$user_id.' and lottery_id='.$lottery_id.' and lottery_award_id !=34')->count();
        } else {
            return M('LotteryLog')->where(array('user_id'=>$user_id,'lottery_id'=>$lottery_id))->count();
        }

    }

    /**
     *
     * @param unknown $lottery_id
     */
    private function get_lottery_award($lottery_id,$id = 0){

        if (0 == $id) {
            return M('LotteryAward')->where("lottery_id = $lottery_id and is_delete = 0 and total > 0")->select();
        } else {
            return M('LotteryAward')->where(array('id'=>$id,'lottery_id'=>$lottery_id,'is_delete'=>0))->find();
        }
    }

    /**
     *
     * @param unknown $lottery_id
     */
    private function get_lottery_base($lottery_id){
        return M('LotteryBase')->where(array('id'=>$lottery_id,'is_delete'=>0))->find();
    }

    /**
     *
     * @param unknown $lottery_id
     */
    private function get_lottery_cond_list($lottery_id) {
        return M('LotteryCond')->where(array('lottery_id'=>$lottery_id,'is_delete'=>0))->select();
    }


    public function get_lottery_num(){

        $lottery_id = trim(I('post.lotteryId',0,'int'));
        if($this->userId == 0){
            $res = [
                'status' => 1,
                'result' => 0,
            ];
        }else{
            $res = [
                'status' => 1,
                'result' => 1+$this->get_lottery_count($this->userId, $lottery_id) - $this->get_use_lottery_count($this->userId,$lottery_id),
            ];
        }
        $this->ajaxReturn($res);
    }


    public function get_lottery_info(){

        $lottery_id = trim(I('post.lotteryId',0,'int'));
        $lottery = $this->get_lottery_base($lottery_id);
        $lottery['service_time'] = time();
        if(!$lottery_id || !$lottery) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请先配置抽奖活动',
                'user_id' => $this->userId,));
        }else{
            $res = [
                'status' => 1,
                'result' => $lottery,
            ];
            $this->ajaxReturn($res);
        }

    }

    /**
     *
     * @param unknown $lottery_id
     * @param unknown $user_id
     */
    public function do_lottery(){

        $lottery_id = trim(I('post.lotteryId',0,'int'));
        $user_id = trim(I('post.userId',$this->userId,'int'));

        $award_list = null;

        if(!$user_id) {
            $this->ajaxReturn(array('status'=>'0','info'=>'请先登录！'));
        }

        if(!$lottery_id) {
            $this->ajaxReturn(array('status'=>'-1','info'=>'异常访问'));
        }

        $lotteryBase = $this->get_lottery_base($lottery_id);

        if(!$lotteryBase){
            $this->ajaxReturn(array('status'=>'-2','info'=>'请选配置活动!'));
        }

        if(time() < $lotteryBase['start_time']) {
            $this->ajaxReturn(array('status'=>'-3','info'=>'活动于`'.date("m-d H:i",$lotteryBase['start_time']).'`开始'));
        }

        if(time() > $lotteryBase['end_time']) {

            $this->ajaxReturn(array('status'=>'-4','info'=>'活动于`'.date("m-d H:i",$lotteryBase['end_time']).'`结束'));
        }

        if(!$this->get_lottery_cond_list($lotteryBase['id'])) {
            $this->ajaxReturn(array('status'=>'-5','info'=>'活动没有配置条件!'));
        }

        $award_list = $this->get_lottery_award($lotteryBase['id']);

        if(!$award_list) {
            $this->ajaxReturn(array('status'=>'-6','info'=>'活动没有配置奖品，请先配置奖品!'));
        }

        M('User')->startTrans();
        $user = M('User')->lock(true)->where('id ='.$user_id)->find();
        $user_name = $user['username'];



        $cnt = $this->get_lottery_count($user_id, $lottery_id) - $this->get_use_lottery_count($user_id,$lottery_id);

        //默认送一次抽奖机会
        if(in_array($lottery_id, array(2,3,5,24,17))) {

            $cnt += 1;

        } else if($lottery_id == 4) { //每天一次机会

            $login_cnt = 0;

            $login_cnt = M('evt20170120')->where('user_id='.$user_id)->count();

            $cnt += $login_cnt;

            if(!M('evt20170120')->where(array('user_id'=>$user_id,'date'=>date('Y-m-d')))->count()) {
                $cnt += 1;
            }

        }

        if ($cnt <= 0) {
            M('User')->commit();
            $this->ajaxReturn(array('status'=>'-7','info'=>'当前没有机会!'));
        }

        foreach ($award_list as $val){
            $arr[$val['id']] = $val['probability'];
        }

        $prize_id = $this->getRand($arr);
        $prize_info = $this->get_lottery_award($lottery_id,$prize_id);



        if($prize_info['total'] <=0){
            unset($prize_info);
        }else{
            $log_arr = array(
                'user_id'=>$user_id,
                'user_name'=>$user_name,
                'lottery_id'=>$lottery_id,
                'lottery_award_id'=>$prize_id,
                'status'=>0,
                'create_time'=>time()
            );
            M('LotteryLog')->add($log_arr);


            M('LotteryAward')->where('id='.$prize_id)->setDec('total',1);
        }


        M('User')->commit();

        if(!$prize_info) {
            \Think\Log::record('userId:'.$user_id.' -- 奖品ID:'.$prize_id);
            $this->ajaxReturn(array('status'=>'-8','info'=>'奖品异常，请联系客服!'));
        }

        //1红包，2现金券，3加息券，4第三方券，5实物奖励

        //记录每天送的一次

        if($lottery_id == 4) {
            if(!M('evt20170120')->where(array('user_id'=>$user_id,'date'=>date('Y-m-d')))->count()) {
                M('evt20170120')->add(array('user_id'=>$user_id,'date'=>date('Y-m-d'),'create_time'=>time()));
            }
        }/* else if($lottery_id == 5) {
            if(!M('evt20170206')->where(array('user_id'=>$user_id))->count()) {
                M('evt20170206')->add(array('user_id'=>$user_id,'create_time'=>time()));
            }
        }*/

        if ($prize_info['type'] <= 3) {

            $expire_time = date("Y-m-d H:i:s", time() + $prize_info['valid_days'] * 86400);

            if($prize_info['type'] == 1){

                $redenvelope = array(
                    'title' => $prize_info['title'],
                    'content' => $prize_info['sub_title'],
                    'user_id' => $user_id,
                    'amount' => $prize_info['amount'],
                    'create_time' => date('Y-m-d H:i:s'),
                    'add_user_id' => 1,
                    'min_due' => $prize_info['min_invest_days'],
                    'min_invest' => $prize_info['min_invest_amount'],
                    'expire_time' => $expire_time,
                    'apply_tag'=>$prize_info['apply_tag'],
                    'type' => 2,
                    'scope' => 1,
                    'add_time' => time(),
                    'source' => $prize_info['source']
                );

                M('UserRedenvelope')->add($redenvelope);

            } else if($prize_info['type'] == 2){

                $cash_coupon = array(
                    'title' => $prize_info['title'],
                    'subtitle' => $prize_info['sub_title'],
                    'amount' => (int)$prize_info['amount'],
                    'type' => 2, // 抽奖
                    'user_id' => $user_id,
                    'expire_time' => $expire_time,
                    'status' => 0,
                    'add_time' => date('Y-m-d H:i:s'),
                    'add_user_id' => 1,
                    'modify_user_id' => 0,
                    'create_time' => time(),
                    'source' => $prize_info['source']
                );

                M("UserCashCoupon")->add($cash_coupon);

            } else if($prize_info['type'] == 3){


                $coupon_id = M('UserInterestCoupon')->where(array('source'=>$prize_info['source']))->getField('coupon_id');

                if(!$coupon_id) {
                    $coupon_id = M('UserInterestCoupon')->max('coupon_id') + 1;
                }

                $interest_coupon = array(
                    'coupon_id'=>$coupon_id,
                    'title'=>$prize_info['title'],
                    'subtitle'=>$prize_info['sub_title'],
                    'user_id'=>$user_id,
                    'interest_rate'=>$prize_info['amount'],
                    'min_due'=>$prize_info['min_invest_days'],
                    'min_invest'=>$prize_info['min_invest_amount'],
                    'expire_time'=>$expire_time,
                    'apply_tag'=>$prize_info['apply_tag'],
                    'status'=>0,
                    'socpe'=>0,
                    'add_time'=>date('Y-m-d H:i:s'),
                    'add_user_id'=>1,
                    'create_time'=>time(),
                    'source'=>$prize_info['source']
                );

                M('UserInterestCoupon')->add($interest_coupon);
            }
        } else if($prize_info['type'] == 6){
            $upjf =  D("UserVipLevel")->updateUserJf($user_id,$prize_info['jf_val']);
        }

        //2017 幸运刮刮乐
        if($lottery_id == 3) {

            if($prize_info['type'] == 4){
                $g = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=30')->count();
                $h = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=31')->count();
                $x = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=32')->count();
                $c = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id=33')->count();

                if($g >= 1 && $h >=1 && $x >=1 && $c >=1) {

                    //666现金券
                    $cash_coupon_666 = 34;

                    $lottery_award666 = M('lotteryLog')->where('user_id='.$user_id .' and lottery_id=3 and lottery_award_id='.$cash_coupon_666)->count();

                    \Think\Log::record('$lottery_award666:'.$lottery_award666);

                    if($lottery_award666 == 0) {

                        $_prize_info = $this->get_lottery_award($lottery_id,$cash_coupon_666);

                        $log_arr = array(
                            'user_id'=>$user_id,
                            'user_name'=>$user_name,
                            'lottery_id'=>$lottery_id,
                            'lottery_award_id'=>$cash_coupon_666,
                            'status'=>0,
                            'create_time'=>time()
                        );

                        M('LotteryLog')->add($log_arr);

                        $_expire_time = date("Y-m-d H:i:s", time() + $_prize_info['valid_days'] * 86400);

                        $cash_coupon = array(
                            'title' => $_prize_info['title'],
                            'subtitle' => $_prize_info['sub_title'],
                            'amount' => (int)$_prize_info['amount'],
                            'type' => 2, // 抽奖
                            'user_id' => $user_id,
                            'expire_time' => $_expire_time,
                            'status' => 0,
                            'add_time' => date('Y-m-d H:i:s'),
                            'add_user_id' => 1,
                            'modify_user_id' => 0,
                            'create_time' => time(),
                            'source' => $_prize_info['source']
                        );

                        M("UserCashCoupon")->add($cash_coupon);

                        $res['dj'] = 1;

                    } else {

                        $g = $g - $lottery_award666;
                        $h = $h - $lottery_award666;
                        $x = $x - $lottery_award666;
                        $c = $c - $lottery_award666;

                        if ($g > 0 && $h > 0 && $x > 0 && $c > 0) {

                            $_prize_info = $this->get_lottery_award($lottery_id,$cash_coupon_666);

                            $log_arr = array(
                                'user_id'=>$user_id,
                                'user_name'=>$user_name,
                                'lottery_id'=>$lottery_id,
                                'lottery_award_id'=>$cash_coupon_666,
                                'status'=>0,
                                'create_time'=>time()
                            );

                            M('LotteryLog')->add($log_arr);

                            $_expire_time = date("Y-m-d H:i:s", time() + $_prize_info['valid_days'] * 86400);

                            $cash_coupon = array(
                                'title' => $_prize_info['title'],
                                'subtitle' => $_prize_info['sub_title'],
                                'amount' => (int)$_prize_info['amount'],
                                'type' => 2, // 抽奖
                                'user_id' => $user_id,
                                'expire_time' => $_expire_time,
                                'status' => 0,
                                'add_time' => date('Y-m-d H:i:s'),
                                'add_user_id' => 1,
                                'modify_user_id' => 0,
                                'create_time' => time(),
                                'source' => $_prize_info['source']
                            );

                            M("UserCashCoupon")->add($cash_coupon);

                            $res['dj'] = 1;
                        }
                    }
                }
            }
        }

        $res['id'] = $prize_info['id'];
        $res['name'] = $prize_info['name'];
        $res['type'] = $prize_info['type'];
        unset($prize_info);
        $this->ajaxReturn(array('status'=>1,'info'=>$res));
    }

    /**
     * 抽奖记录
     * @param unknown $user_id
     */
    public function get_user_lottery_log(){

        $lottery_id = trim(I('post.lotteryId',0,'int'));

        $type = trim(I('post.type',0,'int'));//1所有，2个人，
        $count = trim(I('post.count',20,'int'));//1所有，2个人，

        $user_id = trim(I('post.userId',$this->userId,'int'));

        if($lottery_id) {
            if($type == 2) {
                if(!$user_id) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'请先登录！'));
                }
                $_cond =  "user_id = $user_id and lottery_id = $lottery_id and lottery_award_id <> 131";
            } else {
                $_cond = "lottery_id = $lottery_id and lottery_award_id <> 131";
            }
            $list = M('LotteryLog')->where($_cond)->order('create_time DESC')->limit($count)->select();
            foreach($list as $key => $val){
                $list[$key]['prize_name'] = $this->get_lottery_award($val['lottery_id'],$val['lottery_award_id'])['name'];
            }
            $this->ajaxReturn(array('status'=>1,'info'=>$list));
        }
        $this->ajaxReturn(array('status'=>0,'info'=>'请先配置抽奖活动'));
    }


    /**
     * 抽奖记录
     * @param unknown $user_id
     */
    public function get_lottery_log(){

        $lottery_id = trim(I('post.lotteryId',0,'int'));

        $user_id = trim(I('post.userId',$this->userId,'int'));

        if(!$user_id) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请先登录！'));
        }

        $list = M('LotteryLog')->field('lottery_id,lottery_award_id,create_time')->where(array('user_id'=>$user_id,'lottery_id'=>$lottery_id))->order('create_time desc')->select();
        foreach($list as $key => $val){
            $lottery_award_info = $this->get_lottery_award($val['lottery_id'],$val['lottery_award_id']);
            $list[$key]['prize_name'] = $lottery_award_info['name'];
            $list[$key]['type'] = $lottery_award_info['type'];
            $list[$key]['create_time'] = date("Y-m-d H:i",$val['create_time']);
        }
        $this->ajaxReturn(array('status'=>1,'info'=>$list));

    }


    private function getRand($proArr) {
        $data = '';
        $proSum = array_sum($proArr); // 概率数组的总概率精度

        foreach ($proArr as $k => $v) { // 概率数组循环
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $v) {
                $data = $k;
                break;
            } else {
                $proSum -= $v;
            }
        }
        unset($proArr);

        return $data;
    }

    private function randStr($len = 6, $format = 'ALL')
    {
        switch ($format) {
            case 'ALL':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
            case 'CHAR':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~';
                break;
            case 'NUMBER':
                $chars = '0123456789';
                break;
            default:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
        }
        mt_srand((double) microtime() * 1000000 * getmypid());
        $str = "";
        while (strlen($str) < $len)
            $str .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $str;
    }

    private function checkMobile($phonenumber) {
        if(preg_match("/1[3458]{1}\d{9}$/",$phonenumber)){
            return true;
        }else{
            return false;
        }
    }


    private function send_post($url, $post_data) {

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    private function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

    }




    protected function ajaxReturn($data,$type='',$json_option=0) {
        if(empty($type)) $type  =   "JSON";
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }


}