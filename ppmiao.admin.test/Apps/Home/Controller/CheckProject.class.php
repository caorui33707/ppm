<?php
/**
 * Created by PhpStorm.
 * User: cscjj2008
 * Date: 17/4/21
 * Time: 下午4:06
 */

namespace Home\Controller;
use Think\Controller;

/**
 * 用户存管账号查询
 * Class UserFundController
 * @package Admin\Controller
 */
class CheckProjectController extends Controller
{




    public function check(){
        $pid  = $_GET['pid'];
        $error_data = [];
        $cg_data = $this->get_cg_detail($pid);
        $local_data = $this->get_detail($pid);
        $cg_check_data = [];

        foreach($cg_data as $key => $val){
            if(isset($cg_check_data[$val->platcust])){
                $cg_check_data[$val->platcust] = $cg_check_data[$val->platcust] + $val->vol;
            }else{
                $cg_check_data[$val->platcust] = $val->vol;
            }

        }
//        foreach($cg_check_data as $k => $v){
//            if($v != $local_data[$k]){
//                $error_data[] = ['pid'=>$pid,'plat_cust'=>$k];
//            }
//        }

//            var_dump($local_data);
        $body = '<table  border="1"><tr><td>标ID</td><td>用户客户编号</td><td>数据库现有值</td><td>存管查询值</td></tr>';
        foreach($local_data as $k => $v){
            if($v != $cg_check_data[$k]){
//                var_dump($k);
//                var_dump($local_data[$k]);
//                var_dump($cg_check_data[$k]);
                $error_data[] = ['pid'=>$pid,'plat_cust'=>$k];



                $body .='<tr><td>'.$pid.'</td><td>'.$k.'</td><td>'.$v.'</td><td>'.$cg_check_data[$k].'</td></tr>';

            }
        }

        $body .= '</table>';
//        return $error_data;
        if($error_data){
            $this->sendEmail($body);
        }
    }

    public function get_cg_detail($pid){
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();
        $postData['prod_id'] = $pid;
        $postData['pagesize'] = 200;
        $plainText =  \SignUtil::params2PlainText($postData);
        $sign =  \SignUtil::sign($plainText);
        $postData['signdata'] = $sign;

        $json  = $fd->post("project/buyinfo",$postData);
        $data = json_decode($json);
        return $data->result;
    }

    public function get_detail($pid){
        $sql = "select u.platcust,d.due_capital from s_user_due_detail as d left join s_user as u on u.id = d.user_id where d.`project_id` = $pid and d.user_id > 0 ";
        $datas = M()->query($sql);
        $local_check_data = [];
        foreach($datas as $key => $val){
            if(isset($local_check_data[$val['platcust']])){
                $local_check_data[$val['platcust']] = $local_check_data[$val['platcust']] + $val['due_capital'];
            }else{
                $local_check_data[$val['platcust']] = $val['due_capital'];
            }
        }
        return $local_check_data;
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

        $mail->Subject = '标的投资记录对账';
        $mail->Body    = $body;

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }



}