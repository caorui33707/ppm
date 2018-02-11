<?php
namespace Admin\Controller;

/**
 * 订单...
 * @package Admin\Controller
 */
class RechargeLogController extends AdminController{
    
    private $PageSize = 10;
    
    public function exception_list(){
        
        if(!IS_POST){
            
            $page = I('get.p', 1, 'int'); // 页码
            $start_time = I('get.st', '', 'strip_tags');
            $end_time = I('get.et', '', 'strip_tags');
            $status = I('get.status', -1, 'strip_tags');
            
            $params = array(
                'page' => $page,
                'start_time' => str_replace('|', ' ', $start_time),
                'end_time' => str_replace('|', ' ', $end_time),
                'status' => $status,
            );
            
            $this->assign('params', $params);
            
            $cond = ' 1=1 ';
            
            if($start_time) $cond .= " and create_time>=".strtotime($start_time);
            if($end_time) $cond .= " and create_time<=".strtotime($end_time);
            if($status !=-1) $cond .= " and status=$status";
                        
            $cnt = M('RechargeExceptionLog')->where($cond)->count();
            $Page = new \Think\Page($cnt, $this->PageSize);
            $show = $Page->show();
            $list = M('RechargeExceptionLog')->where($cond)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            
            $res = array();
            
            if(!empty($list)){
                
                foreach ($list as $val) {
                    
                    $user = M('User')->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                    
                    $recharge = M('RechargeLog')->field('amount,device_type')->where(array('recharge_no'=>$val['recharge_no'],'status'=>2))->find();
                    
                    $row['red_amount'] = 0;
                    
                    if($val['red_id'] > 0) {
                       $row['red_amount'] =  M('UserRedenvelope')->where(array('id'=>$val['red_id'],'status'=>1))->getField('amount');
                    }
                    
                    $row['user_name'] = $user['username'];
                    $row['real_name'] = $user['real_name'];
                    $row['recharge_no'] = $val['recharge_no'];
                    $row['amount'] = $recharge['amount'];
                    $row['device_type'] = $this->getDeviceName($recharge['device_type']);
                    //$row['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
                    
                    $row['create_time'] = $val['create_time'];
                    
                    $row['handle_time'] = '';
                    if($val['handle_time']) {
                        $row['handle_time'] = date("Y-m-d H:i:s",$val['handle_time']);
                    }
                    
                    if($val['status'] == 0) {
                        $row['status'] = '未处理';
                    } else if($val['status'] == 1) {
                        $row['status'] = '处理成功';
                    } else {
                        $row['status'] = '处理失败';
                    }
                    
                    $row['id'] = $val['id'];
                    $row['error_info'] = $val['error_info'];
                    
                    $res[] = $row;
                }
                
            }
            
            $this->assign('list', $res);
            $this->assign('show', $show);
            $this->display('index');
        } else {
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $status = trim(I('post.status', -1, 'strip_tags'));
            
            if($start_time) $quest .= '/st/'.str_replace(' ', '|', $start_time);
            if($end_time) $quest .= '/et/'.str_replace(' ', '|', $end_time);
            
            if($status != -1) {
                $quest .= '/status/'.$status;
            }
            
            redirect(C('ADMIN_ROOT') . '/rechargeLog/exception_list'.$quest);
        }
        
        
    }
    
    /**1:ios 2:android 3:h5 4：pc
     * 取设备类型
     * @param unknown $v
     */
    private function getDeviceName($v) {
        $res = '';
        if($v == 1 ) {
            $res = 'ios';
        } else if($v == 2 ){
            $res = 'android';
        }else if($v == 3) {
            $res = 'h5';
        } else if($v == 4) {
            $res = 'pc';
        } else {
            $res = "未知";
        }
        return $res;
    }
    
}