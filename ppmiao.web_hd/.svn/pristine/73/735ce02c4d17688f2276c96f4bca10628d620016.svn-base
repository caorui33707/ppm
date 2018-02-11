<?php
namespace User\Controller;
use Think\Controller;

class AuthController extends BaseController {

    private $base;



    public function login(){

        $user_id = trim(I('get.user_id', '', 'strip_tags'));


        $user = M('user')->where(array('id'=>$user_id))->find();


        if($user){
            $auth = array(
                'uid'             => $user['id'],
                'username'        => $user['username'],
                'platcust'        => $user['platcust'],
                'add_time'          =>$user['add_time'],
                'salt'          =>$user['salt'],
                //'status'          => $user['status'],
            );
            session(USER_ONLINE_SESSION, $auth);
            var_dump($auth);
        }

    }

}