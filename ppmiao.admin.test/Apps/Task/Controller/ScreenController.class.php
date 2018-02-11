<?php
namespace Task\Controller;
use Think\Controller;

/**
 * 大屏幕进度展示接口
 */
class ScreenController extends Controller{

	public function __construct(){
        load('Admin/function');
    }

	/**
	 * 大屏幕进度数据获取
	 * @return [type] [description]
	 */
	public function get_data() {
		header('Access-Control-Allow-Origin:*');  

		$callback = isset($_GET['callback']) ? trim($_GET['callback']) : 'callback'; //jsonp回调参数，必需

		$redis = getRedisNew();

		// $redis->del('screen_data_2018_02_10_10');
		$result = $redis->get('screen_data_2018_02_10_10');

		$res = $result ? $result : '';

		if ($res) {
			$result = $redis->get('screen_data_2018_02_10_10');
		} else {
			// 获取相关数据
			$time = time();

			// 本月目标存量
			$target_stock_month = $this->get_target_stock($time);
			// 当月目标交易额
			$target_transaction_volume_month = $this->get_month_target_transaction($target_stock_month, $time);

			$start = date('Y-m-01', $time);
			$end = date('Y-m-d', strtotime("+1days", strtotime(date('Y-m-t', $time))));
			// 当月实际交易额
			$transaction_volume_month = round(M('UserDueDetail')->where("user_id > 0 and add_time >= '$start' and add_time <= '$end'")->sum('due_capital'), 2);

			// 今日目标交易额获取
			$target_transaction_volume_today = $this->get_today_target_transaction($target_transaction_volume_month, $time);
			// 今日实际交易额
			$start = date('Y-m-d', $time);
			$end = date('Y-m-d', strtotime("+1days", $time));
			$transaction_volume_today = round(M('UserDueDetail')->where("user_id > 0 and add_time >= '$start' and add_time < '$end'")->sum('due_capital'), 2);

			// 实际存量
			$stock_month = round(M('userDueDetail')->where('user_id > 0 and status = 1')->sum('due_capital'), 2);

			// 剩余天数
			$remaining_days = (int)((strtotime('2018-02-10') - $time) / 86400);
			
			$arr = array(
				'target_trading_volume_today' => $target_transaction_volume_today, // 今日目标交易额
				'actual_trading_volume_today' => $transaction_volume_today, // 今日实际交易额

				'target_trading_volume_month' => $target_transaction_volume_month, // 当月目标交易额
				'actual_trading_volume_month' => $transaction_volume_month, // 当月实际交易额

				'target_stock' => 700000000.00, // 本月目标存量
				'actual_stock' => $stock_month, // 实际存量
				'remaining_days' => $remaining_days, // 剩余天数
				'note' => 'target_trading_volume_today: 今日目标交易额，actual_trading_volume_today：今日实际交易额，target_trading_volume_month：当月目标交易额，actual_trading_volume_month：当月实际交易额，target_stock：本月目标存量，actual_stock：本月实际存量，remaining_days：剩余天数'
				);

			$res_data = array(
				'code' => '200',
				'msg' => $arr
				);
			$result = json_encode($res_data);
			$redis->set('screen_data_2018_02_10_10', $result);
			$redis->expire('screen_data_2018_02_10_10', 1800);
		}

		echo $callback . '(' . $result .')';
	}

