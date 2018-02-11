<?php
namespace Admin\Controller;

class CommonController extends AdminController{

    /**
     * 获取债权类型期数(根据债权类型)
     */
    public function getProductStageByType(){
        if(!IS_POST || !IS_AJAX) exit;
        $type = I('post.type', 0, 'int');

        $project = M('Project');
        $stage = 1;
        $curStage = $project->where(array('type'=>$type))->order('stage desc')->getField('stage');
        if($curStage) $stage = $curStage + 1;
        $this->ajaxReturn(array('status'=>1,'info'=>$stage));
    }

    /**
     * 富文本上传图片加水印
     * @return [type] [description]
     */
    public function uploadWaterImg()
    {
        require_once './include/JSON.php';
        header('Content-type: text/html; charset=UTF-8');
        $json = new \Services_JSON();

        if (empty($_FILES) === false) {
            $image_path = $_FILES['imgFile']['tmp_name'];  //图片位置
            $water_path = $_SERVER['DOCUMENT_ROOT'].'/Public/admin/images/water.png';  //水印位置  

            // 添加水印
            $image = new \Think\Image();  
            $image->open($image_path)->water($water_path, 2, 100)->save($image_path);

            $temp_arr = explode(".", $_FILES['imgFile']['name']);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            $new_file_name = Guid() . '.' . $file_ext; 
            $ymd = date("Ymd");

            // 上传到阿里oss路径
            $ossPath = 'Uploads/focus/'.$ymd.'/'.$new_file_name;

            $res = uploadToOss($ossPath, $image_path);
            if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                echo $json->encode(array('error' => 1, 'message' => '图片上传到oss失败'));
                exit;
            }

            \Think\Log::write('upload info:'.json_encode($res),'INFO');
            echo $json->encode(array('error' => 0, 'url' => C('OSS_STATIC_ROOT').'/'.$ossPath));
            exit;
        }
    }

    /**
     * 富文本框上传图片接口
     */
    public function uploadImageForEditor(){
        require_once './include/JSON.php';
        header('Content-type: text/html; charset=UTF-8');
        $json = new \Services_JSON();

        if (empty($_FILES) === false) {
            $image_path = $_FILES['imgFile']['tmp_name'];  //图片位置
            // $water_path = $_SERVER['DOCUMENT_ROOT'].'/Public/admin/images/water.png';  //水印位置  

            // 添加水印
            // $image = new \Think\Image();  
            // $image->open($image_path)->water($water_path, 2, 100)->save($image_path);

            $temp_arr = explode(".", $_FILES['imgFile']['name']);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            $new_file_name = Guid() . '.' . $file_ext; 
            $ymd = date("Ymd");

            // 上传到阿里oss路径
            $ossPath = 'Uploads/focus/'.$ymd.'/'.$new_file_name;

            $res = uploadToOss($ossPath, $image_path);
            if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                echo $json->encode(array('error' => 1, 'message' => '图片上传到oss失败'));
                exit;
            }

            \Think\Log::write('upload info:'.json_encode($res),'INFO');
            echo $json->encode(array('error' => 0, 'url' => C('OSS_STATIC_ROOT').'/'.$ossPath));
            exit;
        }

        // //文件保存目录路径
        // $save_path = '../cg.web/Uploads/';
        // //文件保存目录URL
        // $save_url = C('SITE_ROOT').'/Uploads/';

        // //定义允许上传的文件扩展名
        // $ext_arr = array(
        //     'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
        // );
        // //最大文件大小
        // $max_size = 2000000;

        // $save_path = realpath($save_path) . '/';

        // //PHP上传失败
        // if (!empty($_FILES['imgFile']['error'])) {
        //     switch($_FILES['imgFile']['error']){
        //         case '1':
        //             $error = '超过php.ini允许的大小。';
        //             break;
        //         case '2':
        //             $error = '超过表单允许的大小。';
        //             break;
        //         case '3':
        //             $error = '图片只有部分被上传。';
        //             break;
        //         case '4':
        //             $error = '请选择图片。';
        //             break;
        //         case '6':
        //             $error = '找不到临时目录。';
        //             break;
        //         case '7':
        //             $error = '写文件到硬盘出错。';
        //             break;
        //         case '8':
        //             $error = 'File upload stopped by extension。';
        //             break;
        //         case '999':
        //         default:
        //             $error = '未知错误。';
        //     }
        //     alert($error);
        // }

        // //有上传文件时
        // if (empty($_FILES) === false) {
        //     //原文件名
        //     $file_name = $_FILES['imgFile']['name'];
        //     //服务器上临时文件名
        //     $tmp_name = $_FILES['imgFile']['tmp_name'];
        //     //文件大小
        //     $file_size = $_FILES['imgFile']['size'];
        //     //检查文件名
        //     if (!$file_name) {
        //         echo $json->encode(array('error' => 1, 'message' => "请选择文件。"));
        //         exit;
        //     }
        //     //检查目录
        //     if (@is_dir($save_path) === false) {
        //         echo $json->encode(array('error' => 1, 'message' => "上传目录不存在。"));
        //         exit;
        //     }
        //     //检查目录写权限
        //     if (@is_writable($save_path) === false) {
        //         echo $json->encode(array('error' => 1, 'message' => "上传目录没有写权限。"));
        //         exit;
        //     }
        //     //检查是否已上传
        //     if (@is_uploaded_file($tmp_name) === false) {
        //         echo $json->encode(array('error' => 1, 'message' => "上传失败。"));
        //         exit;
        //     }
        //     //检查文件大小
        //     if ($file_size > $max_size) {
        //         echo $json->encode(array('error' => 1, 'message' => "上传文件大小超过限制。"));
        //         exit;
        //     }
        //     //检查目录名
        //     $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        //     if (empty($ext_arr[$dir_name])) {
        //         echo $json->encode(array('error' => 1, 'message' => "目录名不正确。"));
        //         exit;
        //     }
        //     //获得文件扩展名
        //     $temp_arr = explode(".", $file_name);
        //     $file_ext = array_pop($temp_arr);
        //     $file_ext = trim($file_ext);
        //     $file_ext = strtolower($file_ext);
        //     //检查扩展名
        //     if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
        //         echo $json->encode(array('error' => 1, 'message' => "上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。"));
        //         exit;
        //     }
        //     //创建文件夹
        //     if ($dir_name !== '') {
        //         $save_path .= $dir_name . "/";
        //         $save_url .= $dir_name . "/";
        //         if (!file_exists($save_path)) {
        //             mkdir($save_path);
        //         }
        //     }
        //     $ymd = date("Ymd");
        //     $save_path .= $ymd . "/";
        //     $save_url .= $ymd . "/";
        //     if (!file_exists($save_path)) {
        //         mkdir($save_path);
        //     }
        //     //新文件名
        //     //$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
        //     $new_file_name = Guid() . '.' . $file_ext;
        //     //移动文件
        //     $file_path = $save_path . $new_file_name;
        //     if (move_uploaded_file($tmp_name, $file_path) === false) {
        //         echo $json->encode(array('error' => 1, 'message' => '上传文件失败。'));
        //         exit;
        //     }
        //     @chmod($file_path, 0644);
        //     $file_url = $save_url . $new_file_name;
            
        //     $path = explode('Uploads/',$file_url);
            
        //     //http://testing.ppmiao.com/Uploads/image/20170524/61D32CE6-9EE3-80A9-FD7E-9D7CF3A02E36.png
            
        //     $ossPath = 'Uploads/'.$path[1];
        //     $file = '/mnt/php/cg.web/'.$ossPath;
        //     //file_put_contents('upload.log', $ossPath.'|'.$file,FILE_APPEND);
        //     $res = uploadToOss($ossPath,$file);
        //     if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
        //         echo $json->encode(array('error' => 1, 'message' => '图片上传到oss失败'));
        //         exit;
        //     }
        //     \Think\Log::write('upload info:'.json_encode($res),'INFO');
        //    // echo $json->encode(array('error' => 0, 'url' => $file_url));
        //     echo $json->encode(array('error' => 0, 'url' => C('OSS_STATIC_ROOT').'/'.$ossPath));
        //     exit;
        // }

    }

    /**
     * 富文本编辑器文件管理接口
     */
    public function fileManagerForEditor(){
        C('SHOW_PAGE_TRACE', false);
        require_once './include/JSON.php';

        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = '../web/Uploads/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = C('SITE_WEB') . '/Uploads/';
        //图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        //目录名
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                mkdir($root_path);
            }
        }

        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        //echo realpath($root_path);
        //排序形式，name or size or type
        $order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }

        //排序
        function cmp_func($a, $b) {
            global $order;
            if ($a['is_dir'] && !$b['is_dir']) {
                return -1;
            } else if (!$a['is_dir'] && $b['is_dir']) {
                return 1;
            } else {
                if ($order == 'size') {
                    if ($a['filesize'] > $b['filesize']) {
                        return 1;
                    } else if ($a['filesize'] < $b['filesize']) {
                        return -1;
                    } else {
                        return 0;
                    }
                } else if ($order == 'type') {
                    return strcmp($a['filetype'], $b['filetype']);
                } else {
                    return strcmp($a['filename'], $b['filename']);
                }
            }
        }
        usort($file_list, 'cmp_func');

        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        header('Content-type: text/html; charset=UTF-8');
        $json = new \Services_JSON();
        echo $json->encode($result);
    }

    /**
     * 查询短信剩余量接口
     */
    public function getSmsQueryBalance(){
        if(!IS_POST || !IS_AJAX) exit;
        $errorMsg = array(
            101 => '无此用户',
            102 => '密码错',
            103 => '查询过快（30秒查询一次）',
        );
        $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/QueryBalance?account='.C('SMS_INTDERFACE.account').'&pswd='.C('SMS_INTDERFACE.pswd'));
        $arr = explode("\n", $smsData);
        foreach($arr as $key => $val){
            $arr[$key] = explode(',', $val);
        }
        if($arr[0][1] != 0) $this->ajaxReturn(array('status'=>0, 'info'=>$errorMsg[$arr[0][1]]));
        $this->ajaxReturn(array('status'=>0,'info'=>$arr[1][1]));
    }

    /**
     * 配置Jquery Autocomplete的产品名称异步查询
     */
    public function autoproject(){
        if(!IS_AJAX) exit;

        $query = I('get.query', '', 'strip_tags');
        $tag = I('get.tag', '', 'strip_tags');
        $projectObj = M("Project");
        $rows = array(
            'query' => $query,
            'suggestions' => array(),
        );
        $list = $projectObj->field('id,title,amount')->where("title like '%".$query."%' and is_delete=0")->limit(10)->select();
        foreach($list as $key => $val){
            $tmp = array(
                'value' => $val['title'],
                'data' => ($tag ? $val[$tag] : $val['amount']),
            );
            $rows['suggestions'][] = $tmp;
        }
        echo json_encode($rows);
    }

    /**
     * 调用百度api接口
     */
    public function baiduApi(){
        require_once('include/baidu_api.php');
        $api = new \baidu_api();
        $act = I('post.act', '', 'strip_tags');
        switch($act){
            case 'idcard': // 身份证归属地查询
                $idcard = trim(I('post.idcard', '', 'strip_tags'));
                $ret = $api->idservice($idcard);
                if($ret['errNum'] == 0){
                    $this->ajaxReturn(array('status'=>1,'info'=>$ret['retData']));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret['retMsg'].'('.$ret['errNum'].')'));
                }
                break;
            case 'mobilephoneservice': // 手机号码归属地查询
                $phone = trim(I('post.phone', '', 'strip_tags'));
                $ret = $api->mobilephoneservice($phone);
                if($ret['errNum'] == 0){
                    $this->ajaxReturn(array('status'=>1,'info'=>$ret['retData']));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret['retMsg'].'('.$ret['errNum'].')'));
                }
                break;
            case 'cardinfo': // 银行卡归属地
                $card = trim(I('post.card', '', 'strip_tags'));
                $ret = $api->cardinfo($card);
                if($ret['status'] == 1){
                    $this->ajaxReturn(array('status'=>1,'info'=>$ret['data']));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret['retMsg'].'('.$ret['errNum'].')'));
                }
                break;
        }
    }

    /**
     * 石头api接口
     */
    public function stoneApi(){
        $code = I('post.code', '', 'strip_tags');
        $target = I('post.target', 0, 'int');
        $act = I('post.act', '', 'strip_tags');

        if($target >= 1 && $target <= 3){
            if(empty($code)) $this->ajaxReturn(array('status'=>0,'info'=>'错误码不能为空'));
            $errorCodeObj = M("ErrorCode");
            $detail = $errorCodeObj->where(array('code'=>$code,'type'=>$target))->find();
            if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'错误码信息不存在'));

            $this->ajaxReturn(array('status'=>1,'data'=>$detail));
        }else if($target == 4){ // 石头订单号反查用户信息
            if(empty($code)) $this->ajaxReturn(array('status'=>0,'info'=>'订单号不能为空'));
            if(strlen($code) < 2) $this->ajaxReturn(array('status'=>0,'info'=>'订单号格式不正确'));
            $prevStr = substr($code, 0, 2);
            $userId = 0;
            if(strtoupper($prevStr) == 'ST' || strtoupper($prevStr) == 'YB'){ // 产品订单
                $rechargeLogObj = M("RechargeLog");
                $userId = $rechargeLogObj->where(array('recharge_no'=>$code))->getField('user_id');
            }else if(strtoupper($prevStr) == 'QB'){ // 钱包订单
                $userWalletRecordsObj = M("UserWalletRecords");
                $userId = $userWalletRecordsObj->where(array('recharge_no'=>$code))->getField('user_id');
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'无法识别订单号'));
            }
            if(!$userId) $this->ajaxReturn(array('status'=>0,'info'=>'订单号不存在'));
            $userObj = M("User");
            $uInfo = $userObj->where(array('id'=>$userId))->find();
            $info = '用户ID:'.$uInfo['id'].'<br>';
            $info.= '用户手机:'.$uInfo['username'].'<br>';
            $info.= '用户姓名:'.$uInfo['real_name'].'<br>';
            $this->ajaxReturn(array('status'=>1,'info'=>$info));
        }else if($target == 5){ // 第三方支付订单状态查询接口
            if(empty($code)) $this->ajaxReturn(array('status'=>0,'info'=>'订单号不能为空'));

        }
    }
    
    /**
    * 取合同利率
    * @date: 2017-2-6 下午5:29:44
    * @author: hui.xu
    * @param: ppm
    * @return:
    */
    public function getContractInfo(){
        if(!IS_POST || !IS_AJAX) exit;
        $contract_no = I('post.contract_no', '', 'strip_tags');
        if(!$contract_no)$this->ajaxReturn(array('status'=>1,'info'=>''));     
        $interest = M('Contract')->where(array('name'=>$contract_no))->getField('interest');        
        $this->ajaxReturn(array('status'=>1,'info'=>$interest));
    }   
}