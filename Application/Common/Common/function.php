<?php
function re_xss($list)
{
	if(is_array($list))
		foreach ($list as $key => $value) 
	    {
	        foreach ($value as $keys => $values) 
	        {
	        	if(!is_array($values))
	            	$list[$key][$keys]=htmlspecialchars($values);
	            else
	            	foreach ($values as $k => $v) 
	            		$list[$key][$keys][$k]=htmlspecialchars($v);
	        }
	    }
	else
		$list=htmlspecialchars($list);
    return $list;
}

function get_shop_info($type,$shop_id)
{
	$Shop=D('Shop');

	$data['shop_id']=$shop_id;
	$data['is_open']=1;
	switch($type) {
		case 'name':
			$result=$Shop->where($data)->select();
			if($result[0]['shop_id']!='')
				return $result[0]['shop_name'];
			else
				return 0;
		case 'avator':
			$result=$Shop->where($data)->select();
			if($result[0]['shop_id']!='')
				return substr($result[0]['cover'], 1);
			else
				return 0;
	}
}

function get_goods_mess($goods_id)
{
    $message=D('Message');
    $num=$message->where("goods_id=%d and message_type='0'",$goods_id)->count();
 
    return $num;
}

function is_admin($uid,$username)	//判断是否为管理员，如果是管理员，返回权限等级1,2，否则返回0
{
	$admin=D('Admin');
	$result=$admin->where('admin_uid=%d and admin_name=%d',$uid,$username)->select();
	if(count($result)==1)
	    return $result[0]['admin_grade'];
	else
		return 0;
}

function send_message($from_uid,$from_username,$to_uid,$to_username,$content,$goods_id,$goods_name,$message_type)
{
	$message=D('Message');
    $data['data']=date("Y-m-d H:i:s",time());
    $data['from_uid']=$from_uid;
    $data['from_username']=$from_username;
    $data['to_uid']=$to_uid;
    $data['to_username']=$to_username;
    $data['content']=$content;
    $data['goods_id']=$goods_id;
    $data['status']=1;
    $data['goods_name']=$goods_name;
    $data['message_type']=$message_type;
    $result=$message->data($data)->add();
    $m_title = ' ';
    $m_content = '';
    $collection=M('Newgoods');
    $result=$collection->where('goods_id=%d',$goods_id)->select();
    $goods_name = '';
    if($result[0]['goods_name']!='') {
    	$goods_name = $result[0]['goods_name'];
    }
    switch ($message_type) {
    	case 0:
    		$m_content = '您在南航mall中的主题"'.$goods_name.'"有回复。'.'请访问http://my.nuaa.edu.cn/mall?m=mall&c=goods&id='.$goods_id.'查看';
    		break;
    	case 1:
    		$m_content = '您在南航mall收藏的物品"'.$goods_name.'"已被售出'.'请访问南航mall查看';
    		break;
    	case 2:
    		$m_content = '管理员为您在南航mall中的商品"'.$goods_name.'"完成交易'.'请访问南航mall查看';
    		break;
    	case 3:
    		$m_content = '管理员删除了您在南航mall中的商品"'.$goods_name.'"请访问南航mall查看';
    		break;
    	case 4:
    		$m_content = '您在南航mall中收藏的物品"'.$goods_name.'"已被删除'.'请访问南航mall查看';
    		break;
    }
    if($from_uid != $to_uid) {
	    if($uc_result = uc_pm_send($from_uid,$to_uid,$m_title,$m_content,1,0,0,0) > 0) {
	    	return $result;
	    }
	    else {
	    	/*$debug = M('Debug');
	    	$bug_data['message'] = $uc_result;
	    	$debug->data($bug_data)->add();*/
	    	return 0;
		}
	}
    if($result>0)  return $result; 
    else return 0;
}

function colle_over($goods_id,$type)	//当某件商品被删除或卖出时，发消息通知所有收藏的用户同时删除收藏记录 0为出错，1为成功操作 type1删除 0售出
{
	$collection=D('Collection');
	$result=$collection->where('goods_id=%d',$goods_id)->select();
	$not=1;
	if($result[0]['goods_id']!='')
		if($type==1)
			foreach($result as $key => $value) 
			{
				$res=send_message(session('uid'),session('username'),$value['uid'],$value['username'],'删除',$goods_id,$value['goods_name'],4);
				if($res==0)
				{
					$not=0;
					break;
				}			
			}
		else
			foreach($result as $key => $value) 
			{
				$res=send_message(session('uid'),session('username'),$value['uid'],$value['username'],'卖出',$goods_id,$value['goods_name'],1);
				if($res==0)
				{
					$not=0;
					break;
				}			
			}
	
	return $not;
}

function colle_delete($goods_id)
{
	$collection=M('Collection');
	$resu=$collection->where('goods_id=%d',$goods_id)->delete();
	
	return true;
}

function is_banned()	//判断当前用户是否超出禁言期，如果已超出则修改状态.如果没有禁言，则返回0，否则返回1
{
	$banned=M('Banned');
	$result=$banned->where("uid=%d and is_over='1'",session('uid'))->select();
	if($result[0]['uid']!='')
		$num=count($result);
	else
		$num=0;

	if($num > 0)
	{
		$time=date("Y-m-d",time());
		$end_time=date("Y-m-d",strtotime($result[0]['start_time'].'+'.$result[0]['last_time']).'days');
		
		if(strtotime($time) > strtotime($end_time))	//如果当前的时间大于结束的时间，那么就取消禁言状态，并且返回0
		{
            $num_nes=$banned->where("banned_id=%d and is_over='1'",$result[0]['banned_id'])->count();// echo $banned_id.'fd';
            if($num_nes == 1)    //如果有记录
            {
                $data['is_over']=0;
                $result=$banned->where("banned_id=%d and is_over='1'",$result[0]['banned_id'])->save($data);
            }
            return 0;
		}
		else
			return 1;
	}

	return 0;
}

function is_shopadmin()	//判断是否为店铺管理员
{
	$Shop=D('Shop');
	$condition['admin_id']=session('uid');

	return $Shop->where($condition)->count();	
}

function get_shopid()	//获取当前用户管理的商铺ID
{
	$Shop=D('Shop');
	$condition['admin_id']=session('uid');
	$data=$Shop->where($condition)->select();
	if($data[0]['shop_id'] > 0)
		return  $data[0]['shop_id'];
	else
		return 0;
}
function get_classify_num($num)	//获取分类的当天更新数目
{
	$newgoods=D('Newgoods');
	if(is_numeric($num) && $num >= 0 && $num <=11)
		$result=$newgoods->where("DATE(data)='".date('Y-m-d',time())."' and classify=".$num)->count();
	else
		return 0;

	return $result;
}

function check_ucenter($uid,$username)
{
    $user=uc_get_user($username,0);
    if($user[0]==$uid)
        return 1;

    return 0;
}