<?php
namespace Admin\Controller;
use Think\Controller;
class NoticeController extends Controller
{
	public function index()
	{
		$app_title = '查看公告 - 商城';
	    $this->assign('app_title',$app_title);

	    if(is_numeric($_GET['id']) && $_GET['id'] > 0)//如果商品编号为数字且数字正确
        {
            $id = $_GET['id'];
        }
        if($id == -1)
        {
        	$this->error('要查看的公告不存在');
        	return false;
        }   
        $notice=M('Notice');
        $not=$notice->where('notice_id=%d',$id)->select();
        if(count($not)==1 && $not[0]['notice_id']!='')
        {
        	$this->assign("content",$not[0]);
        }
        else
        {
        	$this->error('要查看的公告不存在');
        	return false;
        }

        //初始化右侧
        //初始化公告
        $Notice=M('Notice');
        $not=$Notice->order('notice_grade desc')->select();
        //print_r($not);
        $this->assign("notice",$not);
        //初始化广告
        $ad=M('Ad');
        $ad_c=$ad->order('grade desc')->select();
        foreach ($ad_c as $key => $value) //默认显示第一张图片
        {
            if($value['content'] !='')
            {
                $x=explode(',',$value['content']);
                $ad_c[$key]['content']=substr($x[0], 1);
                $m=explode('/', $ad_c[$key]['content']);
                $ad_c[$key]['content']=$m[0].'/'.$m[1].'/'.$m[2];
            } 
        }
        $this->assign("ad",$ad_c);
        //初始化最新店铺
        $Shop=M('Shop');
        $sh=$Shop->where("is_open=1")->order("create_time desc")->select();
        if($sh[0]['shop_id']!='')
            $this->assign("new_shop",$sh);

        $this->assign('m','mall');
        $this->display('Index:notice');
	}

	public function add_notice()    //增加全站公告
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
            $Notice=D('Notice');
            $data['notice_title']=$_POST['title'];
            $data['notice_content']=$_POST['content'];
            $data['notice_grade']=$_POST['grade'];
            $data['notice_time']=date("Y-m-d",time());
            $result=$Notice->data($data)->add();
            if($result>0)
                $r['code']=1;
            else
                $r['code']=-5;
        }
        $this->ajaxReturn($r);
    }

    public function delete()	//删除公告
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
        	$Notice=M('Notice');
        	$condition['notice_id']=$_POST['id'];
        	$result=$Notice->where($condition)->delete();
        	if($result==1)
        		$r['code']=1;
        	else
        		$r['code']=-1;
        }
        $this->ajaxReturn($r);
    }
}