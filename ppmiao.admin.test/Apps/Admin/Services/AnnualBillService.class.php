<?php

namespace Admin\Services;

use Admin\Model\MemberStoreUserModel;
use Admin\Model\MemberTasksModel;
use Admin\Model\MemberTaskUserModel;
use Admin\Model\MemberThronesModel;
use Admin\Model\UserVipLevelModel;
use Redis;


class AnnualBillService
{
    public $startDate = '2017-01-01 00:00:00.000000';
    public $endDate   = '2017-12-31 23:59:59.999000';
    public $prefix    = '2017_annual_bill_';
    //缓存时间
    public $timeOut = 3600;

    /**
     * 用户基本信息
     * @param $userId
     * @return array|bool
     */
    public function userInfo($mobile)
    {
        $user = M('user')->where(['username' => $mobile])->find();
        if (!$user) {
            return false;
        }
        //用户基本信息
        //$userRegisterNo           = M('user')->where('id<' . $user['id'])->count();
        $data['user_id']          = $user['id'];
        $data['real_name']        = $user['real_name'] ? $user['real_name'] : '';
        $data['add_time']         = date('Y年n月j日', strtotime($user['add_time']));
        $data['total_days']       = ceil((time() - strtotime($user['add_time'])) / (3600 * 24));
        $data['register_ranking'] = $user['id'];
        return $data;
    }


    /**
     * 投资信息
     * @param $userId
     * @return array
     */
    public function investmentInfo($userId)
    {
        $investmentInfo = [
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

        $userDueDetailObj = M('user_due_detail');
        //投资相关
        $firstInvest = $userDueDetailObj
            ->where("user_id=" . $userId . " and add_time<=" . "'$this->endDate'")
            ->order('id asc')
            ->limit(1)
            ->select();

        if ($firstInvest[0]) {
            $investmentInfo['first_invest_time'] = date('Y年n月j日', strtotime($firstInvest[0]['add_time']));
            //首投金额
            $investmentInfo['first_due_amount'] = sprintf("%.2f", $firstInvest[0]['due_capital']);

            //当前查询用户累计投资金额
            $totalDueAmount                     = $userDueDetailObj
                ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
                ->sum('due_capital');
            $investmentInfo['total_due_amount'] = sprintf("%.2f", $totalDueAmount);

            //累计投资金额小于 xxxx 的用户数
            $sql                = "select count(*) as final_total from (select SUM(due_capital) as amount,user_id from s_user_due_detail where user_id>0 and add_time>=" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'" . " GROUP BY user_id having amount<" . $investmentInfo['total_due_amount'] . ") as final_total";
            $result             = $userDueDetailObj->query($sql);
            $exceedInvestPeople = isset($result[0]['final_total']) ? $result[0]['final_total'] : 0;

            //所有投资用户数
            $totalInvestPeopleSql              = "select count(*) as count_total from (select user_id from s_user_due_detail where user_id>0 and add_time>=" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'" . " group by user_id) as total";
            $totalInvestPeopleResult           = $userDueDetailObj->query($totalInvestPeopleSql);
            $totalInvestPeople                 = $totalInvestPeopleResult[0]['count_total'];
            $investmentInfo['override_amount'] = sprintf("%.1f", $exceedInvestPeople / $totalInvestPeople * 100);

            //当前查询用户累计收益金额
            $currentUserProfitAmount = $userDueDetailObj
                ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . "and add_time<=" . "'$this->endDate'")
                ->sum('due_interest');
            $currentUserProfitAmount = $currentUserProfitAmount ? $currentUserProfitAmount : 0;

            //累计收益金额小于 xxxx 的用户数
            $profitSql                            = "select count(*) as final_total from (select SUM(due_interest) as amount,user_id from s_user_due_detail where user_id>0  and add_time>=" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'" . "  GROUP BY user_id having amount<" . $currentUserProfitAmount . ") as total";
            $userDueDetailResult                  = $userDueDetailObj->query($profitSql);
            $exceedProfitPeople                   = isset($userDueDetailResult[0]['final_total']) ? $result[0]['final_total'] : 1;
            $investmentInfo['total_due_interest'] = sprintf("%.2f", $currentUserProfitAmount);
            $investmentInfo['override_interest']  = sprintf("%.1f", $exceedProfitPeople / $totalInvestPeople * 100);

            //累计投资次数
            $investmentTimes = $userDueDetailObj
                ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . "and add_time<=" . "'$this->endDate'")
                ->count();

            //投资次数
            $investmentInfo['investment_times'] = intval($investmentTimes);
            $user                               = M('user')->where(['id' => $userId])->find();

            //17之前注册
            if (strtotime($user['add_time']) < strtotime($this->startDate)) {
                if (!$investmentTimes) {
                    $investmentInfo['average_times'] = null;
                } else {
                    $investmentInfo['average_times'] = abs(ceil(365 / intval($investmentTimes)));
                }
            }

            //17年间注册
            if (strtotime($user['add_time']) > strtotime($this->startDate) && strtotime($user['add_time']) < strtotime($this->endDate)) {
                if (!$investmentTimes) {
                    $investmentInfo['average_times'] = null;
                } else {
                    //总共天数
                    $days                            = ceil((strtotime($this->endDate) - strtotime($user['add_time'])) / 86400);
                    $investmentInfo['average_times'] = abs(ceil($days / intval($investmentTimes)));
                }
            }

            //17年之后
            if (strtotime($user['add_time']) > strtotime($this->endDate)) {
                $investmentInfo['average_times'] = null;
            }

            //最高单笔投资金额
            $topDueAmount = $userDueDetailObj
                ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . "and add_time<=" . "'$this->endDate'")
                ->max('due_capital');

