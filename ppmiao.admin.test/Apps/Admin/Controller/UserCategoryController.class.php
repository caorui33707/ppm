<?php
/**
 * Created by PhpStorm.
 * User: cscjj2008
 * Date: 17/4/21
 * Time: 下午4:06
 */

namespace Admin\Controller;


/**
 * Class UserCategoryController
 * @package Admin\Controller
 */
class UserCategoryController extends AdminController
{

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
    private  $cate_list = [
        'potential_new',
        'potential_silent',
        'potential_potential',
        'news_online',
        'news_track',
        'news_lost',
        'growing_online',
        'growing_track',
        'growing_lost',
        'growing_mature',
        'lost_fade',
        'lost_lost',
        'silent_online',
        'silent_lost',
    ];
    private $categories = [
        'potential'=>['potential_new','potential_silent','potential_potential'],
        'news'=>['news_online','news_track','news_lost'],
        'growing'=>['growing_online','growing_track','growing_lost','growing_mature'],
        'lost'=>['lost_fade','lost_lost'],
        'silent'=>['silent_online','silent_lost'],
    ];

    public function ghost_money(){
        echo 1;
    }

    public function daily(){
        $categories = [
            [
                'title'=>'潜力',
                'name'=>'potential',
            ],
            [
                'title'=>'新',
                'name'=>'news',
            ],
            [
                'title'=>'成长',
                'name'=>'growing',
            ],
            [
                'title'=>'流失',
                'name'=>'lost',
            ],
            [
                'title'=>'沉睡',
                'name'=>'silent',
            ]
        ];

        $cate_name = [
                'potential'=>'潜力',
                'news'=>'新',
                'growing'=>'成长',
                'lost'=>'流失',
                'silent'=>'沉睡',
        ];

        $last_day = date("Y-m-d",strtotime("-1 day"));
        $this_day = date("Y-m-d");
        M()->db(1,"mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8o.mysql.rds.aliyuncs.com:3306/ppmiao_statistics");
        $data = [];
        foreach($categories as $category){
            for($day = 1 ; $day <= 30 ; $day++){
               // $sql = "select count(*) as num from `s_user_category_loss` where  uid in (select uid from `s_user_category_daily` where  datediff( now(), true_time )=1 and category = '".$category['name']."') and loss_days = $day";
//                echo $sql.'<br>';

//                 $sql = "select count(*) as num
// from `s_user_category_loss` sucl
// where exists (select 1
//         from `s_user_category_daily` suc
//         where suc.uid= sucl.uid and true_time >= '".$last_day."' and true_time < '".$this_day."'
//             and category = '".$category['name']."')
//     and loss_days =  $day";


                $sql = "select count(*) as num
from `s_user_category_loss` sucl
where sucl.uid in  (select uid
        from `s_user_category_daily` suc
        where  true_time >= '".$last_day."' and true_time < '".$this_day."'
            and category = '".$category['name']."')
    and loss_days = $day";

                $count = M()->db(1)->query($sql);
                $data[$category['name']][$day] = $count[0]['num'];
            }
        }
//        var_dump($data);
        $this->assign('data', $data);
        $this->assign('cate_name', $cate_name);
        $this->display();

    }
    public function monthly(){


        $dt = I("post.dt"); // 分成类型
        $date = $this->getDate($dt);


        if(!$dt){
            $base = strtotime(date('Y-m',time()) . '-01 00:00:01');
            $dt= date('Y-m',strtotime('-1 month', $base));
        }


        $cate_name = $this->cate_name;



        $month = $date['month'];
        $last_month = $date['last_month'];


        $month_date = $date['month_date'];
        $last_month_date = $date['last_month_date'];

        //

        $sql1 = "select * from s_user_category_monthly where years_month = '$month'";
        $month_data = M()->db(1,"mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8o.mysql.rds.aliyuncs.com:3306/ppmiao_statistics")->query($sql1);
        $this_month_data = $month_data[0];
        $sql2 = "select * from s_user_category_monthly where years_month = '$last_month'";
        $month_data2 = M()->db(1)->query($sql2);
        $last_month_data = $month_data2[0];


        $categories = [
            'potential'=>['potential_new','potential_silent','potential_potential'],
            'news'=>['news_online','news_track','news_lost'],
            'growing'=>['growing_online','growing_track','growing_lost','growing_mature'],
            'lost'=>['lost_fade','lost_lost'],
            'silent'=>['silent_online','silent_lost'],
        ];



        $cate_list = $this->cate_list;


        $category_count = $this->track($month,$last_month);

        $this->assign('categories', $categories);
        $this->assign('this_month_data', $this_month_data);
        $this->assign('last_month_data', $last_month_data);
        $this->assign('month_date', $month_date);
        $this->assign('last_month_date', $last_month_date);
        $this->assign('cate_name', $cate_name);
        $this->assign('cate_list', $cate_list);
        $this->assign('category_count', $category_count);
        $this->assign('dt', $dt);
        $this->display();






    }

