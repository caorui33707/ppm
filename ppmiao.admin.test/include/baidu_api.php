<?php

class baidu_api{

    private $apikey = 'f2da1ceb659aff708a1b26ea10d84c1a';

    /**
     * ���֤��ѯ
     * @param $idcard
     */
    public function idservice($idcard){
        if(!$idcard) return false;
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/idservice/id?id='.$idcard;
        $header = array(
            'apikey: '.$this->apikey,
        );
        // ���apikey��header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // ִ��HTTP����
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res, true);
    }

    /**
     * �ֻ���������ز�ѯ
     * @param $phone
     */
    public function mobilephoneservice($phone){
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/mobilephoneservice/mobilephone?tel='.$phone;
        $header = array(
            'apikey: '.$this->apikey,
        );
        // ���apikey��header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // ִ��HTTP����
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res, true);
    }

    /**
     * ���п������ز�ѯ
     * @param $card
     */
    public function cardinfo($card){
        $ch = curl_init();
        $url = 'http://apis.baidu.com/datatiny/cardinfo/cardinfo?cardnum='.$card;
        $header = array(
            'apikey: '.$this->apikey,
        );
        // ���apikey��header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // ִ��HTTP����
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        return json_decode($res, true);
    }
}