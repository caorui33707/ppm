<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Home\Model;
use Think\Model;
class UserVipLevelModel extends Model {

    protected $connection = 'MEMBER_CONFIG';
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    public function updateUserJf($user_id,$jf_val){
        $this->startTrans();
        $update_jf = $this->where(['uid'=>$user_id])->setInc('jf_val',$jf_val); // 用户的积分
        $record = D('MemberTaskUser')->addRecord($user_id,$jf_val);


        if($update_jf && $record){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }

    }
}