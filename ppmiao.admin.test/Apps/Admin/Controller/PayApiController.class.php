<?php
namespace Admin\Controller;

/**
 * 第三方支付API接口控制器
 * @package Admin\Controller
 */
class PayApiController extends AdminController{

    /**
     * 检查订单状态(连连)
     */
    public function llCheckOrderStatus(){
        $order = 'RP20150529142924524810'; // 订单号
        echo check_order_pay_status_by_ll($order);
    }

    /**
     * 检查订单状态(盛付通)
     */
    public function sftCheckOrderStatus(){
        $batchNo = '20150615001';
        $detailId = '2015061500639';
        dump(check_order_pay_status_by_sft($batchNo, $detailId));
    }

}