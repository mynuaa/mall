<?php
namespace Mall\Model;
use Think\Model;
class ConfigModel extends Model 
{
	//1新增数据时验证 2编辑数据时候验证  3全部情况下验证（默认） 
	protected $_validate = array
	(
		array('uid','require','uid必须填写！！'), 
		array('contact','1,30','联系方式超出长度！！',0,'length'),
		array('location',array(0,1,2),'非法输入！！',0,'in'),
		array('attention','0,10','非法输入！！',0,'length'),
	);
}