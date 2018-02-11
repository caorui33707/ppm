<?php
namespace Admin\Controller;

/**
 * 合同管理控制器(财务、运营用于核对信息用)
 * @package Admin\Controller
 */
class ContractController extends AdminController{

    /**
     * 合同列表
     */
    public function index(){
		header("Content-type:text/html;charset=utf8");
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 20; // 每页显示条数
            
            $name1 = urldecode(I('get.s', '', 'strip_tags')); // 搜索关键字(合同编号)
            $name2 = urldecode(I('get.s2', '', 'strip_tags')); // 搜索关键字(产品名称)
            
            $end_tt = urldecode(I('get.s3', '', 'strip_tags')); // 搜索关键字(产品名称)

            $contractObj = M('Contract');
            $contractProjectObj = M('ContractProject');

            $conditions = '';
            
            //$conditions2 = '';
            
            //if ($search2) $cond2[] = "project_name like '%" . $search2 . "%'";
            //if ($cond2) $conditions2 = implode(' and ', $cond2);
            
            $contract_id = 0;
            
            /*
            if($conditions2){
                $contract_id = $contractProjectObj->where($conditions2)->getField('contract_id');
                if($contract_id) $cond[] = "id=".$contract_id;
            }*/
            
            if($name1 && $name2) {                
                $name1 = 'ppm'.$name1;
                $name2 = 'ppm'.$name2;                
                $conditions .= "name>='$name1' and name <='$name2'";
            } else {
                if($name1) {
                    $name1 = 'ppm'.$name1;
                    $conditions .= "name='$name1'";
                }
            }

            if($end_tt) {
                $tt = strtotime($end_tt);
                if($conditions) {
                    $conditions .= 'and end_time='.$tt;
                } else {
                    $conditions .= ' end_time='.$tt;
                }
            }
            
            $counts = $contractObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
			
            $show = $Page->show();
            $orderby = 'id desc';
            $list = $contractObj->where($conditions)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach ($list as $key => $val) {
                $list[$key]['lastprice'] = $val['price'];
                $list[$key]['projects'] = $contractProjectObj->where(array('contract_id'=>$val['id']))->select();
                $list[$key]['lastprice'] -= $contractProjectObj->where(array('contract_id'=>$val['id']))->sum('price');
                $list[$key]['days'] = count_days(date('Y-m-d H:i:s',$val['end_time']), date('Y-m-d H:i:s',$val['start_time']));
            }

            $params = array(
                'page' => $page,
                'name1' => $name1,
                'name2' => $name2,
                'tt' => $end_tt,
            );
			
            //$list = $this->multi_array_sort($list,'lastprice');
			
			//$Page->listRows $Page->firstRow
			//数据获取数量
            //$list = $this->data_array_sort($list,$Page->firstRow,$Page->listRows,$counts);
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }else{
            $name1 = trim(I('post.name1', '', 'strip_tags'));
            $name2 = trim(I('post.name2', '', 'strip_tags'));          
            $end_time = trim(I('post.start_time', '', 'strip_tags'));            
            $quest = '';			
            if($name1) $quest .= '/s/'.urldecode($name1);
            if($name2) $quest .= '/s2/'.urldecode($name2);
            if($end_time)$quest .= '/s3/'.urldecode($end_time);
            redirect(C('ADMIN_ROOT') . '/contract/index'.$quest);
        }
    }

    /**
     * 添加合同
     */
    public function add(){
        if(!IS_POST){
            $this->assign("financing_list",M('Financing')->field('id,name')->select());

            $this->display();
        }else{
            $name = I('post.name', '', 'strip_tags');
            $ticket_number = I('post.ticket_number','','strip_tags');
            $price = I('post.price', 0);
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $interest = I('post.interest', 0);
            $fee = I('post.fee', 0);
            
            $drawer = trim(I('post.drawer', '', 'strip_tags'));
            $acceptor = trim(I('post.acceptor','','strip_tags'));


            $draft_type = I('post.draft_type', 0,'int');// 汇票类型
            $accepting_institution = I('post.accepting_institution', '','strip_tags');// 承兑机构
            $guaranty_type = I('post.guaranty_type', 0,'int');// 担保类型
            $guaranty_institution = I('post.guaranty_institution', '','strip_tags');// 担保机构
            $guaranty_agreement = I('post.invest_direction_image', '','strip_tags');// 担保协议
            $gid = I('post.gid', 0,'int');

            if(!$guaranty_type){ //  选择无担保  担保机构清空
                $guaranty_institution = '';
            }
            /*
            $fid = trim(I('post.fid',0));
            $apply_time = trim(I('post.apply_time','','strip_tags'));
            */
            $contractObj = M('Contract');
            if($contractObj->where(array('name'=>$name))->getField('id')) {
                $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的合同'));
            }
            
            if (!$ticket_number) {
                $this->ajaxReturn(array('status'=>0,'info'=>'票号不能为空'));
            }
            
            if($contractObj->where(array('ticket_number'=>$ticket_number))->getField('id')) {
                $this->ajaxReturn(array('status'=>0,'info'=>'票号：`'.$ticket_number.'`已经存在'));
            }
            
            if(!$drawer){
                $this->ajaxReturn(array('status'=>0,'info'=>'出票人不能为空'));
            }
            
            if(!$acceptor){
                $this->ajaxReturn(array('status'=>0,'info'=>'承兑人信息不能为空'));
            }

//            if(!$draft_type){
//                $this->ajaxReturn(array('status'=>0,'info'=>'请选择汇票类型'));
//            }

            if(!$accepting_institution){
                $this->ajaxReturn(array('status'=>0,'info'=>'承兑机构不能为空'));
            }

            if(in_array($draft_type,array(2,3))) {
                if ($guaranty_type == 1) { // 需要担保

                    if(!$guaranty_institution) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '担保机构不能为空'));
                    }

                    $tips_val= I('post.tips_val', 0);
                    if(!$tips_val) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '担保机构不存在!'));
                    }

