<?php
namespace Admin\Controller;
use Think\Controller;
header("Content-Type: text/html; charset=utf8");
Vendor('ThinkphpUcenter.UcApi');
class IndexController extends PublicController {
    public function index()
    { 
        if(session('?uid'))
        {
        	$app_title = '管理员 - 商城';
	        $this->assign('app_title',$app_title);

            $res=is_admin(session('uid'),session('username'));
            if($res>0)
                session('admin_grade',$res);
            else
                $this->error("您没有访问本模块的权限");

            $Notice=M('Notice');
            $result=$Notice->order('notice_time desc')->select();
            $this->assign("notice",$result);
            //

            $Ad=M('Ad');
            $resu=$Ad->order('grade desc')->select();
	        foreach ($resu as $key => $value) //默认显示第一张图片
            {
                if($value['content'] !='')
                {
                    $x=explode(',',$value['content']);
                    $resu[$key]['content']=substr($x[0], 1);
                    $m=explode('/', $resu[$key]['content']);
                    $resu[$key]['content']=$m[0].'/'.$m[1].'/'.$m[2];
                } 
            }
            $this->assign("ad",$resu);
            //print_r($resu);

            //获取待审核的shop的数目和信息
            $Shop=M('Shop');
            $shop_num=$Shop->where('is_open=%d',0)->count();
            $this->assign("shop_num",$shop_num);
            if($shop_num > 0)
            {
                $shop_con=$Shop->where('is_open=%d',0)->select();
                $this->assign("shop",$shop_con);
            }

	        $this->assign('m','mall');
	        $this->display('Index:main');
        }
        else
        {
        	$this->error('尚未登录无法访问本模块',"./?m=mall&load=1");
        	return 0;
        }
    }

    public function banned_log()    //展示禁言目录
    {
        if(session('?uid'))
        {
            $app_title = '管理员 - 商城';
            $this->assign('app_title',$app_title);

            $res=is_admin(session('uid'),session('username'));
            if($res>0)
                session('admin_grade',$res);
            else
                $this->error("您没有访问本模块的权限");
            //print_r($result);

            $banned=M('Banned');
            $result=$banned->order('start_time desc')->select();
            if($result[0]['uid']!='')
                $this->assign('data',$result);
            //print_r($result);
           
            $this->assign('m','mall');
            $this->display('Index:banned_log');
        }
        else
        {
            $this->error('尚未登录无法访问本模块');
            return 0;
        }
    }

    public function no_banned()     //取消禁言
    {
        if(isset($_GET['banned_id']) && $_GET['banned_id']!='' && is_numeric($_GET['banned_id']))
        {
            $banned_id = $_GET['banned_id'];  //如果没有设定页数的值，默认显示第一页。
        }       
        $banned=M('Banned');
        if(is_admin(session('uid'),session('username')) > 0)    //如果是管理员
        {
            $num=$banned->where("banned_id=%d and is_over='1'",$banned_id)->count();// echo $banned_id.'fd';
            if($num == 1)    //如果有记录
            {
                $data['is_over']=0;
                $result=$banned->where("banned_id=%d and is_over='1'",$banned_id)->save($data);

                if($result)
                    $this->success('取消禁言成功！');
                else
                    $this->error('取消禁言失败！！');
            }
        }
        else
        {
            $this->error('您没有访问本模块的权限');
        }
    }

    public function search_goods()	//ajax检索商品名称，并返回
    {
    	if(!IS_AJAX)
        {
        	$this->error("非法操作");
        	$r['code']=-1;
        }
        else if(!session('?uid')) 
        {
            $this->error("非法操作,尚未登录");
            $r['code']=-1;
        }
        else if(is_admin(session('uid'),session('username'))==0) 
        {
            $this->error("无查询权限");
            $r['code']=-1;
        }
        else
        {
        	$Newgoods=M('Newgoods');
            if($_POST['type']==1)
            {
                $map['goods_name'] = array('like','%'.$_POST['goods_name'].'%');
                $result=$Newgoods->where($map)->order('data desc')->page($_POST['page'],10)->select();
                $r['num']=$Newgoods->where($map)->count();
            }
            else if($_POST['type']==2)
            {
                $map['uid'] = $_POST['goods_name'];
                $result=$Newgoods->where($map)->order('data desc')->page($_POST['page'],10)->select();
                $r['num']=$Newgoods->where($map)->count();
            }
            else if($_POST['type']==3)
            {
                $map['username'] = $_POST['goods_name'];
                $result=$Newgoods->where($map)->order('data desc')->page($_POST['page'],10)->select();
                $r['num']=$Newgoods->where($map)->count();
            }
            foreach ($result as $key => $value) 
            {
                foreach ($value as $keys => $values) 
                {
                    $result[$key][$keys]=htmlspecialchars($values);
                }
            }
        	      	
        	$r['code']=1;
        	$r['data']=$result; 
        }
        $this->ajaxReturn($r);
    }

