<?php
namespace Common\Model;
use Think\Model;
class NoticeModel extends Model 
{
	public function get_notice()
	{
		return $this->order('notice_grade desc')->select();
	}
	
}