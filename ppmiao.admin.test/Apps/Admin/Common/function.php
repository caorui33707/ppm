<?php

use OSS\Core\OssException;
/**
 * 检查后台登录验证
 */
function checkAdminAuth(){
    $info = session(ADMIN_SESSION);
    $ip = get_client_ip();
    if(md5($info['uid'].$info['username'].$ip.C('ADMIN_SECRET_KEY').$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT'].$_SERVER['HTTP_USER_AGENT']) != $info['token']) return false;
    return session(ADMIN_SESSION);
}

function post($url, $data = array(), $contentType = 'application/x-www-form-urlencoded'){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, C('API').$url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        echo 'Errno'.curl_error($curl);
    }
    curl_close($curl); // 关键CURL会话
    return json_decode($tmpInfo); // 返回数据
}

// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 检测访问的ip是否为规定的允许的ip
 * Enter description here ...
 */
function check_ip(){
    if(!C('LIMIT_IP')){ // 暂时去掉IP限制
        return true;
        die;
    }

    $ALLOWED_IP = C('ALLOWED_IP');
    $IP = getIP();
    $check_ip_arr = explode('.', $IP);//要检测的ip拆分成数组
    $ipObj = new Org\Net\IpLocation('UTFWry.dat');
    $location = $ipObj->getlocation($IP);
    $excudeArea = C('EXCLUDE_AREA');
    if($excudeArea){
        if(mb_substr($location['country'], 0, mb_strlen($excudeArea, 'utf8'), 'utf-8') == $excudeArea){
            return true;
            die;
        }
    }
    #限制IP
    if(!in_array($IP,$ALLOWED_IP)) {
        foreach ($ALLOWED_IP as $val){
            if(strpos($val, '*')!==false){//发现有*号替代符
                $arr = array();//
                $arr = explode('.', $val);
                $bl = true;//用于记录循环检测中是否有匹配成功的
                for($i = 0; $i < 4; $i++){
                    if($arr[$i] != '*'){//不等于*  就要进来检测，如果为*符号替代符就不检查
                        if($arr[$i] != $check_ip_arr[$i]){
                            $bl = false;
                            break;//终止检查本个ip 继续检查下一个ip
                        }
                    }
                }
                if($bl){//如果是true则找到有一个匹配成功的就返回
                    return;
                    die;
                }
            }
        }
        header('HTTP/1.1 403 Forbidden');
        echo "Access forbidden";
        die;
    }
}

/**
 * 获得访问的IP
 * Enter description here ...
 */
function getIP() {
    return isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]
        :(isset($_SERVER["HTTP_CLIENT_IP"])?$_SERVER["HTTP_CLIENT_IP"]
            :$_SERVER["REMOTE_ADDR"]);
}

/**
 * 权限验证
 */
function checkAuth($name){
    if($_SESSION[ADMIN_SESSION]['uid'] == 1){ // 管理员直接通过
        return true;
    }else{
        $Auth = new \Think\Auth();
        return $Auth->check($name, $_SESSION[ADMIN_SESSION]['uid'], array('in', '1,2'));
    }
}

/**
 * 判断是否是有手机浏览
 */
