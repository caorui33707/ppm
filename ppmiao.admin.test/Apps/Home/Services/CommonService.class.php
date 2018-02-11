<?php

namespace Home\Services;

class CommonService
{
    
    /**
     * 发送短信
     */
    public function sendSms($mobile, $msg)
    {
        $data   = [
            "account"    => "ppm_sms",
            "pswd"       => "fuqianwangluo12_",
            "mobile"     => $mobile,
            "msg"        => $msg,
            "needstatus" => "false"
        ];
        $curl   = 'http://222.73.117.156/msg/HttpBatchSendSM';
        $result = $this->sendPost($curl, $data);

        $result = substr($result, -1);
        if ($result == 0) {
            return true;
        }
        return false;
    }


    /**
     * @param $url
     * @param $post_data
     * @return bool|string
     */
    public function sendPost($url, $postData)
    {
        $postData = http_build_query($postData);
        $options  = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            ]
        ];
        $context  = stream_context_create($options);
        $result   = file_get_contents($url, false, $context);
        return $result;
    }


}
