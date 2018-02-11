<?php
/**
 * 定时处理任务-清理过期红包
 */
namespace Task\Controller;

use Think\Controller;

class ClearExpireBagController extends Controller
{

    public function index()
    {
        $row['status'] = 2;
        $bagList = M("UserRedenvelope")->field("id")->where("status = 0 and unix_timestamp(`expire_time`)< UNIX_TIMESTAMP()") ->select();           
        if (! empty($bagList)) {            
            foreach ($bagList as $val) {                
                M("UserRedenvelope")->where(array('id' => $val['id']))->save($row);
            }
        }
        //exit("处理完成");
    }
}