<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {

    public function index(){
        //redirect(C('ADMIN_ROOT'));
    }
    
    
    //出账异步通知
    public function chargeoff_notify(){
    
        $order_no = I('order_no');
    
        if($order_no) {
    
            $data = M('projectChargeoffLog')->field('id,order_status')->where("order_no = '$order_no'")->find();
    
            if($data && $data['order_status'] == '0') {
                $row['plat_no'] = I('plat_no');
                $row['out_amt'] = I('out_amt');
                $row['platucst'] = I('platucst');
                $row['open_branch'] = I('open_branch');
                $row['withdraw_account'] = I('withdraw_account');
                $row['payee_name'] = I('payee_name');
                $row['pay_finish_date'] = I('pay_finish_date');
                $row['pay_finish_time'] = I('pay_finish_time');
                $row['order_status'] = I('order_status');
                $row['error_info'] = I('error_info');
                $row['error_no'] = I('error_no');
                $row['signdata'] = I('signdata');
                $row['host_req_serial_no'] = I('host_req_serial_no');
    
                M('projectChargeoffLog')->where('id='.$data['id'])->save($row);
    
                echo '{\"recode\":\"success\"}';
            }
    
        }
    }
    
    public function lists(){
        
        header("Content-type:text/html;charset=utf-8");
        
        $projectObj = M('Project');
        $conditions = 'status in(2,6) and is_delete=0';
        $list = $projectObj->where($conditions)->field('id,title,amount,able,new_preferential,project_group_id,status')->order('new_preferential desc')->select();
        
        echo "<table>";
    
        echo "<tr>
                <td width='15%'>标题</td>
                <td width='10%'>分组</td>
                <td width='10%'>融资金额</td>
                <td width='10%'>可售金额</td>
                <td width='15%'>状态</td>
                <td width='10%'>标签</td>
               </tr>";
    
        $n = 0;
        foreach ($list as $val){
            
            if($val['status'] == 1) {
                $str_status = '平台未审核';
            } else if($val['status'] == 2){
                $str_status = '在售中';
            } else{
                $str_status = '银行未审核';
            }
            
            $tg = $this->getTagName($val['new_preferential']);
            $group_name = '';
            if($val['project_group_id']>0) {
                $group_name = M('projectGroup')->where('id='.$val['project_group_id'])->getField('name');
            }
            echo "<tr bgcolor='".$tg[1]."'>
                <td width='5%'>{$val['title']}</td>
                    <td width='10%'>{$group_name}</td>
                    <td width='10%'>{$val['amount']}</td>
                    <td width='10%'>{$val['able']}</td>
                    <td width='8%'>{$str_status}</td>
                    <td width='10%'>{$tg[0]}</td>
                    </tr>";
        }
    
        echo "</table>";
        }
        
        
        
        private function getTagName($tagId) {
            //$s = '';            
            $s = [];            
            if($tagId == 0) {
                $s[] = '普通';
                $s[] = '#FDF5E6';
            }else if($tagId == 1) {
                $s[] = '新人特惠';
                $s[] ='#F7F7F7';
            }else if($tagId == 2) {
                $s[] = '爆款';
                $s[] ='#EEE9BF';
            }else if($tagId == 3) {
                $s[] = 'HOT';
                $s[] ='#EEB422';
            }else if($tagId == 6) {
                $s[] = '活动';
                $s[] ='#C9C9C9';
            }else if($tagId == 8) {
                $s[] = '私人专享';
                $s[] ='#EEC591';
            }else if($tagId == 9){
                $s[] = '月月加薪';
                $s[] ='#EED5B7';
            }
            return $s;
        }
}