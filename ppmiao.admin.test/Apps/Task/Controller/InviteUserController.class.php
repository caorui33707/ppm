<?php
/**
 * 检查用户金额是否与存管方一致
 * 2017/08/01 陈俊杰
 */
namespace Task\Controller;
use Think\Controller;

class CheckUserMoneyController extends Controller {



    /*
     * 主函数
     */
    public function main(){
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        $error = [];
        $users = $this->getUserDetail();
        $body = '<table  border="1"><tr><td>用户ID</td><td>姓名</td><td>数据库现有值</td><td>存管查询值</td></tr>';
        foreach($users as $user){
            $detail = $this->getUserFundMoney($user['platcust']);
            if($detail->result->balance != $user['wallet_totle']){
                $error[$user['id']]['database'] = $user['wallet_totle'];
                $error[$user['id']]['fund'] = $detail->result->balance;
                $body .='<tr><td>'.$user['id'].'</td><td>'.$user['real_name'].'</td><td>'.$user['wallet_totle'].'</td><td>'.$detail->result->balance.'</td></tr>';
            }
        }
        $body .= '</table>';
        $this->sendEmail($body);

    }

    public function getUserDetail(){
        $sql = "select u.id,u.real_name,u.platcust,a.wallet_totle  from s_user_account as a left join s_user as u on u.id = a.user_id where u.platcust <> '' and a.wallet_totle > 0";
        $user_detail = M()->query($sql);

        return $user_detail;


    }

    public function getUserFundMoney($platcust){

        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
        $req['account'] = $platcust;
        $plainText =  \SignUtil::params2PlainText($req);
        $sign =  \SignUtil::sign($plainText);
        $req['signdata'] = $sign;

        $data  = $fd->post('account/info',$req);
        return json_decode($data);
    }

    public function sendEmail($body){
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

        $mail->setFrom('lintao@ppmiao.cn', 'Date Team');
        $mail->addAddress('chenyj@ppmiao.cn');               // Name is optional
        $mail->addAddress('tangxl@ppmiao.cn');               // Name is optional
        $mail->addAddress('xuhui@ppmiao.cn');               // Name is optional
        $mail->addAddress('lintao@ppmiao.cn');               // Name is optional
        $mail->addAddress('chenjunjie@ppmiao.cn');               // Name is optional
//        $mail->addReplyTo('info@example.com', 'Information');
//        $mail->addCC('cc@example.com');
//        $mail->addBCC('bcc@example.com');

//        $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//        $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = '存管账号余额对账';
        $mail->Body    = $body;

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }



}