    public function monthly_excel(){


        $dt = I("get.dt"); // 分成类型
        $date = $this->getDate($dt);


        if(!$dt){
            $base = strtotime(date('Y-m',time()) . '-01 00:00:01');
            $dt= date('Y-m',strtotime('-1 month', $base));
            $date = $this->getDate($dt);
        }


        $cate_name = $this->cate_name;



        $month = $date['month'];
        $last_month = $date['last_month'];


        $month_date = $date['month_date'];
        $last_month_date = $date['last_month_date'];

        //

        $sql1 = "select * from s_user_category_monthly where years_month = '$month'";
        $month_data = M()->db(1,"mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8o.mysql.rds.aliyuncs.com:3306/ppmiao_statistics")->query($sql1);
        $this_month_data = $month_data[0];
        $sql2 = "select * from s_user_category_monthly where years_month = '$last_month'";
        $month_data2 = M()->db(1)->query($sql2);
        $last_month_data = $month_data2[0];


        $categories = [
            'potential'=>['potential_new','potential_silent','potential_potential'],
            'news'=>['news_online','news_track','news_lost'],
            'growing'=>['growing_online','growing_track','growing_lost','growing_mature'],
            'lost'=>['lost_fade','lost_lost'],
            'silent'=>['silent_online','silent_lost'],
        ];



        $cate_list = $this->cate_list;


        $category_count = $this->track($month,$last_month);



//
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=".$dt."用户分成统计.xls");

        $html = '';
        $html.= '<table id="checkList" class="list" style="border: 1px solid gray;" cellpadding="0" cellspacing="0">
                <tbody>
                <tr class="row">
                    <th style="border:1px solid gray" width="150px" align="center">一级分类</th>
                    <th style="border:1px solid gray" width="100px" align="center">人数（人）</th>
                    <th style="border:1px solid gray" width="100px" align="center">占比（%）</th>
                    <th style="border:1px solid gray" width="100px" align="center">二级分类</th>
                    <th style="border:1px solid gray" width="150px" align="center">人数（人）</th>
                    <th style="border:1px solid gray" width="100px" align="center">占比（%）</th>
                </tr>';
        foreach($categories as $key=> $category){
            foreach($category as $k => $cate){
                $html.='<tr>';
                if($k == 0){
                    $html.='<td style="border:1px solid gray" rowspan="'.count($category).'">'.$cate_name[$key].'</td>';
                    $html.='<td style="border:1px solid gray" rowspan="'.count($category).'">'.$this_month_data[$key].'</td>';
                    $html.='<td style="border:1px solid gray" rowspan="'.count($category).'">'.round(  $this_month_data[$key]/$this_month_data['total'] * 100 , 2) . "%".'</td>';
                }
                $html.='<td style="border:1px solid gray">'.$cate_name[$cate].'</td>';
                $html.='<td style="border:1px solid gray">'.$this_month_data[$cate].'</td>';
                $html.='<td style="border:1px solid gray"><php>'.round(  $this_month_data[$cate]/$this_month_data['total'] * 100 , 2) . "%".'</php></td>';
                $html.='</tr>';
            }
        }
        $html.= '</tbody>
            </table>';


        foreach($cate_list as $cate){

            $html.= '<table  class="list" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr class="row">
                        <th style="border:1px solid gray" width="150px" align="center">'.$last_month_date.'</th>
                        <th style="border:1px solid gray" width="100px" align="center">总数（人）</th>
                        <th style="border:1px solid gray" width="100px" align="center">去向（人数）</th>
                        <th style="border:1px solid gray" width="100px" align="center">占比（%）</th>
                        <th style="border:1px solid gray" width="150px" align="center">类别</th>
                        <th style="border:1px solid gray" width="100px" align="center">占比（%）</th>
                        <th style="border:1px solid gray" width="100px" align="center">来源（人数）</th>
                        <th style="border:1px solid gray" width="100px" align="center">总数（人）</th>
                        <th style="border:1px solid gray" width="100px" align="center">'.$month_date.'</th>
                    </tr>';
            foreach($cate_list as $key=> $sub_cate){

                $html.= '<tr>';

                if($key == 0) {
                    $html .= '<td style="border:1px solid gray" rowspan="14">'.$cate_name[$cate].'</td>
                                <td style="border:1px solid gray" rowspan="14">'.$last_month_data[$cate].'</td>';
                }
                $html.= '<td style="border:1px solid gray">'.$category_count[$cate][$sub_cate].'</td>
                        <td style="border:1px solid gray">'.round(  $category_count[$sub_cate][$cate]/$last_month_data[$cate] * 100 , 2) . '%</td>
                        <td style="border:1px solid gray">'.$cate_name[$sub_cate].'</td>
                        <td style="border:1px solid gray">'.round(  $category_count[$sub_cate][$cate]/$this_month_data[$cate] * 100 , 2) . '%</php></td>
                        <td style="border:1px solid gray">'.$category_count[$sub_cate][$cate].'</td>';

                if($key == 0) {
                $html .= '<td style="border:1px solid gray" rowspan="14">'.$this_month_data[$cate].'</td>
                                <td style="border:1px solid gray" rowspan="14">'.$cate_name[$cate].'</td>';
                }
                $html .= '</tr>';
            }
            $html .='
                    </tbody>
                </table>';
        }







        echo $html;





    }

