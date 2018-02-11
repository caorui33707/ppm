<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class MemberTasksModel extends Model {

//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';

    protected $connection = 'MEMBER_CONFIG';
    public function getResult($category = null){

        $where = [
            'is_delete' => 0,
        ];
        if($category){
            $where['category'] = $category;
        }
        $page = I('get.p', 1, 'int'); // 页码
        $count = 20; // 每页显示条数
        $counts = $this->where($where)->order('category desc')->count();


        $Page = new \Think\Page($counts, $count);
        $result = $this->where($where)->order('category desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $params = array(
            'page' => $page,
        );
        return [$result,$show,$params];
    }

    public function getOne($id){
        return $this->where("id = ".$id)->find();
    }

    public function store(){
        $data['category'] = I('post.category');
        $data['repeat'] = I('post.repeat');
        if($data['category'] == 1 && $data['repeat'] == 1){
            return ['status'=>0,'info'=>'创建失败,日常任务无法重复'];
            exit;
        }
        $data['title'] = I('post.title');
        $data['description'] = I('post.description');
        $data['sub_category'] = I('post.sub_category');
        $data['jf_val'] = I('post.jf_val');
        $data['redirect'] = I('post.redirect');
        $data['ext'] = $this->getExt($data['sub_category']);
        $c = $this->add($data);
        if($c){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/tasks'];
        }else{
            return ['status'=>0,'info'=>'创建失败'];
        }
    }

    public function update(){

        $id = I('post.id');
        $sub_category = I('post.sub_category');

        $data['title'] = I('post.title');
        $data['description'] = I('post.description');
        $data['jf_val'] = I('post.jf_val');
        $data['redirect'] = I('post.redirect');
        $data['ext'] = $this->getExt($sub_category);
        $u = $this->where("id = $id")->save($data);
        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/tasks'];
        }else{
            return ['status'=>0,'info'=>'更新失败'];
        }
    }

    public function getExt($sub){
        $ext = [];
        switch($sub){
            case 'RECHARGE':
                $ext['recharge_amount'] = I('post.recharge_amount');
                $ext['recharge_days'] = I('post.recharge_days');
                $ext['recharge_first'] = I('post.recharge_first');
                break;
            case 'BUY':
                $ext['buy_amount'] = I('post.buy_amount');
                $ext['buy_day'] = I('post.buy_day');
                $ext['buy_type'] = [];
                if(is_array(I('post.buy_type'))){
                    $ext['buy_type'] = I('post.buy_type');
                }
                break;
            case 'INVITE_TOTAL':
                $ext['invite_type'] = I('post.invite_type');
                $ext['invite_people_number'] = I('post.invite_people_number');
                $ext['invite_buy_total_amount'] = I('post.invite_buy_total_amount');
                break;
            case 'SIGN':
                $ext['sign_days'] = I('post.sign_days');
                break;
        }





        return json_encode($ext);
    }
}