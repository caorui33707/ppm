<?php
namespace Home\Controller;
use Think\Controller;
use Think\Page;

class ProductController extends BaseController{

    /**
     * 产品首页
     */
    public function index(){

        $user_info = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!IS_POST){
            $type = I('get.type', 1, 'int'); // 大类分类
            $page = I('get.p', 1, 'int'); // 分页页码
            $params = array(
                'type' => $type,
                'page' => $page,
            );
            $this->assign('params', $params);
            if($page > 10){
                redirect(C('WEB_ROOT').'/product/type/'.$type.'/p/10');exit;
            }
            $count = 5;
            $time = time();
            $isScroll = 0; // 页面是否滚动(分页跳转时滚动)
            $pos = strpos($_SERVER['REQUEST_URI'], '/product/type/');
            if($pos !== false) $isScroll = 1;

            $projectObj = M('Project');
            
            $projectModelSectionObj = M('ProjectModelSection');
            $projectModelIncrementObj = M('ProjectModelIncrement');

            // 获取列表页推荐产品信息(新手特惠)<没有购买过产品的用户才能看见>
            if(!$user_info['token']){ // 未登录用户直接显示特殊标

                $specialInfo = $projectObj->where("new_preferential=1 and status=2 and is_delete=0 and (start_time<='".date('Y-m-d H:i:s', $time)."' or is_countdown=1)")->find();
                if($specialInfo) $specialInfo['days'] = count_days($specialInfo['end_time'], date('Y-m-d', $time));
            }else{
                // 检查用户是否购买过产品
                $userDueDetailObj = M("UserDueDetail");
                if(!$userDueDetailObj->where(array('user_id'=>$user_info['id']))->getField('id')) {
                    $specialInfo = $projectObj->where("new_preferential=1 and status=2 and is_delete=0 and (start_time<='".date('Y-m-d H:i:s', $time)."' or is_countdown=1)")->find();
                    if($specialInfo) $specialInfo['days'] = count_days($specialInfo['end_time'], date('Y-m-d', $time));
                    $this->assign('special_info', $specialInfo);
                }else{ // 不是新手
                    $walletInfo['title'] = '票票喵钱包';
                    // 获取石头钱包当天年化利率
                    $userWalletAnnualizedRateObj = M("UserWalletAnnualizedRate");
                    $rate = $userWalletAnnualizedRateObj->where(array('add_time'=>date('Y-m-d', time())))->getField('rate');
                    if(!$rate) $rate = 7.13; // 当天没有年化则使用默认值7.13
                    $walletInfo['rate'] = $rate;
                    $this->assign('wallet_info', $walletInfo);
                }
            }

            if(!$specialInfo['id']){
                $conditions = 'term_type='.$type.' and status>1 and is_delete=0 and (start_time<=\''.date('Y-m-d H:i:s', $time).'\' or is_countdown=1)';
            }else{
                $conditions = 'id='.$specialInfo['id'].' and status>1 and term_type='.$type.' and is_delete=0 and (start_time<=\''.date('Y-m-d H:i:s', $time).'\' or is_countdown=1)';
            }

            if($specialInfo) {
                $specialInfo['id'] = st_encrypt($specialInfo['id'], C('PRODUCT_KEY'));
                $this->assign('special_info', $specialInfo);
            }

            // 分页
            $counts = $projectObj->where($conditions)->count();
            if($counts > 50) $counts = 50;
            $Page = new \Think\PageCustomer($counts, $count); // 自定义分页类
            $show = $Page->show(C('WEB_ROOT').'/product/type/'.$type.'/p/');
            // 获取产品列表
            $fields = 'id,title,type,able,duration,amount,user_interest,money_min,percent,status,term_type,start_time,end_time,lastmonths,new_preferential';
            $list = $projectObj->field($fields)->where($conditions)->order('status asc,global_top desc,local_top desc,user_interest desc,duration asc,add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['id'] = st_encrypt($val['id'], C('PRODUCT_KEY'));
                if($val['status'] != 2){ // 非在售标
                    $list[$key]['days'] = count_days($val['end_time'], $val['start_time']);
                }else{
                    $list[$key]['days'] = count_days($val['end_time'], date('Y-m-d', time()));
                    if($list[$key]['days'] < 0) $list[$key]['days'] = 0;
                }
                $list[$key]['ts'] = (strtotime($val['start_time']) - $time > 0 ? strtotime($val['start_time']) - $time : 0);
                if($val['type'] == 148){ // 博息宝产品获取最大年化率
                    $list[$key]['maxInterest'] = $projectModelSectionObj->where(array('project_id'=>$val['id']))->getField('max_interest');
                }else if($val['type'] == 149){ // 增值产品
                    $list[$key]['maxInterest'] = $projectModelIncrementObj->where(array('project_id'=>$val['id']))->getField('max_interest');
                }
            }

            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('is_scroll', $isScroll); // 如果是分页跳转则滚动页面
            $this->assign('meta_title', '理财产品');
            $this->assign("path",'product');
            $this->assign('meta_keywords', '');
            $this->assign('meta_description', '');
            $this->display('PMProductList');
        }else{

        }
    }

    /**
     * 产品详细
     */
    public function detail(){
        if(!IS_POST){
            $data = filterSpecialChar(I('get.data', '', 'strip_tags'));
            $id = st_decrypt($data, C('PRODUCT_KEY'));
            if(!is_numeric($id)) { // 解密出来非数字则为错误页面
                $this->display('PublicNew:404');exit;
            }
            $time = time();

            if($id === 0){ // 钱包详细页
                redirect(C('WEB_ROOT').'/product/wallet.html');exit;
            }

            $this->assign('data', $data);
            $projectObj = M("Project");
            $investmentDetailObj = M("InvestmentDetail");
            $detail = $projectObj->where("id=".$id." and is_delete=0 and (start_time<='".date('Y-m-d H:i:s', $time)."' or is_countdown=1)")->find();
            $detail['total_due_num'] = $investmentDetailObj->where(array('project_id'=>$id,'status'=>2))->count();
            if(!$detail){
                header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
                $this->display("PublicNew:404");exit;
            }
            if($detail['status'] != 2){
                $detail['days'] = count_days($detail['end_time'], $detail['start_time']);
            }else{
                $detail['days'] = count_days($detail['end_time'], date('Y-m-d', time()));
                if($detail['days'] < 0) $detail['days'] = 0;
            }
            $detail['ts'] = (strtotime($detail['start_time']) - $time > 0 ? strtotime($detail['start_time']) - $time : 0);
            if($detail['type'] == 109 || $detail['type'] == 110) {
                $projectModelFundObj = M("ProjectModelFund");
                $detailEx = $projectModelFundObj->where(array('project_id'=>$detail['id']))->find();
                if($detailEx['description']) $detail['description'] = format_project_descr($detailEx['description']);
                $this->assign('detail', $detail);
                $this->assign('detail_ex', $detailEx);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                $this->display('detail_fund');
            }else if($detail['type'] == 104){
                $projectModelFundObj = M("ProjectModelFund");
                $detailEx = $projectModelFundObj->where(array('project_id'=>$detail['id']))->find();
                if($detailEx['description']) $detail['description'] = format_project_descr($detailEx['description']);
                $this->assign('detail', $detail);
                $this->assign('detail_ex', $detailEx);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                $this->display('detail_dxg');
            }else if($detail['type'] == 139) { // 股权众筹
                $projectModelEquityObj = M("ProjectModelEquity");
                $detailEx = $projectModelEquityObj->where(array('project_id' => $detail['id']))->find();
                if ($detailEx['description']) $detail['description'] = format_project_descr($detailEx['description']);
                $this->assign('detail', $detail);
                $this->assign('detail_ex', $detailEx);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                $this->display('detail_equity');
            }else if($detail['type'] == 148) { // 博息宝产品
                $projectModelSectionObj = M("ProjectModelSection");
                $detailEx = $projectModelSectionObj->where(array('project_id' => $detail['id']))->find();
                $description = str_replace('#chart#', '</p><div id="container" style="width:95%;text-align:center;"></div><p>', $detailEx['description']);
                if ($detailEx['description']) $detail['description'] = format_project_descr($description);

                $this->assign('detail', $detail);
                $this->assign('detail_ex', $detailEx);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                $this->display('detail_section');
            }else if($detail['type'] == 149){ // 增值产品
                $projectModelIncrementObj = M("ProjectModelIncrement");
                $detailEx = $projectModelIncrementObj->where(array('project_id' => $detail['id']))->find();
                if ($detailEx['description']) $detail['description'] = format_project_descr($detailEx['description']);

                $this->assign('detail', $detail);
                $this->assign('detail_ex', $detailEx);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                $this->display('detail_increment');
            }else{
                if($detail['invest_direction_descr']) $detail['description'] = format_project_descr($detail['invest_direction_descr']);
                if($detail['invest_direction_image']) $detail['images'] = format_project_image($detail['invest_direction_image']);
                if($detail['repayment_source_descr']) $detail['bank_image'] = format_project_image($detail['repayment_source_descr']);
                if($detail['repayment_source_image']) $detail['elec_bank_image'] = format_project_image($detail['repayment_source_image']);
                $this->assign('detail', $detail);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                $this->display('PMProductDetails');
            }
        }else{
            $data = filterSpecialChar(I('post.target', '', 'strip_tags'));
            $id = st_decrypt($data, C('PRODUCT_KEY'));
            $money = I('post.money', 0, 'int');
            $min_money = I('post.minmoney', 0, 'int');
            if($money > 0){
                if($money%$min_money != 0){
                    $this->error('购买金额必须是'.$min_money.'的整数倍');exit;
                }
                $rows = array(
                    'id' => $id,
                    'money' => $money,
                );
                $paydata = st_encrypt(json_encode($rows), C('PRODUCT_KEY'));
                redirect(C('WEB_ROOT').'/product/pay/'.$paydata);
            }else{
                $this->error('请输入正确的金额');exit;
            }
        }
    }

    /**
     * 产品购买支付页面
     */
    public function pay(){
        $user_info = checkUserLoginStatus(StorageData(ONLINE_SESSION));
        if(!IS_POST){
            $data = filterSpecialChar(I('get.data', '', 'strip_tags'));
            $this->assign('data', $data);
            $rowsData = json_decode(st_decrypt($data, C('PRODUCT_KEY')));
            $id = $rowsData->id;
            $money = $rowsData->money;
            $act = I('get.act', '', 'strip_tags');

            if(!$money){
                $this->error('起购金额必须大于0');exit;
            }
            if($id === 0){ // 固定为钱包充值
                if(!$user_info['token']){
                    $this->error("您未登录,无法充值钱包");exit;
                }
                $detail['id'] = 0;
                $detail['title'] = "石头钱包";
                $this->assign('detail', $detail);
                $this->assign('money', $money);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                if($_SESSION[LLPAY_SESSION]){
                    $this->assign('edit_data', $_SESSION[LLPAY_SESSION]);
                }
                if($act != 'new'){ // 不是新卡支付
                    // 获取已绑定银行
                    $ret = post(C('API').C('interface.user_bank'),array('token'=>$user_info['token'])); // 请求用户银行卡列表
                    if($ret->code == 0){
                        $bankList = objectToArray($ret->result);
                        foreach($bankList as $key => $val){
                            $bankList[$key]['shortBankCardNo'] = substr($val['bankCardNo'], strlen($val['bankCardNo']) - 4);
                        }
                        $this->assign('bank_list', $bankList);
                    }
                    $this->display('buy_second');
                }else{ // 新卡支付
                    $params = array(
                        'iccard' => hide_whole_idcard($user_info['cardNo']),
                        'name' => hide_whole_name($user_info['realName']),
                        'mobile' => hide_whole_phone($user_info['username']),
                    );
                    $this->assign('params', $params);
                    $this->display('buy_new');
                }
            }else{
                $projectObj = M("Project");
                $detail = $projectObj->where(array('id'=>$id,'is_delete'=>0))->find();
                if(!$detail){
                    header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
                    $this->display("PublicNew:404");
                }
                if($detail['status'] != 2 or strtotime($detail['start_time']) > time()){
                    $this->error('产品未开售或已售罄');exit;
                }
                if($detail['able'] < $money){
                    $this->error('产品剩余金额不足'.$money.'元,无法购买');exit;
                }
                $this->assign('detail', $detail);
                $this->assign('money', $money);
                $this->assign('meta_title', $detail['title']);
                $this->assign('meta_keywords', '');
                $this->assign('meta_description', '');
                if($_SESSION[LLPAY_SESSION]){
                    $this->assign('edit_data', $_SESSION[LLPAY_SESSION]);
                }
                if(!$user_info['token']){ // 未登录
                    $this->display('buy_first');
                }else{ // 已登录
                    if($act != 'new'){ // 不是新卡支付
                        // 获取已绑定银行
                        $ret = post(C('API').C('interface.user_bank'),array('token'=>$user_info['token'])); // 请求用户银行卡列表
                        if($ret->code == 0){
                            $bankList = objectToArray($ret->result);
                            foreach($bankList as $key => $val){
                                $bankList[$key]['shortBankCardNo'] = substr($val['bankCardNo'], strlen($val['bankCardNo']) - 4);
                            }
                            $this->assign('bank_list', $bankList);
                        }
                        $this->display('buy_second');
                    }else{ // 新卡支付
                        $params = array(
                            'iccard' => hide_whole_idcard($user_info['cardNo']),
                            'name' => hide_whole_name($user_info['realName']),
                            'mobile' => hide_whole_phone($user_info['username']),
                        );
                        $this->assign('params', $params);
                        $this->display('buy_new');
                    }
                }
            }
        }else{
            require_once ("include/llpay/lib/llpay_submit.class.php");
            $data = filterSpecialChar(I('post.data', '', 'strip_tags'));
            $type = I('post.type', 1, 'int'); // 支付提交类型(1:第一次支付/2:已登录用户选择银行卡支付/3:已登录用户新卡支付)
            $rowsData = json_decode(st_decrypt($data, C('PRODUCT_KEY')));
            $money = $rowsData->money; // 购买金额
            $productId = $rowsData->id; // 产品ID
            switch($type){ // 支付提交类型
                case 1: //第一次支付
                    $bankcard = filterSpecialChar(trim(I('post.bankcard', '', 'strip_tags'))); // 银行卡
                    $name = filterSpecialChar(trim(I('post.name', '', 'strip_tags'))); // 姓名
                    $iccard = filterSpecialChar(trim(I('post.iccard', '', 'strip_tags'))); // 身份证
                    $mobile = filterSpecialChar(trim(I('post.mobile', '', 'strip_tags'))); // 手机号码
                    $backUrl = C('WEB_ROOT').'/product/pay/'.$data.'?act=edit';
                    break;
                case 2: //已登录用户选择银行卡支付
                    $userBankObj = M("UserBank");
                    $bankId = I('post.bankcard', 0, 'int'); // 银行卡ID
                    if($bankId === 0){ // 钱包支付(随便获取一张用户银行卡信息)
                        $bankcard = $userBankObj->where(array('user_id'=>$user_info['id'],'has_pay_success'=>2))->getField('bank_card_no');
                    }else{ // 银行卡支付
                        $bankcard = $userBankObj->where(array('id'=>$bankId))->getField('bank_card_no');
                    }
                    $name = $user_info['realName'];
                    $iccard = $user_info['cardNo'];
                    $mobile = $user_info['username'];
                    $backUrl = C('WEB_ROOT').'/product/pay/'.$data.'?act=edit';
                    break;
                case 3: //已登录用户新卡支付
                    $bankcard = filterSpecialChar(trim(I('post.bankcard', '', 'strip_tags'))); // 银行卡
                    $name = $user_info['realName'];
                    $iccard = $user_info['cardNo'];
                    $mobile = $user_info['username'];
                    $backUrl = C('WEB_ROOT').'/product/pay/'.$data.'?act=new';
                    break;
            }

            if(!$money){
                $this->error('购买金额必须大于0');exit;
            }
            if(!$bankcard){
                $this->error('银行卡号不能为空');exit;
            }
            if(!$name){
                $this->error('姓名不能为空');exit;
            }
            if(!$iccard){
                $this->error('身份证不能为空');exit;
            }
            if(!$mobile){
                $this->error('手机号码不能为空');exit;
            }
            if(strlen($mobile) != 11){
                $this->error('手机号码格式不正确');exit;
            }

            if($productId === 0){ // 钱包充值
                $detail['id'] = 0;
                $detail['title'] = '石头钱包';
            }else{ // 普通产品购买
                $projectObj = M("Project");
                $detail = $projectObj->where(array('id'=>$productId,'is_delete'=>0))->find();
                if(!$detail){
                    $this->error('购买产品信息不存在');exit;
                }
                if($detail['status'] != 2 or strtotime($detail['start_time']) > time()){
                    $this->error('产品未开售或已售罄');exit;
                }
                if($detail['able'] < $money){
                    $this->error('产品剩余金额不足'.$money.'元,无法购买');exit;
                }
            }

            $rows = array(
                'id' => $productId,
                'project_title' => $detail['title'],
                'mobile' => $mobile,
                'cardNo' => $iccard,
                'bankCardNo' => $bankcard,
                'amount' => $money,
                'realName' => $name,
                'deviceType' => 4,
                'channel' => 'pc',
                'back_url' => $backUrl, // 返回修改URL
            );
            if($productId == 0){ // 钱包充值(生成订单)
                $rows['token'] = $user_info['token'];
                $rows['payWay'] = 1;
                $rows['requestid'] = null;
                $rows['validatecode'] = null;
                $ret = post(C('API').C('interface.user_wallet_recharge'), $rows);
            }else{ // 普通产品购买
                if($bankId === 0){ // 钱包支付(生成订单)
                    $rows['token'] = $user_info['token'];
                    $rows['payWay'] = 3;
                    $ret = post(C('API').C('interface.project_investV2'), $rows);
                }else{ // 普通银行卡支付(生成订单)
                    $rows['token'] = $user_info['token'];
                    $rows['payWay'] = 1;
                    $ret = post(C('API').C('interface.project_invest'), $rows);
                }
            }
            if($ret->code == '0'){
                if($bankId === 0){ // 钱包支付(直接支付成功)
                    if($ret->result->errorCode == '0000'){
                        $this->success('申购产品['.$detail['title'].']成功~!申购金额'.$money.'元', C('WEB_ROOT')."/user/product", 10);exit;
                    }else{
                        $this->error($ret->result->errorMsg);exit;
                    }
                }else{ // 普通支付(跳转到第三方支付页面)
                    $rows['user_id'] = ($ret->result->id? $ret->result->id : $user_info['id']);
                    // 生成风控参数
                    $risk_item = array(
                        'frms_ware_category' => 2009,
                        'user_info_mercht_userno' => ($ret->result->id ? $ret->result->id : $user_info['id']),
                        'user_info_bind_phone' => ($ret->result->mobile ? $ret->result->mobile : ($user_info['username'] ? $user_info['username'] : $mobile)),
                        'user_info_dt_register' => date('YmdHis', $ret->result->addTime/1000),
                        'user_info_full_name' => ($ret->result->realName ? $ret->result->realName : $name),
                        'user_info_id_type' => 0,
                        'user_info_id_no' => ($ret->result->cardNo ? $ret->result->cardNo : ($user_info['cardNo'] ? $user_info['cardNo'] : $bankcard)),
                        'user_info_identify_state' => 0,
                    );
                    // 使用json_encode是不转义中文汉字(目前只有用户名字不需要转义)
                    $name = $risk_item['user_info_full_name'];
                    $risk_item['user_info_full_name'] = 'stonereplacename';
                    $rows['risk_item'] = str_replace('stonereplacename', $name, json_encode($risk_item));
                    $rows['result'] = objectToArray($ret->result);
                    $_SESSION[LLPAY_SESSION] = $rows; // 用户填写基本信息

                    //商户用户唯一编号
                    $user_id = $rows['user_id'];
                    //支付类型
                    $busi_partner = 101001;
                    //商户订单号
                    $no_order = $rows['result']['rechargeNo'];
                    //商户网站订单系统中唯一订单号，必填
                    //付款金额
                    $money_order = $rows['amount'];
                    //必填
                    //商品名称
                    $name_goods = $detail['title'];
                    //订单地址
                    //$url_order = $rows['result']['project_title'];
                    //订单描述
                    $info_order = $detail['title'];
                    //银行网银编码
                    //$bank_code = $_POST['bank_code'];
                    //支付方式
                    //$pay_type = $_POST['pay_type'];
                    //卡号
                    $card_no = $rows['bankCardNo'];
                    //姓名
                    $acct_name = $name;
                    //身份证号
                    $id_no = $risk_item['user_info_id_no'];
                    //协议号
                    $no_agree = '';
                    //修改标记
                    $flag_modify = 0;
                    //风险控制参数
                    $risk_item = $rows['risk_item'];
                    //分账信息数据
                    $shareing_data = '';
                    //返回修改信息地址
                    $back_url = $rows['back_url'];
                    //订单有效期
                    $valid_order = C('oid_partner.valid_order');
                    //服务器异步通知页面路径
                    if($productId == 0){ // 钱包充值
                        $notify_url = C('wallet_notify_url');
                    }else{ // 购买普通产品
                        $notify_url = C('llpay_notify_url');
                    }
                    //需http://格式的完整路径，不能加?id=123这类自定义参数

                    //页面跳转同步通知页面路径
                    $return_url = C('WEB_ROOT')."/product/pay_result.html";
                    //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

                    /************************************************************/
                    date_default_timezone_set('PRC');
                    //构造要请求的参数数组，无需改动
                    $parameter = array (
                        "version" => trim(C('llpay_config.version')),
                        "oid_partner" => trim(C('llpay_config.oid_partner')),
                        "sign_type" => trim(C('llpay_config.sign_type')),
                        "userreq_ip" => trim(C('llpay_config.userreq_ip')),
                        "id_type" => trim(C('llpay_config.id_type')),
                        "valid_order" => trim($valid_order),
                        "user_id" => $user_id,
                        "timestamp" => local_date('YmdHis', time()),
                        "busi_partner" => $busi_partner,
                        "no_order" => $no_order,
                        "dt_order" => local_date('YmdHis', time()),
                        "name_goods" => $name_goods,
                        "info_order" => $info_order,
                        "money_order" => $money_order,
                        "notify_url" => $notify_url,
                        "url_return" => $return_url,
                        "url_order" => '',
                        "bank_code" => '',
                        "pay_type" => '',
                        'no_agree' => $no_agree,
                        "shareing_data" => $shareing_data,
                        "risk_item" => $risk_item,
                        "id_no" => $id_no,
                        "acct_name" => $acct_name,
                        "flag_modify" => $flag_modify,
                        "card_no" => $card_no,
                        "back_url" => $back_url,
                    );

                    //建立请求
                    $llpaySubmit = new \LLpaySubmit(C('llpay_config'));
                    $html_text = $llpaySubmit->buildRequestForm($parameter, "post", "确认");
                    $this->assign('content', $html_text);
                    $this->display('buy_check');
                }
            }else{
                if($ret->code == '21026'){ // 超过购买限制次数,直接返回产品列表页
                    $this->error($ret->errorMsg, C('WEB_ROOT').'/product/');exit;
                }else{
                    $this->error($ret->errorMsg);exit;
                }
            }
        }
    }

    /**
     * 支付结果通知页面
     */
    public function pay_result(){
        require_once ("include/llpay/lib/llpay_notify.class.php");

        //计算得出通知验证结果
        $llpayNotify = new \LLpayNotify(C('llpay_config'));
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //请在这里加上商户的业务逻辑程序代

        //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
        //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
        $is_notify = true;
        include_once ('include/llpay/lib/llpay_cls_json.php');
        $json = new \JSON();
        $str = file_get_contents("php://input");
        $val = $json->decode($str);

        $oid_partner = trim($val-> { 'oid_partner' });
        $dt_order = trim($val-> { 'dt_order' });
        $no_order = trim($val-> { 'no_order' });
        $oid_paybill = trim($val-> { 'oid_paybill' });
        $money_order = trim($val-> { 'money_order' });
        $result_pay = trim($val-> { 'result_pay' });
        $settle_date = trim($val-> { 'settle_date' });
        $info_order = trim($val-> { 'info_order' });
        $pay_type = trim($val-> { 'pay_type' });
        $bank_code = trim($val-> { 'bank_code' });
        $sign_type = trim($val-> { 'sign_type' });
        $sign = trim($val-> { 'sign' });

        if($result_pay == 'SUCCESS'){ // 支付成功
            $_SESSION[LLPAY_SESSION] = null; // 清空支付信息session
            $productObj = M("Project");
            $rechargeLogObj = M("RechargeLog");
            $userWalletRecordsObj = M("UserWalletRecords");
            $rechargeInfo = $rechargeLogObj->where(array('recharge_no'=>$no_order))->find();
            $projectId = $rechargeInfo['project_id'];
            if(!$projectId){ // 不在产品购买记录信息表(查找钱包充值记录表)
                if($userWalletRecordsObj->where(array('recharge_no'=>$no_order))->getField('id')){
                    $this->success('钱包充值 '.$money_order.'元成功~!', C('WEB_ROOT').'/user/wallet', 5);
                }else{
                    $this->error('未知错误');
                }
            }else{
                $projectTitle = $productObj->where(array('id'=>$projectId))->getField('title');
                // 未登录用户获取用户身份信息(自动登录)
                $uinfo = checkUserLoginStatus(StorageData(ONLINE_SESSION));
                if(!$uinfo['token']){
                    $ret = post(C('API').C('interface.pay_success'), array('userId'=>$rechargeInfo['user_id'],'rechargeNo'=>$rechargeInfo['recharge_no'],'tradeNo'=>$oid_paybill));
                    if($ret->code == '0'){
                        $userObj = M("User");
                        $userInfo = $userObj->where(array('id'=>$rechargeInfo['user_id']))->find();
                        $tokenInfo = array(
                            'id' => $userInfo['id'],
                            'username' => $userInfo['username'],
                            'realName' => $userInfo['real_name'],
                            'cardNoAuth' => $userInfo['card_no_auth'],
                            'mobile' => $userInfo['mobile'],
                            'cardNo' => $userInfo['card_no'],
                            'token' => $ret->result,
                            'verify' => md5($ret->result.C('SESSION_KEY').get_client_ip()),
                        );
                        StorageData(ONLINE_SESSION, $tokenInfo);
                        $this->success('申购产品['.$projectTitle.']成功~!申购金额: '.$money_order.'元', C('WEB_ROOT').'/user/product', 5);
                    }else{
                        $this->success('申购产品['.$projectTitle.']成功~!申购金额: '.$money_order.'元．登录后可查看购买记录，<a href="'.C('WEB_ROOT').'/login/">立即登录</a>', C('WEB_ROOT').'/product/', 5);
                    }
                }else{
                    $this->success('申购产品['.$projectTitle.']成功~!申购金额: '.$money_order.'元', C('WEB_ROOT').'/user/product', 5);
                }
            }
        }else{ // 支付失败
            dump($val);exit;
            $this->success('交易失败');eixt;
        }
    }

    /**
     * 打新股
     */
    public function daxingu(){
        $this->assign('meta_title', '打新股');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display();
    }

    /**
     * AB混合基金
     */
    public function abfund(){
        $this->assign('meta_title', '石头公募基金结构化A、B类产品');
        $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
        $this->assign('meta_description', '石头理财');
        $this->display();
    }

    /**
     * 钱包详细页
     */
    public function wallet(){
        if(!IS_POST){
            $user_info = checkUserLoginStatus(StorageData(ONLINE_SESSION));
            $this->assign('isLogin', ($user_info['token'] ? 1 : 0));
            $this->assign('meta_title', '石头钱包');
            $this->assign('meta_keywords', '石头理财,P2P金融,理财,理财产品');
            $this->assign('meta_description', '石头理财');
            $this->display();
        }else{
            // 钱包充值
            $money = I('post.money', 0, 'int');
            if($money > 0){
                $rows = array(
                    'id' => 0,
                    'money' => $money,
                );
                redirect(C('WEB_ROOT').'/product/pay/'.st_encrypt(json_encode($rows), C('PRODUCT_KEY')));
            }else{
                $this->error('充值金额不正确');exit;
            }
        }
    }

}