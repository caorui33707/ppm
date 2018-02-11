<?php
/**
 * Created by PhpStorm.
 * User: cscjj2008
 * Date: 17/4/21
 * Time: 下午4:06
 */

namespace Admin\Controller;


/**
 * 对账
 * Class UserFundController
 * @package Admin\Controller
 */
class LiquidateController extends AdminController
{

    private $liquiType = ['liquidateAction_fundData'=>1,'liquidateAction_withdrawData'=>2];

    public function fundData(){

        $key = 'liquidateAction_fundData';
        $dif_data = [];
        if($todayLiquidate =  $this->checkTodayLiquidate($key)){
            $dif_data = $this->getDifData($key);
            $today = 2;
        }else if($this->checkTodayFile($key)){
            $today = 0;
        }else{
            $today = 1;
        }


        $this->assign("dif_data",$dif_data);
        $this->assign("today",$today);
        $this->display();
    }

    public function withdrawData(){

        $key = 'liquidateAction_withdrawData';
        $dif_data = [];
        if($todayLiquidate =  $this->checkTodayLiquidate($key)){
            $dif_data = $this->getDifData($key);
            $today = 2;
        }else if($this->checkTodayFile($key)){
            $today = 0;
        }else{
            $today = 1;
        }


        $this->assign("dif_data",$dif_data);
        $this->assign("today",$today);
        $this->display();
    }

    public function balanceInfo(){
        $this->display();
    }

    /**
     * 下载对账文件
     */
    public function downloadLiquidateData(){

        $key = I('post.liquidateType');
        if(!$key)$key='liquidateAction_fundData';
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
//        echo "/** <br>*4.6.10	平台客户编号查询    userAction_getPlatCustInfo<br>*/<br>";

//        $plainText =  \SignUtil::params2PlainText($req);
//        $sign =  \SignUtil::sign($plainText);
//        $req['signdata'] = $sign;

        $date = date("Ymd");
        $date =  date('Ymd',strtotime("-1 day"));
        $postData = [
            'plat_no' => 'XIB-PPM-A-52553211',
            'order_no' => 'FQWLC20170729134458bgz',
            'partner_trans_date' => $date,
            'partner_trans_time' => '100000',
            'paycheck_date' => (string)$date,
            'precheck_flag' => '0',
        ];


        $plainText =  \SignUtil::params2PlainText($postData);
        $sign =  \SignUtil::sign($plainText);
        $postData['signdata'] = $sign;

        $data  = $fd->out_post($key,$postData);

        $this->saveFiles($data,$key);


        $this->ajaxReturn(array('status'=>1,'info'=>'下载对账文件成功'));
    }


    function saveFiles($data,$key){


        $fileName = '/mnt/logs/LiquidateData/'.date('Ymd').$key.'.txt';
        $myFile = fopen($fileName, "a") or die("Unable to open file!");//这个是在c盘根目录生成文件
        fwrite($myFile, $data);//写入内容,可以写多次哦,不过没啥意义,因为你拼接好字符串,一次写入就行了
        fclose($myFile);//关闭该操作
    }

    function checkTodayFile($key){
        $fileName = '/mnt/logs/LiquidateData/'.date('Ymd').$key.'.txt';
        return is_file($fileName);
    }

    function  checkTodayLiquidate($key){
        $type = $this->liquiType[$key];
        $condition = [
            'type' => $type,
            'date' => date('Ymd'),
        ];
        return M('Liquidate')->where($condition)->find();


    }

    function getDifData($key){
        $condition = [
            'type' => $this->liquiType[$key],
            'date' => date('Ymd'),
        ];
        return M('LiquidateDetail')->where($condition)->select();
    }


}