<?php
namespace Home\Controller;
use Think\Controller;

class ProjectController extends Controller {

    public function check(){
        $pid  = $_GET['pid'];
        $error_data = [];
        $cg_data = $this->get_cg_detail($pid);
        $local_data = $this->get_detail($pid);

        // 处理存管数据格式
        $cg_check_data = [];
        $cg_check_data_detail = [];
        foreach($cg_data as $key => $val){
            $cg_check_data[$val->order_no] = $val->vol;
            $cg_check_data_detail[$val->order_no] = $val;
        }

        // 处理本地数据格式
        $local_check_data = [];
        $local_check_data_detail = [];
        foreach($local_data as $key => $val){
            $local_check_data[$val['recharge_no']] = $val['inv_succ'];
            $local_check_data_detail[$val['recharge_no']] = $val;
        }

        $body = '<table  border="1"><tr><td>标ID</td><td>用户客户编号</td><td>数据库订单号</td><td>存管订单号</td><td>姓名</td><td>时间</td><td>金额</td></tr>';

        $res = array_merge($local_check_data, $cg_check_data);

        foreach ($res as $key => $value) {
            if (isset($cg_check_data[$key]) && !isset($local_check_data[$key])) {
                // 本地没有，存管有
                $error_data[] = ['pid'=>$pid, 'plat_cust'=>$cg_check_data_detail[$key]->platcust];

                $body .='<tr><td>'.$pid.'</td><td>'.$cg_check_data_detail[$key]->platcust.'</td><td></td><td>'.$key.'</td><td>'.$cg_check_data_detail[$key]->name.'</td><td>'.$cg_check_data_detail[$key]->trans_date.' '.$cg_check_data_detail[$key]->trans_time.'</td><td>'.$cg_check_data_detail[$key]->vol.'</td></tr>';

            } elseif (!isset($cg_check_data[$key]) && isset($local_check_data[$key])) {
                // 本地有，存管没有
                $error_data[] = ['pid'=>$pid, 'plat_cust'=>$local_check_data_detail[$key]['platcust']];

                $body .='<tr><td>'.$pid.'</td><td>'.$local_check_data_detail[$key]['platcust'].'</td><td>'.$key.'</td><td></td><td>'.$local_check_data_detail[$key]['real_name'].'</td><td>'.$local_check_data_detail[$key]['add_time'].'</td><td>'.$local_check_data_detail[$key]['inv_succ'].'</td></tr>';
            }
        }

        $body .= '</table>';

//        return $error_data;
        if($error_data){
            $this->sendEmail($body);
        } 
        // else {
        //     $this->sendEmail('标ID'.$pid.'-没有错误数据');
        // }
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
        $sql = "select d.recharge_no, d.inv_succ, d.add_time, u.real_name, u.platcust from s_investment_detail as d left join s_user u on u.id = d.user_id where d.`project_id` = $pid and d.user_id > 0 ";
        $datas = M()->query($sql);

        return $datas;



        $local_check_data = [];
        foreach($datas as $key => $val){
            if(isset($local_check_data[$val['recharge_no']])){
                $local_check_data[$val['recharge_no']] = $local_check_data[$val['recharge_no']] + $val['inv_succ'];
            }else{
                $local_check_data[$val['recharge_no']] = $val['inv_succ'];
            }
        }
        // $sql = "select u.platcust, d.due_capital from s_user_due_detail as d left join s_user as u on u.id = d.user_id where d.`project_id` = $pid and d.user_id > 0 ";
        // $datas = M()->query($sql);
        // $local_check_data = [];
        // foreach($datas as $key => $val){
        //     if(isset($local_check_data[$val['platcust']])){
        //         $local_check_data[$val['platcust']] = $local_check_data[$val['platcust']] + $val['due_capital'];
        //     }else{
        //         $local_check_data[$val['platcust']] = $val['due_capital'];
        //     }
        // }
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
        // $mail->addAddress('tangxl@ppmiao.cn');               // Name is optional
        // $mail->addAddress('xuhui@ppmiao.cn');               // Name is optional
        // $mail->addAddress('lintao@ppmiao.cn');               // Name is optional
        // $mail->addAddress('raomz@ppmiao.cn');               // Name is optional
        // $mail->addAddress('chenjunjie@ppmiao.cn');               // Name is optional
        $mail->addAddress('dongw@ppmiao.com');               // Name is optional
        $mail->addAddress('lfy@ppmiao.com');               // Name is optional
        $mail->addAddress('lx@ppmiao.com');               // Name is optional
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