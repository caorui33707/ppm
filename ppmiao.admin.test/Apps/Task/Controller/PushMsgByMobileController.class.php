<?php

namespace Task\Controller;

use Think\Controller;


class PushMsgByMobileController extends Controller
{

    public function __construct()
    {
        load('Admin/function');
    }

    public function push()
    {

        $time = time();

        $where = [
            'status'    => 0,
            'is_delete' => 0,
            'push_time' => ['elt', $time],
        ];
        $list  = M('MsgPush')->field('id,target,content,registration_id,android_ver,ios_ver,push_extra,mobile_group_ids')
            ->where($where)->select();
        if (!$list) {
            return;
        }
        sleep(1);
        $this->run($list);

    }

    public function run($list)
    {
        if (!$list) {
            return;
        }
        foreach ($list as $val) {
            $id = $val['id'];
            //只推送一次
            if ($val['target'] == 2) {
                $idArr       = array_filter(explode('#', $val['mobile_group_ids']));
                $idArrStr    = implode(',', $idArr);
                $emptyIdsArr = [];
                $appArr      = [];
                $pushUsers   = M("user")->field('id,registration_id,username,channel,last_channel,device_type,latest_device_type')->where("username in(" . $idArrStr . ")")->select();
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

                foreach ($appArr as $key => $value) {
                    $extra = json_decode($val['push_extra'], true);
                    $text = json_encode($val['content']);
                    $text = preg_replace_callback('/\\\\\\\\/i',function($str){
                        return '\\';
                    },$text);
                    $content = json_decode($text);
                    if (isset($value['android']) && $value['android']) {
                        $this->pushMessageLimit($value['android'],$content,'android',$extra,$key);
                    }
                    if (isset($value['ios']) && $value['ios']) {
                        $this->pushMessageLimit($value['ios'],$content,'ios',$extra,$key);
                    }
                }
            }
            M('MsgPush')->where('id=' . $id)->save(['status' => 2, 'done_time' => time()]);
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

}