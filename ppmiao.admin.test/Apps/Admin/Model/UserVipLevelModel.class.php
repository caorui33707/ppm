<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class UserVipLevelModel extends Model {

    protected $connection = 'MEMBER_CONFIG';
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    public function getVipUserCount($vip_id){
        return $this->where('vip_level = '.$vip_id)->count();
    }

    /**
     * 获取某个等级的所有用户id
     * @return [type] [description]
     */
    public function getVipUserIds($vip_id) {
    	return $this->field('uid')->where('vip_level = '.$vip_id)->order('uid asc')->select();
    }

    /**
     * 获取一组用户的vip等级
     * @return [type] [description]
     */
    public function getUsersLevel($user_ids) {

        $where['uid'] = array('in',$user_ids);
        return $this->field('vip_level, uid')->where($where)->order('uid asc')->select();
    }

}