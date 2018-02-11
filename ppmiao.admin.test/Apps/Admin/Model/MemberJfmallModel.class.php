<?php
/**
 * 会员中心，积分商城model
 * @author      mozarlee
 * @time        2017-12-21 17:06:40
 * @created by  Sublime Text 3
 */
namespace Admin\Model;
use Think\Model;
use Admin\Model\UploadModel;
class MemberJfmallModel extends Model {

	protected $connection = 'MEMBER_CONFIG';

	private $pageSize = 20; // 每页显示条数

	/**
	 * 获取数据/根据标题查询
	 * @param  string $title [description]
	 * @return [type]        [description]
	 */
	public function getResult($title = '', $status = -1) {
		$where = "is_delete = 0";

		if ($status >= 0) {
        	$where .= " and status = $status";
        }

        if ($title != '') {
            $where .= " and title like '%$title%'";
        }

        $page = I('get.p', 1, 'int'); // 页码
        $counts = $this->where($where)->order('status desc, orders desc, modify_time desc')->count();

        $Page = new \Think\Page($counts, $this->pageSize);
        $result = $this->where($where)->order('status desc, orders desc, modify_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();

        $params = array(
            'page' => $page,
        );
        return [$result, $show, $params];
	}

	/**
	 * 获取单条记录
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getOne($id) {
		$detail =  $this->where("id= $id")->find();
        return $detail;
	}

	/**
	 * 录入新增数据
	 * @return [type] [description]
	 */
	public function add_insert(){
		if (!$_FILES || !isset($_FILES['image'])) {
            return array('status'=>0,'info'=>'图片不能为空');
		}

		$image = $_FILES['image'];
        $ossPath = 'Uploads/focus/'.time().$image['name'];
        $file = $image['tmp_name'];

        $res = uploadToOss($ossPath,$file);
        if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
            return array('status'=>0,'info'=>'oss图片上传失败');
        }

		$image_url = $res['info']['url'];

        $data['title'] = I('post.title');
        $data['jf_val'] = I('post.jf_val');
        $data['status'] = I('post.status', 1);
        $data['image'] = $image_url;
        $data['url'] = I('post.url');
        $data['orders'] = I('post.orders', 0);

        $c = $this->add($data);

        if($c){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/MemberJfmall/index'];
        }else{
            return ['status'=>0,'info'=>'创建失败'];
        }
    }

    /**
     * 保存更新数据
     * @return [type] [description]
     */
    public function edit_update() {
    	$image_url = null;

    	if ($_FILES) {
    		$image = $_FILES['image'];
	        $ossPath = 'Uploads/focus/'.time().$image['name'];
	        $file = $image['tmp_name'];

	        $res = uploadToOss($ossPath,$file);
	        if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
	            return array('status'=>0,'info'=>'oss图片上传失败');
	        }

	        $image_url = $res['info']['url'];
    	} else {
            $image_url = I('post.old_image');
    	}


    	$id = I('post.id');

        $data['title'] = I('post.title');
        $data['jf_val'] = I('post.jf_val');
        $data['status'] = I('post.status', 1);
        $data['image'] = $image_url;
        $data['url'] = I('post.url');
        $data['orders'] = I('post.orders', 0);
        $u = $this->where("id = $id")->save($data);

        if($u){
            return ['status'=>1,'info'=>C('ADMIN_ROOT').'/MemberJfmall/index'];
        }else{
            return ['status'=>0,'info'=>'更新失败'];
        }
    }



}