<?php
namespace Common\Model;
use Think\Model;
class MessageModel extends Model 
{
	public function get_goods_mess($goods_id)	//获取某个商品的消息条数
	{ 
    	return $this->where("goods_id=%d and message_type='0'",$goods_id)->count();
	}

	public function get_user_mess($uid)
	{
		return $this->where("to_uid=%d and status='1'",$uid)->count();
	}

	public function get_message($id)
	{
		return $this->where("goods_id=%d and message_type='0'",$id)->select();
	}

	public function get_ten_mess($uid)
	{
		$mess=$this->where("to_uid=%d and status='1'",session('uid'))->order('data desc')->select();
        if(count($mess)<10) 
        {
            $mess_old=$this->where("to_uid=%d and status='0'",session('uid'))->order('data desc')->limit(10-count($mess))->select();
        }
        if(count($mess)==0)
            $mess=$mess_old;
        else if(count($mess_old)!=0)   
            $mess=array_merge($mess,$mess_old);

        return $mess;
	}

	public function read_mess($to_uid,$goods_id)
	{
		$mess=$this->where("to_uid=%d and goods_id=%d",$to_uid,$goods_id)->select();
		foreach ($mess as $key => $value) 
		{
			$mess[$key]['status']=0;
			$res=$this->where('message_id=%d',$value['message_id'])->save($mess[$key]);
		}
		if($res===0)
			return -1;
		else
			return count($mess);
	}
}