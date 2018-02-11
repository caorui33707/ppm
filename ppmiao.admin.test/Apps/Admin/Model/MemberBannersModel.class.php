<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class MemberBannersModel extends Model {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';

//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';
    protected $fields = array('title', 'type','image', 'action','orders','ext','status','add_id','add_time','id_delete');

    public function getResult(){
        $where = [
            'is_delete' => 0,
        ];
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        $counts = $this->where($where)->order('orders desc')->count();


        $Page = new \Think\Page($counts, $count);
        $result = $this->where($where)->order('orders desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $params = array(
            'page' => $page,
        );
        return [$result,$show,$params];
    }


    public function store(){
        $info = UploadModel::upload();

        if($info == 0){

            return array('status'=>0,'info'=>'oss图片上传失败');
        }else{

            $data['title'] = I('post.title');
            $data['ext'] = I('post.ext');
            $data['action'] = I('post.action');
            $data['type'] = I('post.type');
            $data['status'] = I('post.status',0);
            $data['add_id'] = 1;
            $data['image'] = $info[0];
            $data['orders'] = $this->getNewOrder();
            $c = $this->add($data);
            if($c){
                return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/banners'];
            }else{
                return ['status'=>0,'info'=>'创建失败'];
            }
        }
//        var_dump($info);
    }
    public function getNewOrder(){
        $data = $this->field('max(orders) as max')->find();
        return $data['max']+1;
    }


    public function getOne($id){


        $detail =  $this->where("id= $id")->find();
        return $detail;
    }

    public function update(){


        $id = I('post.id');

        if($_FILES){
            $info = UploadModel::upload();
            if($info == 0){
                return array('status'=>0,'info'=>'oss图片上传失败');
            }
            $data['image'] = $info[0];
        }

        $data['title'] = I('post.title');
        $data['ext'] = I('post.ext');
        $data['type'] = I('post.type');
        $data['action'] = I('post.action');
        $data['status'] = I('post.status',0);


        $u = $this->where("id = $id")->save($data);
        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/banners'];
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
}