<?php

class baidu_api{

    private $apikey = 'f2da1ceb659aff708a1b26ea10d84c1a';

    /**
     * 身份证查询
     * @param $idcard
     */
    public function idservice($idcard){
        if(!$idcard) return false;
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/idservice/id?id='.$idcard;
        $header = array(
            'apikey: '.$this->apikey,
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res, true);
    }

    /**
     * 手机号码归属地查询
     * @param $phone
     */
    public function mobilephoneservice($phone){
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/mobilephoneservice/mobilephone?tel='.$phone;
        $header = array(
            'apikey: '.$this->apikey,
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res, true);
    }

    /**
     * 银行卡归属地查询
     * @param $card
     */
    public function cardinfo($card){
        $ch = curl_init();
        $url = 'http://apis.baidu.com/datatiny/cardinfo/cardinfo?cardnum='.$card;
        $header = array(
            'apikey: '.$this->apikey,
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res, true);
    }
}