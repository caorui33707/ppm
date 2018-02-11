<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model\RelationModel;
class MemberStoreModel extends RelationModel {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';
    protected $_link = array(
        'commodity'=>array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'        => 'MemberCommodities',
            'foreign_key'        => 'commodity_id',
            // 定义更多的关联属性
        ),
    );
    public function _condition($_where){
        foreach((array)$_where as $k=>$v){
            if($this->_link[$k]){
                $this->_link[$k]['condition'] = $v;
            }
        }
        return $this->_link;
    }
    public function getResult($type = 0,$name = '0'){

        if($type != 0){
            $this->_link['commodity']['condition']="type = $type";
        }
        if($name != '0'){
            $where = "is_delete = 0 and title like '%$name%'";
        }else{
//            echo $name;
            $where = [
                'is_delete' => 0,
            ];
        }


//        echo $where;
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        $counts = $this->relation(true)->where($where)->order('orders desc')->count();


        $Page = new \Think\Page($counts, $count);
        $result = $this->relation(true)->where($where)->order('orders desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        foreach($result as $key=>$val){
            $result[$key]['use_count'] = $this->getUseCount($val['source'],$val['commodity']['type']);
        }
        $params = array(
            'page' => $page,
        );
        return [$result,$show,$params];
    }


    public function store(){

        $data['title'] = I('post.name');
        $data['hot'] = I('post.hot');
        $data['remark'] = I('post.remark');
        $data['jf_val'] = I('post.jf_val');
        $data['status'] = I('post.status',0);
        $data['orders'] = $this->getNewOrder();
        $data['commodity_id'] = I('post.commodity_id');
        $c = $this->add($data);

        if($c && $this->setSource($c,'store')){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/store'];
        }else{
            return ['status'=>0,'info'=>'创建失败'];
        }
    }

    public function update(){
        $id = I('post.id');

        $data['title'] = I('post.name');
        $data['hot'] = I('post.hot');
        $data['remark'] = I('post.remark');
        $data['status'] = I('post.status');
        $data['jf_val'] = I('post.jf_val');
        $data['commodity_id'] = I('post.commodity_id');
        $u = $this->where("id = $id")->save($data);

        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/store'];
        }else{
            return ['status'=>0,'info'=>'更新失败'];
        }
    }


    public function getOne($id){
        $detail =  $this->relation(true)->where("id= $id")->find();
        $detail['commodity']['project_type'] = D("MemberCommodities")->type_to_name($detail['commodity']['project_type']);
        return $detail;
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

    public function setSource($id,$key){
        $source = $key."_".$id;

        $data['source'] = $source;
        return $this->where('id='.$id)->save($data);
    }

    public function getUseCount($source,$type){
        if($type == 1 || $type == 2){
            $model[1] = 'UserRedenvelope';
            $model[2] = 'UserInterestCoupon';
            $where = [
                'source' => $source,
                'status' => 1,
            ];
            $count = M($model[$type])->where($where)->count();
        }else{
            $count = '';
        }
        return $count;
    }

}