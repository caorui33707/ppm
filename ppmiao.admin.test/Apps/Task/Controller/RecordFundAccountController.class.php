<?php
/**
 * 处理任务-幽灵账号自动购买
 * 2016/12/28 陈俊杰
 */
namespace Task\Controller;
use Think\Controller;

class RecordFundAccountController extends Controller
{
    public function main(){

        $data = $this->getResult();
        $allType = [1,5,9];
        foreach($allType as $val){
            $insert = [
                'value' => $data[$val],
                'type' => 3,
                'dest_account' => $val,
                'date' => date('Y-m-d'),
            ];
            M('FundAccount')->add($insert);
        }
    }
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