//                    if(!$guaranty_agreement){
//                        $this->ajaxReturn(array('status'=>0,'info'=>'担保协议不能为空'));
//                    }
                }
            }
            /*
            if(!$apply_time){
                $this->ajaxReturn(array('status'=>0,'info'=>'申请日期不能为空'));
            }
            
            if(!$fid){
                $this->ajaxReturn(array('status'=>0,'info'=>'请选择融资人'));
            }*/
            
            $time = time();
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'name' => $name,
                'ticket_number'=>$ticket_number,
                'price' => $price,
                'start_time' => strtotime($start_time),
                'end_time' => strtotime($end_time),
                'interest' => $interest,
                'fee' => $fee,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
                'drawer'=>$drawer,
                'acceptor'=>$acceptor,

                'draft_type'=>$draft_type,
                'accepting_institution'=>$accepting_institution,
                'guaranty_type'=>$guaranty_type,
                'guaranty_institution'=>$guaranty_institution,
                'guaranty_agreement'=>$guaranty_agreement,
                'gid'=>$gid,
                //'fid'=>$fid,
                //'apply_time'=>$apply_time
            );
            if(!$contractObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C("ADMIN_ROOT").'/contract/index'));
        }
    }

    /**
     * 编辑合同
     */
    public function edit(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $search = urldecode(I('get.s', '', 'strip_tags')); // 搜索关键字
            $params = array(
                'page' => $page,
                'search' => $search,
            );
            $id = I('get.id', 0, 'int');
            $contractObj = M('Contract');
            $detail = $contractObj->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('合同信息不存在或已被删除');exit;
            }
            $this->assign("financing_list",M('Financing')->field('id,name')->select());
            $this->assign('params', $params);
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 0, 'int');
            $search = I('post.search', 0, 'strip_tags');
            $id = I('post.id', 0, 'int');
            $name = I('post.name', '', 'strip_tags');
            $ticket_number = I('post.ticket_number','','strip_tags');
            $price = I('post.price', 0);
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $interest = I('post.interest', 0);
            $fee = I('post.fee', 0);
            
            $drawer = trim(I('post.drawer', '', 'strip_tags'));
            $acceptor = trim(I('post.acceptor','','strip_tags'));

            $draft_type = I('post.draft_type', 0,'int');// 汇票类型
            $accepting_institution = I('post.accepting_institution', '','strip_tags');// 承兑机构
            $guaranty_type = I('post.guaranty_type', 0,'int');// 担保类型
            $guaranty_institution = I('post.guaranty_institution', '','strip_tags');// 担保机构
            $guaranty_agreement = I('post.invest_direction_image', '','strip_tags');// 担保协议
            $gid = I('post.gid', 0,'int');

            if(!$guaranty_type){ //  选择无担保  担保机构清空
                $guaranty_institution = '';
            }