    public function track($month,$last_month){
        ini_set("memory_limit", "1000M");
        
        ini_set("max_execution_time", 0);
        $this_month = $month;
        M()->db(1,"mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8.mysql.rds.aliyuncs.com:3306/ppmiao_statistics");
        $categories = $this->cate_list;
        foreach($categories as $last_category){
            foreach($categories as $this_category){

                $sql = "select num from s_user_category_proportion where this_month = '$this_month' and last_month = '$last_month' and sub_category_before = '$last_category' and sub_category = '$this_category'";
                       // echo $sql.'<br>';
                $count = M()->db(1)->query($sql);
                $data[$last_category][$this_category] = $count[0]['num'];
            }
        }
        return $data;




    }
    public function export(){
        set_time_limit(0);
        exit();


        $cate_name = $this->cate_name;
        $type = I("get.type"); // 分成类型
        $days = I("get.days", 0, "int"); // 流失天数


        $file_name = date('Ymd',strtotime("-1 day")).'_'.$cate_name[$type].'_持续天数'.$days.'_用户列表';


        $sql = "select ac.real_name_auth, day.uid,day.current_capital_money,day.sub_category,loss.peak_capital_money from s_user_category_loss as loss left join s_user_category_daily as day on day.uid = loss.uid  left join  `ppmiao_test`.s_user as ac on ac.id = loss.uid  where datediff( now(), day.true_time )=1 and day.category = '$type' and loss.loss_days = $days limit 15000";
        $list = M()->db(1,"mysql://pptang_123:E8b9J7TjPs0u4Nf@rm-uf6s86ucfa1mvy1m8o.mysql.rds.aliyuncs.com:3306/ppmiao_statistics")->query($sql);

        vendor('PHPExcel.PHPExcel');

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("票票喵票据")
            ->setLastModifiedBy("票票喵票据")
            ->setTitle("title")
            ->setSubject("subject")
            ->setDescription("description")
            ->setKeywords("keywords")
            ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('下载的数据表')
            ->setCellValue("A1", "编号")
            ->setCellValue("B1", "用户ID")
            ->setCellValue("C1", "用户分类")
            ->setCellValue("D1", "用户二级分类")
            ->setCellValue("E1", "是否投资")
            ->setCellValue("F1", "是否投资定期")
            ->setCellValue("G1", "投资定期次数（次）")
            ->setCellValue("H1", "现资产状况（定期）（元）")
            ->setCellValue("I1", "总资产峰值（定期）（元）")
            ->setCellValue("J1", "用户流失追踪时间（天）");

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
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
        foreach ($list as $key => $val) {

            if($val['current_capital_money'] > 0){
                $is_project = '是';
            }else{
                $is_project = '否';
            }
            $due_time = $this->get_user_project_time($val['uid']);
//            var_dump($due_time);


            if($val['real_name_auth'] == 1 || $val['current_capital_money']>0){
                $is_money = '是';
            }else{
                $is_money = '否';
            }

            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $key+1); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['uid']); // 收款银行账号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$cate_name[$type]); // 开户行所在省
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$cate_name[$val['sub_category']]); // 开户行所在省
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$is_money); // 开户行所在市
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$is_project); // 开户行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$due_time); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['current_capital_money']); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,$val['peak_capital_money']); // 商户订单号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("J".$pos,$days); // 商户备注
            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function get_user_project_time($user_id){
        return M('UserDueDetail')->where('user_id ='.$user_id)->count();
    }

    function getCurMonthLastDay($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month -1 day'));
    }



    function getDate($dt){
        if(!$dt){
            $base = strtotime(date('Y-m',time()) . '-01 00:00:01');
            $date['month'] = date('Ym',strtotime('-1 month', $base));
            $date['month_date'] = date('Y-m-d',strtotime('-1 min', $base));

            $date['last_month'] = date('Ym',strtotime('-2 month', $base));
            $date['last_month_date'] = date('Y-m-d',strtotime('-1 month -1 min', $base));


        }else{
            $base = strtotime($dt);
            $date['month'] = date('Ym',$base);
            $date['month_date'] = date('Y-m-d',strtotime('+1 month -1 min', $base));

            $date['last_month'] = date('Ym',strtotime('-1 month', $base));
            $date['last_month_date'] = date('Y-m-d',strtotime('-1 min', $base));


        }
        return $date;
    }
    public function clearUserCode(){
        $mobile = I("get.mobile"); // 分成类型
        $url = "http://wechat.ppmiao.com/clearUserCode/$mobile/admin";
        echo file_get_contents($url);
    }


}