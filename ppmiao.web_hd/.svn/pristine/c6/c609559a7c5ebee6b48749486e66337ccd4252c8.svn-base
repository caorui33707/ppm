<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Home\Model;
use Think\Model;
class MemberTaskUserModel extends Model {

//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';

    public function addRecord($user_id,$jf_val){
        $data = [
            'user_id'=>$user_id,
            'jf_val'=>$jf_val,
            'task_id'=>26,
            'sub_category'=>'ACTIVITY'
        ];
        $c = $this->add($data);
        return $c;
    }
}