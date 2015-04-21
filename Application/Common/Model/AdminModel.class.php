<?php
namespace Common\Model;
use Think\Model;
class AdminModel extends Model 
{
	public function check_admin($uid)	//返回管理员等级
	{
		$result=$this->where('admin_uid=%d',$uid)->select();
		if($result[0]['admin_id']!='')
			return $result[0]['admin_grade'];
		else
			return 0;
	}
}