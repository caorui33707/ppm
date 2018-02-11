<?php
//公共函数
function toDate($time, $format = 'Y-m-d H:i:s') {
    if (empty ( $time )) {
        return '';
    }
    $format = str_replace ( '#', ':', $format );
    return date ($format, $time );
}

/**
 * 获取微妙
 * @return float
 */
function getMillisecond() {
    $time = time();
    list($t1, $t2) = explode(' ', microtime());
    $millisecond = (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    return str_replace($time, '', $millisecond);
}

function getNodeGroupName($id) {
    if (empty ( $id )) {
        return '未分组';
    }
    if (isset ( $_SESSION ['nodeGroupList'] )) {
        return $_SESSION ['nodeGroupList'] [$id];
    }
    $Group = D ( "Group" );
    $list = $Group->getField ( 'id,title' );
    $_SESSION ['nodeGroupList'] = $list;
    $name = $list [$id];
    return $name;
}

function getGroupName($id) {
    if ($id == 0) {
        return '无上级组';
    }
    if ($list = F ( 'groupName' )) {
        return $list [$id];
    }
    $dao = D ( "Role" );
    $list = $dao->select( array ('field' => 'id,name' ) );
    foreach ( $list as $vo ) {
        $nameList [$vo ['id']] = $vo ['name'];
    }
    $name = $nameList [$id];
    F ( 'groupName', $nameList );
    return $name;
}

function pwdHash($password, $type = 'md5') {
    return hash ( $type, $password );
}

/**
 * 生成GUID
 * @return string
 */
function Guid(){
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid = substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
    return $uuid;
}

function status_403(){
    header('HTTP/1.1 403 Forbidden');
    echo "Access forbidden";
    die;
}

function GhostUser($uid = 0){
    $users = array(
        '101'=>array('13600009252','赵丽坤',101),
        '102'=>array('13300006969','芮宇浩',102),
        '103'=>array('13300001518','余栋韬',103),
        '104'=>array('17800001844','杨岚芝',104),
        '105'=>array('15200005715','赵量',105),
        '106'=>array('13000006566','沈立军',106),
        '107'=>array('13400003922','李娜',107),
        '108'=>array('18600008078','陈大鹏',108),
        '109'=>array('18800000708','林轩',109),
        '110'=>array('15800009477','王艳 ',110),
        '111'=>array('18700000140','钱东',111),
        '112'=>array('13500006064','李天涯',112),
        '113'=>array('18200004205','王勇',113),
        '114'=>array('15100009049','朱健康',114),
        '115'=>array('15300002022','郑浩',115),
        '116'=>array('17800001844','廖建东',116),
        '117'=>array('15900003419','陆剑波',117),
        '118'=>array('15200004790','方辉',118),
        '119'=>array('18800003520','方勇',119),
        '120'=>array('18200009291','吴建杰',120),
        '121'=>array('13807228569','饶小梦',121),
        '122'=>array('15874859755','徐晓慧',122),
        '123'=>array('18682000030','沈文',123),
        '124'=>array('13654663321','章立彪',124),
        '125'=>array('13822556877','石磊',125),
        '126'=>array('13847772158','史家聪',126),
        '127'=>array('15162248801','刘宜德 ',127),
        '128'=>array('15177417650','赵嘉琪',128),
        '129'=>array('18200006005','俞晓佳',129),
        '130'=>array('13300001171','韦俐芬',130),
    );
    if($uid == 0){
        return $users;
    } else {
        return $users[$uid];
    }
    
}


/**
 *  根据身份证号码获取性别
 *  @param string $idcard    身份证号码
 *  @return int $sex 性别 1男 2女 0未知
 */
function get_sex($idcard) {
    if(empty($idcard)) return null; 
    $sexint = (int) substr($idcard, 16, 1);
    return $sexint % 2 === 0 ? '女' : '男';
}


/**
 *  根据身份证号码计算年龄
 *  @param string $idcard    身份证号码
 *  @return int $age
 */
function get_age($idcard){  
    if(empty($idcard)) return null; 
    #  获得出生年月日的时间戳 
    $date = strtotime(substr($idcard,6,8));
    #  获得今日的时间戳 
    $today = strtotime('today');
    #  得到两个日期相差的大体年数 
    $diff = floor(($today-$date)/86400/365);
    #  strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比 
    $age = strtotime(substr($idcard,6,8).' +'.$diff.'years')>$today?($diff+1):$diff; 
    return (int)$age; 
} 