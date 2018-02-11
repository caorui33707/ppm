<?php
/**
 * 会员中心-积分商城管理
 * @author      mozarlee
 * @time        2017-12-21 16:49:07
 * @created by  Sublime Text 3
 */

namespace Admin\Controller;
class MemberJfmallController extends AdminController {


    /**
     * 积分商城管理，商品列表
     * @return [type] [description]
     */
    public function index() {
        $name = I('get.name', '');
        $status = I('get.status', -1);

        list($goods, $show, $params) = D('MemberJfmall')->getResult($name, $status);

        $params['name'] = $name;
        $params['status'] = $status;

        $this->assign('goods', $goods);
        $this->assign('show', $show);
        $this->assign('params', $params);
        $this->display();
    }

    /**
     * 新增
     */
    public function add() {
        $this->display();
    }

    /**
     * 录入新增数据
     */
    public function add_insert() {
        $create = D('MemberJfmall')->add_insert();
        $this->ajaxReturn($create);
    }

    /**
     * 编辑
     * @return [type] [description]
     */
    public function edit() {
        $id = I('get.id', 0, 'int');
        $detail = D('MemberJfmall')->getOne($id);
        $this->assign('detail', $detail);
        $this->display();
    }

    /**
     * 保存更新数据
     * @return [type] [description]
     */
    public function edit_update() {
        $update = D('MemberJfmall')->edit_update();
        $this->ajaxReturn($update);
    }

    /**
     * 删除
     * @return [type] [description]
     */
    public function delete() {
        if(IS_AJAX) {
            $id = I('post.id', 0, 'int');
            $model = I('post.model');

            if(!$id) {
                $this->ajaxReturn(array('status' => 1, 'info' => "参数错误"));
            }

            $status = D($model)->where(array('id ='.$id))->getField('status');
            if($status == 1) {
                $this->ajaxReturn(array('status' => 1, 'info' => "请先下架"));
            }

            $dd['is_delete'] = 1;

            if(! D($model)->where("id = $id")->save($dd)) {
                $this->ajaxReturn(array('status' => 1, 'info' => "删除失败"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "删除成功"));
        } else {
            $this->ajaxReturn(array('status' => 1, 'info' => "非法访问"));
        }
    }




}
