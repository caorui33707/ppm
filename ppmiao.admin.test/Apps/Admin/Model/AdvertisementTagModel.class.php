<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/5/25
 * Time: 上午10:57
 */
namespace Admin\Model;
use Think\Model;
class AdvertisementTagModel extends Model {

    private $_tabale = 'AdvertisementTag';

    const TAG_DEFAULT = 'tag_default_2018_02_09';

    /**
     * @return string
     * 获取所有的产品公告标签
     */
    public function getAdvertisementTagWhere($f=0){
        $time = time();
        $tagDefault = array();
        if($f) {
            $tagDefault = $this->getSpecialTag();
        }
        $tags = M($this->_tabale)->field('id,tag_title')->where('tag_type =2 and end_time > '. $time . ' and start_time < ' . $time . ' and is_delete = 0 ')->select();

        $tags = array_merge($tagDefault,$tags);

        return $tags;
    }

    public function getAdvertisementTagAll(){
        $time = time();
        $tagDefault = $this->getSpecialTag();

        $tags = M($this->_tabale)->field('id,tag_title')->where('is_delete = 0 and end_time > '. $time . ' and start_time < ' . $time )->select();
        $tags = array_merge($tagDefault,$tags);
        return $tags;
    }

    public function getAdvertisementTagType($type=1){ // 1:默认标签 2:其他标签
        $time = time();
        if($type==1){
            $tags = $this->getSpecialTag();
        }else{
            $tags = M($this->_tabale)->field('id,tag_title')->where('is_delete = 0 and end_time > '. $time . ' and start_time < ' . $time )->select();
        }
        return $tags;
    }

    //内部 特殊标签
    private function getSpecialTag(){
        $tagDefault = F(self::TAG_DEFAULT);
        if(!$tagDefault) {
            $tagDefault = M($this->_tabale)->field('id,tag_title')->where('tag_type =1 and is_delete = 0 ')->select();
            F(self::TAG_DEFAULT,$tagDefault);
        }
        return $tagDefault;
    }

}