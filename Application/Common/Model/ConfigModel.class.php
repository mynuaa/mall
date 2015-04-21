<?php
namespace Common\Model;
use Think\Model;

class ConfigModel extends Model 
{
	//1新增数据时验证 2编辑数据时候验证  3全部情况下验证（默认） 
	protected $_validate = array
	(
		array('uid','require','uid必须填写！！'), 
		array('contact','1,30','联系方式超出长度！！',0,'length'),
		array('location',array(0,1,2),'非法输入！！',0,'in'),
		array('attention','0,26','非法输入！！',0,'length'),
	);
	public function get_likes($uid)		//返回关注信息的数组
	{
		$data=$this->where('uid=%d',$uid)->select();
		if(!empty($data[0]['uid']))
		{
			$data[0]['attention']=explode(',',$data[0]['attention']);
			foreach ($data[0]['attention'] as $key => $value) 
			{
				if($value = 'x')
				{
					$data[0]['attention'][$key]=0;
				}
			}

			return $data[0]['attention'];
		}

        return 0;
	}

	public function get_attention()	//右侧 我的关注 
	{

	}

	public function get_config($uid)
	{
		$data=$this->where('uid=%d',$uid)->select();
		if(!empty($data[0]['uid']))
		{
			$data[0]['attention']=explode(',',$data[0]['attention']);
		}

		return $data[0];
	}

	public function set_config($data)
	{
		$list=$this->where('uid=%d',$data['uid'])->select();
		$data['data']=date("Y-m-d H:i:s",time());
		if(empty($list[0]['uid']))	//如果不存在设置
		{
			return $this->data($data)->add()>0?1:-1;
		}
		else
		{
			return $this->where('uid=%d',$data['uid'])->save($data)>0?1:-2;
		}
	}
}