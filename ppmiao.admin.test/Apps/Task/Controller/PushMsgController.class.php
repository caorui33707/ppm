<?php
namespace Task\Controller;
use Think\Controller;


class PushMsgController extends Controller {

    public function __construct(){
        load('Admin/function');
    }

    /**
     * 推送消息，每5分钟执行一资
     * @date: 2017-7-10 下午5:43:23
     * @author: hui.xu
     * @param: variable
     * @return:
     */


    public function run_new(){
        $time = time();

        $device_type = I('get.device_type', 0, 'int');
        if(!$device_type) {
            return;
        }

        if($device_type == 2){
            $ver = 'android_ver';
        } else if($device_type == 1){
            $ver = 'ios_ver';
        }else{
            return;
        }

        $where = array(
            'status'=>0,
            'is_delete'=>0,
            'push_time'=>array('elt',$time),
            // $ver=>array('egt','2.0')
        );

        $list = M('MsgPush')->field('id,target,content,registration_id,android_ver,ios_ver,push_extra,mobile_group_ids')
            ->where($where)->select();//"status = 0 and is_delete=0  and push_time<=$time"

        if(!$list) {
            return;
        }

        $this->put_file(M('MsgPush')->_sql());

        sleep(1);
        $this->run($list,$device_type);

    }

    public function run($list,$device_type){

        if(!$list) {
            return;
        }

        foreach ($list as $val) {
            $id =  $val['id'] ;

            if( $val['target']!=2){ // !$val['mobile_group_ids'] &&
                if($this->device_type_push_android($id,$device_type)) {
                    //单个推送
                    if($val['target'] == 1) {
                        //"latest_device_type = $device_type  加  latest_device_type
                        $condWhere = "registration_id = '".$val['registration_id']."' and latest_device_type = $device_type";
                        $users = M('User')->where($condWhere)->field('id,registration_id,channel,last_channel,device_type')->find();
                        if($users) {
                            $platform = '';  //device_type
                            if($users['latest_device_type'] ==2) {
                                $platform = 'android';
                            } else if($users['latest_device_type'] ==1){
                                $platform = 'ios';
                            }
                            $_app = getAppId(trim($users['last_channel'])); //channel

                            $extra = json_decode(  $val['push_extra'],true);

                            $text = json_encode($val['content']);
                            $text = preg_replace_callback('/\\\\\\\\/i',function($str){
                                return '\\';
                            },$text);
                            $content =  json_decode($text);

                            $result = pushMsg($content, $val['registration_id'], $platform,$extra,$_app);
                        }

                    } else {
                        $pushExtra = $val['push_extra'];

                        $text = json_encode($val['content']);
                        $text = preg_replace_callback('/\\\\\\\\/i',function($str){
                            return '\\';
                        },$text);
                        $content =  json_decode($text);

                       // $content   = $val['content'];

                        if($device_type == 2){
                            $ver_type = 'android_ver';
                        } else if($device_type == 1){
                            $ver_type = 'ios_ver';
                        }


                        $this->device_type_push($device_type,$val,$content, $pushExtra,$ver_type);

                    }

                    M('MsgPush')->where('id='.$id)->save(array('status'=>2,'done_time'=>time()));
                }
            }
        }

    }

    /*
     *  type = 2 ,android,修改状态 status = 1
     */
    private function device_type_push_android($id,$device_type){
        $save = array();
        $mtime = time();
        $where = array('id'=>$id,'status'=>1);
        $r = M('MsgPush')->where($where)->find();
        $this->put_file(M('MsgPush')->_sql());

        if($device_type == 1){
            $mtime += 10;
        }else if($device_type == 2){
            $mtime += 20;
        }

        if($r){
            $save = array('mtime'=>$mtime);
        }else{
            $save = array('status'=>1,'mtime'=>$mtime);
        }
        $m = M('MsgPush')->where('id='.$id)->save($save);
        $this->put_file(M('MsgPush')->_sql());
        return $m;
    }

    /*
     * 全推 和 版本推 按 latest_device_type 推  2 android  1 ios
     */

    private function device_type_push($device_type,$val,$content, $pushExtra,$ver='android_ver'){
        if($val[$ver] === '0') {//android ios 所有渠道
            $this->push($device_type, 0, $content, $pushExtra);

        } else { //android按版本推送

            if($val[$ver]>0) { //版本存在
                $android_ver_arr = explode(",", $val[$ver]);
                if ($android_ver_arr) {
                    foreach ($android_ver_arr as $Aval) {
                        $this->push($device_type, $Aval, $content, $pushExtra);
                    }
                }
            }
        }

        return;
    }

    /**
     * 函数用途描述
     * @date: 2017-10-13 下午1:58:40
     * @author: hui.xu
     * @param: $device_type 1:ios ,2:android,$ver 版本列表 0 为全部版本，$content内容，$extra 扩展参数
     * @return:
     */
    private function push($device_type,$ver,$content,$extra){

        $platform = '';

        if($device_type == 2){
            $platform = 'android';
        } else if($device_type == 1){
            $platform = 'ios';
        }

        $extra = json_decode( $extra,true);

        $cond = "latest_device_type = $device_type and registration_id >0"; //device_type

        if($ver>0){
            $cond .= " and app_version='$ver'";
        }else{
            $cond .= " and app_version >='2.0'";
        }



        $users = M('User')->where($cond)->field('id,registration_id,channel,last_channel')->group('registration_id')->order('id desc')->select();
        $this->put_file(M('User')->_sql());


        $pg = 500;
        $i = 0;
        $userIdArr = $registrationArr = $lastChannelArr =  array();
        foreach ($users as $arr){
            $k = intval($i/$pg);
            $userIdArr[$k][] = $arr['id'];
            $registrationArr[$k][] = trim($arr['registration_id']);
            $lastChannelArr[$k] = trim($arr['last_channel']);
            $i++;
        }


        foreach ($userIdArr as $key=>$val){

            $last_channel = $lastChannelArr[$key];
            $_app = getAppId($last_channel);//channel

            $registration_strs = implode(',',$registrationArr[$key]);
            $uid_strs = implode(',',$val);

            $result = pushMsg($content, $registration_strs, $platform, $extra,$_app);

            if($result->isOk){
                $this->put_file('true'.$_app.'_'.$uid_strs.'_'.$platform.'_'.$ver);
            }else{
                $this->put_file('false'.$_app.'_'.$uid_strs.'_'.$platform.'_'.$ver);
            }
        }





//        foreach ($users as $val){
//
//            $_app = getAppId(trim($val['last_channel']));//channel
//
//            $result = pushMsg($content, trim($val['registration_id']), $platform, $extra,$_app);
//
//            if($result->isOk){
//                $this->put_file('true'.$_app.'_'.$val['id'].'_'.$platform.'_'.$ver);
//            }else{
//                $this->put_file('false'.$_app.'_'.$val['id'].'_'.$platform.'_'.$ver);
//            }
//        }


    }


    public function put_file($s){
        $fileName = LOG_PATH  .date("Y-m-d"). 'push.log';
        $s  = '['.date("Y-m-d H:i:s").']  '.$s."\r\n";
        file_put_contents($fileName,$s,FILE_APPEND);
    }

}