            $investmentInfo['top_amount'] = sprintf("%.2f", $topDueAmount);

            //最长投资天数
            $longInvestmentDays                     = $userDueDetailObj
                ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . "and add_time<=" . "'$this->endDate'")
                ->max('duration_day');
            $investmentInfo['long_investment_days'] = intval($longInvestmentDays);
        }

        //累计获得积分
        $jf                               = new UserVipLevelModel();
        $jfTotal                          = $jf->where(['uid' => $userId])->find();
        $totalJf                          = isset($jfTotal['jf_val']) ? intval($jfTotal['jf_val']) : 0;
        //$consumptionJf                    = M('user_exchange_log')->where("uid=" . $userId)->sum('jf_consumed');
        //积分商城
        $shopJf = new MemberTaskUserModel();
        $shopJfTotal = $shopJf->where("task_id=26 AND user_id=".$userId." AND jf_val< 0")->sum('jf_val');

        //消费积分
        $xJf = new MemberStoreUserModel();
        $xJfTotal = $xJf->where("user_id=".$userId)->sum('jf_val');

        $investmentInfo['total_integral'] = $totalJf + abs($shopJfTotal) +  $xJfTotal;
        return $investmentInfo;
    }


    /**
     * 获取用户VIP信息
     * @param $userId
     * @return UserVipLevelModel|array|bool|mixed
     */
    public function getVipInfo($userId)
    {
        if (!$userId) {
            return false;
        }
        $data = [];
        $vip  = new UserVipLevelModel();
        $vip  = $vip->where(['uid' => $userId])->find();
        if (!$vip) {
            $vipLevel = 0;
        } else {
            $vipLevel = $vip['vip_level'];
        }
        $pri        = new MemberThronesModel();
        $privileges = $pri->where("is_delete = 0 and vip_id <=" . $vipLevel)->field('title')->select();
        $priData    = [];
        if ($privileges) {
            foreach ($privileges as $key => $val) {
                array_push($priData, $val['title']);
            }
        }
        $data['vip_level'] = $vipLevel;
        $data['privilege'] = $priData;
        return $data;

    }


    /**
     * 获取邀请人数和邀请人数的收益
     * @param $userId
     * @return bool
     *
     */
    public function getInviteNumbers($userId)
    {
        if (!$userId) {
            return false;
        }
        $userInviteCount = M('user_invite_list')
            ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . "and add_time<=" . "'$this->endDate'")
            ->field('invited_user_id')
            ->select();
        $arrId           = [];
        $data['people']  = 0;
        $data['profit']  = 0;
        if (isset($userInviteCount[0]['invited_user_id'])) {
            foreach ($userInviteCount as $key => $val) {
                if($val['invited_user_id']){
                    array_push($arrId, $val['invited_user_id']);
                }
            }
            $arrId               = array_unique($arrId);
            $ids                 = implode($arrId, ',');
            $totalDueInterestSql = "select sum(due_interest) as due_interest_total  from s_user_due_detail where add_time>'2017-01-01 00:00:00.000000' and add_time<='2017-12-31 23:59:59.999000' and user_id in(" . $ids . ")";
            $userDueDetailObj    = M('user_due_detail');
            $result              = $userDueDetailObj->query($totalDueInterestSql);
            $data['people']      = count($arrId);
            $data['profit']      = isset($result[0]['due_interest_total']) ? sprintf("%.2f", $result[0]['due_interest_total']) : 0;
        }

        return $data;
    }


    /**
     * 福利 红包加息券信息
     * @param $userId
     * @return mixed
     *
     */
    public function welfareInfo($userId)
    {
        $redis = $this->redis();

        $userDueDetailObj = M('user_due_detail');

        //所有用户使用红包金额
        $redAmount = $redis->get($this->prefix . 'red_amount');
        if (!$redAmount) {
            $redAmount = $userDueDetailObj
                ->where("add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
                ->sum('red_amount');
            $redis->set($this->prefix . 'red_amount', $redAmount, $this->timeOut);
        }

        //查询用户使用红包总金额
        $currentUserReadAmount = $userDueDetailObj
            ->where("user_id=" . $userId . " and add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
            ->sum('red_amount');

        //使用红包人数
        $redPeopleCount = $redis->get($this->prefix . 'red_count');
        if (!$redPeopleCount) {
            //使用红包人数
            $userRedSql = "select count(*) as count_total from (select count(*) from `s_user_due_detail` where user_id>0 and red_amount>0 and add_time>='2017-01-01 00:00:00.000000' and add_time<='2017-12-31 23:59:59.999000' GROUP BY user_id) as a";
            $userRedQuery = $userDueDetailObj->query($userRedSql);
            $redPeopleCount       = isset($userRedQuery[0]['count_total']) ? $userRedQuery[0]['count_total'] : 0;
            $redis->set($this->prefix . 'red_count', $redPeopleCount, $this->timeOut);
        }

        //使用加息券人数
        $userInterestCouponCount = $redis->get($this->prefix . 'coupon_count');
        if (!$userInterestCouponCount) {
            //使用加息券人数
            $userCouponSql = "select count(*) as count_total from (select count(*) from `s_user_due_detail` where user_id>0 and interest_coupon>0 and add_time>='2017-01-01 00:00:00.000000' and add_time<='2017-12-31 23:59:59.999000' GROUP BY user_id) as a";
            $userCouponQuery = $userDueDetailObj->query($userCouponSql);
            $userInterestCouponCount       = isset($userCouponQuery[0]['count_total']) ? $userCouponQuery[0]['count_total'] : 0;
            $redis->set($this->prefix . 'coupon_count', $userInterestCouponCount, $this->timeOut);
        }


        //所有加息券收益
        $couponIncome = $redis->get($this->prefix . 'coupon_income');
        if (!$couponIncome) {
            $couponSql          = "select sum(final_total) as coupon_total from (select (due_capital*duration_day*interest_coupon/100/365) as final_total from s_user_due_detail where interest_coupon>0  and add_time>=" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'" . ") as total";
            $couponIncomeResult = $userDueDetailObj->query($couponSql);
            $couponIncome       = isset($couponIncomeResult[0]['coupon_total']) ? $couponIncomeResult[0]['coupon_total'] : 0;
            $redis->set($this->prefix . 'coupon_income', $couponIncome, $this->timeOut);
        }

        //当前用户使用加息券收益
        $currentUserCouponSql      = "select sum(final_total) as coupon_total from (select (due_capital*duration_day*interest_coupon/100/365) as final_total from s_user_due_detail where interest_coupon>0 and user_id=" . $userId . " and add_time>=" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'" . ") as total";
        $currentCouponIncomeResult = $userDueDetailObj->query($currentUserCouponSql);
        $currentUserCouponIncome   = isset($currentCouponIncomeResult[0]['coupon_total']) ? $currentCouponIncomeResult[0]['coupon_total'] : 0;

        $data['average_due_interest']       = sprintf("%.2f", ($redAmount + $couponIncome) / ($redPeopleCount + $userInterestCouponCount));
        $data['total_welfare_due_interest'] = sprintf("%.2f", ($currentUserCouponIncome + $currentUserReadAmount));
        return $data;
    }

    /**
     * 标签信息
     * @param $userId
     */
    public function labelInfo($userId)
    {
        $userDueDetailObj = M('user_due_detail');

        $redPeopleCount = $userDueDetailObj
            ->where("user_id=" . $userId . " and red_amount>0 and add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
            ->count();

        //使用加息券人数
        $userInterestCouponCount = $userDueDetailObj
            ->where("user_id=" . $userId . " and interest_coupon>0 and add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
            ->count();

        //是否短标控
        $totalLongAmount = $userDueDetailObj
            ->where("user_id=" . $userId . " and duration_day>=110 and add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
            ->count();

        $totalShortAmount = $userDueDetailObj
            ->where("user_id=" . $userId . " and duration_day<110 and add_time>" . "'$this->startDate'" . " and add_time<=" . "'$this->endDate'")
            ->count();

        //是否夜猫
        $nightTotal   = M('investment_detail')
            ->where("user_id=" . $userId . " and (TIME(add_time)>'18:00:00' OR TIME(add_time)<='08:00:00')")
            ->count();

        $dayTotal = M('investment_detail')
            ->where("user_id=" . $userId . " and (TIME(add_time)>'08:00:00' AND TIME(add_time)<='18:00:00')")
            ->count();

        $data = [
            'voucher'       => $userInterestCouponCount > $redPeopleCount ? 1 : 0,//是否加息券控
            'red_envelopes' => $userInterestCouponCount < $redPeopleCount ? 1 : 0,//是否红包控
            'short_bid'     => $totalLongAmount < $totalShortAmount ? 1 : 0, //是否短标控
            'long_bid'      => $totalLongAmount > $totalShortAmount ? 1 : 0,//是否长标控
            'night_owl'     => $nightTotal > $dayTotal ? 1 : 0,//是否夜猫
        ];
        return $data;
    }

    /**
     * 获取redis实例
     * @return Redis
     */
    public function redis()
    {
        $redis = new Redis();
        $redis->connect(C("REDIS_HOST"), C("REDIS_PORT"));
        $redis->auth(C("REDIS_AUTH"));
        return $redis;

    }


    /**
     * @param $url
     * @param $post_data
     * @return bool|string
     */
    public function sendPost($url, $postData)
    {
        $postData = http_build_query($postData);
        $options  = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            ]
        ];
        $context  = stream_context_create($options);
        $result   = file_get_contents($url, false, $context);
        return $result;
    }


    /**
     * 生成随机验证码
     *
     * @param int $length
     *
     * @return int
     */
    public function generateCode($length = 6)
    {
        return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }

}
