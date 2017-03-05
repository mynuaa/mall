<?php
namespace Mall\Controller;
use Think\Controller;
class NewgoodsController extends PublicController
{
	public function index()
	{
		$Message = D('Message');
		if(!session('?uid'))
		{
			$this->error("发布新需求需要先登录",'./?m=mall&load=1');
			return false;
		}
		if(session('?uid'))
        {
             $this->assign('mess_id',$Message->get_user_mess(session('uid')));
        }
		$Config=D('Config');
		$Shop=D('Shop');
        $data=$Config->where('uid=%d',session('uid'))->select();
        $this->assign('data',$data[0]);
		
		$this->assign('username',session('username'));
        $this->assign('uid',session('uid'));
        $this->assign('ver',session('?verify_code'));

		$app_title = '发布新需求 - 纸飞机';
		$this->assign('m','mall');
    	$this->assign('app_title',$app_title);

    	$shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",1);

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
        $sh=$Shop->where("is_open=1")->order("create_time desc")->select();
        if($sh[0]['shop_id']!='')
            $this->assign("new_shop",$sh);

    	$this->display('Index:newgoods');
    	// if($_POST['in_where']!=1)
    	// {
    	// 	$this->display('Index:newgoods');
    	// }
    	// else
    	// 	$this->error('换客天地尚未开放，敬请期待');
	}

	public function ver()	//生成验证码
	{
		$config = array(    
			'fontSize'  => 12,    // 验证码字体大小    
			'length'    => 4,     // 验证码位数    
			'useNoise'  => false, // 关闭验证码杂点
			'expire'    => 600,
			'useCurve'	=> false,
			'bg'		=> array(197,235,221),
			);
		$Verify = new \Think\Verify($config);	//验证码
		$Verify->entry();
	}

	public function check_ver()	//ajax检测验证码
	{	
    	$r['code']=0;
		$verify = new \Think\Verify();    
		if($verify->check($_POST['ver_val']))
		{
			$r['code']=1;
		}
		$this->ajaxReturn($r);
	}

	public function add_goods()
	{
		//echo "<pre>";print_r($_POST);
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
		if(is_banned())
		{
			$this->error('您已被禁言，不能发布商品');
			return false;
		}
		if($_POST['is_shop']=="on")
			$data['Concern_shopid']=get_shopid();

		$verify = new \Think\Verify();    
		$upload = new \Think\Upload();
		$upload->maxSize   =     10242880 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath  =      './';
		
		$Newgoods = D("Newgoods");
		$Config = D("Config");
		$data['uid']=session('uid');
		$data['data']=date("Y-m-d H:i:s",time());
		$data['username']=session('username');
		//自动更新个人设置
		$conf_data['uid']=session('uid');
		$conf_data['contact']=$_POST['contact'][0];
		$conf_data['location']=$_POST['location1'];
		$con=$Config->set_config($conf_data);
		//print_r($conf_data);

		foreach ($_POST['goods_name'] as $key=>$value)
		{
			$data['photo']='';
			$data['need_type']=$_POST['need_type'.$_POST['table_num_box'][$key]];
			$data['price']=$_POST['price'][$key];
			$data['description']=$_POST['description'][$key];
			$data['location']=$_POST['location'.$_POST['table_num_box'][$key]];
			$data['classify']=$_POST['classify'.$_POST['table_num_box'][$key]];
			$data['goods_name']=$_POST['goods_name'][$key];
			$data['contact']=$_POST['contact'][$key];
			
			if($data['need_type']==1)//出售
			{
				$res=$Newgoods->create($data,4);
			}
			else
			{
				$res=$Newgoods->create($data);
			}

			if(!$res)
			{
				$this->error($Newgoods->getError());
			}
			else
			{
				if($data['need_type']==1)	//出售时
				{
					foreach($_FILES['photo'.$_POST['table_num_box'][$key]]['name'] as $keys => $values) 
					{
						$_FILES['x']['name'][0]= $_FILES['photo'.$_POST['table_num_box'][$key]]['name'][$keys];
						$_FILES['x']['type'][0]= $_FILES['photo'.$_POST['table_num_box'][$key]]['type'][$keys];
						$_FILES['x']['tmp_name'][0]= $_FILES['photo'.$_POST['table_num_box'][$key]]['tmp_name'][$keys];
						$_FILES['x']['error'][0]= $_FILES['photo'.$_POST['table_num_box'][$key]]['error'][$keys];
						$_FILES['x']['size'][0]= $_FILES['photo'.$_POST['table_num_box'][$key]]['size'][$keys];
					
						$info = $upload->uploadOne($_FILES['x']);
						if(!$info)
						{
							if($upload->getError()!="没有文件被上传！")
								$this->error($upload->getError());
						}
						else
						{   
							$data['photo'] = $data['photo'].$info['savepath'].$info['savename'].",";
							$info['savepath']=ltrim($info['savepath'],'.');
							$image = new \Think\Image();
							$image->open('./Uploads'.$info['savepath'].$info['savename']);
							if($image->width() > 1500 || $image->height() >1500)
								$image->thumb(1500,1500,\Think\Image::IMAGE_THUMB_FILLED)->save('./Uploads'.$info['savepath']."Cut_".$info['savename']);
							else if($image->width() > 800 || $image->height() >800)
								$image->thumb(800,800,\Think\Image::IMAGE_THUMB_FILLED)->save('./Uploads'.$info['savepath']."Cut_".$info['savename']);
							else if($image->width() >300 || $image->height() >300)
								$image->thumb(300,300,\Think\Image::IMAGE_THUMB_FILLED)->save('./Uploads'.$info['savepath']."Cut_".$info['savename']);
							$image->thumb(300, 300,\Think\Image::IMAGE_THUMB_FILLED)->save('./Uploads'.$info['savepath']."thumb_".$info['savename']);
							$image->open('./Uploads'.$info['savepath'].$info['savename']);
							$image->thumb(200,200)->save('./Uploads/'.$info['savepath'].$info['savename']);
						}
					}				
				}	
				$result[$key]=$Newgoods->add($data);
			}	
		}
		if($result && $con)
			$this->success("发布新需求成功！更新设置成功！",'./?m=mall');
		else if(!$con && $result)
		{
			$this->success("发布新需求成功！",'./?m=mall');
		}
		else
			$this->error('发布需求失败~,请联系纸飞机管理员');
	}
}