//            if(!$draft_type){
//                $this->ajaxReturn(array('status'=>0,'info'=>'请选择汇票类型'));
//            }

            if(!$accepting_institution){
                $this->ajaxReturn(array('status'=>0,'info'=>'承兑机构不能为空'));
            }

            if(in_array($draft_type,array(2,3))) {
                if ($guaranty_type == 1) { // 需要担保

                    if(!$guaranty_institution) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '担保机构不能为空'));
                    }

                    $tips_val= I('post.tips_val', 0);
                    if(!$tips_val) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '担保机构不存在!'));
                    }

//                    if(!$guaranty_agreement){
//                        $this->ajaxReturn(array('status'=>0,'info'=>'担保协议不能为空'));
//                    }
                }
            }
            
            //$fid = trim(I('post.fid',0));
            //$apply_time = trim(I('post.apply_time','','strip_tags'));
            
            $contractObj = M('Contract');
            if($contractObj->where("name='".$name."' and id<>".$id)->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的合同'));
            $time = time();
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'name' => $name,
                'ticket_number'=>$ticket_number,
                'price' => $price,
                'start_time' => strtotime($start_time),
                'end_time' => strtotime($end_time),
                'interest' => $interest,
                'fee' => $fee,
                'modify_time' => $time,
                'modify_user_id' => $uid,
                'drawer'=>$drawer,
                'acceptor'=>$acceptor,

                'draft_type'=>$draft_type,
                'accepting_institution'=>$accepting_institution,
                'guaranty_type'=>$guaranty_type,
                'guaranty_institution'=>$guaranty_institution,
                'guaranty_agreement'=>$guaranty_agreement,
                'gid'=>$gid,
                //'fid'=>$fid,
                //'apply_time'=>$apply_time
            );
            if(!$contractObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/contract/index/p/'.$page.($search?'/s/'.$search:'')));
        }
    }

    /**
     * 添加合同下面的项目
     */
    public function add_project(){
        if(!IS_POST){
            $cid = I('get.cid', 0, 'int');
            $contractObj = M('Contract');
            $detail = $contractObj->where(array('id'=>$cid))->find();
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $cid = I('post.cid', 0, 'int');
            $projectName = I('post.project_name', '', 'strip_tags');
            $price = I('post.price', 0);
            $remark = I('post.remark', '', 'strip_tags');

            $contractProjectObj = M('ContractProject');
            $projectObj = M("Project");

            if($contractProjectObj->where(array('project_name'=>$projectName))->getField('id')) {
                
                $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的产品'));
            }

            $pid = $projectObj->where(array('title'=>$projectName,'is_delete'=>0))->getField('id');
            
            if(!$pid) {
                $this->ajaxReturn(array('status'=>0,'info'=>'产品名称为:`'.$projectName.'`的产品不存在！该值要与产品标题一致'));
            }
            
            $time = time();
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'contract_id' => $cid,
                'project_id' => $pid,
                'project_name' => $projectName,
                'price' => $price,
                'remark' => $remark,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$contractProjectObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加产品失败,请重试'));
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 编辑合同下面的项目
     */
    public function edit_project(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $contractObj = M('Contract');
            $contractProjectObj = M('ContractProject');
            $detail = $contractProjectObj->where(array('id'=>$id))->find();
            if(!$detail){
                $this->error('产品信息不存在或已被删除');exit;
            }
            $this->assign('detail', $detail);
            $this->assign('contract_name', $contractObj->where(array('id'=>$detail['contract_id']))->getField('name'));
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $projectName = I('post.project_name', '', 'strip_tags');
            $price = I('post.price', 0);
            $remark = I('post.remark', '', 'strip_tags');
            
            $pid = I('post.id', 0, 'int');

            $contractProjectObj = M('ContractProject');

            if($contractProjectObj->where("project_name='".$projectName."' and id<>".$id)->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同名称的产品'));
            
            //if(!$pid) {
                $pid = M("Project")->where(array('title'=>$projectName,'is_delete'=>0))->getField('id');
                if(!$pid) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'产品名称为:`'.$projectName.'`的产品不存在！该值要与产品标题一致'));
                }
            //}
            
            $time = time();
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'project_name' => $projectName,
                'price' => $price,
                'project_id'=>$pid,
                'remark' => $remark,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$contractProjectObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑产品失败,请重试'));
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 合同文件管理
     */
    public function upload(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $contractObj = M('Contract');
            $contractFilesObj = M('ContractFiles');
            $list = $contractFilesObj->where(array('contract_id'=>$id))->order('add_time desc')->select();
            $timestamp = time();
            $token = md5('unique_salt' . $timestamp);
            $this->assign('list', $list);
            $this->assign('timestamp', $timestamp);
            $this->assign('token', $token);
            $this->assign('id', $id);
            $this->assign('contract_name', $contractObj->where(array('id'=>$id))->getField('name'));
            $this->display();
        }else{
            $targetFolder = '/Uploads/contract'; // Relative to the root
            $verifyToken = md5('unique_salt' . $_POST['timestamp']);
            if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
                $tempFile = $_FILES['file_upload']['tmp_name'];
                $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;

                // Validate the file type
                $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
                $fileParts = pathinfo($_FILES['file_upload']['name']);

                if (in_array($fileParts['extension'], $fileTypes)) {
                    $contract_id = $_POST['cid'];
                    $filename = Guid() . '.' . $fileParts['extension'];
                    $targetFile = rtrim($targetPath, '/') . '/' . $filename;
                    if(!move_uploaded_file($tempFile, $targetFile)){
                        $this->error('上传失败!');
                    }else{
                        $time = time();
                        $uid = $_SESSION[ADMIN_SESSION]['uid'];
                        $contractFilesObj = M('ContractFiles');
                        $rows = array(
                            'contract_id' => $contract_id,
                            'filename' => $filename,
                            'add_time' => $time,
                            'add_user_id' => $uid,
                        );
                        if(!$contractFilesObj->add($rows)){
                            $this->error('添加文件信息失败,请重试');
                        }else{
                            $this->success('上传成功~!', C('ADMIN_ROOT').'/contract/upload/id/'.$contract_id);
                        }
                    }
                } else {
                    $this->error('不支持的文件格式');
                }
            }else{
                $this->error('没有可上传的文件');
            }
        }
    }
	//比较数组值
    public function multi_array_sort($multi_array,$sort_key,$sort=SORT_DESC){
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort($key_array,$sort,$multi_array);
        return $multi_array;
    }
	 //数组重新排序
    public function data_array_sort($sort_distance_arr,$begin,$num,$total){
        $start = intval($begin);
        $limit = intval($num);
        $last_arr = array();
        $i=0;
        foreach($sort_distance_arr as $k=>$v){
            if($i == $limit){
                break;
            }else{
                if($i==0){
                    array_push($last_arr,$sort_distance_arr[$start]);
                }else{
                    $start++;
                    if($start<$total){
                        array_push($last_arr,$sort_distance_arr[$start]);
                    }
                }
                $i++;

            }
        }
        return $last_arr;
    }
    
    public function export(){
        vendor('PHPExcel.PHPExcel');
        $name1 = urldecode(I('get.s', '', 'strip_tags')); // 搜索关键字(合同编号)
        $name2 = urldecode(I('get.s2', '', 'strip_tags')); // 搜索关键字(产品名称)
        $end_tt = urldecode(I('get.s3', '', 'strip_tags')); // 搜索关键字(产品名称)
        
        $conditions = '';
        
        if($name1 && $name2) {
            
            //$name1 = 'ppm'.$name1;
            //$name2 = 'ppm'.$name2;
            
            $conditions .= "name>='$name1' and name <='$name2'";
        } else {
            if($name1) {
                //$name1 = 'ppm'.$name1;
                $conditions .= "name='$name1'";
            }
        }
        
        if($end_tt) {
            $tt = strtotime($end_tt);
            if($conditions) {
                $conditions .= 'and end_time='.$tt;
            } else {
                $conditions .= ' end_time='.$tt;
            }
        }    
            
        $contractObj = M('Contract');
        
        $list = $contractObj->where($conditions)->order('id desc')->select();
        
        if(!$list) {
            exit('没有数据');
        }
        
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()
                    ->setCreator("票票喵票据")
                    ->setLastModifiedBy("票票喵票据")
                    ->setTitle("title")
                    ->setSubject("subject")
                    ->setDescription("description")
                    ->setKeywords("keywords")
                    ->setCategory("Category");
               
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('合同列表')
                    ->setCellValue("A1", "期数")
                    ->setCellValue("B1", "票号")
                    ->setCellValue("C1", "出票日")
                    ->setCellValue("D1", "到期日")
                    ->setCellValue("E1", "票面金额")
                    ->setCellValue("F1", "出票人")
                    ->setCellValue("G1", "承兑人信息");
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        // 设置列表值
        $pos = 2;       
        foreach ($list as $key => $val) {      
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['name']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['ticket_number']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,date("Y-m-d",$val['start_time']));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,date("Y-m-d",$val['end_time']));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['price']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['drawer']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['acceptor']); 
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="合同列表('.date('Y-m-d H:i:s').').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    // 上传图片
    public function load(){
        $image = '';

        if($_FILES){
//            $config = array(
//                'maxSize'    =>    3145728,
//                'rootPath' => C('UPLOAD_PATH'),
//                'savePath'   =>    '',
//                'saveName'   =>    array('uniqid',''),
//                'exts'       =>    array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
//                'autoSub'    =>    true,
//                'subName'    =>    array('date','Ymd'),
//            );
//            $upload = new \Think\Upload($config);// 实例化上传类
//            // 上传文件
//            $info   =   $upload->upload();
            $ext_arr =  array('jpg', 'gif', 'png', 'jpeg', 'bmp');
            $maxSize = 3145728;
            $info = $_FILES['img'];

            if($info) {
                $file =  $_FILES['img']['tmp_name'];
                $file_name =  $_FILES['img']['name'];
              //  $image = $info['img']['savepath'].$info['img']['savename'];
               // $ossPath = 'Uploads/focus/'.$image;
                //$file = C('localPath').$image;

                //获得文件扩展名
                $temp_arr = explode(".", $file_name);
                $file_ext = array_pop($temp_arr);
                $file_ext = trim($file_ext);
                $file_ext = strtolower($file_ext);

                if (in_array($file_ext, $ext_arr) === false) {
                    $this->ajaxReturn(array('status'=>0,'info'=>$file_ext ));
                    $this->ajaxReturn(array('status'=>0,'info'=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。"));
                }
                $size = $info['size'];
                if($size>$maxSize){
                    $this->ajaxReturn(array('status'=>0,'info'=>"上传文件大小超过限制。"));
                }

                $image = date('Ymd').'/'.time().'.'.$file_ext;
                $ossPath = 'Uploads/focus/'.$image;

               // $file = C('localPath').$image;
                $res = uploadToOss($ossPath,$file);
                if($res['info']['http_code']!= 200 || $res['oss-request-url'] == '') {
                    $this->ajaxReturn(array('status'=>0,'info'=>'oss图片上传失败'));
                }
                \Think\Log::write('upload info:'.json_encode($res),'INFO');
            }
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'请选择广告图'));
        }

        $this->ajaxReturn(array('status'=>1,'image'=>$image));
       // json_encode(array('image'=>$image,'status'));



        //return $image;
    }

}