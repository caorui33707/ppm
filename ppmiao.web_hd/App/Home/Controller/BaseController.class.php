<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller{

    public function _initialize(){

    }

    public function _empty(){
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("Public:404");
        //status_403();
    }

    protected function _jump_url_arr($ch){ // 所有安卓渠道和地址
        switch ($ch) {
            case 'llb':
                $url = 'https://image.ppmiao.com/download/app-llb-release-97-2.6.0.apk';
                break;
            case 'diantai':
                $url = 'https://image.ppmiao.com/download/app-diantai-release-97-2.6.0.apk';
                break;
            case 'InMoBi':
                $url = 'https://image.ppmiao.com/download/app-inmobi-release-97-2.6.0.apk';
                break;
            case 'danlan':
                $url = 'https://image.ppmiao.com/download/app-danlan-release-97-2.6.0.apk';
                break;
            case 'jrtt':
                $url = 'https://image.ppmiao.com/download/app-jrtt-release-97-2.6.0.apk';
                break;
            case 'jrtt2':
                $url = 'https://image.ppmiao.com/download/app-jrtt2-release-97-2.6.0.apk';
                break;
            case 'fcjn':
                $url = 'https://image.ppmiao.com/download/app-fcjn-release-97-2.6.0.apk';
                break;
            case 'dianxin':
                $url = 'https://image.ppmiao.com/download/app-dianxin-release-97-2.6.0.apk';
                break;
            case 'shenma':
                $url = 'https://image.ppmiao.com/download/app-shenma-release-97-2.6.0.apk';
                break;
            case 'xihuzhisheng':
                $url = 'https://image.ppmiao.com/download/app-xihuzhisheng-release-97-2.6.0.apk';
                break;
            case 'huisuoping':
                $url = 'https://image.ppmiao.com/download/app-huisuoping-release-97-2.6.0.apk';
                break;
            case 'chubao':
                $url = 'https://image.ppmiao.com/download/app-chubao-release-97-2.6.0.apk';
                break;
            case 'PPTV':
                $url = 'https://image.ppmiao.com/download/app-PPTV-release-97-2.6.0.apk';
                break;
            case '_907':
                $url = 'https://image.ppmiao.com/download/app-_907-release-97-2.6.0.apk';
                break;
            case 'wenjuanxing':
                $url = 'https://image.ppmiao.com/download/app-wenjuanxing-release-97-2.6.0.apk';
                break;
            case 'wenjuanxing2':
                $url = 'https://image.ppmiao.com/download/app-wenjuanxing2-release-97-2.6.0.apk';
                break;
            case 'qutoutiao':
                $url = 'https://image.ppmiao.com/download/app-qutoutiao-release-97-2.6.0.apk';
                break;
            case 'zhonghua':
                $url = 'https://image.ppmiao.com/download/app-zhonghua-release-97-2.6.0.apk';
                break;
            case 'qixi':
                $url = 'https://image.ppmiao.com/download/app-qixi-release-97-2.6.0.apk';
                break;
            case '_918zs':
                $url = 'https://image.ppmiao.com/download/app-_918zs-release-97-2.6.0.apk';
                break;
            case 'tengxun':
                $url = 'https://image.ppmiao.com/download/app-tengxun-release-97-2.6.0.apk';
                break;
            case 'jumeitoutiao':
                $url = 'https://image.ppmiao.com/download/app-jumeitoutiao-release-97-2.6.0.apk';
                break;
            case 'qq1':
                $url = 'https://image.ppmiao.com/download/app-qq1-release-97-2.6.0.apk';
                break;
            case 'qq2':
                $url = 'https://image.ppmiao.com/download/app-qq2-release-97-2.6.0.apk';
                break;
            case 'qq3':
                $url = 'https://image.ppmiao.com/download/app-qq3-release-97-2.6.0.apk';
                break;
            case 'qq4':
                $url = 'https://image.ppmiao.com/download/app-qq4-release-97-2.6.0.apk';
                break;
            case 'qq5':
                $url = 'https://image.ppmiao.com/download/app-qq5-release-97-2.6.0.apk';
                break;
            case 'aiqiyi':
                $url = 'https://image.ppmiao.com/download/app-aiqiyi-release-97-2.6.0.apk';
                break;
            case 'wydz':
                $url = 'https://image.ppmiao.com/download/app-wydz-release-97-2.6.0.apk';
                break;
            case 'dailuopan':
                $url = 'https://image.ppmiao.com/download/app-dailuopan-release-97-2.6.0.apk';
                break;
            case 'xjzdy':
                $url = 'https://image.ppmiao.com/download/app-xjzdy-release-97-2.6.0.apk';
                break;
            case 'quanzi':
                $url = 'https://image.ppmiao.com/download/app-quanzi-release-97-2.6.0.apk';
                break;
            case 'mmsc':
                $url = 'https://image.ppmiao.com/download/app-mmsc-release-97-2.6.0.apk';
                break;
            case 'bbs':
                $url = 'https://image.ppmiao.com/download/app-bbs-release-97-2.6.0.apk';
                break;
            case 'qlgdt':
                $url = 'https://image.ppmiao.com/download/app-qlgdt-release-97-2.6.0.apk';
                break;
            case 'qltt':
                $url = 'https://image.ppmiao.com/download/app-qltt-release-97-2.6.0.apk';
                break;
            case 'byong1':
                $url = 'https://image.ppmiao.com/download/app-byong1-release-97-2.6.0.apk';
                break;
            case 'byong2':
                $url = 'https://image.ppmiao.com/download/app-byong2-release-97-2.6.0.apk';
                break;
            case 'byong3':
                $url = 'https://image.ppmiao.com/download/app-byong3-release-97-2.6.0.apk';
                break;
            case 'byong4':
                $url = 'https://image.ppmiao.com/download/app-byong4-release-97-2.6.0.apk';
                break;
            case 'byong5':
                $url = 'https://image.ppmiao.com/download/app-byong5-release-97-2.6.0.apk';
                break;
            case 'ws1':
                $url = 'https://image.ppmiao.com/download/app-ws1-release-97-2.6.0.apk';
                break;
            case 'ws2':
                $url = 'https://image.ppmiao.com/download/app-ws2-release-97-2.6.0.apk';
                break;
            case 'ws3':
                $url = 'https://image.ppmiao.com/download/app-ws3-release-97-2.6.0.apk';
                break;
            case 'ws4':
                $url = 'https://image.ppmiao.com/download/app-ws4-release-97-2.6.0.apk';
                break;
            case 'ws5':
                $url = 'https://image.ppmiao.com/download/app-ws5-release-97-2.6.0.apk';
                break;
            case 'wifiktt':
                $url = 'https://image.ppmiao.com/download/app-wifiktt-release-97-2.6.0.apk';
                break;
            case 'gjc':
                $url = 'https://image.ppmiao.com/download/app-gjc-release-97-2.6.0.apk';
                break;
            case 'ttcf':
                $url = 'https://image.ppmiao.com/download/app-ttcf-release-97-2.6.0.apk';
                break;
            case 'dshb':
                $url = 'https://image.ppmiao.com/download/app-dshb-release-97-2.6.0.apk';
                break;
            case 'yjwh':
                $url = 'https://image.ppmiao.com/download/app-yjwh-release-97-2.6.0.apk';
                break;
            case 'lqw': // 利趣网
                $url = 'https://image.ppmiao.com/download/app-lqw-release-97-2.6.0.apk';
                break;
            case 'hnw': //海鸟窝
                $url = 'https://image.ppmiao.com/download/app-hnw-release-97-2.6.0.apk';
                break;
            case 'llyh'://资产存管
                $url = 'https://image.ppmiao.com/download/app-llyh-release-97-2.6.0.apk';
                break;
            case 'tyjf'://天翼积分
                $url = 'https://image.ppmiao.com/download/app-tyjf-release-97-2.6.0.apk';
                break;
            case 'fg'://飞购
                $url = 'https://image.ppmiao.com/download/app-fg-release-97-2.6.0.apk';
                break;
            case 'yimeng'://yimeng
                $url = 'https://image.ppmiao.com/download/app-yimeng-release-97-2.6.0.apk';
                break;
            case 'cjxw'://财经新闻
                $url = 'https://image.ppmiao.com/download/app-cjxw-release-97-2.6.0.apk';
                break;
            case 'jinrongtt': //金融头条
                $url = 'https://image.ppmiao.com/download/app-jinrongtt-release-97-2.6.0.apk';
                break;
            case 'kek': //科尔康
                $url = 'https://image.ppmiao.com/download/app-kek-release-97-2.6.0.apk';
                break;
            //default:
            //$url = 'https://image.ppmiao.com/download/app-llb-release-97-2.6.0.apk';
        }

        return $url;
    }

}