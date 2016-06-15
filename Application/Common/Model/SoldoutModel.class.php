<?php
namespace Common\Model;
use Think\Model;
class SoldoutModel extends Model 
{
	public function check_exist($goods_id)
	{
		return $this->where("goods_id=%d",$goods_id)->count();
	}

	public function get_goods($goods_id)
	{
		$goods=$this->where("goods_id=%d",$goods_id)->select();
		$goods[0]['photo']=explode(',',$goods[0]['photo']);
        foreach ($goods[0]['photo'] as $key => $value)
        {
        	$goods[0]['photo'][$key]=substr($value,1);
        }

        foreach ($goods[0] as $key => $value) 	//xss处理
        {
            if($key!="photo")
                $goods[0][$key]=htmlspecialchars($value);
        }
        //检测是否为商铺商品
        if($goods[0]['Concern_shopid'] != 0)   //如果属于商铺商品
        {
            $goods[0]['username']=get_shop_info('name',$goods[0]['Concern_shopid']);
            $goods[0]['shop_avator']=get_shop_avator($goods[0]['Concern_shopid']);
        }

        return $goods[0];
	}
    
    public function get_num($condition='')  //获取记录总数
    {
        return $this->where($condition)->count();
    }

    public function get_page($condition='',$page)   //获取某一页的所有数据
    {
        $list = $this->page($page,10)->where($condition)->order('data desc')->select();    
        foreach ($list as $key => $value) 
        {
            if($value['photo'] !='')//默认显示第一张图片
            {
                $x=explode(',',$value['photo']);
                $list[$key]['photo']=substr($x[0], 1);
            } 
            if(strlen($value['goods_name']) > 28)   //限制题目长度
            {
                $list[$key]['goods_name']=mb_substr($value['goods_name'],0,12,'utf-8').'...';
            }
            if(strlen($value['description']) > 280) //限制内容长度
            {
                $list[$key]['description']=mb_substr($value['description'],0,140,'utf-8').'...';
            }
            if($value['Concern_shopid'] != 0)   //如果属于商铺商品
            {
                $list[$key]['username']=get_shop_info('name',$value['Concern_shopid']);
                $list[$key]['shop_avator']=get_shop_avator($value['Concern_shopid']);
            }
        }

        return $list;
    }
}