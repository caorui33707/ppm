<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/7/17
 * Time: 下午2:57
 */

namespace Admin\Controller;




class FundAccountController extends AdminController
{
    private $accountType = [0=>'自有子账户',1=>'自有子账户',5=>'抵用金子账户',6=>'加息金子账户',9=>'奖励金子账户'];
    private $accountMoney = [1=>'0',5=>'3',6=>'3',9=>'2'];
    private $doType = [2=>'充值',1=>'转账'];

    public function index(){


        $data = D('FundAccount')->getResult();
        $this->assign('data', $data);
        $this->assign('accountType', $this->accountType);
        $this->display();
    }

    public function recharge(){

        $count = 15;
        $conditions = "type = 1 or type = 2";
        $counts = M('FundAccount')->where($conditions)->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = M('FundAccount')->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();



        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('accountType', $this->accountType);
        $this->assign('doType', $this->doType);


        $money = D('FundAccount')->getSubAccount(1);
        $this->assign('money', $money);
        $this->display();
    }
    public function recharge_store(){


        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
//        echo "/** <br>*4.6.10	平台客户编号查询    userAction_getPlatCustInfo<br>*/<br>";

//        $plainText =  \SignUtil::params2PlainText($req);
//        $sign =  \SignUtil::sign($plainText);
//        $req['signdata'] = $sign;
//        $postData  = [
//            'dest_account' => '1',
//        ];


        $postData['amount'] = I('post.amount');


        $plainText =  \SignUtil::params2PlainText($postData);
        $sign =  \SignUtil::sign($plainText);
        $postData['signdata'] = $sign;
        $data  = $fd->post("trade/platcharge",$postData);
        $re = json_decode($data);
        if($re->code == 0){
            $this->signTransLog(2,0,I('post.amount'));
        }


        header('Content-Type:application/json; charset=utf-8');
        exit( $data );
    }

    public function transfer(){
        $this->display();
    }
    public function transfer_store(){


        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
//        echo "/** <br>*4.6.10	平台客户编号查询    userAction_getPlatCustInfo<br>*/<br>";

//        $plainText =  \SignUtil::params2PlainText($req);
//        $sign =  \SignUtil::sign($plainText);
//        $req['signdata'] = $sign;
        $postData  = [
            'dest_account' => I('post.dest_account'),
        ];


        $postData['amt'] = I('post.amount');
        $plainText =  \SignUtil::params2PlainText($postData);
        $sign =  \SignUtil::sign($plainText);
        $postData['signdata'] = $sign;


        $data  = $fd->post("trade/plataccounttrans",$postData);
        $re = json_decode($data);
        if($re->code == 0){
            $this->signTransLog(1,I('post.dest_account'),I('post.amount'));
        }


        header('Content-Type:application/json; charset=utf-8');
        exit( $data );
    }

    public function detail(){
        $accountType = I('get.type');
        $datetime = I('get.dt', '', 'strip_tags');
        if(!$datetime) $datetime = date('Y-m-d');
        if(!$accountType) $accountType = 1;

        $money = D('FundAccount')->getSubAccount($accountType);

        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();

        $postData['platcust'] = '01';
        $postData['acct_type'] = $accountType;
        $postData['start_date'] = $datetime.' 00:00:00';
        $postData['end_date'] = $datetime.' 23:59:59';
//        var_dump($postData);

        $plainText =  \SignUtil::params2PlainText($postData);
        $sign =  \SignUtil::sign($plainText);
        $postData['signdata'] = $sign;


        $data  = $fd->post("account/fundlist",$postData);



        $obj = json_decode($data);

        $list = $obj->result->fundList;
        $in = 0;$out=0;

        foreach($list as $key=>$val){
            if($val->amt_type == '收入'){
                $in = $in+$val->amt;
            }else if($val->amt_type == '支出'){
                $out = $out+$val->amt;
            }
        }
        $mao_in = $in-$out;


        $this->assign('start_time', $datetime);
        $this->assign('money', $money);
        $this->assign('in', $in);
        $this->assign('out', $out);
        $this->assign('mao_in', $mao_in);
        $this->assign('type', $accountType);
        $this->assign('name', $this->accountType[$accountType]);
        $this->assign('last_money', $this->accountMoney[$accountType]);
        $this->display();

    }


    public function record(){
        $acc = I('get.acc');
        $count = 15;
        $conditions = "type = 3 and dest_account = ".$acc;
        $counts = M('FundAccount')->where($conditions)->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list = M('FundAccount')->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();


        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('name', $this->accountType[$acc].'记录');
        $this->assign('accountType', $this->accountType);

        $this->display();


    }

    public function signTransLog($type,$dest_account,$amount){
        $insert = [
            'type' => $type,
            'dest_account'=>$dest_account,
            'value' => $amount
        ];

        M('FundAccount')->add($insert);
    }

}