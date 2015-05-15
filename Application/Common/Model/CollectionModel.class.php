<?php
namespace Common\Model;
use Think\Model;
class CollectionModel extends Model 
{
	public function get_collection($list)	//查询当前用户的收藏记录，并且赋值到$list中 顺便查询消息记录
	{
		foreach ($list as $key => $value)   
        {
			$condition['goods_id']=$value['goods_id'];
	        $result=$this->where($condition)->select();
	        $list[$key]['col_num']=count($result);
	        foreach ($result as $keys => $values) 
	        {
	            if($values['uid']==session('uid'))
	            {
	                $list[$key]['is_co']=1;
	                break;
	            }
	        }
	    }
	    return $list;
	}

	public function get_one_collection($goods)
	{
        $result=$this->where('goods_id=%d',$goods['goods_id'])->select();
        $goods['col_num']=(!empty($result[0]['uid']))?count($result):0;
        foreach ($result as $keys => $values) 
        {
            if($values['uid']==session('uid'))
            {
                $goods['is_co']=1;
                break;
            }
        }

        return $goods;
	}

	public function exist($uid,$goods_id)
	{
		$condition['uid']=$uid;
        $condition['goods_id']=$goods_id;
        return $this->where($condition)->count();
	}

	public function add_colle($uid,$username,$goods_id,$goods_name)
	{
		$data['uid']=$uid;
        $data['goods_name']=$goods_name;
        $data['username']=$username;
        $data['goods_id']=$goods_id;
        $data['collect_time']=date("Y-m-d H:i:s",time());
        return $this->add($data);
	}

	public function get_alllikes($goods_id)
	{
		$result=$this->where('goods_id=%d',$goods_id)->select();
		foreach($result as $key => $value)
        { 
            if($value['uid']==session('uid'))
            { 
                unset($result[$key]);
            } 
        }

        return $result;
	}

	public function get_collect_num($goods_id)
	{
		return $this->where('goods_id=%d',$goods_id)->count();
	}
	public function get_user_colle_num($uid)
	{
		return $this->where('uid=%d',$uid)->count();
	}
	public function get_user_collection($uid,$page)
	{
		$list=$this->where('uid=%d',$uid)->page($page,10)->order('collect_time desc')->select();
		/*foreach ($list as $key => $value) 
        {
            if(strlen($value['goods_name']) > 24)
            {
                $list[$key]['goods_name']=mb_substr($value['goods_name'],0,12,'utf-8').'...';
            }
            if(strlen($value['description']) > 280)
            {
                $list[$key]['description']=mb_substr($value['description'],0,140,'utf-8').'...';
            }
        }*/

		return $list;
	}
}
