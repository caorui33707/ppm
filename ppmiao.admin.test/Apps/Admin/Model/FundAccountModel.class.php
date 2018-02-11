<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class FundAccountModel extends Model {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';

//    protected $dbName = 'ppmiao_dev_2017';

//    protected $connection = 'MEMBER_CONFIG';
//    protected $fields = array('title', 'type','image', 'action','orders','ext','status','add_id','add_time','id_delete');
    protected $tableName = 'share';
    public function getResult(){
        $data = [];
        $allType = [1,5,9];
        foreach($allType as $val){
            $data[$val] = $this->getSubAccount($val);
        }
        return $data;

    }

    public function getSubAccount($type){

        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
//        echo "/** <br>*4.6.10	平台客户编号查询    userAction_getPlatCustInfo<br>*/<br>";

//        $plainText =  \SignUtil::params2PlainText($req);
//        $sign =  \SignUtil::sign($plainText);
//        $req['signdata'] = $sign;
        $postData  = [
            'account' => '01',
            'acct_type' => $type,
        ];

        $plainText =  \SignUtil::params2PlainText($postData);
        $sign =  \SignUtil::sign($plainText);
        $postData['signdata'] = $sign;


        $data  = $fd->post("account/balace",$postData);
        $obj = json_decode($data);
        return $obj->result->balance;
    }
}