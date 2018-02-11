<?php
namespace Admin\Controller;

/**
 * 担保机构
 * Class GuarantyController
 * @package Admin\Controller
 */
class GuarantyController extends AdminController {

    protected $pageSize = 15;

    /**
     * 担保机构管理
     */
    public function list(){
        $counts = M('Financing')->count();
        $Page = new \Think\Page($counts, $this->pageSize);
        $list = M('Financing')->where(['type'=>2])->order("id desc")->limit($Page->firstRow.",".$Page->listRows)->select();
        $res = array();
        foreach ($list as $val) {
            $val['project_status_2'] = M('Project')->where(array('guarantee_status'=>1,'gid'=>$val['id'],'is_delete'=>0))->count();// 担保中
            $val['project_status_5'] = M('Project')->where(array('repay_review'=>2,'gid'=>$val['id'],'is_delete'=>0))->count();// 已还款

            $val['project_status_6'] = M('Project')->where(array('guarantee_status'=>2,'gid'=>$val['id'],'is_delete'=>0))->count();// 已代偿

            $companyUser = M('companyUser')->where(['id'=>$val['company_user_id']])->find();
            if($companyUser){
                $val['status'] =$companyUser['status'];
            }else{
                $val['status'] = 0;
            }

            $res[] = $val;
        }
        $this->assign('show',$Page->show());
        $this->assign("list",$res);
        $this->display();
    }

    /**
     * 担保机构编辑
     */
    public function edit(){
        $financingObj = M('Financing');
        if(IS_POST){//添加/编辑
            $id = I('post.id',0,'int');
            $name = trim(I('post.name','','strip_tags'));
            // $intro = trim(I("post.intro",'','strip_tags'));
            $legal_person = trim(I("post.legal_person",'','strip_tags'));
            $license = trim(I("post.license",'','strip_tags'));
            $address = trim(I("post.address",'','strip_tags'));

            $platform_account = trim(I("post.platform_account",'','strip_tags'));
            $bank_id = trim(I("post.bank_id",'0','strip_tags'));
            $bank_card_no = trim(I("post.bank_card_no",'','strip_tags'));
            $acct_name = trim(I("post.acct_name",'','strip_tags'));
            $bank_code = trim(I("post.bank_code",'','strip_tags'));

            if(!$name) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请输入担保机构'));
            }
            // if(!$intro) {
            //     $this->ajaxReturn(array('status'=>0,'info'=>'请填写担保机构简称'));
            // }
            if(!$legal_person) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写法人'));
            }
            if(!$license) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写营业执照'));
            }
            if(!$address) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写担保机构所在地'));
            }
            if(!$platform_account) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写平台客户号'));
            }
            if(!$bank_id) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择开户行'));
            }
            if(!$bank_code) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写银行编号'));
            }
            if(!$bank_card_no) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写银行卡号'));
            }
            if(!$acct_name) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写绑卡人姓名'));
            }

            $time = date("Y-m-d H:i:s");

            if($id){//编辑

                $old_financing = $financingObj->where(array('id'=>$id, 'type' => 2))->find();
                if(!$old_financing) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'更新担保机构失败,重新更新 id,不存在'));
                }

                if($financingObj->where("name =  '$name' and type = 2 and id !=".$id)->count()) {
                    $this->ajaxReturn(array('status'=>0,'info'=>"担保机构 `$name` 已经存在"));
                }

                $row = array(
                    'edit_time'=>$time,
                    'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                    'name'=>$name,
                    'intro'=>$name,
                    'legal_person'=>$legal_person,
                    'license'=>$license,
                    'address'=>$address,
                    'platform_account'=>$platform_account,
                    'bank_card_no'=>$bank_card_no,
                    'bank_id'=>$bank_id,
                    'acct_name'=>$acct_name,
                    'bank_code'=>$bank_code,
                    'type' => 2
                );

                $update_status = $financingObj->where(array('id'=>$id, 'type' => 2))->save($row);

                if ($update_status) {
                    $this->ajaxReturn(array('status'=>2,'info'=>C('ADMIN_ROOT').'/Guaranty/list'));
                } else {
                    $this->ajaxReturn(array('status'=>0,'info'=>'更新担保机构失败,重新更新'));
                }

            } else {//添加

                $financing = $financingObj->where(array('name'=>$name, 'type' => 2))->find();
                if($financing){
                    $this->ajaxReturn(array('status'=>0,'info'=>'该担保机构已经录入了'));
                }
                $row = array(
                    'name'=>$name,
                    'intro'=>$name,
                    'legal_person'=>$legal_person,
                    'license'=>$license,
                    'address'=>$address,
                    'add_time'=>$time,
                    'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                    'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                    'edit_time'=>$time,
                    'platform_account'=>$platform_account,
                    'bank_card_no'=>$bank_card_no,
                    'bank_id'=>$bank_id,
                    'acct_name'=>$acct_name,
                    'bank_code'=>$bank_code,
                    'type' => 2
                );

                $add_status = $financingObj->add($row);
                if($add_status){
                    $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/Guaranty/list'));
                }else{
                    $this->ajaxReturn(array('status'=>1,'info'=>'录入担保机构失败'));
                }
            }
        } else {
            $id = I('get.id',0,'int');
            if($id){
                $info = $financingObj->where(array('id'=>$id, 'type' => 2))->find();
            }
            $bank_list = M('baseBanks')->field('bank_no,bank_name')->select();
            $this->assign("id",$id);
            $this->assign("detail",$info);
            $this->assign("bank_list",$bank_list);
            $this->display();
        }
    }

    /**
     * 担保机构删除
     */
    public function del(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        if(M('Financing')->where(array('id'=>$id, 'type' => 2))->count()) {
            if(M('Project')->where(array('gid'=>$id))->count()) {
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败，该担保机构已经有产品在卖了'));
            }
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败，该记录不存在'));
        }

        if(!M('Financing')->where(array('id'=>$id, 'type' => 2))->delete()) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
        }
        $this->ajaxReturn(array('status'=>1));
    }

}