    public function search_user()   //ajax查询用户 type=1 按uid查询 type=2 按username查询
    {
        if(!IS_AJAX)
        {
            $this->error("非法操作");
            $r['code']=-1;
        }
        else if(!session('?uid')) 
        {
            $this->error("非法操作,尚未登录");
            $r['code']=-1;
        }
        else if(is_admin(session('uid'),session('username'))==0) 
        {
            $this->error("无查询权限");
            $r['code']=-1;
        }
        else
        {
            if($_POST['type']==1)
            {
                $result=uc_get_user($_POST['search_content'],1);
            }
            else if($_POST['type']==2)
            {
                $result=uc_get_user($_POST['search_content'],0);
            }
                    
            $r['code']=1;
            $r['data']=$result; 
        }
        $this->ajaxReturn($r);
    }

    public function give_admin()    //ajax给予用户管理员权限
    {
        if(!IS_AJAX)
        {
            $r['code']=-1;
        }
        else if(!session('?uid')) 
        {
            $r['code']=-1;
        }
        else if(is_admin(session('uid'),session('username'))!=2) 
        {
            $r['code']=-2;      //管理员权限不够
        }
        else
        {
            $admin=M('Admin');
            if($_POST['type']==1)
            {
                $uid=$_POST['data'];
                $result=uc_get_user($_POST['data'],1);
                $username=$result[1];
            }
            else if($_POST['type']==2)
            {
                $result=uc_get_user($_POST['data'],0);
                $uid=$result[0];
                $username=$_POST['data'];
            }
            $num=$admin->where('admin_uid=%d',$uid)->count();
            if($num!=0)
                $r['code']=-3;      //已经是管理员
            else
            {
                $data['admin_uid']=$uid;
                $data['admin_name']=$username;
                $data['admin_grade']=1;
                $data['admin_time']=date("Y-m-d",time());
                $res=$admin->data($data)->add();
                if($res>0)
                    $r['code']=1;
                else
                    $r['code']=-1;
            }
        }
        $this->ajaxReturn($r);
    }

    public function give_banned()   //ajax禁言
    {
        if(!IS_AJAX)
        {
            $r['code']=-1;
        }
        else if(!session('?uid')) 
        {
            $r['code']=-1;
        }
        else if(is_admin(session('uid'),session('username'))==0) 
        {
            $r['code']=-2;      //非管理员无法进行操作
        }
        else
        {
            $banned=M('Banned');
            if($_POST['type']==1)
            {
                $uid=$_POST['data'];
                $result=uc_get_user($_POST['data'],1);
                $username=$result[1];
            }
            else if($_POST['type']==2)
            {
                $result=uc_get_user($_POST['data'],0);
                $uid=$result[0];
                $username=$_POST['data'];
            }
            $num=$banned->where("uid=%d and is_over='1'",$uid)->count();
            if($num>0)  //
            {
                $r['code']=-3;  //已被禁言
            }
            else
            {
                $data['start_time']=date("Y-m-d H:i:s",time());
                $data['uid']=$uid;
                $data['username']=$username;
                $data['last_time']=$_POST['bannned_time'];
                $data['is_over']=1;
                $data['admin_username']=session('username');
                $result=$banned->data($data)->add();
                if($result>0)
                    $r['code']=1;
                else
                    $r['code']=-1;   
            }   
        }
        $this->ajaxReturn($r);
    }

    public function delete()    //ajax删除信息
    {
        if(!IS_AJAX)
            $r['code']=-1;
        else
        {
            if(!session('?uid'))  
            {
                $r['code']=-2;
            }
            else 
            {
                if(is_admin(session('uid'),session('username'))==0)
                {
                    $r['code']=-3;
                }
                else
                {     
                    $Newgoods=M("Newgoods");
                    $goods=$Newgoods->where("goods_id=%d",$_POST['goods_id'])->select();
                    $res=send_message(session('uid'),session('username'),$goods[0]['uid'],$goods[0]['username'],'',$_POST['goods_id'],$goods[0]['goods_name'],'3');
                    $ress=colle_over($_POST['goods_id'],1);
                    $dele_res=colle_delete($_POST['goods_id']);

                    $result=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->delete();
                    $r['res']=$res;
                    $r['ress']=$ress;
                    $r['dele_res']=$dele_res;
                    $r['result']=$result;
                     
                    if($result && $res && $ress && $dele_res)
                    {
                        $r['code']=1;
                    }  
                    else
                        $r['code']=-1;
                }     
            }  
        } 
        $this->ajaxReturn($r);
    }
}

?>