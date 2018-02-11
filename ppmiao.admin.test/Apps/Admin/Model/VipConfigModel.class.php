<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class VipConfigModel extends Model {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';
    public function getResult(){
        $vips = $this->select();
        foreach($vips as $key=> $vip){
            $vips[$key]['count'] = D('UserVipLevel')->getVipUserCount($vip['id']);
        }
        return $vips;
    }

    /**
     * 获取vip等级名称
     * @param  [type] $vip_id [description]
     * @return [type]         [description]
     */
    public function getVipName($vip_id) {
    	return $this->field('name')->where('id = '.$vip_id)->find();
    }
}