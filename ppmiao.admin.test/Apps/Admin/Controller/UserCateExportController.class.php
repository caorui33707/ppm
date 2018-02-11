<?php
/**
 * 导出不同类型用户列表
 * @author      mozarlee
 * @time        2017-12-22 17:22:05
 * @created by  Sublime Text 3
 */

namespace Admin\Controller;

class UserCateExportController extends AdminController
{
    private $start = 0; // 导出数据偏移量
    private $limit = 200000; // 数据条数

    private $type; // cate类型
    private $dt; // 日期

    // 统计数据连接
    // 测试
    // private $conn = 'mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8o.mysql.rds.aliyuncs.com:3306/ppmiao_statistics';
    // 生产
    private $conn = 'mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8.mysql.rds.aliyuncs.com:3306/ppmiao_statistics';

    private  $cate_name = [
        'potential'=>'潜力用户',
        'potential_new'=>'新增用户',
        'potential_silent'=>'沉默用户',
        'potential_potential'=>'潜力用户',
        'news'=>'新用户',
        'news_online'=>'新用户在线',
        'news_track'=>'新用户待追踪',
        'news_lost'=>'新用户流失',
        'growing'=>'成长用户',
        'growing_online'=>'成长用户在线',
        'growing_track'=>'成长用户待追踪',
        'growing_lost'=>'成长流失',
        'growing_mature'=>'成熟用户',
        'lost'=>'流失用户',
        'lost_fade'=>'衰退用户',
        'lost_lost'=>'流失用户',
        'silent'=>'沉睡用户',
        'silent_online'=>'沉睡用户',
        'silent_lost'=>'沉睡流失',
    ];

    /**
     * 获取某个类型的用户数量
     * @return [type] [description]
     */
    public function get_count() {
        $this->type = I('get.type', '');
        $this->dt = I('get.dt', '');

        $l = 10000; // 默认每次导出一万条数据

        $count_uids = $this->get_count_uids();
        
        $result = array();
        // 计算一共需要导出的次数，默认每次导出一万条数据
        $r = ceil($count_uids / $l);
        for ($i = 0; $i < $r; $i++) { 
            $result[] = array(
                'start' => $i * $l + 1,
                'end' => ($i + 1) * $l
                );
        }

        $this->assign('data', $result);

        $this->assign('count_uids', $count_uids);
        $this->assign('type', $this->type);
        $this->assign('type_name', $this->cate_name[$this->type]);
        $this->assign('dt', $this->dt);
        $this->display();
    }

    /**
     * 导出入口
     * @return [type] [description]
     */
    public function index() {
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);

        $this->type = I('get.type', '');
        $this->dt = I('get.dt', '');

        $this->start = I('get.start', 0);
        $this->limit = I('get.limit', $this->limit);

