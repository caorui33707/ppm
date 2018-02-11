<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 查询手机号归属地为1.重庆 2.吉林 3.黑龙江三个地区用户具体数量及排名（以手机号码区分省份）-票票喵理财用户部分地区统计需求
 */

class MobileLocationController extends Controller {
    // private $api_url = 'https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=';

    /**
     * 分页获取用户手机号
     * @return [type] [description]
     */
    public function get_mobiles() {
        $page = I('get.page', 1, 'int'); // 页码
        $count = I('get.count', 10000, 'int');; // 每页显示条数

        $offset = 0;
        if ($page > 1) {
            $offset = ($page - 1) * $count;
        }

        $mobiles = M('User')->field('id,username')->order('id asc')->limit($offset . ',' . $count)->select();
        echo json_encode($mobiles);
    }

    /**
     * 检测用户是否投资过
     * @return [type] [description]
     */
    public function check_user_invest() {
        $uid = I('get.uid', 0, 'int'); // 页码
        $invest_res = M('userDueDetail')->field('id')->where('user_id = '.$uid)->find();

        if (!empty($invest_res)) {
            echo true;
        } else {
            echo false;
        }
    }

    // public function index() {
    //     header("Content-type: text/html; charset=utf-8");
    //     ini_set("memory_limit", "10M");
    //     ini_set("max_execution_time", 0);

    //     $mobiles = M('User')->order('id desc')->getField('id,username');

    //     $register_chongqing_count = 0;
    //     $invest_chongqing_count = 0;
    //     $register_jiling_count = 0;
    //     $invest_jiling_count = 0;
    //     $register_heilongjiang_count = 0;
    //     $invest_heilongjiang_count = 0;

    //     $result = array();
    //     foreach ($mobiles as $key => $value) {

    //         $res = $this->curl_get($this->api_url.$value);
    //         preg_match("/province:(.*),/", $res, $output_array);

    //         $province = null;
    //         if (count($output_array) > 1) {
    //             $province = $output_array[1];
    //             $province = str_replace('\'', '', $province);
    //         }

    //         if (!empty($province)) {
    //             // 1.重庆 2.吉林 3.黑龙江
    //             $arr = explode('重庆', $province);
    //             if (count($arr) > 1) {
    //                 $register_chongqing_count ++;
    //                 $invest_res = M('userDueDetail')->field('id')->where('user_id = '.$key)->find();
    //                 if (!empty($invest_res)) {
    //                     // 注册且投资
    //                     $invest_chongqing_count ++;
    //                 }
    //                 continue;
    //             }

    //             $arr = explode('吉林', $province);
    //             if (count($arr) > 1) {
    //                 $register_jiling_count ++;
    //                 $invest_res = M('userDueDetail')->field('id')->where('user_id = '.$key)->find();
    //                 if (!empty($invest_res)) {
    //                     // 注册且投资
    //                     $invest_jiling_count ++;
    //                 }
    //                 continue;
    //             }

    //             $arr = explode('黑龙江', $province);
    //             if (count($arr) > 1) {
    //                 $register_heilongjiang_count ++;
    //                 $invest_res = M('userDueDetail')->field('id')->where('user_id = '.$key)->find();
    //                 if (!empty($invest_res)) {
    //                     // 注册且投资
    //                     $invest_heilongjiang_count ++;
    //                 }
    //                 continue;
    //             }
    //         }
    //     }

    //     $result = array(
    //         'register_chongqing_count' => $register_chongqing_count,
    //         'invest_chongqing_count' => $invest_chongqing_count,
    //         'register_jiling_count' => $register_jiling_count,
    //         'invest_jiling_count' => $invest_jiling_count,
    //         'register_heilongjiang_count' => $register_heilongjiang_count,
    //         'invest_heilongjiang_count' => $invest_heilongjiang_count,
    //         );
    //     var_dump($result);
    //     die();
    //     // $this->export($result);
    //     // dump($result);
    // }

    // /**
    //  * 导出
    //  * @param  [type] $data [description]
    //  * @return [type]       [description]
    //  */
    // private function export($data) {
    //     vendor('PHPExcel.PHPExcel');
    //     $objPHPExcel = new \PHPExcel();

    //     $objPHPExcel->setActiveSheetIndex(0)->setTitle('省市用户数量统计')->setCellValue("A1", "省")->setCellValue("B1", "用户数量");
    //     $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
    //     $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    //     $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

    //     // 设置列表值
    //     $pos = 2;
    //     foreach ($data as $key => $value) {
    //         $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $key);
    //         $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $value);
    //         $pos ++;
    //     }

    //     header("Content-Type: application/vnd.ms-excel");
    //     header('Content-Disposition: attachment;filename="省市用户数量('.date('Y-m-d').').xls"');
    //     header('Cache-Control: max-age=0');
    //     $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    //     $objWriter->save('php://output');
        
    //     exit;
    // }

    /**
     * curl_get
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    private function curl_get($url) {
        //初始化
        $ch = curl_init();//设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);//执行并获取HTML文档内容

        $output = curl_exec($ch);//释放curl句柄
        curl_close($ch);//打印获得的数据

        $output = mb_convert_encoding($output, 'utf-8', 'GBK,UTF-8,ASCII');
        return $output;
    }
}