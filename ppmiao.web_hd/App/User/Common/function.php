<?php
/**
 * 判断是否是微信客户端访问
 * @return bool
 */
function is_weixin(){
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
        return true;
    }
    return false;
}

/**
 * 判断是否属手机
 * @return bool
 */
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}


function change_money($amount){
    if(is_numeric( $amount )){
        $amount = $amount/10000;
        $amount = '限额'.$amount.'万元';
    }
    return $amount;
}
function bankInfo($data){
    if($data->bankStatus == 2){
        $weihu = "(维护中)";
    }else{
        $weihu = "";
    }
    return $data->bankName."（单笔".$data->limitTimes." 单日".$data->limitDay." 单月".$data->limitMonth."）".$weihu;
}

/**
 * 判断移动设备类型
 * @return string
 */
function get_device_type(){
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    if(strpos($agent, 'iphone') || strpos($agent, 'ipad')){
        $type = 'ios';
    }
    if(strpos($agent, 'android')){
        $type = 'android';
    }
    return $type;
}

/**
 * 隐藏身份证部分号码
 */
function hide_whole_idcard($idcard){
    if(strlen($idcard) >= 16) return substr($idcard, 0, 4).'************'.substr($idcard, strlen($idcard) - 2);
}

/**
 * 隐藏名字部分
 * @param $name
 */
function hide_whole_name($name){
    $len = mb_strlen($name, 'utf-8');
    $result = '';
    if($len > 2){
        $result = mb_substr($name, 0, 1, 'utf-8');
        for($i = 0; $i < $len-2; $i++) $result .= '*';
        $result .= mb_substr($name, $len - 1, 1, 'utf-8');
    }else if($len > 1){
        $result = mb_substr($name, 0, 1, 'utf-8').'*';
    }else{
        $result = '*';
    }
    return $result;
}

function get_idcard_tail_number($idcard){
    if(strlen($idcard) >= 16) return '**** **** **** **** '.substr($idcard, strlen($idcard) - 4);
}

/**
 * 获取银行卡尾号
 * @param $card
 */
function get_card_tail_number($card){
    if(strlen($card) >= 16) return substr($card, strlen($card) - 4);
}





/**
 * 获取银行卡尾号其余拼接*
 * @param $card
 */
function get_card_tail_number_star($card){

    $count = strlen($card)-4;
    $str = '';
    for ($i=0;$i<$count;$i++){
        $str = $str.'*';
    }
    $str = $str.substr($card, -4);
    return $str;
}

/**
 * 隐藏部分手机号码
 */
function hide_whole_phone($mobile){
    if(strlen($mobile) >= 11) return substr($mobile, 0, 3).'****'.substr($mobile, strlen($mobile) - 4);
}

function status_403(){
    header('HTTP/1.1 403 Forbidden');
    echo "Access forbidden";
    die;
}

/**
 * 把对象转成数组
 * @param $e
 * @return array|void
 */
function objectToArray($e){
    $e = (array)$e;
    foreach($e as $k=>$v){
        if( gettype($v)=='resource' ) return;
        if( gettype($v)=='object' || gettype($v)=='array' )
            $e[$k]=(array)objectToArray($v);
    }
    return $e;
}

/**
 * 格式化新版项目内容描述
 */
function format_project_descr($descr){
    $arr = explode('<!--line-->', trim($descr));
    foreach($arr as $key => $val){
        $arr[$key] = explode('<!--title-->', trim($val));
        foreach($arr[$key] as $k => $v){
            $arr[$key][$k] = str_replace("\r\n", "<br>", trim($v));
        }
    }
    return $arr;
}

/**
 * 格式化新版项目图片链接
 * @param $image
 */
function format_project_image($image){
	return explode("\r\n", trim($image));
}

function post($url, $data = array(), $contentType = 'application/x-www-form-urlencoded'){ // 模拟提交数据函数
    $data['deviceType'] = 4;
    $data['versionName'] = '1.4';
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
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
        echo 'Errno:'.curl_error($curl);
    }
    curl_close($curl); // 关键CURL会话
    $data = json_decode($tmpInfo);
    if($data->isEnc == 'Y'){ // 加密数据
        return json_decode(decodeServiceData($data->resText));
    }else{
        return json_decode($data->resText); // 返回数据
    }
}

/**
 * 服务器数据解密处理
 */
