<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model\RelationModel;
class VipPointModel extends RelationModel {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';
    protected $fields = array('multiple');

    protected $_link = array(
        'vip'=>array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'        => 'VipConfig',
            'foreign_key'        => 'vip_id',
            // 定义更多的关联属性
        ),
    );
    public function getResult(){
        return $this->relation(true)->select();
    }

    public function updateMultiple(){

        $this->multiple = I('post.multiple');
        $id = I('post.id');
        return $this->where("id = $id")->save(); // 根据条件更新记录
    }
}