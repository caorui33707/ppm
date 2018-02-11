<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model\RelationModel;
use Admin\Model\UploadModel;

class MemberThronesModel extends RelationModel {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';
    protected $fields = array('title', 'vip_id', 'image', 'image_off','orders','status');
    protected $_link = array(
        'vip'=>array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'        => 'VipConfig',
            'foreign_key'        => 'vip_id',
            // 定义更多的关联属性
        ),
    );
    public function getResult(){
        return $this->relation(true)->where("is_delete = 0")->order('orders desc')->select();
    }


    public function getOne($id){
        return $this->where("id= $id")->find();
    }

    public function store(){
        $info = UploadModel::upload();

        if($info == 0){

            return array('status'=>0,'info'=>'oss图片上传失败');
        }else{

            $data['title'] = I('post.name');
            $data['vip_id'] = I('post.vip_id');
            $data['image'] = $info[0];
            $data['image_off'] = $info[1];
            $data['orders'] = $this->getNewOrder();
            $c = $this->add($data);
            if($c){
                return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/thrones'];
            }else{
                return ['status'=>0,'info'=>'创建失败'];
            }
        }
//        var_dump($info);
    }
    public function update(){
        $id = I('post.id');
        if($_FILES){
            $info = UploadModel::upload();
            if($_FILES['image']){
                $data['image'] = $info[0];
                if($_FILES['image_off']){
                    $data['image_off'] = $info[1];
                }else{
                    $data['image_off'] = I('post.img_off');
                }
            }else{
                $data['image_off'] = $info[0];
                $data['image'] = I('post.img');
            }

        }

        $data['title'] = I('post.name');
        $data['vip_id'] = I('post.vip_id');
        $u = $this->where("id = $id")->save($data);
        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/thrones'];
        }else{
            return ['status'=>0,'info'=>'更新失败'];
        }


    }
    /**
     * @param $direction:up or down
     * @param $id 位移的id
     * @return string
     */
    public function move($direction,$id){
        $obj = $this->where("id = $id")->find();
        $move_orders = $obj['orders'];
        $moved = $this->getOrderFind($direction,$move_orders);

        if(!$moved){
            return ['status'=>0,'info'=> '已经无法移动'];
        }else{
            $move2 = $this->where("id = $id")->save(['orders'=>$moved['orders']]);
            $move1 = $this->where("id = ".$moved['id'])->save(['orders'=>$move_orders]);
            if($move1 && $move2){
                return ['status'=>1];
            }
        }




    }


    public function getOrderFind($direction ,$move_orders){
        if ( $direction == 1 ) {
            $moved = $this->where(" orders > $move_orders and is_delete = 0")->order("orders ASC")->find();
        } else if($direction == -1) {
            $moved = $this->where(" orders < $move_orders and is_delete = 0")->order("orders DESC")->find();
        }
        return $moved;
    }

    public function getNewOrder(){
        $data = $this->field('max(orders) as max')->find();
        return $data['max']+1;
    }



}