<?php
namespace Mall\Model;
use Think\Model;

class BbModel extends Model
{
	public function insert($data='')
	{
		return $this->data($data)->add();
	}

	public function select_x($username='')
	{
		return $this->where("username=".$username)->select();
	}

}