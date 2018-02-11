<?php
namespace Home\Controller;
use Think\Controller;
use OSS\Core\OssException;


class ApiController extends BaseController{
    
    /**
     * android \ ios 调用
     * 上传头像
     */
    private $seckey = 'zHQGfyGky2qO8GcDlq2KjHiaphYU1Ukc'; // 秘钥
    
    public function uploadAvatar() {
        
        $img = I('post.img','','strip_tags');
        $username = I('post.UserPhone','','strip_tags');
        $sign = I('post.sign','','strip_tags');
        
        $ret = array();
        
        if(!$img || !$username || !$sign) {
            $ret['status'] = 0;
            $ret['info'] = '参数不完整';
            $ret['img_url'] = '';
            exit(json_encode($ret));
        }
        
        
        if ($sign != md5($username.$this->seckey)){
            
            $ret['status'] = 0;
            $ret['info'] = '签名不对';
            $ret['img_url'] = '';
            exit(json_encode($ret));
        }
        
        $img = base64_decode($img);
        
        $save_path = './Uploads/';
        
        $save_url = 'avatar/'.date('Ymd');
        
        if (!file_exists($save_path.$save_url)) {
            mkdir($save_path.$save_url);
            @chmod($save_path.$save_url, 0755);
        }
        
        $file = $save_path.$save_url.'/'.$username.'_'.date('YmdHis').'.jpg';
        
        $state = file_put_contents($file, $img);
        
        if($state > 0) {
            
           $dd['avatar'] = $save_url.'/'.$username.'_'.date('YmdHis').'.jpg';
          
           if(!M('User')->where(array('username'=>$username))->save($dd)) {
               $ret['status'] = 0;
               $ret['info'] = '上传保存数据库失败';
               $ret['img_url'] = $dd['avatar'];
               exit(json_encode($ret));
           }

           $ossPath = 'Uploads/'.$dd['avatar'];
           $file = '/usr/share/nginx/html/web/'.$ossPath;
           $res = $this->uploadToOss($ossPath, $file);
           
           if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
               $ret['status'] = 0;
               $ret['info'] = '上传失败oss，请联系客服';
               $ret['img_url'] = '';
               exit(json_encode($ret));
           }
           
           $ret['status'] = 1;
           $ret['info'] = '上传成功';
           $ret['img_url'] = $dd['avatar'];
           exit(json_encode($ret));
            
        }
        else{
            $ret['status'] = 0;
            $ret['info'] = '上传失败，请联系客服';
            $ret['img_url'] = '';
            exit(json_encode($ret));
        }
    }
    
    /**
     * android \ ios 调用
     * 反馈
     */
    public function app_suggest() {
        error_reporting(0);
        $imgstr = trim(I('post.img','','strip_tags'));
        $username = trim(I('post.UserPhone','','strip_tags'));
        $content = trim(I('post.content','','strip_tags'));
        $sign = trim(I('post.sign','','strip_tags'));
        $device_type = trim(I('post.device_type','0','int'));
    
        $ret = array();
        
        if (!$username) {
            \Think\Log::write('请先登录','INFO');
            $ret['status'] = 0;
            $ret['info'] = '请先登录';
            exit(json_encode($ret));
        }
        
        if((!$imgstr && !$content) && $sign ) {
            \Think\Log::write('参数不完整','INFO');
            $ret['status'] = 0;
            $ret['info'] = '参数不完整';
            exit(json_encode($ret));
        }
    
        if ($sign != md5($username.$this->seckey)){
            \Think\Log::write('签名不对'.$sign.'-'.$username.'-'.$this->seckey,'INFO');
            $ret['status'] = 0;
            $ret['info'] = '签名不对';
            exit(json_encode($ret));
        }
    
        $user_id = M('User')->where(array('username'=>$username))->getField('id');
    
        if (!$user_id) $user_id = 0;
    
        $dd['user_id'] = $user_id;
        $dd['device_type'] = $device_type;
        $dd['content'] = $content;
        $dd['contact_way'] = $username;
        $dd['add_time'] = date("Y-m-d H:i:s");
        $dd['add_user_id'] = $user_id;
    
        $rid = M('Suggest')->add($dd);
    
        if(!$rid) {
            \Think\Log::write('写入数据失败','INFO');
            $ret['status'] = 0;
            $ret['info'] = '写入数据失败';
            exit(json_encode($ret));
        }
    
        $img_str = '';
    
        if($imgstr) {
    
            $img_list = explode('#',$imgstr);
            
            $i = 0;
            
            foreach ($img_list as $key => $val) {
                
                if ($i > 3) {
                    break;
                }
                
                $img = base64_decode($val);
    
                $save_path = './Uploads/';
                
                $save_url = 'suggest/'.date('Ymd');
                
                if (!file_exists($save_path.$save_url)) {
                    @mkdir($save_path.$save_url);
                    @chmod($save_path.$save_url, 0755);
                }
                
                $file = $save_url.'/'.$username.'_'.date('YmdHis').'_'.$key.'.jpg';
                $state = @file_put_contents($save_path.$file, $img);
                if($state > 0) {
                    
                    if (!$img_str) {
                        $img_str .= $file;
                    } else {
                        $img_str .= '|'.$file;
                    }
                    
                    $ossPath = 'Uploads/'.$file;
                    $files = '/usr/share/nginx/html/web/'.$ossPath;
                    $res = $this->uploadToOss($ossPath, $files);
                    
                    if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                        $ret['status'] = 0;
                        $ret['info'] = '上传失败oss，请联系客服';
                        $ret['img_url'] = '';
                        exit(json_encode($ret));
                    }
                    
                }
                
                $i ++;
            }
        }
    
        if($img_str) {
            $ff['img'] = $img_str;
            if(!M('Suggest')->where(array('id'=>$rid))->save($ff)) {
                \Think\Log::write('保存图片路径失败','INFO');
                $ret['status'] = 2;
                $ret['info'] = '保存图片路径失败';
                exit(json_encode($ret));
            }
        }
        $ret['status'] = 1;
        $ret['info'] = '上传成功';
        exit(json_encode($ret));
    }
    
   private function uploadToOss($file,$content){
        Vendor('OSS.autoload');
        $accessKeyId = 'LTAIyjZrdfEy1cqh';
        $accessKeySecret = 'uPEn87WaV2O0tOnscAwCqm7iTUdxip';
        $endpoint = 'http://oss-cn-hangzhou.aliyuncs.com';
        $bucketName = 'ppmiao-image';
        try {
            $content = file_get_contents($content,'r');
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->putObject($bucketName, $file, $content);
        } catch (OssException $e) {
            print $e->getMessage();
        }
        return (array)$res;
    }
    
    /**
    * java 调用，从ｏｏｓ　下截图片到本y
    * @date: 2017-6-13 下午1:30:21
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function downloadImg(){
        $url = I('post.url','','strip_tags');
        $sign = I('post.sign','','strip_tags');
        $ret = array();
        
        if(!$url || !$sign) {
            $ret['status'] = 0;
            $ret['info'] = '参数不完整';
            $ret['img_url'] = '';
            exit(json_encode($ret));
        }
        
        if ($sign != md5($url.$this->seckey)){
        
            $ret['status'] = 0;
            $ret['info'] = '签名不对';
            $ret['img_url'] = '';
            exit(json_encode($ret));
        }
        
        $file_url = 'https://image.ppmiao.com/Uploads/'.$url;      
        
        $arr = explode('/',$url);
        $save_to = './Uploads/'.$arr[0].'/'.$arr[1];
        
        if (!file_exists($save_to)) {
            mkdir($save_to);
            @chmod($save_to, 0755);
        }
        
        $content = file_get_contents($file_url);
        $state = file_put_contents($save_to.'/'.$arr[2], $content);
        
        if($state > 0){
            $ret['status'] = 1;
            exit(json_encode($ret));
        } else{
            $ret['status'] = 0;
            $ret['info'] = '写入失败';
            exit(json_encode($ret));
        }
    }
    
    //出账异步通知调用
    
    public function chargeoffNotify(){
        
        $input = file_get_contents("php://input");
        
        \Think\Log::write($input,'INFO');
    
        $order_no = I('order_no');
    
        if($order_no) {
    
            $data = M('projectChargeoffLog')->field('id,order_status')->where("order_no = '$order_no'")->find();
    
            if($data && $data['order_status'] == '0') {
                $row['plat_no'] = I('plat_no');
                $row['out_amt'] = I('out_amt');
                $row['platcust'] = I('platcust');
                $row['open_branch'] = I('open_branch');
                $row['withdraw_account'] = I('withdraw_account');
                $row['payee_name'] = I('payee_name');
                $row['pay_finish_date'] = I('pay_finish_date');
                $row['pay_finish_time'] = I('pay_finish_time');
                $row['order_status'] = I('order_status');
                $row['error_info'] = I('error_info');
                $row['error_no'] = I('error_no');
                $row['signdata'] = I('signdata');
                $row['host_req_serial_no'] = I('host_req_serial_no');
    
                M('projectChargeoffLog')->where('id='.$data['id'])->save($row);
                
                $this->ajaxReturn(['recode'=>'success'],'JSON');
            }
        } 
    }
}