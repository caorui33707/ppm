<?php

namespace Admin\Controller;

use Admin\Services\AnnualBillService;

/**
 * 年度账单
 * Class AnnualBillController
 * @package Admin\Controller
 */
class AnnualBillController extends \Think\Controller
{
    public $startDate = '2017-01-01 00:00:00.000000';

    public $endDate = '2017-12-31 23:59:59.999000';

    public $prefix = '2017_annual_bill_';

    //缓存时间
    public $timeOut = 300;

    public $limit = 2000;

    private $annualBillService = null;

    public $userInfo = [
        'register_ranking' => '',//用户id
        'add_time'         => '',//注册时间
        'real_name'        => '',//姓名
        'total_days'       => '',//已注册多少天
        'current_time'     => '',//当前时间
    ];

    public $investmentInfo = [
        'first_invest_time'    => '',//首次投资时间
        'first_due_amount'     => '',//首次投资金额
        'total_due_amount'     => '',//累计投资金额
        'override_amount'      => '',//累计投资超过多少用户
        'total_due_interest'   => '',//累计收益
        'override_interest'    => '',//累计投资收益超过多少用户
        'investment_times'     => '',//累计投资次数
        'average_times'        => '',//多少天投资一次
        'top_amount'           => '',//最高单笔投资金额
        'total_integral'       => '',//累计获得的积分
        'long_investment_days' => ''//最长项目期限
    ];

    public $vipInfo = [
        'grade'     => '', //vip等级
        'privilege' => '' //特权
    ];

    public $inviteInfo = [
        'invite_numbers'         => 0, //邀请人数
        'total_inv_due_interest' => '', //邀请好友帮好友赚的钱
    ];

    public $welfareInfo = [
        'average_due_interest'       => '',//用户平均使用红包加息券收益金额
        'total_welfare_due_interest' => '',//用户使用红包和加息券获得的额外收益总和
    ];

    public $labelInfo = [
        'voucher'       => '',//是否加息券控
        'red_envelopes' => '',//是否红包控
        'finance'       => '',//是否理财高手
        'short_bid'     => '',//是否短标控
        'long_bid'      => '',//是否长标控
        'tu_hao'        => '',//是否土豪
        'new_people'    => '',//是否新人
        'night_owl'     => ''//是否夜猫
    ];

    public function __construct()
    {
        parent::__construct();
        $this->annualBillService = new AnnualBillService();
    }

    /**
     * 年度账单
     */
    public function bill()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST, OPTIONS');
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        if (!$mobile) {
            $this->ajaxReturn([
                'data' => [], 'status' => 5001, 'info' => '请填写有效的手机号！'
            ]);
        }
        $userInfo = $this->annualBillService->userInfo($mobile);
        if (!$userInfo) {
            $this->ajaxReturn(['data' => [], 'status' => 4001, 'info' => '用户未找到']);
        }
        $userId = $userInfo['user_id'];
        //读取缓存
        $redis               = $this->annualBillService->redis();
        $billResultStr       = $this->prefix . $userId;
        $billResultStrResult = $redis->get($billResultStr);
        if ($billResultStrResult) {
            $returnData = json_decode($billResultStrResult, true);
            $this->ajaxReturn([
                'data' => $returnData, 'status' => 2000, 'info' => 'success'
            ]);
        }

        //用户基本信息
        $this->userInfo['register_ranking'] = intval($userInfo['register_ranking']);
        $this->userInfo['real_name']        = $userInfo['real_name'];
        $this->userInfo['add_time']         = $userInfo['add_time'];
        $this->userInfo['total_days']       = $userInfo['total_days'];
        $this->userInfo['current_time']     = date('Y年n月j日');

        //投资相关
        $investmentInfo                               = $this->annualBillService->investmentInfo($userId);
        $this->investmentInfo['first_invest_time']    = $investmentInfo['first_invest_time'];////首次投资时间
        $this->investmentInfo['first_due_amount']     = $investmentInfo['first_due_amount'];////首次投资金额
        $this->investmentInfo['total_due_amount']     = $investmentInfo['total_due_amount'];//累计投资金额
        $this->investmentInfo['override_amount']      = $investmentInfo['override_amount'];//累计投资超过多少用户
        $this->investmentInfo['total_due_interest']   = $investmentInfo['total_due_interest'];//累计收益
        $this->investmentInfo['override_interest']    = $investmentInfo['override_interest'];//累计投资收益超过多少用户
        $this->investmentInfo['investment_times']     = $investmentInfo['investment_times'];//累计投资次数
        $this->investmentInfo['average_times']        = $investmentInfo['average_times'] ? $investmentInfo['average_times'] : '';//多少天投资一次
        $this->investmentInfo['top_amount']           = $investmentInfo['top_amount'];//最高单笔投资金额
        $this->investmentInfo['total_integral']       = $investmentInfo['total_integral'];//累计获得的积分
        $this->investmentInfo['long_investment_days'] = $investmentInfo['long_investment_days'];//累计获得的积分

