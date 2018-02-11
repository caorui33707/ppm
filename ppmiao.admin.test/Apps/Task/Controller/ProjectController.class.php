<?php
namespace Task\Controller;
use Think\Controller;

class ProjectController extends Controller {
    
    /**
     * 产品出账 每天早上9点半执行一次
     * @date: 2017-7-10 下午5:43:23
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function chargeoff(){
        /*
        if((date('w') == 6) || (date('w') == 0)){
            \Think\Log::write('今天是周'.date('w'),'INFO');
            exit();
        }
        */
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
        
        $projectObj = M('Project');
        
        $day_time = strtotime(date('Y-m-d'));
        
        $list = $projectObj->field('id,title,status,soldout_time,fid,repay_review')->where('status in(2,3) and is_delete=0')->select();
        
        if($list) {
            foreach ($list as $key=>$val) {
                
                if($val['repay_review']>=2){
                    continue;
                }
                
                if($val['soldout_time']) {
                    $soldout_time = strtotime(date('Y-m-d',strtotime($val['soldout_time'])));
                    if(($day_time - $soldout_time)/86400 >= 10){
                        continue;
                    }
                } 
                
                $financingInfo = M('Financing')->where('id='.$val['fid'])->find();
                
                if(!$financingInfo) {
                    continue;
                }
                
                $amt = $this->get_balace($val['id']);
                
                if($amt>0) {
                    
                    unset($req);
                    
                    $req['prod_id'] = $val['id'];
                    
                    unset($list);
                    
                    $list[] = array('platcust'=>$financingInfo['platform_account'],
                        'out_amt'=>$amt,
                        'open_branch'=>$financingInfo['bank_code'],
                        'withdraw_account'=>$financingInfo['bank_card_no'],
                        'payee_name'=>$financingInfo['name'],
                        'is_advance'=>'1',
                        'client_property'=>'1',
                        'bank_id'=>$financingInfo['bank_id']
                    );
                    
                    $req['charge_off_list'] = json_encode($list);
                    
                    $plainText =  \SignUtil::params2PlainText($req);
                    
                    $sign =  \SignUtil::sign($plainText);
                    
                    $req['signdata'] = \SignUtil::sign($plainText);
                    
                    $res_str = $fd->post('/project/chargeoff',$req);
                    
                    \Think\Log::write('出账请求id:'.$val['id'].'-'.$res_str,'INFO');

                    if($res_str) {
                        
                        $data['fid'] = $financingInfo['id'];
                        $data['project_id'] = $val['id'];
                        $data['memo'] = $res_str;
                        $data['user_id'] = '88';
                        $data['create_time'] = date("Y-m-d H:i:s");
                        
                        $res = json_decode($res_str,true);
                        $data['order_no'] = $res['result']['order_no'];
                        M('projectChargeoffLog')->add($data);
                        
                        if($res['code'] == '0'){
                            \Think\Log::write($val['title'].' 出账成功;出账金额是：'.$amt,'INFO');
                        } else {
                            \Think\Log::write($val['title'].' 出账失败;错误：'.$res['errorMsg'],'INFO');
                        }
                    } else {
                        \Think\Log::write($val['title'].' 出账失败;'.$res['errorMsg'],'INFO');
                    }
                } else {
                    \Think\Log::write($val['title'].'标的金额查询失败','INFO');
                }
            }
        }
        
    }
    
    private function get_balace($prod_id){
        
        $amt = 0;
        
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $balace['prod_id'] = $prod_id;
        $balace['type'] = '01';
        
        $plainText =  \SignUtil::params2PlainText($balace);
        
        $sign =  \SignUtil::sign($plainText);
        
        $balace['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        $res_str = $fd->post('/project/balace',$balace);
        
        \Think\Log::write('id:'.$prod_id.'-'.$res_str,'INFO');
        
        $res = json_decode($res_str,true);
        
        if($res['code'] == 0){
            $amt = $res['result'];
        } 
        return $amt;
    }
    
    public function checkChargeoff(){
        
        $userduedetailObj = M('userDueDetail');
        $projectChargeoffLogObj = M('projectChargeoffLog');
        
        $datetime = date("Y-m-d");        
        $sql = "select project_id,soldout_time,title from s_project_chargeoff_log as pclog left join s_project as pp on pclog.project_id = pp.id where pclog.create_time>'$datetime' and pp.status = 3 and pp.soldout_time<'$datetime' and pp.is_delete=0";
        $list = M()->query($sql);
        
        $falg = false;
        
        $html = "<table>
                 <tr>
                    <td width='10%' align='center'>名称</td>
                    <td width='10%' align='center'>售完时间</td>
                    <td width='10%' align='center'>融资金额</td>
                    <td width='10%' align='center'>出账金额</td>
                    <td width='10%' align='center'>差额</td>
                 </tr>
            ";
        
        foreach ($list as $key =>$val){
            
            $list[$key]['amt'] = $userduedetailObj->where('project_id='.$val['project_id'].' and user_id>0')->sum('due_capital');
            $list[$key]['out_amt'] = $projectChargeoffLogObj->where('project_id='.$val['project_id'].' and order_status = 1')->sum('out_amt');
            
            if($list[$key]['out_amt']<$list[$key]['amt']) {
                $falg = true;
                $html .= "
                 <tr>
                    <td width='10%' align='center'>".$val['title']."</td>
                    <td width='10%' align='center'>".date("Y-m-d H:i:s",strtotime($val['soldout_time']))."</td>
                    <td width='10%' align='center'>".$list[$key]['amt']."</td>
                    <td width='10%' align='center'>".$list[$key]['out_amt']."</td>
                    <td width='10%' align='center'>".($list[$key]['amt'] - $list[$key]['out_amt'])."</td>
                 </tr>
            ";
            }
        }
        $html .="</table>";
        
        if($falg){
            $this->sendEmail($html);
        }
    }
    
    private  function sendEmail($body){
        vendor('PHPMailer.PHPMailerAutoload');
    
        $mail = new \PHPMailer;
    
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.exmail.qq.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'lintao@ppmiao.cn';                 // SMTP username
        $mail->Password = '123456Qwe';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;
        $mail->CharSet = "UTF-8";                       // TCP port to connect to
    
        $mail->setFrom('lintao@ppmiao.cn', '存管出账日志');
        $mail->addAddress('yangy@ppmiao.cn');               // Name is optional
        $mail->addAddress('chenyj@ppmiao.cn');
        $mail->addAddress('tengchong@ppmiao.cn');               // Name is optional
        $mail->addAddress('xuhui@ppmiao.cn');               // Name is optional
        //$mail->addAddress('lintao@ppmiao.cn');               // Name is optional
        //$mail->addAddress('raomz@ppmiao.cn');               // Name is optional
        //$mail->addAddress('chenjunjie@ppmiao.cn');               // Name is optional
        
        $mail->isHTML(true);                                  // Set email format to HTML
    
        $mail->Subject = '标的账户未出完记录';
        $mail->Body    = $body;
    
        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }
}