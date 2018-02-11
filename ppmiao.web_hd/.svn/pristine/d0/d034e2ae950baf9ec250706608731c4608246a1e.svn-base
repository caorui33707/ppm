<?php
namespace Home\Controller;
use Think\Controller;

class CommonController extends Controller{

    //获取验证码
    public function verify(){
        ob_end_clean();
        $config = array(
            'fontSize'    =>    30,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            'useNoise'    =>    true, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
   
    
    public function getcode(){
        $product = '';
        $extno = '';
        $code = createSMSCode(5);  
        $msg = '您申请的手机短信的验证码为：'.$code.'，有效期1分钟.';
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        $data = [
            'account' =>  C('SMS_NAME'),
            'pswd' =>  C('SMS_PWD'),
            'mobile' =>  $mobile,
            'msg' =>  $msg,
            'needstatus' =>  'false',
            'product' => $product,
            'extno' => $extno
        ];
        $re = curlPost(C('SMS_URL'),$data);
        $arr = explode(",", $re);
        if($arr) {
            if($arr[1] == 0) {                
                S(md5($mobile),$code,60);                
                $this->ajaxReturn(array('status'=>1,'info'=>''));
            }
        }
        $this->ajaxReturn(array('status'=>0,'info'=>''));
    }
    
    public function getS(){
        $mobile = '18606502829';
        echo S(md5($mobile));
    }
    
    /**
    * 存管企业用户提现发送手机验证码
    * @date: 2017-7-12 下午5:45:54
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function msgcode(){
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        
        $plainText =  \SignUtil::params2PlainText($req);
        
        $sign =  \SignUtil::sign($plainText);
        
        $req['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        return json_decode($fd->post('/project/publish',$req),true);
    }

    /**
     * 人民币小写转大写
     *
     * @param string $number 数值
     * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆"
     * @param bool $is_round 是否对小数进行四舍五入
     * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30，
     *             有的系统要求输出"壹仟玖佰陆拾元零叁角"，实际上"壹仟玖佰陆拾元叁角"也是对的
     * @return string
     */
    protected function num2rmb($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE){
        // 将数字切分成两段
        $parts = explode('.', $number, 2);
        $int = isset($parts[0]) ? strval($parts[0]) : '0';
        $dec = isset($parts[1]) ? strval($parts[1]) : '';

        // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
        $dec_len = strlen($dec);
        if (isset($parts[1]) && $dec_len > 2)
        {
            $dec = $is_round
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1)
                : substr($parts[1], 0, 2);
        }

        // 当number为0.001时，小数点后的金额为0元
        if(empty($int) && empty($dec))
        {
            return '零';
        }

        // 定义
        $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖');
        $uni = array('','拾','佰','仟');
        $dec_uni = array('角', '分');
        $exp = array('', '万');
        $res = '';

        // 整数部分从右向左找
        for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++)
        {
            $str = '';
            // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
            for($j = 0; $j < 4 && $i >= 0; $j++, $i--)
            {
                $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位
                $str = $chs[$int{$i}] . $u . $str;
            }
            //echo $str."|".($k - 2)."<br>";
            $str = rtrim($str, '0');// 去掉末尾的0
            $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0
            if(!isset($exp[$k]))
            {
                $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位
            }
            $u2 = $str != '' ? $exp[$k] : '';
            $res = $str . $u2 . $res;
        }

        // 如果小数部分处理完之后是00，需要处理下
        $dec = rtrim($dec, '0');

        // 小数部分从左向右找
        if(!empty($dec))
        {
            $res .= $int_unit;

            // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
            if ($is_extra_zero)
            {
                if (substr($int, -1) === '0')
                {
                    $res.= '零';
                }
            }

            for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++)
            {
                $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位
                $res .= $chs[$dec{$i}] . $u;
            }
            $res = rtrim($res, '0');// 去掉末尾的0
            $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0
        }
        else
        {
            $res .= $int_unit . '整';
        }
        return $res;
    }


    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args) {
        $this->display(404);
    }


}