        //vip相关
        $vipInfo                    = $this->annualBillService->getVipInfo($userId);
        $this->vipInfo['grade']     = $vipInfo ? $vipInfo['vip_level'] : 0;
        $this->vipInfo['privilege'] = $vipInfo['privilege'];

        //邀请相关
        $invitedInfo                                = $this->annualBillService->getInviteNumbers($userId);
        $this->inviteInfo['invite_numbers']         = $invitedInfo['people'];
        $this->inviteInfo['total_inv_due_interest'] = $invitedInfo['profit'];

        //红包加息券相关
        $welfareInfo                                     = $this->annualBillService->welfareInfo($userId);
        $this->welfareInfo['average_due_interest']       = $welfareInfo['average_due_interest'];
        $this->welfareInfo['total_welfare_due_interest'] = $welfareInfo['total_welfare_due_interest'];

        //标签相关
        $labelsInfo                       = $this->annualBillService->labelInfo($userId);
        $this->labelInfo['voucher']       = $labelsInfo['voucher'];
        $this->labelInfo['red_envelopes'] = $labelsInfo['red_envelopes'];
        $this->labelInfo['short_bid']     = $labelsInfo['short_bid'];
        $this->labelInfo['long_bid']      = $labelsInfo['long_bid'];
        $this->labelInfo['night_owl']     = $labelsInfo['night_owl'];
        $this->labelInfo['tu_hao']        = $investmentInfo['total_due_amount'] > 250000 ? 1 : 0;
        $this->labelInfo['new_people']    = $this->vipInfo['grade'] <= 2 ? 1 : 0;
        $this->labelInfo['finance']       = $this->vipInfo['grade'] >= 5 ? 1 : 0;

        $returnData = [
            'userInfo'       => $this->userInfo,
            'investmentInfo' => $this->investmentInfo,
            'vipInfo'        => $this->vipInfo,
            'inviteInfo'     => $this->inviteInfo,
            'welfareInfo'    => $this->welfareInfo,
            'labelInfo'      => $this->labelInfo
        ];
        $redis->set($billResultStr, json_encode($returnData), $this->timeOut);
        $this->ajaxReturn([
            'data' => $returnData, 'status' => 2000, 'info' => 'success'
        ]);
    }

    /**
     * 发送短信验证码
     */
    public function sendSms()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:GET, POST, OPTIONS');

        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        if (!$mobile) {
            $this->ajaxReturn([
                'data' => [], 'status' => 5001, 'info' => '请填写有效的手机号！'
            ]);
        }
        $code   = $this->annualBillService->generateCode();
        $data   = [
            "account"    => "ppm_sms",
            "pswd"       => "fuqianwangluo12_",
            "mobile"     => $mobile,
            "msg"        => $code,
            "needstatus" => "false"
        ];
        $curl   = 'http://222.73.117.156/msg/HttpBatchSendSM';
        $result = $this->annualBillService->sendPost($curl, $data);
        $result = substr($result, -1);
        if ($result == 0) {
            $redis = $this->annualBillService->redis();
            $redis->set($mobile, $code, 120);
            $this->ajaxReturn([
                'data' => [], 'status' => 2000, 'info' => '短信验证码发送成功'
            ]);
        }
    }

    /**
     * 检查短信验证码
     */
    public function checkSms()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:GET, POST, OPTIONS');

        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        $code   = trim(I('post.code', '', 'strip_tags'));
        if (!$mobile) {
            $this->ajaxReturn(['data' => [], 'status' => 4002, 'info' => '请填写有效的手机号！']);
        }
        if (!$code) {
            $this->ajaxReturn(['data' => [], 'status' => 4002, 'info' => '短信验证码不能为空！']);
        }
        $redis  = $this->annualBillService->redis();
        $result = $redis->get($mobile);
        if ($result) {
            if ($result == $code) {
                $this->ajaxReturn(['data' => [], 'status' => 2000, 'info' => '短信验证码正确']);
            } else {
                $this->ajaxReturn(['data' => [], 'status' => 4003, 'info' => '短信验证码错误']);
            }
        } else {
            $this->ajaxReturn(['data' => [], 'status' => 4003, 'info' => '短信验证码错误']);
        }
    }

    /**
     * redis 测试
     */
    public function testRedis()
    {
        ///phpinfo();
        $redis = $this->annualBillService->redis();
        $redis->set('dev_redis_test', '11111');
        $result = $redis->get('dev_redis_test');
        var_dump($result);
    }


    public function test()
    {
        $userRegisterNo = M('user')->where('id<'. 65936)->count();
        var_dump($userRegisterNo);
    }


}