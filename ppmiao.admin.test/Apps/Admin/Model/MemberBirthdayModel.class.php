<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model\RelationModel;
class MemberBirthdayModel extends RelationModel {
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
    public function getResult($title = ''){

        if($title != ''){
            $where = "is_delete = 0 and title like '%$title%'";
        }else{
//            echo $name;
            $where = [
                'is_delete' => 0,
            ];
        }
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        $counts = $this->where($where)->count();


        $Page = new \Think\Page($counts, $count);
        $result = $this->relation(true)->where($where)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();

        foreach($result as $key=>$val){
            $result[$key]['use_count'] = $this->getUseCount($val['source'],$val['commodity']['type']);

            // 查询所属VIP等级
            $vip_config_res = D('VipConfig')->getVipName($val['vip_id']);
            $result[$key]['vip_name'] = isset($vip_config_res['name']) ? $vip_config_res['name'] : '';
        }

        $params = array(
            'page' => $page,
            'title' => $title,
        );
        return [$result,$show,$params];
    }


    public function getOne($id){
        $detail =  $this->relation(true)->where("id= $id")->find();
        $detail['commodity']['project_type'] = D("MemberCommodities")->type_to_name($detail['commodity']['project_type']);
        return $detail;
    }

    /**
     * 添加数据
     * @return [type] [description]
     */
    public function store(){

        $data['title'] = I('post.title');
        $data['status'] = I('post.status',0);
        $data['commodity_id'] = I('post.commodity_id');
        $data['vip_id'] = I('post.vip_id');

        $c = $this->add($data);
        if($c && $this->setSource($c,'birthday')){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/birthday'];
        }else{
            return ['status'=>0,'info'=>'创建失败'];
        }
    }

    /**
     * 更新生日礼包
     * @return [type] [description]
     */
    public function update(){

        $id =  I('post.id');

        $data['title'] = I('post.title');
        $data['status'] = I('post.status',0);
        $data['commodity_id'] = I('post.commodity_id');
        $data['vip_id'] = I('post.vip_id');

        // if(!$this->checkChangeStatus($id,$data['status'])){
        //     return array('status'=>0,'info'=>'无法同时上架两种生日礼包');
        // }

        $u = $this->where("id = $id")->save($data);
        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/birthday'];
        }else{
            return ['status'=>0,'info'=>'更新失败'];
        }
    }

    public function checkChangeStatus($id,$status= 2){
        $detail =  $this->where("id= $id")->find();

        if($detail['status'] != $status){
            if($detail['status'] == 1){
                return true;
            }else{
                $count = $this->where("status = 1")->count();
                if($count > 0){
                    return false;
                }else{
                    return true;
                }
            }
        }
        return true;
    }

    public function setSource($id,$key){
        $source = $key."_".$id;

        $data['source'] = $source;
        return $this->where('id='.$id)->save($data);
    }


    public function getUseCount($source,$type){
        if($type == 1 || $type == 2 || $type == 3){
            $model[1] = 'UserRedenvelope';
            $model[2] = 'UserInterestCoupon';
            $model[3] = 'UserCashCoupon';
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