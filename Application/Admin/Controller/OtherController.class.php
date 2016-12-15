<?php
namespace Admin\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');
class OtherController extends PublicController
{
	public function index()	//查看管理员列表
	{
		if(session('?uid'))
        {
			$app_title = '查看管理员 - 商城';
		    $this->assign('app_title',$app_title);

		    $res=is_admin(session('uid'),session('username'));
            if($res>0)
                session('admin_grade',$res);
            else
                $this->error("您没有访问本模块的权限");

	        $Admin=M('Admin');
	        $not=$Admin->order('admin_grade desc')->select();
	        $this->assign("data",$not);

	        $this->display('Index:admin_list');
	    }
	    else
        {
        	$this->error('尚未登录无法访问本模块');
        	return 0;
        }
	}

	public function delete_admin()	//删除管理员权限
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
        	$Admin=M('Admin');
        	$result=$Admin->where('admin_id=%d',$_POST['admin_id'])->delete();
        	if($result==1)
        		$r['code']=1;
        	else
        		$r['code']=-1;
        }
        $this->ajaxReturn($r);
	}

    public function add_ad()    //增加广告(全站推荐)
    {
        if(!session('?uid'))
        {
            $this->error("发布新需求需要先登录",'./?m=mall');
            return false;
        }
        if(!IS_POST)
        {
            $this->error('非法请求');
            return false;
        }  
        if(is_admin(session('uid'),session('username'))!=2) 
        {
            $this->error('管理员权限不够');
            return false;
        }
        $upload = new \Think\Upload();
        $upload->maxSize   =     10242880 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath  =      './';

        $Ad = D("Ad");
        $data['grade']=$_POST['grade'];
        $data['href']=$_POST['href'];
        $data['time']=date("Y-m-d H:i:s",time());

        $info  =  $upload->upload();    
        if(!$info) 
        {
            $this->error($upload->getError());    
            return false;
        }
        else
        {
            foreach($info as $file)
            {        
                $data['content']=$file['savepath'].$file['savename'];    
            }
            $result=$Ad->data($data)->add();
            if($result)
                $this->success('增加广告成功');
        }


    }

    public function delete_ad()     //ajax删除广告
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
            $Ad=M('Ad');
            $result=$Ad->where('ad_id=%d',$_POST['ad_id'])->delete();
            if($result==1)
                $r['code']=1;
            else
                $r['code']=-1;
        }
        $this->ajaxReturn($r);
    }

    public function get_bug()   //获取提交的bug
    {
        $Bug=M('Bug');
        $result=$Bug->order('time desc')->select();
        $this->assign("bug",re_xss($result));
        $this->display('Index:bug');
        //print_r($result);

        $app_title = '查看bug反馈';
        $this->assign('app_title',$app_title);
    }

    public function shop_edit_page()    //展示编辑商铺信息的页面
    {
        if(session('?uid'))
        {
            $app_title = '查看管理员 - 商城';
            $this->assign('app_title',$app_title);

            $res=is_admin(session('uid'),session('username'));
            if($res>0)
                session('admin_grade',$res);
            else
                $this->error("您没有访问本模块的权限");

            $id=$_GET['id'];
            $Shop=M('Shop');
            $result=$Shop->where('shop_id=%d',$id)->select();
            $this->assign("shop",$result[0]);

            $this->display('Index:shop_content');

        }
        else
        {
            $this->error('尚未登录无法访问本模块');
            return 0;
        }

        
    }

    public function save_shop() //管理员保存编辑后的商铺信息
    {
        if(session('?uid') || !IS_POST)
        {
            $app_title = '查看管理员 - 商城';
            $this->assign('app_title',$app_title);

            $res=is_admin(session('uid'),session('username'));
            if($res>0)
                session('admin_grade',$res);
            else
                $this->error("您没有访问本模块的权限");

            $Shop=D('Shop');
            $data['shop_id']=$_POST['shop_id'];
            $data['shop_name']=$_POST['shop_name'];
            $data['admin_id']=$_POST['admin_id'];
            $data['admin_name']=$_POST['admin_name'];

            $user=uc_get_user($_POST['admin_name'],0);
            if($user[0]==$_POST['admin_id'])
            {
                if($Shop->save_shop_content($data))
                    $this->success("保存成功",'./?m=admin');
                else
                    $this->success("操作失败",'./?m=admin');
            }
            
        }
        else
        {
            $this->error('尚未登录无法访问本模块');
            return 0;
        }
    }

    public function get_tj()    //获取统计结果
    {
        if(!IS_AJAX || !session('?uid'))
        {
            $r['code']=-1;
        }
        else if(is_admin(session('uid'),session('username'))<=0) 
        {
            $r['code']=-2;      //管理员权限不够
        }
        else
        {
            $r['code']=1;
            $Newgoods=M('Newgoods');
            $r['all_newgoodsnum_chu']=$Newgoods->where("need_type='1'")->count();//总出
            $r['all_newgoodsnum_qiu']=$Newgoods->where("need_type='0'")->count();//总求
            $r['today_newgoodsnum_chu']=$Newgoods->where("DATE(data)='".date('Y-m-d',time())."' and need_type='1'")->count();//昨日出
            $r['today_newgoodsnum_qiu']=$Newgoods->where("DATE(data)='".date('Y-m-d',time())."' and need_type='0'")->count();//昨日求
            $Soldout=M('Soldout');
            $r['all_soldgoodsnum_chu']=$Soldout->where("need_type='1'")->count();//总出 成交
            $r['all_soldgoodsnum_qiu']=$Soldout->where("need_type='0'")->count();//总求 成交
            $r['today_soldgoodsnum_chu']=$Soldout->where("CLOSE_TIME='".date('Y-m-d',time())."' and need_type='1'")->count();//昨日出 成交
            $r['today_soldgoodsnum_qiu']=$Soldout->where("CLOSE_TIME='".date('Y-m-d',time())."' and need_type='0'")->count();//昨日求 DATE
            $Config=M('Config');
            $r['all_usernum']=$Config->count();//总发帖数
            $r['today_usernum']=$Config->where("DATE(data)='".date('Y-m-d',time())."'")->count();//今日贴数
        }
        $this->ajaxReturn($r);
    }

    public function get_chart(){
        if(!IS_AJAX || !session('?uid')){
            $r['code']=-1;
        }else if(is_admin(session('uid'),session('username'))<=0) {
            $r['code']=-2;      //管理员权限不够
        }else{
            $r['code']=1;
            $Soldout=M('Soldout');
            
            for($i=0;$i<7;$i++){//直接在服务端输出迎合g2库的json格式
                $r["chu"][$i]['sum']=$Soldout->where("CLOSE_TIME='".date('Y-m-d',strtotime("-{$i} day"))."' and need_type='1'")->count();
                $r["chu"][$i]['date']=date('m月d日',strtotime("-{$i} day"));
                $r["qiu"][$i]['sum']=$Soldout->where("CLOSE_TIME='".date('Y-m-d',strtotime("-{$i} day"))."' and need_type='0'")->count();
                $r["qiu"][$i]['date']=date('m月d日',strtotime("-{$i} day"));
            }
        
        }
        $this->ajaxReturn($r);
    }
}