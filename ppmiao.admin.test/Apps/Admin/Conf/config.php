<?php
return array(
    'LIMIT_IP' => false, // 是否限制IP
    'EXCLUDE_AREA' => '浙江省', // 排除区域
    'S_THEME' => 'altair_', // 模板名称
    'ALLOWED_IP' => array(), // 允许访问后台IP地址
    'ADMIN_SECRET_KEY' => 'JnpTTXYFuJgytSU26i2YAAbfZ6irRgAB', // 后台管理员验证密钥
    'ALLOW_VERIFY' => false, // 是否开启验证码
    'LASTEST_REPAY_DAYS' => 7, // 获取最新付息列表获取周期天数
    'SMS_INTDERFACE' => array( // 短信发送接口配置
        'ip' => '222.73.117.156',
        'port' => '80',
        'account' => 'ppm_sms', // 账号
        'pswd' => 'fuqianwangluo12_', // 密码
    ),
    
    'GHOST_ACCOUNT'=>true,//是否显示幽灵账购买记录 ture显示
	//'配置项'=>'配置值'
    'SHOW_PAGE_TRACE' => false,
    'URL_MODEL' => 1, // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'URL_HTML_SUFFIX' => '',
    'APP_AUTOLOAD_PATH'         =>  '@.TagLib',
    'USER_AUTH_ON'              =>  true,
    'USER_AUTH_TYPE'			=>  2,		// 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'             =>  'authId',	// 用户认证SESSION标记
    'ADMIN_AUTH_KEY'			=>  'administrator',
    'USER_AUTH_MODEL'           =>  'AdminUser',	// 默认验证数据表模型
    'AUTH_PWD_ENCODER'          =>  'md5',	// 用户认证密码加密方式
    'USER_AUTH_GATEWAY'         =>  '/Public/login',// 默认认证网关
    'NOT_AUTH_MODULE'           =>  'Public',	// 默认无需认证模块
    'REQUIRE_AUTH_MODULE'       =>  '',		// 默认需要认证模块
    'NOT_AUTH_ACTION'           =>  '',		// 默认无需认证操作
    'REQUIRE_AUTH_ACTION'       =>  '',		// 默认需要认证操作
    'GUEST_AUTH_ON'             =>  false,    // 是否开启游客授权访问
    'GUEST_AUTH_ID'             =>  0,        // 游客的用户ID
    'DB_LIKE_FIELDS'            =>  'title|remark',
    'RBAC_ROLE_TABLE'           =>  's_admin_role',
    'RBAC_USER_TABLE'           =>  's_admin_role_user',
    'RBAC_ACCESS_TABLE'         =>  's_admin_access',
    'RBAC_NODE_TABLE'           =>  's_admin_node',
    'TMPL_ACTION_ERROR'         =>  'Public:success', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'       =>  'Public:success', // 默认成功跳转对应的模板文件
    'DEFAULT_CONTROLLER'        =>  'Public',
    'DEFAULT_ACTION'            =>  'login',
    
    'UPLOAD_PATH' => 'e:/testing/Uploads/focus/',    //../web/Uploads/focus/
    
    'API_PUSH_URL' => 'http://120.55.243.86:8087/jpush',//'http://114.55.85.42:8087/jpush',
    
    'accessKeyId'       => 'LTAIyjZrdfEy1cqh',
    'accessKeySecret'   => 'uPEn87WaV2O0tOnscAwCqm7iTUdxip',
    'endpoint'          => 'http://oss-cn-hangzhou.aliyuncs.com',
    'bucketName'        => 'ppmiao-image-test', //ppmiao-image
    'localPath'         => 'e:/testing/Uploads/focus/',
   // 'localPath'         => '/mnt/php/testing/Uploads/focus/',
    
    
    // 连连支付配置
    'llpay_config' => array(
        'oid_partner' => '201503261000259502', //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
        'key' => 'STLC-ZJYT', //安全检验码，以数字和字母组成的字符
        'version' => '1.2',
        'id_type' => '0', //证件类型
        'sign_type' => strtoupper('MD5'), //签名方式 不需修改
        'valid_order' => '10080', //订单有效时间  分钟为单位，默认为10080分钟（7天）
        'input_charset' => strtolower('utf-8'), //字符编码格式 目前支持 gbk 或 utf-8
        'transport' => 'https', //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    ),
    // 盛付通配置
    'sftpay_config' => array(
        'customer_no' => '451776', // 商户号
        'key' => 'pvv1bdc7edbg3vq0', //安全检验码，以数字和字母组成的字符
        'charset' => 'utf-8',
        'sign_type' => 'MD5',
    ),
    
    //测试
    'AppStore_pushtest' => array(
        'APP_KEY' =>'2007b0ac3d9c009848b3fc37',
        'MASTER_SECRET' =>'68caf8516cf29b45cddc91ea',
    ),

    /*
    //AppStore_personal
    'AppStore_personal_JPUSH'=>array(
        'APP_KEY' => 'ab9e001f75aeb9023313de47',
        'MASTER_SECRET' => '6f6c814faf20bac19a59c7dd',
    ),
    
    //AppStore_pro
    'AppStore_pro_JPUSH'=>array(
        'APP_KEY' => '03860366a689b1c059a4ddf5',
        'MASTER_SECRET' => '5564e80ca60837c06fe88d33',
    ),
    
    // 至尊版
    'AppStore_honorable' => array(
        'APP_KEY' => '73f11338ac8c9d2aaf4aa5f7',
        'MASTER_SECRET' => '55472771604d3010a49a340c'
    ),
    
    // 回馈版
    'AppStore_feedback' => array(
        'APP_KEY' => '60931129a74436e95cb56f69',
        'MASTER_SECRET' => 'e461dce5565007e55570b0e8'
    ),
    //新增福利版
    'AppStore_welfare' => array(
        'APP_KEY' => '96d2f0b88a2ba349f52752f7',
        'MASTER_SECRET' => '521de5c6f56ea4f410037ed7'
    ),
    
    //票票喵  新增旗舰版
    'AppStore_flagship' => array(
        'APP_KEY' => '5d306af5d7b4304fb6ed46e9',
        'MASTER_SECRET' => 'e65e055d0291bf9f2bfe3bfa'
    ),
    
    'AppStore_Anniversary'=>array(
        'APP_KEY' => '6eda39d4b8f109ab78084e8d',
        'MASTER_SECRET' => '7d992869a0e7995ff2d2c21e'
    ),
    
    'AppStore_Professional'=>array(
        'APP_KEY' => '00fcbf1fbc1bc0930a7319cc',
        'MASTER_SECRET' => '93b345faefdc0f1a28c3bd90'
    ),
    
    'AppStore_VIP'=>array(
        'APP_KEY' => '90e232069e18d61394825b95',
        'MASTER_SECRET' => '653f6ad4dd8323b79d92a0d6'
    ),
    
    'AppStore_classical'=>array(
        'APP_KEY' => 'b0b8a97c5a681bd6670576b6',
        'MASTER_SECRET' => 'f0caa461fa7c24cac737ac9e'
    ),
    
    'AppStore_Financing'=>array(
        'APP_KEY' => '014f5585ab5dd563b7d66278',
        'MASTER_SECRET' => '029aa14dc5d7c1aba6147705'
    ),
    
    'AppStore_Investment'=>array(
        'APP_KEY' => 'a67e6c00af6e848463c849f6',
        'MASTER_SECRET' => 'efc9df837177fab3b1a2f'
    ),
    
    'AppStore_qqjrFinance'=>array(
        'APP_KEY' => '21eae0b661d0ad20d9ddd4c8',
        'MASTER_SECRET' => '2dee05a994dc0511709c7444'
    ),
    
    'AppStore_qqjrSpecial'=>array(
        'APP_KEY' => '99789f080effaa3d93327579',
        'MASTER_SECRET' => '8f5a177a936cf952b13a638b'
    ),
    
    'AppStore_PPMFeedback'=>array(
        'APP_KEY' => '049b7cfcd2b261290e3ccabd',
        'MASTER_SECRET' => 'd19b302f58a4c632e4737495'
    ),
    
    'AppStore_PPMLuxury'=>array(
        'APP_KEY' => '048e4e6e1576c36c91442271',
        'MASTER_SECRET' => 'ca1256130282e5d37f8017a0'
    ),
    
    'AppStore_qqjwFinance'=>array(
        'APP_KEY' => '0f3c33aee29d570e80eafb46',
        'MASTER_SECRET' => 'd1da4c1f7313b012c334f64f'
    ),
    
    'AppStore_miaodppmFinancial'=>array(
        'APP_KEY' => '11db9dfd19dc7cd9ce4b519c',
        'MASTER_SECRET' => '1e9355c3e0c1e69707044bc8'
    ),
    
    */
           
    //Auth权限设置
    'AUTH_CONFIG' => array(
        'AUTH_ON' => true,  // 认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP' => 's_auth_group', // 用户组数据表名
        'AUTH_GROUP_ACCESS' => 's_auth_group_access', // 用户-用户组关系表
        'AUTH_RULE' => 's_auth_rule', // 权限规则表
        'AUTH_USER' => 's_member', // 用户信息表
    ),
    'ACCESS_ARRAY' => array( // 后台权限配置
        array(
            'name' => 'project',
            'title' => '产品管理',
            'icon' => 'zq',
            'sub' => array(
                array('title'=>'产品管理','url'=>'project/index','show'=>1),
                array('title'=>'添加产品','url'=>'project/add','show'=>1),
                array('title'=>'产品列表分组','url'=>'projectGroupTag/index','show'=>1),
                array('title'=>'产品类型管理','url'=>'project/project_type','show'=>1),
                array('title'=>'产品分组管理','url'=>'project/project_group','show'=>1),
                array('title'=>'财务审核','url'=>'interest/repay_review','show'=>1),
                array('title'=>'付息列表','url'=>'interest/lastestpay','show'=>1),
                array('title'=>'日销售额(汇总)','url'=>'project/daysales','show'=>1),
                array('title'=>'日销售额(明细)','url'=>'project/daysaledetails','show'=>1),                
                array('title'=>'银行卡管理','url'=>'project/bank','show'=>1),
                //array('title'=>'银行限额管理','url'=>'bank/bank_limit','show'=>1),
                //array('title'=>'银行提现限额管理','url'=>'bank/bank_pay_way','show'=>1),
                array('title'=>'幽灵账户','url'=>'project/ghostaccount','show'=>1),
                array('title'=>'每日还本付息','url'=>'project/project_repay','show'=>1),
                array('title'=>'预设协议利率','url'=>'project/project_protocol_rate','show'=>1),
                array('title'=>'融资方管理','url'=>'project/financing','show'=>1),
                array('title'=>'担保方管理','url'=>'guaranty/list','show'=>1),
                array('title'=>'平台时实存量','url'=>'project/platform_stock','show'=>1),
                array('title'=>'出账记录','url'=>'project/chargeoff_log','show'=>1),
                array('title'=>'标的成废记录','url'=>'project/establish_log','show'=>1),
                array('title'=>'平台余额','url'=>'wallet/stockList','show'=>1),
                array('title'=>'退汇管理','url'=>'project/refund_notify_index','show'=>1),
                array('title'=>'掉单管理','url'=>'order/depository_index','show'=>1),
            ),
        ),
        /*
        array(
            'name' => 'wallet',
            'title' => '钱包管理',
            'icon' => 'wallet',
            'sub' => array(
				array('title'=>'转入/转出(导)','url'=>'wallet/import_export_list','show'=>1),
                array('title'=>'转入/转出(查)','url'=>'wallet/deposit','show'=>1),
                array('title'=>'转入/转出(审)','url'=>'wallet/deposit_do','show'=>1),
                array('title'=>'用户提现','url'=>'wallet/takeout','show'=>1),
                array('title'=>'计息列表','url'=>'wallet/interest','show'=>1),
                array('title'=>'钱包计息异常','url'=>'wallet/err_interest','show'=>1),
                array('title'=>'每日流水','url'=>'wallet/dayflow','show'=>1),
                array('title'=>'投资记录','url'=>'wallet/investment','show'=>1),
				array('title'=>'预设年化利率','url'=>'wallet/setrate','show'=>1),
                array('title'=>'钱包利息表','url'=>'wallet/timeperiod_wallet_interest','show'=>1),
                array('title'=>'每日应付利息','url'=>'wallet/earnings','show'=>1),
                array('title'=>'钱包每天存量','url'=>'wallet/stockList','show'=>1),
            ),
        ),*/
        array(
            'name' => 'contract',
            'title' => '合同管理',
            'icon' => 'contract',
            'sub' => array(
                array('title'=>'合同列表','url'=>'contract/index','show'=>1),
            ),
        ),
        
        array(
            'name' => 'DynamicRate',
            'title' => '浮动加息管理',
            'icon' => 'zq',
            'sub' => array(
                array('title'=>'产品统计','url'=>'DynamicRate/project_statistics','show'=>1),
                array('title'=>'每日数据','url'=>'DynamicRate/daily_statistics','show'=>1),
                array('title'=>'月月加息配置','url'=>'DynamicRate/index','show'=>1),
            ),
        ),
        
        
      
        /*
        array(
            'name' => 'finance',
            'title' => '财务报表',
            'icon' => 'finance',
            'sub' => array(
                array('title'=>'融资人付款明细','url'=>'finance/FinancingPayment','show'=>1),
                array('title'=>'项目下发管理','url'=>'finance/issued','show'=>1),
                array('title'=>'结构化公募基金','url'=>'finance/fund','show'=>1),
            ),
        ),*/
        array(
            'name' => 'message',
            'title' => '消息管理',
            'icon' => 'message',
            'sub' => array(
                array('title'=>'消息管理','url'=>'message/index','show'=>1),
                array('title'=>'发布消息','url'=>'message/add','show'=>1),
                array('title'=>'评论列表','url'=>'message/comment','show'=>1),
                array('title'=>'推送消息','url'=>'message/push','show'=>1),
                array('title'=>'发送短信','url'=>'message/send_sms','show'=>1),
                array('title'=>'个人消息','url'=>'message/personal','show'=>1),
                array('title'=>'意见建议','url'=>'message/suggest','show'=>1),
                array('title'=>'推送列表','url'=>'message/msg_push_index','show'=>1),
                array('title'=>'推送','url'=>'message/msg_push_add','show'=>1),
                
            ),
        ),
        array(
            'name' => 'task',
            'title' => '任务管理',
            'icon' => 'task',
            'sub' => array(
                array('title'=>'任务列表','url'=>'task/index','show'=>1),
            ),
        ),
        array(
            'name' => 'statistics',
            'title' => '统计分析',
            'icon' => 'statistics',
            'sub' => array(
                array('title'=>'渠道管理','url'=>'statistics/channel','show'=>1),
                array('title'=>'渠道统计','url'=>'statistics/channel_statistics','show'=>1),

                array('title'=>'用户统计(旧)','url'=>'statistics/recharge','show'=>1),
                array('title'=>'用户统计(新)','url'=>'statistics/recharge_new','show'=>1),

                array('title'=>'每日统计(旧)','url'=>'statistics/daily_statistics_old','show'=>1),
                array('title'=>'每日统计(新)','url'=>'statistics/daily_statistics','show'=>1),

                array('title'=>'还款数据','url'=>'statistics/repayment_data','show'=>1),
                
                array('title'=>'已还款列表','url'=>'statistics/repayment_list','show'=>1),
                array('title'=>'待还款列表','url'=>'statistics/wait_repayment_list','show'=>1),
                
                array('title'=>'钱包统计','url'=>'statistics/wallet_data','show'=>1),
                array('title'=>'销售图表','url'=>'statistics/sales_figures','show'=>1),
                array('title'=>'收益统计','url'=>'statistics/profit_statistics','show'=>1),
                array('title'=>'用户查询','url'=>'statistics/user_search','show'=>1),
				array('title'=>'每日数据','url'=>'statistics/statistics_daily_data','show'=>1),
                array('title'=>'支付渠道统计','url'=>'statistics/statistics_payment_channel','show'=>1),
                array('title'=>'销售额(支付渠道)','url'=>'statistics/sales_channel','show'=>1),
                array('title'=>'定期存量(图表)','url'=>'statistics/statistics_scalar_money','show'=>1),//平台存量
                array('title'=>'定期存量(列表)','url'=>'statistics/inventory_list','show'=>1),//平台存量
                array('title'=>'总销售额','url'=>'statistics/statistics_platform_sales','show'=>1),
                                
                array('title'=>'渠道存量查询','url'=>'statistics/custom_channel_query','show'=>1),
            ),
        ),
        array(
            'name' => 'tracking',
            'title' => '行为跟踪',
            'icon' => 'tracking',
            'sub' => array(
                array('title'=>'行为跟踪','url'=>'tracking/index','show'=>1),
                array('title'=>'录入用户跟踪类型','url'=>'tracking/add','show'=>1),
                array('title'=>'查询用户跟踪内容','url'=>'tracking/search','show'=>1),
                array('title'=>'负责人跟踪内容','url'=>'tracking/queryuser','show'=>1),
            ),
        ),
        array(
            'name' => 'activities',
            'title' => '活动管理',
            'icon' => 'activities',
            'sub' => array(
                array('title'=>'首次购买返现','url'=>'activities/newmoney','show'=>0),
                array('title'=>'新手标购买记录','url'=>'activities/newman_record','show'=>0),
                array('title'=>'爆款标购买记录','url'=>'activities/bk_record','show'=>0),
                array('title'=>'赢收益,送豪礼','url'=>'activities/ysyshl','show'=>0),
				array('title'=>'活动送现金券','url'=>'activities/red_envelope','show'=>0),
                array('title'=>'A轮融资回馈用户','url'=>'activities/financing_activity','show'=>0),
                array('title'=>'活动配置','url'=>'event/event_conf_index','show'=>1),
                array('title'=>'返券活动配置','url'=>'activity/index','show'=>1),
                array('title'=>'返券活动奖励','url'=>'activityAward/index','show'=>1),
                array('title'=>'抽奖活动配置','url'=>'lottery/lottery_base_index','show'=>1),
                array('title'=>'抽奖活动日志','url'=>'lottery/lottery_log','show'=>1),
                array('title'=>'会员等级配置','url'=>'vip/level_index','show'=>1),
                array('title'=>'会员积分兑换','url'=>'exchange/index','show'=>1),
                array('title'=>'会员领取奖励','url'=>'vipWeeklyAward/index','show'=>1),
                array('title'=>'任务图标配置','url'=>'vip/mission_type_index','show'=>1),
                array('title'=>'签到奖励配置','url'=>'signIn/index','show'=>1),
                array('title'=>'新手活动标数据统计','url'=>'hd/event20170407','show'=>1),
                array('title'=>'好友邀请活动(客服)','url'=>'hd/inviteList','show'=>1),
                array('title'=>'好友邀请活动(运营)','url'=>'hd/yy_inviteList','show'=>1),
                array('title'=>'好友邀请数据复盘','url'=>'hd/invite_report','show'=>1),
                
            ),
        ),
        array(
            'name' => 'advertisement',
            'title' => '广告管理',
            'icon' => 'advertisement',
            'sub' => array(
                array('title'=>'广告列表','url'=>'advertisement/index','show'=>1),
                array('title'=>'添加广告','url'=>'advertisement/add','show'=>1),
                array('title'=>'弹窗列表','url'=>'advPopup/index','show'=>1),
                array('title'=>'悬浮ICON','url'=>'advSuspend/index','show'=>1),

                array('title'=>'导航图标管理','url'=>'advIcon/index','show'=>1),

                array('title'=>'首页公告','url'=>'noticet/index','show'=>1),               
                array('title'=>'产品公告列表','url'=>'advertisement/project_notic_index','show'=>1),
                array('title'=>'公告标签管理','url'=>'advertisement/project_notic_tag_index','show'=>1),//
                array('title'=>'启动页列表','url'=>'advertisement/boot_page_index','show'=>1),
                array('title'=>'提现说明','url'=>'advertisement/withdrawals_explain_index','show'=>1),
            ),
        ),
        array(
            'name' => 'version',
            'title' => '版本管理',
            'icon' => 'version',
            'sub' => array(
                array('title'=>'版本更新','url'=>'version/upgrade','show'=>1),
                array('title'=>'Android历史版本','url'=>'version/android','show'=>1),
                array('title'=>'IOS历史版本','url'=>'version/ios','show'=>1),
                array('title'=>'版本号统计','url'=>'version/stat','show'=>1),
                array('title'=>'ios上线配置','url'=>'version/ios_upgrade','show'=>1),
            ),
        ),
        array(
            'name' => 'log',
            'title' => '日志管理',
            'icon' => 'log',
            'sub' => array(
                array('title'=>'登录日志','url'=>'log/login','show'=>1),
                array('title'=>'操作日志','url'=>'log/operation','show'=>0),
                array('title'=>'订单异常记录','url'=>'rechargeLog/exception_list','show'=>1),
            ),
        ),
        array(
            'name' => 'auth',
            'title' => '权限管理',
            'icon' => 'auth',
            'sub' => array(
                array('title'=>'管理员管理','url'=>'auth/member','show'=>1),
                array('title'=>'权限组管理','url'=>'auth/group','show'=>1),
            ),
        ),
        array(
            'name' => 'redenvelope',
            'title' => '红包管理',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'红包发放','url'=>'redenvelope/add','show'=>1),
                array('title'=>'批量指定发放','url'=>'redenvelope/addBatch','show'=>1),
                array('title'=>'红包数据管理','url'=>'redenvelope/index','show'=>1),   
                array('title'=>'红包发放记录','url'=>'redenvelope/packetlist','show'=>1),
                array('title'=>'红包每日数据','url'=>'redenvelope/day_packetlist','show'=>1),
                array('title'=>'红包核销','url'=>'redenvelope/cancel_out_index','show'=>1),
            ),
        ),
        array(
            'name' => 'InterestCoupon',
            'title' => '券包管理',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'券包发放','url'=>'InterestCoupon/add','show'=>1),
                array('title'=>'批量指定发放','url'=>'InterestCoupon/addBatch','show'=>1),
                array('title'=>'券包数据管理','url'=>'InterestCoupon/index','show'=>1),
                array('title'=>'券包发放记录','url'=>'InterestCoupon/history_index','show'=>1),
                array('title'=>'券包每日数据','url'=>'InterestCoupon/day_index','show'=>1),
                array('title'=>'券包核销','url'=>'InterestCoupon/cancel_out_index','show'=>1),
            ),
        ),

        array(
            'name' => 'CashCoupon',
            'title' => '奖励管理',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'现金券发放','url'=>'CashCoupon/add','show'=>1),
                array('title'=>'现金券审核','url'=>'CashCoupon/check_index','show'=>1),
                array('title'=>'现金券发放记录','url'=>'CashCoupon/cash_index','show'=>1),
                array('title'=>'推荐发放记录','url'=>'CashCoupon/invite_index','show'=>1),
            ),
        ),

        array(
            'name' => 'UserCategory',
            'title' => '用户分层',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'月统计','url'=>'UserCategory/monthly','show'=>1),
                array('title'=>'日统计','url'=>'UserCategory/daily','show'=>1),
            ),
        ),
        
        array(
            'name' => 'Share',
            'title' => '分享管理',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'分享列表','url'=>'Share/index','show'=>1),
            ),
        ),
        /*
        array(
            'name' => 'UserDeposit',
            'title' => '存管账户',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'充值提现日志','url'=>'UserDeposit/orderLog','show'=>1),
            ),
        ),*/

        array(
            'name' => 'Member',
            'title' => '会员体系',
            'icon' => 'user',
            'sub' => array(
                array('title'=>'VIP配置','url'=>'Member/index','show'=>1),
                array('title'=>'VIP积分加倍','url'=>'Member/points','show'=>1),
                array('title'=>'特权管理','url'=>'Member/thrones','show'=>1),
                array('title'=>'生日礼包','url'=>'Member/birthday','show'=>1),
                array('title'=>'每月福利','url'=>'Member/monthly','show'=>1),
                array('title'=>'积分任务','url'=>'Member/tasks','show'=>1),
                array('title'=>'票票商城管理','url'=>'Member/store','show'=>1),
                array('title'=>'Banner管理','url'=>'Member/banners','show'=>1),
                array('title'=>'商品管理','url'=>'Member/commodities','show'=>1),
            ),
        ),
        array(
            'name' => 'FundAccount',
            'title' => '平台存管账户',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'充值提现日志','url'=>'UserDeposit/orderLog','show'=>1),
                array('title'=>'账户总览','url'=>'FundAccount/index','show'=>1),
                array('title'=>'充值','url'=>'FundAccount/recharge','show'=>1),
                array('title'=>'转账','url'=>'FundAccount/transfer','show'=>1),
                array('title'=>'自有子账户','url'=>'FundAccount/detail?type=1','show'=>1),
                array('title'=>'抵用金子账户','url'=>'FundAccount/detail?type=5','show'=>1),
//                array('title'=>'加息金子账户','url'=>'FundAccount/detail?type=6','show'=>1),
                array('title'=>'奖励金子账户','url'=>'FundAccount/detail?type=9','show'=>1),
            ),
        ),

        array(
            'name' => 'UserFund',
            'title' => '存管查询',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'存管查询','url'=>'UserFund/index','show'=>1),
            ),
        ),
        array(
            'name' => 'Liquidate',
            'title' => '存管对账',
            'icon' => 'wallet',
            'sub' => array(
                array('title'=>'充值资金对账','url'=>'Liquidate/fundData','show'=>1),
                array('title'=>'提现资金对账','url'=>'Liquidate/withdrawData','show'=>1),
            ),
        ),
        
    ),
);