        if (array_key_exists($this->type, $this->cate_name)) {
            $this->export_data();
        } else {
            echo '不存在的请求';
        }
    }

    //导出说明:因为EXCEL单表只能显示104W数据，同时使用PHPEXCEL容易因为数据量太大而导致占用内存过大，
    //因此，数据的输出用csv文件的格式输出，但是csv文件用EXCEL软件读取同样会存在只能显示104W的情况，所以将数据分割保存在多个csv文件中，并且最后压缩成zip文件提供下载
    public function put_csv()
    {
        set_time_limit(0);
        ini_set("max_execution_time", 0);
        
        $this->type = I('post.type', ''); // 导出类型
        $this->dt = I('post.dt', ''); // 导出月份

        $page = I('post.page', ''); // 导出页数
        $sql_limit = I('post.limit', 10000); // 每页条数

        if (empty($this->type) || empty($this->dt) || empty($page)) {
            $this->ajaxReturn(array('status' => 500,'info' => '参数为空.'));
        }

        $head = array('用户ID','手机号','姓名','等级','累计投资总额','当月累计投资额','本月在投总额','最后一笔投资金额','投资期限','最后一笔投资时间');

        $this->start = ($page - 1) * $sql_limit; // 查询偏移量
        $this->limit = $sql_limit; // 数据条数

        $uids_res = $this->get_uids();
        // 处理用户id
        $uids = array();
        foreach ($uids_res as $value) {
            $uids[] = $value['uid'];
            unset($value['uid']);
            unset($value);
        }
        unset($uids_res);

        // 获取导出数据
        $dataArr = $this->get_users($uids);

        if (empty($dataArr[0])) {
            $this->ajaxReturn(array('status' => 200,'info' => null));
            exit();
        }

        $path = $this->get_file_path($page);
        chmod($path, 777);
        unlink($path);
        $fp = fopen($path, 'w+') or die("Can't Open File");
        fputcsv($fp, $head);

        // buffer计数器
        $cnt = 0;
        // 每隔$temp行，刷新一下输出buffer，不要太大，也不要太小
        $temp = 5000;
        foreach ($dataArr[0] as $value) {
            $cnt++;
            if ($temp == $cnt) {
                //刷新一下输出buffer，防止由于数据过多造成问题
                ob_flush();
                flush();
                $cnt = 0;
            }

            if (isset($dataArr[1][$value['id']])) {
                $value['level'] = $dataArr[1][$value['id']];
            }
            fputcsv($fp, $value);
        }

        fclose($fp);  // 文件关闭
        $this->ajaxReturn(array('status' => 200,'info' => $page));
        exit();
    }

    /**
     * 导出压缩文件
     * @return [type] [description]
     */
    public function export_zip() {
        $this->type = I('get.type', ''); // 导出类型
        $this->dt = I('get.dt', ''); // 导出月份
        $page = I('get.pages', ''); // 页数

        if (is_numeric($page)) {
            // echo "string";die();
            // 只有一条数据，单个文件，直接下载
            $filename = $this->get_file_path($page);
            $fileinfo = pathinfo($filename);
            header('Content-type: application/x-'.$fileinfo['extension']);
            header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
            header('Content-Length: '.filesize($filename));
            readfile($filename);
            // 删除临时文件
            unlink($filename);
            exit();
        } else {
            // 有多条数据，多个文件，压缩下载
            $fliename_arr = array();
            $page_arr = explode(',', $page);
            foreach ($page_arr as $val) {
                $filename_arr[] = $this->get_file_path($val);
            }

            //进行多个文件压缩
            $zip = new \ZipArchive();
            // 压缩文件路径
            $filename = $this->dt.$this->cate_name[$this->type] . ".zip";
            $base_path = $this->get_base_path();
            $file_path = $base_path.$filename;

            unlink($file_path); // 如果存在先删除
            $zip->open($file_path, \ZipArchive::CREATE);   //打开压缩包
            foreach ($filename_arr as $file) {
                $zip->addFile($file, basename($file));   //向压缩包中添加文件
            }
            $zip->close();  //关闭压缩包
            foreach ($filename_arr as $file) {
                unlink($file); //删除csv临时文件
            }

            //输出压缩文件提供下载
            header("Cache-Control: max-age=0");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . basename($file_path)); // 文件名
            header("Content-Type: application/zip"); // zip格式的
            header("Content-Transfer-Encoding: binary"); //
            header('Content-Length: ' . filesize($file_path)); //
            @readfile($file_path);//输出文件;
            unlink($file_path); //删除压缩包临时文件
        }

    }

    /**
     * 获取路径
     * @return [type] [description]
     */
    private function get_base_path() {
        // @TODO 如果项目目录结构发生变化，修改这里的路径，导出文件临时缓存
        $str = dirname(__FILE__);
        $file_path = str_replace("Apps/Admin/Controller", '', $str).'Runtime/';
        return $file_path;
    }

    /**
     * 获取文件路径
     * @return [type] [description]
     */
    private function get_file_path($page) {
        $filename = $this->dt.$this->cate_name[$this->type].'第'.$page.'页.csv';
        $file_path = $this->get_base_path();
        $path = $file_path.$filename;
        return $path;
    }

    /**
     * 获取用户数据
     * @return [type] [description]
     */
    private function get_users($uids) {
        $str = implode(',', $uids);

        $start = date('Y-m-01', strtotime($this->dt));
        $end = date('Y-m-01', strtotime('+1month', strtotime($this->dt)));

        $model = new \Think\Model();
        // 查询要导出的数据信息
        $sql = "select t0.id as id, t0.username as username, t0.real_name as real_name,
            null level,

            (select sum(t2.due_capital) from s_user_due_detail t2 where t0.id = t2.user_id) as total_invest,
            (select sum(t2.due_capital) from s_user_due_detail t2 where t0.id = t2.user_id and t2.add_time >= '$start' and t2.add_time < '$end') as month_total_invest,
            (select sum(t2.due_capital) from s_user_due_detail t2 where t0.id = t2.user_id and t2.status != 2 and t2.add_time >= '$start' and t2.add_time < '$end') as month_investing,
            (select due_capital from s_user_due_detail t2 where t0.id = t2.user_id order by t2.add_time desc limit 0,1) last_invest_val,
            (select duration_day from s_user_due_detail t2 where t0.id = t2.user_id order by t2.add_time desc limit 0,1) last_invest_days,
            (select add_time from s_user_due_detail t2 where t0.id = t2.user_id order by t2.add_time desc limit 0,1) last_invest_time

            from s_user t0 where t0.id in ($str)";

        // 查询用户等级
        $users_vip_level = D('UserVipLevel')->getUsersLevel($uids);
        // 处理数据格式
        $users_level = array();
        foreach ($users_vip_level as $key => $value) {
            $users_level[$value['uid']] = $value['vip_level'];
        }

        $q = $model->query($sql);
        return [$q, $users_level];
    }

    /**
     * 导出数据入口
     * @return [type] [description]
     */
    private function export_data() {
        $res = $this->get_uids();

        if (!empty($res)) {
            // 处理用户id
            $uids = array();
            foreach ($res as $value) {
                $uids[] = $value['uid'];
                unset($value['uid']);
                unset($value);
            }
            unset($res);
            $str = implode(',', $uids);
            unset($res);

            $start = date('Y-m-01', strtotime($this->dt));
            $end = date('Y-m-01', strtotime('+1month', strtotime($this->dt)));

            $model = new \Think\Model();
            // 查询要导出的数据信息
            $sql = "select t0.id as id, t0.username as username, t0.real_name as real_name,
                null level,

                (select sum(t2.due_capital) from s_user_due_detail t2 where t0.id = t2.user_id) as total_invest,
                (select sum(t2.due_capital) from s_user_due_detail t2 where t0.id = t2.user_id and t2.add_time >= '$start' and t2.add_time < '$end') as month_total_invest,
                (select sum(t2.due_capital) from s_user_due_detail t2 where t0.id = t2.user_id and t2.status != 2 and t2.add_time >= '$start' and t2.add_time < '$end') as month_investing,
                (select due_capital from s_user_due_detail t2 where t0.id = t2.user_id order by t2.add_time desc limit 0,1) last_invest_val,
                (select duration_day from s_user_due_detail t2 where t0.id = t2.user_id order by t2.add_time desc limit 0,1) last_invest_days,
                (select add_time from s_user_due_detail t2 where t0.id = t2.user_id order by t2.add_time desc limit 0,1) last_invest_time

                from s_user t0 where t0.id in ($str)";

            // 查询用户等级
            $users_vip_level = D('UserVipLevel')->getUsersLevel($uids);
            // 处理数据格式
            $users_level = array();
            foreach ($users_vip_level as $key => $value) {
                $users_level[$value['uid']] = $value['vip_level'];
            }

            $q = $model->query($sql);

            if (!empty($q)) {
                $this->do_export($q, $users_level);
            } else {
                echo "用户数据为空！";
                exit();
            }
        }
        echo "没有需要导出的数据";
    }

    /**
     * 获取需要导出的数量
     * @return [type] [description]
     */
    private function get_count_uids() {
        $time = date('Ym', strtotime($this->dt));

        // 获取需要导出的用户id
        M()->db(1, $this->conn);
        $sql = "select count(*) as count from `s_user_category_track` where `sub_category` = '$this->type' and years_month = '$time' order by uid";
        $query_res = M()->db(1)->query($sql);
        return $query_res[0]['count'];
    }

    /**
     * 获取需要导出的用户id
     * @return [type]       [description]
     */
    private function get_uids() {
        $time = date('Ym', strtotime($this->dt));

        // 获取需要导出的用户id
        M()->db(1, $this->conn);
        $sql = "select uid from `s_user_category_track` where `sub_category` = '$this->type' and years_month = '$time' order by uid asc limit $this->start, $this->limit";

        $query_res = M()->db(1)->query($sql);

        return $query_res;
    }

    /**
     * 执行导出操作
     * @return [type] [description]
     */
    private function do_export($data, $users_level){
        vendor('PHPExcel.PHPExcel');

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("票票喵")
            ->setLastModifiedBy("票票喵")
            ->setTitle("title")
            ->setSubject("subject")
            ->setDescription("description")
            ->setKeywords("keywords")
            ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('下载的数据表')
            ->setCellValue("A1", "用户ID")
            ->setCellValue("B1", "手机号")
            ->setCellValue("C1", "姓名")
            ->setCellValue("D1", "等级")
            ->setCellValue("E1", "累计投资总额")
            ->setCellValue("F1", "当月累计投资额")
            ->setCellValue("G1", "本月在投总额")
            ->setCellValue("H1", "最后一笔投资金额")
            ->setCellValue("I1", "投资期限")
            ->setCellValue("J1", "最后一笔投资时间");

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        // 设置列表值
        $pos = 2;
        foreach ($data as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['id']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['username']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['real_name']);

            $level = '';
            if (isset($users_level[$val['id']])) {
                $level = $users_level[$val['id']];
            }

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$level);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['total_invest']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['month_total_invest']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['month_investing']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['last_invest_val']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,$val['last_invest_days']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("J".$pos,$val['last_invest_time']);
            $pos += 1;
        }
        ob_end_clean();

        $file_name = '用户分层-'.$this->cate_name[$this->type].$this->dt;
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$file_name.'.csv"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


}