	/**
	 * 今日目标交易额获取
	 * @param float $target_transaction_volume_month 当月目标交易额
	 * @return [type] [description]
	 */
	private function get_today_target_transaction($target_transaction_volume_month, $time = 0) {

		$res = null;
		$begin_date = date('Y-m-01', $time);
		$end_date = date('Y-m-t', $time);

		// 周末天数
		$weekend_days = $this->get_weekend_days($begin_date, $end_date);
		// 获取本月日期
		$days_detail = $this->get_weekend_days_detail($time);

		$is_weekend = $this->check_weekend($time);
		if ($is_weekend) {
			// 是周末，周末的“今日目标交易额”：当月目标交易额*0.15/本月周末天数
			$res = round($target_transaction_volume_month * 0.15 / $weekend_days, 2);
			return $res;
		} else {
			// 不是周末,分为回款为0的工作日的“今日目标交易额”和剩余工作日的“今日目标交易额”,但是要看周一的前两天是否有回款
			// 当日时间范围
			$start_time = null;
			$end_time = null;
			$w = date('w', $time);
			if ($w == 1) {
				// 周一还款 是上周六、日的总额
				$start_time = date('Y-m-d', strtotime("-2days",$time));
				$end_time = date("Y-m-d",$time);
			} elseif ($w == 5) {
				// 周五 当天还款
				// $start_time = date('Y-m-d', $time);
				$start_time = date('Y-m-d', strtotime("-1days",$time));
				$end_time = date("Y-m-d", strtotime("+1days",$time));
			} else {
				// 其他工作日，是当前日期前一天的还款金额
				$start_time = date('Y-m-d', strtotime("-1days",$time));
				$end_time = date("Y-m-d",$time);
			}

			// 当日实际回款金额
			$back_money = M('UserDueDetail')->where("user_id > 0 and due_time >= '$start_time' and due_time < '$end_time'")->sum('due_capital');
			// 当日实际已回款金额
			// $back_money = M('UserWalletRecords')->where("type = 4 and pay_status = 2 and add_time >= '$start_time' and add_time < '$end_time'")->sum('value');

			// 当月回款总额
			$start = date('Y-m-01', $time);
			$end = date('Y-m-d', strtotime("+1days", strtotime(date('Y-m-t', $time))));
			$total_back_amount = M('UserDueDetail')->where("user_id > 0 and due_time >= '$start' and due_time < '$end'")->sum('due_capital');

			if ($back_money > 0) {
				// 回款不为0的工作日的“今日目标交易额”：（当月目标交易额-周末目标交易额总量-回款为0的工作日目标交易额总量）*当日回款金额/当月回款总额
				$res = round(($target_transaction_volume_month - $target_transaction_volume_month*0.15 - $target_transaction_volume_month*0.03) * $back_money / $total_back_amount, 2);
				return $res;
			} else {
				// 回款为0的工作日的“今日目标交易额”：当月目标交易额*0.03/当月回款为0工作日的天数
				// 本月日期列表
				$working_days = $days_detail[1]; // 本月工作日期
				// 统计当月回款为0工作日的天数
				$count_back_zero_days = $this->count_back_zero_days($working_days);
				// 回款为0的工作日的“今日目标交易额”：当月目标交易额*0.03/当月回款为0工作日的天数

				if ($count_back_zero_days > 0) {
					$res = round($target_transaction_volume_month * 0.03 / $count_back_zero_days, 2);
				} else {
					$res = round($target_transaction_volume_month * 0.03, 2);
				}

				return $res;

				// // 标识是否回款为0,1是0否
				// $temp = 0;
				// // 如果当前日期回款为0，判断当前日期是否是周一
				// $w = date('w', $time);
				// if ($w == 1) {
				// 	// 如果当前日期是周一，查询上周末两天是否有回款，有则该日期回款不为0
				// 	// 查询周六周日回款都为0，当前日期回款为0
				// 	$saturday = date('Y-m-d', strtotime('-2days', $time));
				// 	$sunday = date('Y-m-d', strtotime('-1days', $time));

				// 	$new_back_money = M('UserDueDetail')->where("user_id > 0 and due_time >= '$saturday' and due_time < '".date('Y-m-d', strtotime("+1days", strtotime($saturday)))."'")->sum('due_capital');
				// 	$new_back_money_2 = M('UserDueDetail')->where("user_id > 0 and due_time >= '$sunday' and due_time < '".date('Y-m-d', strtotime("+1days", strtotime($sunday)))."'")->sum('due_capital');

				// 	// 回款为0
				// 	if (!$new_back_money && !$new_back_money_2) {
				// 		$temp = 1;
				// 	} else {
				// 		$temp = 0;
				// 		$back_money = $back_money + $new_back_money + $new_back_money_2;
				// 	}

				// } else {
				// 	// 回款为0
				// 	$temp = 1;
				// }

				// if ($temp > 0) {
				// 	// 回款为0的工作日的“今日目标交易额”：当月目标交易额*0.03/当月回款为0工作日的天数
				// 	// 本月日期列表
				// 	$working_days = $days_detail[1]; // 本月工作日期
				// 	// 统计当月回款为0工作日的天数
				// 	$count_back_zero_days = $this->count_back_zero_days($working_days);
				// 	// 回款为0的工作日的“今日目标交易额”：当月目标交易额*0.03/当月回款为0工作日的天数
				// 	$res = number_format($target_transaction_volume_month * 0.03 / $count_back_zero_days, 2);
				// } else {
				// 	$res = ($target_transaction_volume_month - $target_transaction_volume_month*0.15 - $target_transaction_volume_month*0.03) * $back_money / $total_back_amount;
				// }

			}

		}
	}

