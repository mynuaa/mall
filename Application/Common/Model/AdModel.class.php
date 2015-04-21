<?php
namespace Common\Model;
use Think\Model;
class AdModel extends Model 
{
	public function get_ad()
	{
		$result=$this->order('grade desc')->select();
        foreach ($result as $key => $value) //默认显示第一张图片
        {
            if($value['content'] !='')
            {
                $x=explode(',',$value['content']);
                $result[$key]['content']=substr($x[0], 1);
                $m=explode('/', $result[$key]['content']);
                $result[$key]['content']=$m[0].'/'.$m[1].'/'.$m[2];
            } 
        }
		return $result;
	}
}