function decodeServiceData($encodeData){
    $pwd = C('SERVICE_PASSWORD'); // 服务器密码
    require_once 'include/DES.php';
    $obj = new \JoDES();
    return $obj->decode($encodeData, $pwd);
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
 * AB级基金净值计算公式
 * @param $startNet 母基金起始点净值
 * @param $currentNet 母基金当前净值
 * @param $type AB级类型(DXG:打新股)
 */
function formula_fund_net($startNet, $currentNet, $type = 'A'){
    if($currentNet - $startNet < 0){
        if($type == 'A'){
            return ($currentNet - $startNet)*3 + $startNet;
        }else if($type == 'B'){
            return $startNet;
        }else if($type == 'DXG'){
            return $startNet;
        }
    }else{
        if($type == 'A'){
            return ($currentNet - $startNet)*2.6 + $startNet;
        }else if($type == 'B'){
            return ($currentNet - $startNet)*0.2 + $startNet;
        }else if($type == 'DXG'){
            return $currentNet;
        }
    }
}

/**
 * 昨日涨跌幅
 * @param $yesterdayNet 昨日净值
 * @param $beforeYesterdayNet 前日净值
 */
function formula_fund_yesterday_rose_decline($beforeYesterdayNet, $yesterdayNet){
    return (($yesterdayNet - $beforeYesterdayNet)/$beforeYesterdayNet)*100;
}

/**
 * 期间涨跌值
 * @param $startNet 起始净值
 * @param $yesterdayNet 昨日净值
 * @param $isDXG 是否是打新股(默认false)
 */
function formula_fund_period_rose_decline($startNet, $yesterdayNet, $isDXG = false){
    if(!$isDXG){
        if($yesterdayNet - $startNet >= 0){
            return (($yesterdayNet - $startNet)/$startNet)*100;
        }else{
            return (($yesterdayNet - $startNet)/$startNet)*100;
        }
    }else{
        if(($yesterdayNet - $startNet)/$startNet > 0.18){
            return (0.18 + (($yesterdayNet - $startNet)/$startNet - 0.18)/2)*100;
        }else{
            return (($yesterdayNet - $startNet)/$startNet)*100;
        }
    }
}

/**
 * 期间年化收益率
 * @param $startNet 起始净值
 * @param $yesterdayNet 昨日净值
 * @param $days 期间天数
 */
function formula_fund_period_annualized_rate($startNet, $yesterdayNet, $days){
    return ((($yesterdayNet - $startNet)/$startNet)/$days)*365*100;
}

/**
 * 数据存储方法
 * @param $key
 * @param string $data
 */
function StorageData($key, $data = ''){
    if($data === ''){ // 返回数据
        return $_SESSION[$key];
    }else if($data === null){ // 清除数据
        $_SESSION[$key] = null;
    }else{ // 设置数据
        $_SESSION[$key] = $data;
    }
}

/**
 * 检查用户登录状态
 */
function checkUserLoginStatus($session){
    if(!$session) return false;
    if(!$session['token']) return false;
    if(!$session['verify']) return false;
    if($session['verify'] != md5($session['token'].C('SESSION_KEY'))) return false;//get_client_ip()
    return $session;
}

/**
 * 过滤特殊字符
 */
function filterSpecialChar($str){
    $filter = array("%", "'", "\"", ";", "&", "\\");
    return str_replace($filter, '', $str);
}

/**
 * 功能：对字符串进行加密处理
 * @param $str 需要加密的内容
 * @param $key 密钥
 * @return string
 */
function st_encrypt($str,$key){ //加密函数
    srand((double)microtime() * 1000000);
    $encrypt_key=md5(rand(0, 32000));
    $ctr=0;
    $tmp='';
    for($i=0;$i<strlen($str);$i++){
        $ctr=$ctr==strlen($encrypt_key)?0:$ctr;
        $tmp.=$encrypt_key[$ctr].($str[$i] ^ $encrypt_key[$ctr++]);
    }
    $str = base64_encode(passport_key($tmp,$key));
    // 转义反斜杠
    $str = str_replace('/', '@@', $str);
    return $str;
}

/**
 * 功能：对字符串进行解密处理
 * @param $str 需要解密的密文
 * @param $key 密钥
 * @return string
 */
function st_decrypt($str,$key){
    // 把反斜杠回复
    $str = str_replace('@@', '/', $str);
    $str=passport_key(base64_decode($str),$key);
    $tmp='';
    for($i=0;$i<strlen($str);$i++){
        $md5=$str[$i];
        $tmp.=$str[++$i] ^ $md5;
    }
    return $tmp;
}

/**
 * 辅助函数
 * @param $str
 * @param $encrypt_key
 * @return string
 */
function passport_key($str,$encrypt_key){
    $encrypt_key=md5($encrypt_key);
    $ctr=0;
    $tmp='';
    for($i=0;$i<strlen($str);$i++){
        $ctr=$ctr==strlen($encrypt_key)?0:$ctr;
        $tmp.=$str[$i] ^ $encrypt_key[$ctr++];
    }
    return $tmp;
}

function createSMSCode($length = 4){
    $min = pow(10 , ($length - 1));
    $max = pow(10, $length) - 1;
    return rand($min, $max);
}

/**
 * 通过CURL发送HTTP请求
 * @param string $url  //请求URL
 * @param array $postFields //请求参数
 * @return mixed
 */
function curlPost($url,$postFields){
    $postFields = http_build_query($postFields);
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
    $result = curl_exec ( $ch );
    curl_close ( $ch );
    return $result;
}

function is_idcard( $id )
{
    $id = strtoupper($id);
    $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if(!preg_match($regx, $id))
    {
        return FALSE;
    }
    if(15==strlen($id)) //检查15位
    {
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
        if(!strtotime($dtm_birth))
        {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    else      //检查18位
    {
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
        if(!strtotime($dtm_birth)) //检查生日日期是否正确
        {
            return FALSE;
        }
        else
        {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign = 0;
            for ( $i = 0; $i < 17; $i++ )
            {
                $b = (int) $id{$i};
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id,17, 1))
            {
                return FALSE;
            } //phpfensi.com
            else
            {
                return TRUE;
            }
        }
    }

}
function getTime(){
    $h = date('H');
    if($h >=5 && $h <=12){
        return '上午';
    }elseif($h >=12 && $h <=18){
        return '下午';
    }else{
        return '晚上';
    }
}

function tagToType($apply_tag){
    $tags = [0=>'普通',1=>'新手', 2=>'爆款',6=>'活动',8=>'个人专享'];
    $new_tag=[];
    foreach (explode(':',$apply_tag) as $key => $val){
        $new_tag[] = $tags[$val];
    }
    $string = implode('、',$new_tag);

    return "适用标签：“"."$string"."”";
}