	/**
	 * 获取当月回款为0的工作日的天数
	 * @return [type] [description]
	 */
	private function count_back_zero_days($working_days) {

		$count_back_zero_days = 0;

		foreach ($working_days as $key => $value) {

			$start_time = null;
			$end_time = null;
			$time = strtotime($value);
			$w = date('w', $time);

			if ($w == 1) {
				// 周一还款 是上周六、日的总额
				$start_time = date('Y-m-d', strtotime("-2days",$time));
				$end_time = date("Y-m-d",$time);
			} elseif ($w == 5) {
				// 周五 当天还款
				// $start_time = date('Y-m-d', $time);
				$start_time = date('Y-m-d', strtotime("-1days",$time));
				$end_time = date("Y-m-d", strtotime("+1days",$time));
			} else {
				// 其他工作日，是当前日期前一天的还款金额
				$start_time = date('Y-m-d', strtotime("-1days",$time));
				$end_time = date("Y-m-d",$time);
			}

			$back_money = M('UserDueDetail')->where("user_id > 0 and due_time >= '$start_time' and due_time < '$end_time'")->sum('due_capital');

			if (!$back_money) {
				$count_back_zero_days ++;
			}

			// $back_money = M('UserDueDetail')->where("user_id > 0 and due_time >= '$value' and due_time < '".date('Y-m-d', strtotime("+1days", strtotime($value)))."'")->sum('due_capital');

			// if (!$back_money) {
			// 	// 如果当前日期回款为0，判断当前日期是否是周一
			// 	$w = date('w', strtotime($value));
			// 	if ($w == 1) {
			// 		// 如果当前日期是周一，查询上周末两天是否有回款，有则该日期回款不为0
			// 		// 查询周六周日回款都为0，当前日期回款为0
			// 		$saturday = date('Y-m-d', strtotime('-2days', strtotime($value)));
			// 		$sunday = date('Y-m-d', strtotime('-1days', strtotime($value)));

			// 		$new_back_money = M('UserDueDetail')->where("user_id > 0 and due_time >= '$saturday' and due_time < '".date('Y-m-d', strtotime("+1days", strtotime($saturday)))."'")->sum('due_capital');
			// 		$new_back_money_2 = M('UserDueDetail')->where("user_id > 0 and due_time >= '$sunday' and due_time < '".date('Y-m-d', strtotime("+1days", strtotime($sunday)))."'")->sum('due_capital');
					
			// 		if (!$new_back_money && !$new_back_money_2) {
			// 			$count_back_zero_days ++;
			// 		}

			// 	} else {
			// 		// 当前日期不是周一而且回款为0，count++
			// 		$count_back_zero_days ++;
			// 	}
			// }
		}

		return $count_back_zero_days;
	}

