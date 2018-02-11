<?php
namespace Admin\Controller;

class Common2Controller extends \Think\Controller {
    
    /**
    * 查合同票号
    * @date: 2017-5-26 下午5:52:26
    * @author: hui.xu
    * @param: ticket_number
    * @return:
    */
    public function checkTicketNumber(){
        $ticket_number = I('post.ticket_number','','strip_tags');
        if (!$ticket_number) {
            $this->ajaxReturn(array('status'=>0,'info'=>'票号不能为空'));
        }
        if(M('Contract')->where(array('ticket_number'=>$ticket_number))->getField('id')) {
            $this->ajaxReturn(array('status'=>0,'info'=>'票号：`'.$ticket_number.'`已经存在'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
    
    
    
    /**
     * 取合同利率
     * @date: 2017-2-6 下午5:29:44
     * @author: hui.xu
     * @param: contract_no
     * @return:
     */
    public function getContractInfo(){
        if(!IS_POST || !IS_AJAX) exit;
        $contract_no = I('post.contract_no', '', 'strip_tags');
        if(!$contract_no)$this->ajaxReturn(array('status'=>1,'info'=>''));
        $contract_info = M('Contract')->field('interest,end_time,acceptor,draft_type,accepting_institution,guaranty_type,guaranty_institution,gid')->where(array('name'=>$contract_no))->find();
        $contract_info['end_time'] = date('Y-m-d H:i:s',$contract_info['end_time']);
        $this->ajaxReturn(array('status'=>1,'info'=>$contract_info));
    }
    
    /**
     * 产品分组信息
     * @date: 2017-2-6 下午5:29:44
     * @author: hui.xu
     * @param: tga_id
     * @return:
     */
    public function getProjectGroupInfo(){
        if(!IS_POST || !IS_AJAX) exit;
        $tag_id = I('post.tga_id', '', 'strip_tags');
        //if(!$contract_no)$this->ajaxReturn(array('status'=>1,'info'=>''));
        
        if($tag_id < 0) {
            $projectGroup = M('projectGroup')->field('id,name')->select();
        }else{
            $projectGroup = M('projectGroup')->field('id,name')->where(array('tag'=>$tag_id))->select();
        }
        $this->ajaxReturn(array('status'=>1,'info'=>$projectGroup));
    }
    
    
    /**
     * 查询短信剩余量接口
     */
    public function getSmsQueryBalance(){
        if(!IS_POST || !IS_AJAX) exit;
        $errorMsg = array(
            101 => '无此用户',
            102 => '密码错',
            103 => '查询过快（30秒查询一次）',
        );
        $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/QueryBalance?account='.C('SMS_INTDERFACE.account').'&pswd='.C('SMS_INTDERFACE.pswd'));
        $arr = explode("\n", $smsData);
        foreach($arr as $key => $val){
            $arr[$key] = explode(',', $val);
        }
        if($arr[0][1] != 0) $this->ajaxReturn(array('status'=>0, 'info'=>$errorMsg[$arr[0][1]]));
        $this->ajaxReturn(array('status'=>0,'info'=>$arr[1][1]));
    }

    /**
     * 配置Jquery Autocomplete的产品名称异步查询
     */
    public function autoproject(){
        if(!IS_AJAX) exit;

        $query = I('get.query', '', 'strip_tags');
        $tag = I('get.tag', '', 'strip_tags');
        $projectObj = M("Project");
        $rows = array(
            'query' => $query,
            'suggestions' => array(),
        );
        $list = $projectObj->field('id,title,amount')->where("title like '%".$query."%' and is_delete=0")->limit(10)->select();
        foreach($list as $key => $val){
            $tmp = array(
                'value' => $val['title'],
                'data' => ($tag ? $val[$tag] : $val['amount']),
            );
            $rows['suggestions'][] = $tmp;
        }
        echo json_encode($rows);
    }

    /**
     * 调用百度api接口
     */
    public function baiduApi(){
        require_once('include/baidu_api.php');
        $api = new \baidu_api();
        $act = I('post.act', '', 'strip_tags');
        switch($act){
            case 'idcard': // 身份证归属地查询
                $idcard = trim(I('post.idcard', '', 'strip_tags'));
                $ret = $api->idservice($idcard);
                if($ret['errNum'] == 0){
                    $this->ajaxReturn(array('status'=>1,'info'=>$ret['retData']));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret['retMsg'].'('.$ret['errNum'].')'));
                }
                break;
            case 'mobilephoneservice': // 手机号码归属地查询
                $phone = trim(I('post.phone', '', 'strip_tags'));
                $ret = $api->mobilephoneservice($phone);
                if($ret['errNum'] == 0){
                    $this->ajaxReturn(array('status'=>1,'info'=>$ret['retData']));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret['retMsg'].'('.$ret['errNum'].')'));
                }
                break;
            case 'cardinfo': // 银行卡归属地
                $card = trim(I('post.card', '', 'strip_tags'));
                $ret = $api->cardinfo($card);
                if($ret['status'] == 1){
                    $this->ajaxReturn(array('status'=>1,'info'=>$ret['data']));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>$ret['retMsg'].'('.$ret['errNum'].')'));
                }
                break;
        }
    }

    /**
     * 担保方信息
     * @date: 2018-1-25
     * @author: cr
     * @return:
     */
    public function getGuarantyInfo(){
        if(!IS_POST || !IS_AJAX) exit;
        $guaranty_institution = I('post.guaranty_institution', '', 'strip_tags');

        //if(!$contract_no)$this->ajaxReturn(array('status'=>1,'info'=>''));

        $array = array(
            'type'=>2,
            'name'=>$guaranty_institution,
        );

        $financing =  M('financing')->where($array)->find();
        if(!$financing) {
            $this->ajaxReturn(array('status' => 0, 'info' => '担保机构不存在'));
        }

        $arrayUser = array(
            'type'=>2,
            'id'=>$financing['company_user_id'],
            'stattus'=>3,
        );

        $company_user = M('company_user')->where($arrayUser)->find();
        if($company_user){
            $this->ajaxReturn(array('status' => 1, 'info' => $financing));
        }else{
            $this->ajaxReturn(array('status' => 0, 'info' => '担保机构审核中'));
        }
        return;
    }

  
}