function isMobile(){
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 消息推送
 * @param $content 推送内容
 * @param string $reg_id 指定推送设备注册ID
 * @param string $platform 指定推送设备(ios,android)
 * @return string
 */
function pushMsg($content, $reg_id='', $_platform='', $extra = array(),$app=0){
    require_once("vendor/autoload.php");
    $timeout = 3600; // 推送消息延时时间

    switch ($app){
        case 0:
            $appKey = C('AppStore_pushtest.APP_KEY');
            $masterSecret = C('AppStore_pushtest.MASTER_SECRET');
            break;
    }

    /*
    switch ($app) {
        case 0:
            $appKey = C('JPUSH.APP_KEY');
            $masterSecret = C('JPUSH.MASTER_SECRET');
            break;
        case 1:
            $appKey = C('AppStore_personal_JPUSH.APP_KEY');
            $masterSecret = C('AppStore_personal_JPUSH.MASTER_SECRET');
            break;
        case 2:
            $appKey = C('AppStore_pro_JPUSH.APP_KEY');
            $masterSecret = C('AppStore_pro_JPUSH.MASTER_SECRET');
            break;
        case 3:
            $appKey = C('AppStore_honorable.APP_KEY');
            $masterSecret = C('AppStore_honorable.MASTER_SECRET');
            break;
        case 4:
            $appKey = C('AppStore_feedback.APP_KEY');
            $masterSecret = C('AppStore_feedback.MASTER_SECRET');
            break;
        case 5:
            $appKey = C('AppStore_welfare.APP_KEY');
            $masterSecret = C('AppStore_welfare.MASTER_SECRET');
            break;
        case 6:
            $appKey = C('AppStore_flagship.APP_KEY');
            $masterSecret = C('AppStore_flagship.MASTER_SECRET');
            break;
        case 7:
            $appKey = C('AppStore_Anniversary.APP_KEY');
            $masterSecret = C('AppStore_Anniversary.MASTER_SECRET');
            break;
        case 8:
            $appKey = C('AppStore_Professional.APP_KEY');
            $masterSecret = C('AppStore_Professional.MASTER_SECRET');
            break;
        case 9:
            $appKey = C('AppStore_VIP.APP_KEY');
            $masterSecret = C('AppStore_VIP.MASTER_SECRET');
            break;
        case 10:
            $appKey = C('AppStore_classical.APP_KEY');
            $masterSecret = C('AppStore_classical.MASTER_SECRET');
            break;
        case 11:
            $appKey = C('AppStore_Financing.APP_KEY');
            $masterSecret = C('AppStore_Financing.MASTER_SECRET');
            break;
        case 12:
            $appKey = C('AppStore_Investment.APP_KEY');
            $masterSecret = C('AppStore_Investment.MASTER_SECRET');
            break;
        case 13:
            $appKey = C('AppStore_qqjrFinance.APP_KEY');
            $masterSecret = C('AppStore_qqjrFinance.MASTER_SECRET');
            break;
        case 14:
            $appKey = C('AppStore_qqjrSpecial.APP_KEY');
            $masterSecret = C('AppStore_qqjrSpecial.MASTER_SECRET');
            break;
        case 15:
            $appKey = C('AppStore_PPMFeedback.APP_KEY');
            $masterSecret = C('AppStore_PPMFeedback.MASTER_SECRET');
            break;
        case 16:
            $appKey = C('AppStore_PPMLuxury.APP_KEY');
            $masterSecret = C('AppStore_PPMLuxury.MASTER_SECRET');
            break;
        case 17:
            $appKey = C('AppStore_qqjwFinance.APP_KEY');
            $masterSecret = C('AppStore_qqjwFinance.MASTER_SECRET');
            break;
        case 18:
            $appKey = C('AppStore_miaodppmFinancial.APP_KEY');
            $masterSecret = C('AppStore_miaodppmFinancial.MASTER_SECRET');
            break;
                
    }
    */

    $client = new \JPush\JPushClient($appKey, $masterSecret);
    try{
        if($reg_id){
            $reg = \JPush\Model\registration_id(explode(',', $reg_id));
        }else{
            $reg = \JPush\Model\all;
        }

        if($_platform){
            $platform = \JPush\Model\platform($_platform);
            if($_platform == 'android'){
                $notification = \JPush\Model\notification($content, \JPush\Model\android($content, null, null, $extra));
            }else if($_platform == 'ios'){
                $notification = \JPush\Model\notification($content, \JPush\Model\ios($content, null, null, true, $extra, null));
            }
        }else{
            $platform = \JPush\Model\all;
            $notification = \JPush\Model\notification($content,
                \JPush\Model\android($content, null, null, $extra),
                \JPush\Model\ios($content, null, null, null, $extra, null));
        }

        $result = $client->push()
            //->setOptions(\JPush\Model\options(null, $timeout, null, true, 0))
            ->setOptions(\JPush\Model\options(null, $timeout, null, false, 0))
            ->setPlatform($platform)
            ->setAudience($reg)
            ->setNotification($notification)
            //  ->printJSON()
            ->send();

//        $br = '</br>';
//
//        echo 'Push Success.' . $br;
//        echo 'sendno : ' . $result->sendno . $br;
//        echo 'msg_id : ' .$result->msg_id . $br;
//        echo 'Response JSON : ' . $result->json . $br;exit;


        return $result;
    }catch(APIRequestException $e){ // \JPush\Exceptions\APIConnectionException
        return $e->getMessage();
    }catch(APIConnectionException $e){ // \JPush\Exceptions\APIRequestException
        return $e->getMessage();
    }
}

/**
 * 记录管理员登录日志
 */
function loginLog($uid, $username, $status = 0){
    $time = time();
    $ip = get_client_ip();
    $ipObj = new Org\Net\IpLocation('UTFWry.dat');
    $location = $ipObj->getlocation($ip);
    if($location['country'] != '局域网'){
        $locationStr = $location['country'].$location['area'];
    }else{
        $locationStr = '局域网';
    }

    $adminLogLoginObj = M('AdminLogLogin');
    $rows = array(
        'uid' => $uid,
        'username' => $username,
        'ip' => $ip,
        'location' => $locationStr,
        'status' => $status,
        'time_add' => $time,
    );
    $adminLogLoginObj->add($rows);
}

/**
 * 数字钱转化成大写金额
 * @param $money
 * @return mixed
 */
function money($money){
    static $cnums = array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"),
    $cnyunits = array("圆","角","分"),
    $grees = array("拾","佰","仟","万","拾","佰","仟","亿");
    list($ns1, $ns2) = explode(".", $money, 2);
    $ns2 = array($ns2[1], $ns2[0]);
    $ret = array_merge($ns2, array(implode("", _money_unit(str_split($ns1), $grees)), ""));
    $ret = implode("", array_reverse(_money_unit($ret, $cnyunits)));
    return str_replace(array_keys($cnums), $cnums, $ret);
}

/**
 * 函数主要是格式化数字为中文大写
 * @param $list
 * @param $units
 * @return array
 */
function _money_unit($list,$units){
    $ul=count($units);
    $xs=array();
    foreach (array_reverse($list) as $x){
        $l=count($xs);
        if ($x!="0" || !($l%4)){
            $n= ($x=='0'?'':$x).($units[($l-1)%$ul]);
        }
        else{
            $n = is_numeric($xs[0][0]) ? $x : '';
        }
        array_unshift($xs,$n);
    }
    return $xs;
}

/**
 * 格式化新版项目内容描述
 */
function format_project_descr($descr){
    $arr = explode('<!--line-->', $descr);
    foreach($arr as $key => $val){
        $arr[$key] = explode('<!--title-->', $val);
    }
    return $arr;
}

/**
 * 格式化新版项目图片链接
 * @param $image
 */
function format_project_image($image){
    return explode("\r\n", $image);
}

/**
 * 隐藏银行卡部分号码
 */
function hide_whole_cardno($cardNo){
    return substr($cardNo, 0, 4).'******'.substr($cardNo, strlen($cardNo) - 4);
}

/**
 * 隐藏身份证部分号码
 */
function hide_whole_idcard($idcard){
    return substr($idcard, 0, 4).'**********'.substr($idcard, strlen($idcard) - 4);
}

/**
 * 获取每月第一天与最后一天
 * @param $date
 * @return array
 */
function get_the_month($date){
    $firstday = date('Y-m-01', strtotime($date));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return array($firstday,$lastday);
}

/**
 * 计算两个日期之间的天数差
 * @param $a
 * @param $b
 * @return float
 */
function count_days($a, $b){
    return floor(abs(strtotime($a)-strtotime($b))/86400);
}

/**
 * 检查代付订单状态(连连支付)
 * @param $paybill 支付单号(连连支付支付单号)
 * @return 连连支付处理结果
 */
function check_order_pay_status_by_ll($order){
    require_once ("include/ll_cash_pay/lib/llpay_apipost_submit.class.php");
    //构造要请求的参数数组，无需改动
    $parameter = array (
        "oid_partner" => trim(C('llpay_config.oid_partner')),
        "sign_type" => trim(C('llpay_config.sign_type')),
        "no_order" => $order,
        "oid_paybill" => '',
        "type_dc" => 1, //收付标识
    );
    //建立请求
    $url = "https://yintong.com.cn/traderapi/orderquery.htm";
    $llpaySubmit = new LLpaySubmit(C('llpay_config'));
    $ret = $llpaySubmit->buildRequestJSON($parameter, $url);
    return $ret;
}

/**
 * 向第三方提交代付订单(连连支付)
 * @param $params 参数数组
 */
function pay_order_by_ll($params){
//    require_once ("include/ll_cash_pay/lib/llpay_apipost_submit.class.php");
//    //建立请求
//    $url = "https://yintong.com.cn/traderapi/cardandpay.htm";
//    C('llpay_config.sign_type', strtoupper('RSA'));
//    $llpaySubmit = new LLpaySubmit(C('llpay_config'));
//    $ret = $llpaySubmit->buildRequestJSON($params, $url);
//    return $ret;
}

/**
 * 检查代付订单状态(盛付通)
 */
function check_order_pay_status_by_sft($batchNo, $detailId){
    require_once ("include/sft_cash_pay/BatchPaymentUtils.php");
    $obj = new QueryRequest();
    $obj->setBatchNo($batchNo);
    $obj->setDetailId($detailId);
    $obj->setCustomerNo(C('sftpay_config.customer_no'));
    $obj->setCharset(C('sftpay_config.charset'));
    $obj->setSignType(C('sftpay_config.sign_type'));
    $obj->setSignKey(C('sftpay_config.key'));
    $obj->setSign($obj->sign());
    $obj->setIsDebug(true);
    return $obj->query();
}

/**
 * 向第三方提交代付订单(盛付通)
 */
function pay_order_by_sft($params){
//    require_once ("include/sft_cash_pay/BatchPaymentUtils.php");
//    $obj = new DirectApplyRequest();
//    $obj->setBatchNo($params['batchNo']);
//    $obj->setCallbackUrl($params['callbackUrl']);
//    $obj->setTotalAmount($params['totalAmount']);
//    $obj->setCustomerNo(C('sftpay_config.customer_no'));
//    $obj->setCharset(C('sftpay_config.charset'));
//    $obj->setSignType(C('sftpay_config.sign_type'));
//
//    $item = new ApplyInfoDetail();
//    $item->setId($params['id']);
//    $item->setProvince($params['province']);
//    $item->setCity($params['city']);
//    $item->setBranchName($params['branchName']);
//    $item->setBankName($params['bankName']);
//    $item->setAccountType('C');
//    $item->setBankUserName($params['bankUserName']);
//    $item->setBankAccount($params['bankAccount']);
//    $item->setAmount($params['amount']);
//    $item->setRemark($params['remark']);
//
//    $obj->setDetails($item);
//    $list = new \Org\Util\ArrayList();
//    $list->add($item);
//    $obj->setDetails($list);
//    $obj->setSign($obj->sign());

}

/**
 * 发送个人消息
 * @params $from_uid 发送者UID
 * @params $to_uid 发送给用户的UID
 * @params $message 消息内容
 * @params $action 动作编号
 * @params $ext 扩展信息
 */
function send_personal_message($from_uid = 0,$to_uid, $message, $action = 0, $ext = ''){
    $messagePersonalObj = M('MessagePersonal');
    $messagePersonalContentObj = M('MessagePersonalContent');

    $rowsContent = array(
        'content' => $message,
        'ext' => $ext,
    );
    $msgID = $messagePersonalContentObj->add($rowsContent);
    if($msgID){
        $rows = array(
            'sender_uid' => $from_uid,
            'recipient_uid' => $to_uid,
            'message_content_id' => $msgID,
            'action' => $action,
            'is_read' => 0,
            'is_delete' => 0,
            'add_time' => date('Y-m-d H:i:s', time()),
        );
        if(!$messagePersonalObj->add($rows)){
            return false;
        }else{
            return true;
        }
    }else{
        return false;
    }
}

/**
 * 自动生成一个幽灵账户的手机号码
 */
function auto_create_phone_for_ghost(){
    $qz = array(134,135,136,137,138,139,150,151,152,157,158,159,182,187,188,147,130,131,132,155,156,186,145,133,153,189); // 运营商手机号码前缀
    $middle = rand(0, 9999); // 中段号码(4位)
    $last = rand(0, 9999); // 尾号(4位)
    for($i = 3-strlen($middle); $i >= 0 ; $i--){
        $middle = '0'.$middle;
    }
    for($i = 3-strlen($last); $i >= 0; $i--){
        $last = '0'.$last;
    }
    return $qz[rand(0, count($qz)-1)].$middle.$last;
}

/**
 * 计算基金收益率(104,109,110,148)
 * @param $projectID 产品ID
 */
function cal_fund_percent($projectID){
    $projectObj = M("Project");
    $fundDetailObj = M("FundDetail");
    $detail = $projectObj->field('id,advance_end_time,end_time,type,user_interest')->where(array('id'=>$projectID))->find();
    if(in_array($detail['type'], array(104,109,110))){ // 普通基金
        $projectModelFundObj = M("ProjectModelFund");
        $detailExt = $projectModelFundObj->where(array('project_id'=>$projectID))->find();
    }else if($detail['type'] == 148){ // 搏息宝
        $projectModelSectionObj = M("ProjectModelSection");
        $detailExt = $projectModelSectionObj->where(array('project_id'=>$projectID))->find();
    }else{
        return 0;exit;
    }
    $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
    if(!$detail['advance_end_time']) $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
    else $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
    $today = date('Y-m-d', time()); // 当前时间点
    if($today < $timeEnd) $timeEnd = $today;
    $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=".$detailExt['fund_id']." and datetime>='".$timeStart."' and datetime<='".$timeEnd."'")->order('datetime asc')->select(); // 关联基金净值列表
    if(count($fundList) > 0 && $fundList[0]['datetime'] != $timeStart){ // 补全由于遇到节假日而不存在的净值数据
        // 遇到节假日期间净值则取节假日之前的第一个有效净值
        $fundStart = $fundDetailObj->where("fund_id=" . $detailExt['fund_id'] . " and datetime<'".$fundList[0]['datetime']."'")->order("datetime desc")->getField('val');
        // 当前取出净值数据往前推至真实进入点日期
        $valRows = array(
            'val' => $fundStart,
            'datetime' => $timeStart,
        );
        array_unshift($fundList, $valRows);
    }
    $percent = 0; // 基金类收益率
    if(count($fundList) > 1){ // 两个净值点以上
        $fundStart = $fundList[0]['val']; // 起始净值
        $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

        switch($detail['type']){
            case 104: // 打新股,收益超过18%分成
                if($fundEnd - $fundStart > 0){
                    if(($fundEnd - $fundStart)/$fundStart > 0.18){ // 分成
                        $percent = 0.18 + (($fundEnd - $fundStart)/$fundStart - 0.18)/2;
                    }else{
                        $percent = ($fundEnd - $fundStart)/$fundStart;
                    }
                }
                break;
            case 109: // B类基金,杠杆0.2
                if($fundEnd - $fundStart > 0){
                    $fundEndB = ($fundEnd - $fundStart)*0.2 + $fundStart;
                    $percent = ($fundEndB - $fundStart)/$fundStart;
                }
                break;
            case 110: // A类基金,杠杆2.6
                if($fundEnd - $fundStart > 0){
                    $fundEndA = ($fundEnd - $fundStart)*2.6 + $fundStart;
                    $percent = ($fundEndA - $fundStart)/$fundStart;
                }else if($fundEnd - $fundStart < 0){
                    $fundEndA = ($fundEnd - $fundStart)*3 + $fundStart;
                    $percent = ($fundEndA - $fundStart)/$fundStart;
                }
                break;
            case 148: // 搏息宝
                $percent = round(($fundEnd - $fundStart)/$fundStart, 4)*100;
                if($percent < $detail['user_interest']) $percent = $detail['user_interest']/100;
                else if($percent > $detailExt['max_interest']) $percent = $detailExt['max_interest']/100;
                else $percent = $percent/100;
                break;
        }
    }else{
        if($detail['type'] == 148){ // 获取搏息宝最低年化率
            $percent = $detail['user_interest']/100;
        }
    }
    return $percent;
}

/**
 * 判断是否是日期格式
 * @param $str
 */
function isDateFormat($str){
    return strtotime($str) !== false && strlen($str) == 10;
}
/**判断当前日期是星期几 */
function getWeek($date){
    $week = date("w",$date);
    switch($week){
        case 1:
            return "星期一";
            break;
        case 2:
            return "星期二";
            break;
        case 3:
            return "星期三";
            break;
        case 4:
            return "星期四";
            break;
        case 5:
            return "星期五";
            break;
        case 6:
            return "星期六";
            break;
        case 0:
            return "星期日";
            break;
    }
}

/**
 * 积光推送日志
 */
function JPushLog($s) {
    $fileName = LOG_PATH  . 'jgpush.log';
	$s  = '['.date("Y-m-d H:i:s").']  '.$s."\r\n";
    file_put_contents($fileName,$s,FILE_APPEND);
}

/**
* 更新用户信息推送
* @date: 2017-3-15 下午5:13:25
* @author: hui.xu
* @param: $GLOBALS
* @return: 
*/
function updatePushMsg($data,$path=''){
    //$data = array('pushType'=>2,'registrationId'=>'120c83f760168ef20e8','position'=>3,'page'=>3,'lastDeviceType'=>2);
    $data = http_build_query($data);
    $opts = array(
        'http'=>array(
            'method'=>"POST",
            'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
            "Content-length:".strlen($data)."\r\n" .
            "Cookie: foo=bar\r\n" .
            "\r\n",
            'content' => $data,
        )
    );
    $cxContext = stream_context_create($opts);
    $sFile = file_get_contents(C('API_PUSH_URL').$path, false, $cxContext);
}

function uploadToOss($file,$content){
    Vendor('OSS.autoload');
    $accessKeyId = C('accessKeyId');
    $accessKeySecret = C('accessKeySecret');
    $endpoint = C('endpoint');
    try {
        $content = file_get_contents($content,'r');
        $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $res = $ossClient->putObject(C('bucketName'), $file, $content);
    } catch (OssException $e) {
        print $e->getMessage();
    }
    return (array)$res;
}


function getAppId($channel_name){
    
    $_app = 0;
    
    /*
    if($channel_name == 'AppStore_personal') {
        $_app = 1;
    } else if($channel_name == 'AppStore_pro') {
        $_app = 2;
    } else if($channel_name == 'AppStore_honorable'){
        $_app = 3;
    } else if($channel_name == 'AppStore_feedback'){
        $_app = 4;
    } else if($channel_name == 'AppStore_welfare'){
        $_app = 5;
    } else if($channel_name == 'AppStore_flagship'){
        $_app = 6;
    } else if($channel_name == 'AppStore_Anniversary'){
        $_app = 7;
    } else if($channel_name == 'AppStore_Professional'){
        $_app = 8;
    } else if($channel_name == 'AppStore_VIP'){
        $_app = 9;
    } else if($channel_name == 'AppStore_classical'){
        $_app = 10;
    } else if($channel_name == 'AppStore_Financing'){
        $_app = 11;
    } else if($channel_name == 'AppStore_Investment'){
        $_app = 12;
    } else if($channel_name == 'AppStore_qqjrFinance'){
        $_app = 13;
    } else if($channel_name == 'AppStore_qqjrSpecial'){
        $_app = 14;
    } else if($channel_name == 'AppStore_PPMFeedback'){
        $_app = 15;
    } else if($channel_name == 'AppStore_PPMLuxury'){
        $_app = 16;
    } else if($channel_name == 'AppStore_qqjwFinance'){
        $_app = 17;
    } else if($channel_name == 'AppStore_miaodppmFinancial'){
        $_app = 18;
    }
    */
    
    return $_app;
}

function getRedis(){
    $redis = new Redis();
    $redis->connect(C("REDIS_HOST"),C("REDIS_PORT"));
    $redis->auth(C("REDIS_AUTH"));
}


/**
 * 上报标的信息
 * @date: 2017-7-3 上午11:16:12
 * @author: hui.xu
 * @param: variable
 * @return:
 */
function publish($id,$platcust=null){

    $projectObj = M('Project');
    $financingObj = M('Financing');
    $contractObj = M('Contract');

    vendor('Fund.FD');
    vendor('Fund.sign');

    $projectInfo = M('Project')->field('id,title,amount,start_time,end_time,fid,contract_no,user_interest,duration_type')
    ->where('id='.$id)->find();

    $req['prod_id'] = $projectInfo['id'];
    $req['prod_name'] = $projectInfo['title'];
    $req['total_limit'] = $projectInfo['amount'];
    $req['sell_date'] = date('Y-m-d H:i:s',strtotime($projectInfo['start_time']));
    $req['expire_date'] = date('Y-m-d H:i:s',strtotime($projectInfo['end_time']));

    //标的期限类型 1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)
    $dd['cycle'] = $projectInfo['duration_type'];

    if($projectInfo['duration_type'] == 1) {
        $cycle_unit = 2;
    } else {
        $cycle_unit = 3;
    }
    //cycle_unit	是	String	周期单位  1日 2周 3月 4季 5年
    $req['cycle_unit'] = $cycle_unit;//$projectInfo['id'];
    $req['ist_year'] = $projectInfo['user_interest']/100;

    $contract = $contractObj->field('add_time,fee')->where("name='".$projectInfo['contract_no']."'")->find();

    $financing = M('Financing')->field('bank_id,bank_card_no,platform_account,acct_name,bank_code')
    ->where('id='.$projectInfo['fid'])->find();

    $dd['cust_no'] = $financing['platform_account'];
    $dd['reg_date'] = date('Y-m-d',strtotime($contract['add_time']));
    $dd['reg_time'] = date('h:i:s',strtotime($contract['add_time']));
    $dd['financ_int'] = '0.15';
    $dd['fee_int'] = $contract['fee']/100;
    $dd['financ_purpose'] = '融资';
    $dd['use_date'] = date('Y-m-d',strtotime($projectInfo['end_time']));
    $dd['open_branch'] = $financing['bank_code'];
    $dd['withdraw_account'] = $financing['bank_card_no']; //收款银行卡号
    $dd['account_type'] = '2'."";//2企业，1个人
    $dd['payee_name'] = $financing['acct_name'];
    $dd['financ_amt'] = $projectInfo['amount'];

    $arr[] = $dd;

    $req['financing_info_list'] = json_encode($arr);

    if($platcust){
        $compensation = array(
            array(
                'platcust'=>$platcust,
                'type'=>0
            )
        );
        $req['compensation'] = json_encode($compensation);
    }


    $plainText =  \SignUtil::params2PlainText($req);

    $sign =  \SignUtil::sign($plainText);

    $req['signdata'] = \SignUtil::sign($plainText);
        //[{"platcust":"2017072118403082310815220","type":"0"}];
    $fd  = new \FD();

    $ret = $fd->post('/project/publish',$req);


    \Think\Log::write('提交银行返回：'.$ret,'INFO');

    return json_decode($ret,true);
}


function shutdown_function(){
    $e = error_get_last();
    print_r($e);
}