	/**
	 * 获取本月目标存量
	 * @return [type] [description]
	 */
	private function get_target_stock($time = 0) {
		$y = date('Y', $time);
		$m = date('m', $time);

		$res = 0.00;

		if ($y == '2017' && $m == '12') {
			$res = 600000000.00;
		} elseif ($y == '2018' && $m == '01') {
			$res = 676000000.00;
		} elseif ($y == '2018' && $m == '02') {
			$res = 700000000.00;
		}
		return $res;
	}

	/**
	 * 获取当月目标交易额
	 * @param  integer $time [description]
	 * @return [type]        [description]
	 */
	private function get_month_target_transaction($target_stock_month, $time = 0) {
		$y = date('Y', $time);
		$m = date('m', $time);

		$start = date('Y-m-01', $time);
		$end = date('Y-m-d', strtotime("+1days", strtotime(date('Y-m-t', $time))));

		// 上月月底日期
		$last_month_end = date('Y-m-t', strtotime('-1 month', $time));
		// 上月月底实际存量
		$last_month_stock = M('StatisticsNetprofit')->where("stat_date = '$last_month_end' and channel_id = 0")->getField('money');
		// 当月回款总额
		$total_back_amount = M('UserDueDetail')->where("user_id > 0 and due_time >= '$start' and due_time < '$end'")->sum('due_capital');

		// 当月目标交易额 = 本月目标存量-上月底实际存量+当月回款总额
		$target_transaction_volume_month = round($target_stock_month - $last_month_stock + $total_back_amount, 2);

		return $target_transaction_volume_month;
	}

	/**
	 * 计算一段时间内周末的天数或者非周末的天数
	 * @param  [type]  $start_date [description]
	 * @param  [type]  $end_date   [description]
	 * @param  boolean $is_workday [description]
	 * @uses $count = $this->get_weekend_days('2017-12-01', '2017-12-03'); // 获取一段时间内的周末天数
	 * @return [type]              [description]
	 */
	private function get_weekend_days($start_date,$end_date,$is_workday = false){

		if (strtotime($start_date) > strtotime($end_date)) list($start_date, $end_date) = array($end_date, $start_date);

		$start_reduce = $end_add = 0;
		$start_N = date('N',strtotime($start_date));
		$start_reduce = ($start_N == 7) ? 1 : 0;
		$end_N = date('N',strtotime($end_date));

		in_array($end_N,array(6,7)) && $end_add = ($end_N == 7) ? 2 : 1;

		$alldays = abs(strtotime($end_date) - strtotime($start_date))/86400 + 1;
		$weekend_days = floor(($alldays + $start_N - 1 - $end_N) / 7) * 2 - $start_reduce + $end_add;

		if ($is_workday){
			$workday_days = $alldays - $weekend_days;
			return $workday_days;
		}
		return $weekend_days;
	}

	/**
	 * 获取本月周末的日期
	 * @param  integer $time [description]
	 * @return [type]        [description]
	 */
	private function get_weekend_days_detail($time = 0) {
		$weekend_days = array();
		$working_days = array();
		$all_days = array();

		$year = date("Y", $time);
		$month = date("m", $time);
		$days = date("t", $time);
		for ($i=1; $i <= $days; $i++) { 
		    $day = $year.'-'.$month.'-'.$i;
		    $w = date('w',strtotime($day));
		    if ($w == 6 || $w ==0) {
				// echo $day.' 是周末<br />';
				$weekend_days[] = $day;
		    } else {
		   		$working_days[] = $day;
		    }
		    $all_days[] = $day;
		} 
		$res = array($weekend_days, $working_days, $all_days);
		return $res;
	}

	/**
	 * 判断某天是否是周末
	 * @param timestamp $time 时间戳
	 * @return bool true表示是周末，false不是
	 */
	private function check_weekend($time = 0) {
		if((date('w', $time) == 6) || (date('w', $time) == 0)) {
			return true;
		}
		return false;
	}



}