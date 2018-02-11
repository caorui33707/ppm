<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class UploadModel extends Model {

    static public function upload(){
//        var_dump($_FILES);
        $status = 1;
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath' => C('UPLOAD_PATH'),
            'savePath'   =>    '',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
            'autoSub'    =>    true,
            'subName'    =>    array('date','Ymd'),
        );
        $upload = new \Think\Upload($config);// 实例化上传类
        // 上传文件
        $info   =   $upload->upload($_FILES);
//        var_dump($info);
        foreach($info as $img){
            $image[] = $img['savepath'].$img['savename'];
            $ossPath = 'Uploads/focus/'.$img['savepath'].$img['savename'];
            $file = C('localPath').$img['savepath'].$img['savename'];
            $res = uploadToOss($ossPath,$file);
            if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                $status = 0;
            }
            \Think\Log::write('upload info:'.json_encode($res),'INFO');
        }

//        var_dump($res);
        if($status == 0){
            return $status;
        }else{
            return $image;
        }

    }
}