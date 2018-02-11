<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class MemberCommoditiesModel extends Model {
//    protected $connection = 'mysql://ppmiao_coin:epudP)fH62NNXp9Xt3taLTUJ@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_coin';
//    protected $dbName = 'ppmiao_dev_2017';


    protected $connection = 'MEMBER_CONFIG';
    public function getResult($title = 0){
        if($title != '0'){
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
        $result = $this->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
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

            $data['name'] = I('post.name');
            $data['title'] = I('post.title');
            $data['type'] = I('post.type');
            $data['category'] = 1;
            $data['image'] = $info[0];

            if($data['type'] == 1 || $data['type'] == 2){
                $data['amount'] = I('post.amount');
                $data['valid_days'] = I('post.valid_days');
                $data['min_invest_days'] = I('post.min_invest_days');
                $data['min_invest_amount'] = I('post.min_invest_amount');
                $data['start_time'] = I('post.start_time');
                if($data['start_time'] == ''){
                    unset($data['start_time']);
                }
                $data['project_type'] = implode(':', I('post.project_type'));
            }






            $c = $this->add($data);
            if($c){
                return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/commodities'];
            }else{
                return ['status'=>0,'info'=>'创建失败'];
            }
        }
//        var_dump($info);
    }
    public function update(){


        $id = I('post.id');
        $commodity = $this->getOne($id);
        if($_FILES){
            $info = UploadModel::upload();
            if($info == 0){
                return array('status'=>0,'info'=>'oss图片上传失败');
            }
            $data['image'] = $info[0];
        }

        $data['name'] = I('post.name');
        $data['title'] = I('post.title');
        $data['category'] = 1;

        if($commodity['type'] == 1 || $commodity['type'] == 2){
            $data['amount'] = I('post.amount');
            $data['valid_days'] = I('post.valid_days');
            $data['min_invest_days'] = I('post.min_invest_days');
            $data['min_invest_amount'] = I('post.min_invest_amount');
            $data['start_time'] = I('post.start_time');
            if($data['start_time'] == ''){
                unset($data['start_time']);
            }
            $data['project_type'] = implode(':', I('post.project_type'));
        }


        $u = $this->where("id = $id")->save($data);
        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/member/commodities'];
        }else{
            return ['status'=>0,'info'=>'创建失败'];
        }
    }

    public function search($type = 0){
        if($type != 0){
            $where['type'] = $type;
        }
        $where['is_delete'] = 0;
        $result = $this->where($where)->order('add_time DESC')->select();

        $result[0]['project_type'] = $this->type_to_name($result[0]['project_type']);
        return $result;
    }

    public function getOne($id){

        $detail =  $this->where("id= $id")->find();
        $detail['project_type'] = explode(':',$detail['project_type']);
        return $detail;
    }

    /**
     *
     */
    public function type_to_name($type){
        $tab_arr = [0=>'普通',1=>'新人特惠',2=>'爆款',6=>'活动',8=>'私人专享'];
        $type_arr = explode(':',$type);
        $type_name = [];
        foreach($type_arr as $val){
            $type_name[] = $tab_arr[$val];
        }
        return implode(',',$type_name);


    }

    public function deleteAble($id){
        $detail = $this->getOne($id);
        $b = D('MemberBirthday')->where(" status = 1 and is_delete = 0 and commodity_id = ".$detail['id'])->find();
        $m = D('MemberMonthly')->where(" status = 1 and is_delete = 0 and commodity_id = ".$detail['id'])->find();
        $s = D('MemberStore')->where(" status = 1 and is_delete = 0 and commodity_id = ".$detail['id'])->find();

        if($b || $m || $s){
            return false;
        }else{
            return true;
        }

    }


    public function getUse($source,$model){

        $low = $this->humpToLine($model);


        if(is_numeric($source)){
            $m = explode('_',$low);

            $where = [
                $m['2'].'_id' => $source,
            ];
            $page = I('get.p', 1, 'int'); // 页码
            $count = 20; // 每页显示条数
            $counts = D($model)->where($where)->count();


            $Page = new \Think\Page($counts, $count);
            $result =  D($model)
                ->where($where)
                ->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $show = $Page->show();
            $params = array(
                'page' => $page,
            );
            foreach($result as $key=>$val){
                $userinfo = $this->getUserInfo($val['user_id']);
                $result[$key]['real_name'] = $userinfo['real_name'];
                $result[$key]['username'] = $userinfo['username'];
            }


        }else{
            $where = [
                'source' => $source
            ];
            $page = I('get.p', 1, 'int'); // 页码
            $count = 20; // 每页显示条数
            $counts = M($model)->where($where)->count();


            $Page = new \Think\Page($counts, $count);
            $result =  M($model)
                ->field($low.'.*,s_user.real_name,s_user.username')
                ->where($where)
                ->join('s_user on s_user.id = '.$low.'.user_id')
                ->order($low.'.id desc')->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
            $show = $Page->show();
            $params = array(
                'page' => $page,
            );
        }


        return [$result,$show,$params];
    }
    /*
     * 驼峰转下划线
     */
    private function humpToLine($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);
        return 's'.$str;
    }

    private function getUserInfo($id){
        return  M('User')->find($id);
    }

}