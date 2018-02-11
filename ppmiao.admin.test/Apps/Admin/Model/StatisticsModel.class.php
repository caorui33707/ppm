<?php
namespace Admin\Model;
use Think\Model;

class StatisticsModel extends Model{
    /**
     * @return string
     */
    private $consKeyArray = array('wenjuanxing','wenjuanxing2');

    public function __construct(){

    }

    //  $totleRegisteredUsers = $this->statisticsModel->regUserTotle($channelIdChannelWhere,$now_time,$channelKey);
    public function regUserTotle($cons_key,$now_time,$chn=0){


        $sqlChn = "  id in (select id from s_user where channel_id=" . $chn . " OR id IN (SELECT user_id FROM s_user_channel WHERE channel LIKE '".$cons_key."%' ) ) ";

        if(in_array($cons_key,$this->consKeyArray)){ // 特殊处理
            $sqlChn = "  id in (select id from s_user where channel_id=" . $chn . " OR id IN (SELECT user_id FROM s_user_channel WHERE channel = '".$cons_key."' ) ) ";
        }

        $totleRegisteredUsers = M('user')->where( $sqlChn . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'")->count();


//        $totleRegisteredUsers = M('user')->field('id')->
//        union("select count(id)  AS tp_count  from s_user_channel where ".$channelIdChannelWhere ." LIMIT 1")->
//        where("channel_id=" . $chn . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'")->count();

        return $totleRegisteredUsers;
    }
    // 流失用户
    public function  lossUserCount($dayTime,$uidArr){ // 按天
        if(!$uidArr){
            return 0;
        }

        $last_day = date("Y-m-d",strtotime($dayTime)-24*3600);
        $this_day = date("Y-m-d",strtotime($dayTime));
        $day = (int)date("d",strtotime($dayTime));
        M()->db(3,"mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8o.mysql.rds.aliyuncs.com:3306/ppmiao_statistics");
//
        $uidArreCond = implode(',',$uidArr);
        $sql = "select uid
                        from `s_user_category_daily` suc
                        where  true_time >= '".$last_day."' and true_time < '".$this_day."'
        and category = 'lost' and uid in (" .$uidArreCond. ")";

        $uids = M()->db(3)->query($sql);
        $uids = array_map('current', $uids);
        //$uidMergeArr = array_unique(array_merge($uids,$uidArr));
        $uidCond = implode(',',$uids);

        if(!$uids){
            M()->db(0);
            return 0;
        }


        $sql = "select count(*) as num
                from `s_user_category_loss` sucl
                where sucl.uid in  ($uidCond)
                    and  sucl.loss_days >= 30"; // loss_days = $day and
        $count = M()->db(3)->query($sql);

        M()->db(0);
        $num = array_map('current', $count);

        return $num[0];

    }

    //渠道所属uid
    public function channelUserId($ch,$consKey,$now_time=0){ // 今天,0 为所有天
        $sql = 'select * from  ( ';
        $sql .= " select id as user_id,add_time from s_user where channel_id = ".$ch ;
        $sql .=" union ";
        $sql .=" select user_id,add_time from s_user_channel where channel LIKE '$consKey%'" ;
        $sql .= " ) as t  " ;
        if($now_time=false){
            $sql .= "where add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'";
        }
        $row = M()->query($sql);

        $uids = array_map('current', $row);
        return $uids;
    }
}