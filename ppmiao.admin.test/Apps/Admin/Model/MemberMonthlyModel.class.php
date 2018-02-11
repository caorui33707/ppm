<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model\RelationModel;
class MemberMonthlyModel extends RelationModel {

    protected $connection = 'MEMBER_CONFIG';
//    protected $dbName = 'ppmiao_dev_2017';
    protected $_link = array(
        'commodity'=>array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'        => 'MemberCommodities',
            'foreign_key'        => 'commodity_id',
            // 定义更多的关联属性
        ),
        'vip'=>array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'        => 'VipConfig',
            'foreign_key'        => 'vip_id',
            // 定义更多的关联属性
        ),
    );
    public function getResult($vip_id = 0){
        $where = [
            'is_delete' => 0,
        ];
        if($vip_id != 0){
            $where['vip_id'] = $vip_id;
        }
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        $counts = $this->where($where)->count();


        $Page = new \Think\Page($counts, $count);
        $result = $this->relation(true)->where($where)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        foreach($result as $key=>$val){
            $result[$key]['use_count'] = $this->getUseCount($val['source'],$val['commodity']['type']);
        }
        $params = array(
            'page' => $page,
            'vip_id' => $vip_id,
        );
        return [$result,$show,$params];
    }

    public function getOne($id){
        $detail =  $this->relation(true)->where("id= $id")->find();
        $detail['commodity']['project_type'] = D("MemberCommodities")->type_to_name($detail['commodity']['project_type']);
        return $detail;
    }
    public function store(){

        $data['year'] = I('post.year');
        $data['month'] = I('post.month');
        $data['remark'] = I('post.remark');
        $data['title'] = I('post.title');
        $data['status'] = I('post.status',0);
        $data['commodity_id'] = I('post.commodity_id');
        $data['vip_id'] = I('post.vip_id');
        $c = $this->add($data);
        if($c && $this->setSource($c,"monthly")){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/monthly'];
        }else{
            return ['status'=>0,'info'=>'创建失败'];
        }
    }
    public function update(){

        $id =  I('post.id');

        $data['year'] = I('post.year');
        $data['month'] = I('post.month');
        $data['title'] = I('post.title');
        $data['remark'] = I('post.remark');
        $data['status'] = I('post.status',0);
        $data['commodity_id'] = I('post.commodity_id');
        $data['vip_id'] = I('post.vip_id');
        $u = $this->where("id = $id")->save($data);
        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/monthly'];
        }else{
            return ['status'=>0,'info'=>'更新失败'];
        }
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