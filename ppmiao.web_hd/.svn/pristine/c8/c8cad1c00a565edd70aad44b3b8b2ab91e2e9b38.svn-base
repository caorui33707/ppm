<?php
return array(
    'MOBILE_SITE' => 'http://wap.piaopiaomiao.com',
    'WEB_ROOT' => 'http://testing.ppmiao.com',
    'STATIC_ROOT' => 'http://testing.ppmiao.com/Public',
    'COMPANY_NAME' => '杭州票票喵信息科技有限公司', // 企业名称
    'IMG_ROOT'=>'https://image.ppmiao.com/Public',
    'NET_SAFE' => '', // 网安号
    'ICP' => '浙ICP备15009112号', // IPC号
    'META_AUTHOR' => '票票喵', // meta作者
    'OTHER_URL' => array('www.ppmiao.cn', 'ppmiao.com.cn', 'www.ppmiao.com.cn', 'ppmiao.cn'), // 从其他URL跳转过来
    'SHORT_URL' => array('ppmiao.cn','ppmiao.com.cn','www.ppmiao.cn'), // 短域名
    'CheckCode' => '0f9CqmWMHGhyRUjD', // 校验码
    'DEVICE_ACTIVATION' => 380283, // app设备激活数
    'SERVICE_PASSWORD' => '5Df8$&@S', // 服务器秘钥
    'SESSION_KEY' => 'mL~aF37y0zB(i@wv', // Session秘钥
    'VCODE_KEY' => '4UD1]W3u', // 验证码秘钥
    'PRODUCT_KEY' => 'mvi^@)m^+-vJ%fts', // 产品详细秘钥

    'ONLINE_SESSION' => '__user', // 用户登录session
    'LLPAY_SESSION' => '__llpay', // 连连支付session
    'SMS_SESSION' => '__sms_login', // 短信session

    'APP_DOWNLOAD_ANDROID' => 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.ppmiao.app&g_f=991653', // 安卓app下载地址
    'APP_DOWNLOAD_IOS' => 'https://itunes.apple.com/cn/app/id1234916517?mt=8', // 苹果app下载地址
    'APP_DOWNLOAD_MICRO' => 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.ppmiao.app&g_f=991653', // 微下载链接(会自动根据手机类型识别下载)
    'APP_DOWNLOAD_WEIXIN' => 'http://a.app.qq.com/o/simple.jsp?pkgname=cn.ppmiao.app', // 微信下载链接

	//'配置项'=>'配置值'
	'URL_MODEL' => 1, // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',            // 数据库类型
    'DB_HOST'               =>  'rm-uf6s86ucfa1mvy1m8.mysql.rds.aliyuncs.com',    // 服务器地址(内网)
    'DB_NAME'               =>  'ppmiao_test',            // 数据库名
    'DB_USER'               =>  'ppmiao',             // 用户名
    'DB_PWD'                =>  'PPmiao1234',             // 密码
    'DB_PORT'               =>  '3306',             // 端口
    'DB_PREFIX'             =>  's_',               // 数据库表前缀
    // 错误/正确信息显示模板
    'TMPL_ACTION_ERROR'     =>  'PublicNew:dispatch_jump_web',
    'TMPL_ACTION_SUCCESS'     =>  'PublicNew:dispatch_jump_web',

    // 服务器api接口
    'API' => 'http://rest.stlc.cn:8988/stone-rest-v2/rest/',
    'llpay_notify_url' => 'http://rest.stlc.cn:8988/stone-rest-v2/payment/notify/payNotify.htm', // 连连支付异步结果通知url
    'wallet_notify_url' => 'http://rest.stlc.cn:8988/stone-rest-v2/payment/notify/walletRechargeNotify.htm', // 钱包支付通知url
    'interface' => array(
        'recommend' => 'project/queryRecommendProject.json', // 首页推荐信息接口
        'recommend2' => 'project/queryRecommendProjectV2.json', // 首页精品推荐(新版)
        'recommend3' => 'project/queryRecommendProjectV3.json', // 首页精品推荐(新版)
        'project_detail' => 'project/detail.json',  // 产品详细信息接口
        'project_detailV2' => 'project/detailV2.json',  // 产品详细信息接口(V2)
        'project_invest' => 'project/invest.json', // 购买产品提交接口
        'project_investV2' => 'project/investV2.json', // 购买产品提交接口(V2)
        'project_list' => 'project/queryInProgressProject.json', // 产品列表接口
        'project_list2' => 'project/queryInProgressProjectV2.json', // 产品列表接口
        'project_more' => 'project/moreProject.json', // 更多列表(termType 先传空/status 状态(2:在售/3:售罄)/pageNo)
        'user_center' => 'user/queryInvestDetail.json', // 个人中心接口(pageNo,token)
        'user_login' => 'user/login.json', // 用户登录接口
        'login_sms' => 'user/getSmsCode.json', // 用户登录发送短信接口
        'user_pdetail' => 'user/dueDetail.json', // 已购买产品详细接口
        'user_logout' => 'user/logout.json', // 用户注销接口
        'user_bank' => 'user/queryBindBankCard.json', // 用户绑定银行卡接口
        'user_payway_list' => 'user/payWayList.json', // 用户绑定银行卡接口(包含钱包)(projectId,token)
        'message_list' => 'message/getMessage.json', // 消息列表接口
        'message_detail' => 'message/detail.json', // 消息详细接口
        'message_comment' => 'user/mComment.json', // 发表评论接口
        'comment_list' => 'user/commentList.json', // 评论列表接口
        'user_info' => 'user/userInfo.json', // 用户信息接口
        'user_bindbank' => 'user/bindBranchBankName.json', // 完善银行卡信息接口(bankCardNo,area,bankAddress,areaId)
        'user_bank_detail' => 'user/queryBankCardDetail.json', // 用户银行卡详细信息接口(id)
        'user_redpoint' => 'user/redPoint.json', // 用户是否有待处理事件接口
        'pay_success' => 'user/paySuccess.json', // 客户端支付成功通知服务器接口(userId,rechargeNo,tradeNo)
        'subscribe_project' => 'user/subscribeProject.json', // 订阅爆款接口(projectId)
        'cwsp_remind' => 'user/cwspRemind.json', // 查询用户是否有订阅标的到期提醒接口(projectId)
        'fund_worth' => 'user/fundWorthLine.json', // 基金数据查询接口(token,fundId:基金ID,month:比如查3个月数据传3)
        'user_due_detailV2' => 'user/dueDetailV2.json', // 用户产品已购买详情页面接口(token,investDetailId)
        'user_message' => 'user/personalMessage.json', // 用户个人消息接口
        'user_message_detail' => 'user/personalMessageDetail.json', // 用户个人消息详细接口(标记个人消息已读接口)
        'user_wallet_recharge' => 'user/walletRecharge.json', // 钱包充值接口
        'user_wallet_withdrawal' => 'user/walletWithdrawal.json', // 钱包提现接口(token,amount,bankId)
    ),
    // 连连支付配置
    'llpay_config' => array(
        'oid_partner' => '201503261000259502', //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
        'key' => 'STLC-ZJYT', //安全检验码，以数字和字母组成的字符
        //'oid_partner' => '201408071000001543',
        //'key' => '201408071000001543test_20140812',
        'version' => '1.0',
        'sign_type' => strtoupper('MD5'), //签名方式 不需修改
        'valid_order' => '10', //订单有效时间  分钟为单位，默认为10080分钟（7天）
        'input_charset' => strtolower('utf-8'), //字符编码格式 目前支持 gbk 或 utf-8
        'transport' => 'http', //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'userreq_ip' => '', // 防钓鱼ip 可不传或者传下滑线格式
        'id_type' => 0, // 证件类型
    ),
    
    'SMS_NAME' =>'ppm_sms',
    'SMS_PWD'  =>'fuqianwangluo12_',
    'SMS_URL'  =>'http://222.73.117.156/msg/HttpBatchSendSM',
    
    'upload_config' => array(
        'maxSize'    =>    3145728,
        'rootPath'   =>    '../newppmiao.web/Uploads/company/',
        'savePath'   =>    '',
        'saveName'   =>    array('uniqid',''),
        'exts'       =>    array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
        'autoSub'    =>    true,
        'subName'    =>    array('date','Ymd'),
    ),
    'PAY_URL' => 'http://120.55.243.86:8089/recharge/lianlian',//http://120.55.243.86:8089/recharge',//http://114.55.85.42:8089/recharge',
);
