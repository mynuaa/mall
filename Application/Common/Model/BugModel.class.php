<?php
namespace Common\Model;
use Think\Model;
class BugModel extends Model 
{
	public function add_bug($uid,$username,$bug_content,$email)
	{
		$data['uid']=$uid;
        $data['username']=$username;
        $data['time']=date("Y-m-d H:i:s",time());
        $data['bug_content']=$bug_content;
        $data['email']=$email;

        return $this->add($data);
	}
}