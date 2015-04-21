<?php
namespace Common\Model;
use Think\Model;
class ShopModel extends Model 
{
	public function get_new_shop()
	{
        return $this->where("is_open=1")->limit(5)->order("create_time desc")->select();
	}

	public function save_shop_content($data)	//保存商品信息
	{
		return $this->where('shop_id=%d',$data['shop_id'])->save($data);
	}

	public function get_shop($shop_id)
	{
		$shop=$this->where('shop_id=%d and is_open=1',$shop_id)->select();
        $shop[0]['cover']=substr($shop[0]['cover'], 1);

		return $shop[0];
	}

	public function get_num($condition='')
	{
		return $this->where($condition)->count();
	}

	public function get_shopid($uid)
	{
		$result=$this->where("admin_id=%d and is_open='1'",$uid)->select();

		if($result[0]['admin_id']!='')
			return $result[0]['shop_id'];

		return 0;
	}

}