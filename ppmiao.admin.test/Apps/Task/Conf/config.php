<?php
$config =  array(
    //'配置项'=>'配置值'
    'SMS_INTDERFACE' => array( // 短信发送接口配置
        'ip' => '222.73.117.158',
        'port' => '80',
        'account' => 'stlc_sms', // 账号
        'pswd' => 'Txb123456', // 密码
    ),

//    //测试
//    'AppStore_pushtest' => array(
//        'APP_KEY' =>'2007b0ac3d9c009848b3fc37',
//        'MASTER_SECRET' =>'68caf8516cf29b45cddc91ea',
//    ),
);


return array_merge(include APP_PATH.'Admin/Conf/config.php',$config);