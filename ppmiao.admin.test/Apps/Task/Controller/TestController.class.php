<?php
namespace Task\Controller;
use Think\Controller;


class TestController extends Controller
{

    public function __construct()
    {
        load('Admin/function');
    }

    public function test1(){
        for($i=0;$i<=10000;$i++){
            $this->put_file('test1_'.$i);
        }

    }

    public function test2(){
        for ($i=0;$i<=100;$i++){
            $this->put_file('test2_'.$i);
        }
    }

    public function put_file($s){
        $fileName = LOG_PATH  . 'test.log';
        $s  = '['.date("Y-m-d H:i:s").']  '.$s."\r\n";
        file_put_contents($fileName,$s,FILE_APPEND);
    }
}