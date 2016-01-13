<?php
namespace Mall\Controller;
use Think\Controller;

header("Content-Type: text/html; charset=utf8");

class EditController extends PublicController
{
	public function index()	//编辑页面
	{
		$Newgoods = D("Newgoods");
        $Message = D('Message');
        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');
        $Admin = D('Admin');

		if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));
		//店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);

        if(is_numeric($_GET['ed']) && $_GET['ed'] > 0)    $ed = $_GET['ed'];
        if(!$Newgoods->check_exist($ed))
        {
        	$this->error('对应的需求信息不存在或者是已关闭，3秒后链接到上一页');
        }

        if(!$Newgoods->is_mygoods(session('uid'),$ed) && !$Admin->check_admin(session('uid')))
        {
        	$this->error('不能编辑非本人发布的需求信息');
        }

        $goods=$Newgoods->get_goods($ed);
        $this->assign('goods',$goods);	//已在model里完成xss处理
        $this->assign('photo',$goods['photo']);

    	//初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = '编辑商品 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid'));
        $this->assign('username',session('username'));
        $this->assign('m','mall');
        $this->display('Index:edit');
	}

	public function save()	//更新信息
	{
		if(session('?uid')==0)
		{
			$this->error('尚未登录，不可进行编辑操作');
		}
		if(!IS_POST)
		{
			$this->error('非法请求');
		}

		$Newgoods = D("Newgoods");
		$Admin = D('Admin');
		$goods=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->select();
		if(!$Newgoods->check_exist($_POST['goods_id']))
		{	
        	$this->error('您编辑的商品不存在！');
        }
		if(!$Newgoods->is_mygoods(session('uid'),$_POST['goods_id']) && !$Admin->check_admin(session('uid')))
        {
        	$this->error('不能编辑非本人发布的需求信息');
        }

		$upload = new \Think\Upload();
		$upload->maxSize   =     10242880 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath  =      './';

		$data['goods_id']=$_POST['goods_id'];
		$data['data']=date("Y-m-d H:i:s",time());
		$data['need_type']=$_POST['need_type'];
		$data['price']=$_POST['price'];
		$data['description']=$_POST['description'];
		$data['location']=$_POST['location'];
		$data['classify']=$_POST['classify'];
		$data['goods_name']=$_POST['goods_name'];
		$data['contact']=$_POST['contact'];
		if($data['need_type']==1)//出售
		{
			$res=$Newgoods->create($data,4);
		}
		else
		{
			$res=$Newgoods->create($data);
		}

		if(!$res)		//数据库验证失败
		{
			$this->error($Newgoods->getError());
			return false;
		}
		else
		{
			$data['photo'] = $goods[0]['photo'];
			//print_r($data);
			if($data['need_type']==1)	//出售时
			{
				foreach($_FILES['photo']['name'] as $keys => $values)
				{
					$_FILES['x']['name'][0]= $_FILES['photo']['name'][$keys];
					$_FILES['x']['type'][0]= $_FILES['photo']['type'][$keys];
					$_FILES['x']['tmp_name'][0]= $_FILES['photo']['tmp_name'][$keys];
					$_FILES['x']['error'][0]= $_FILES['photo']['error'][$keys];
					$_FILES['x']['size'][0]= $_FILES['photo']['size'][$keys];
				
					$info = $upload->uploadOne($_FILES['x']);
					if(!$info)
					{
						if($upload->getError()!="没有文件被上传！")
						{
							$this->error($upload->getError());
							break;
						}	
					}
					else
					{   
						//echo $data['photo'].$info['savepath'].$info['savename'];
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

			if($Newgoods->save_goods($data) == 1)
			{
				$this->success("保存成功~",'./?m=mall&c=goods&id='.$_POST['goods_id']);
			}	
			else
			{
				$this->error("保存失败~请联系纸飞机管理员");
			}				
		}	
	}

	public function delete_img()	//ajax删除图片
	{
        if(!IS_AJAX)
		{
			$r['code']=-1;
		}   
        if(session('?uid')==0)
		{
			$r['code']=-1;
		}
		$Newgoods = D("Newgoods");
		$goods = $Newgoods->where('goods_id=%d',$_POST['goods_id'])->select();
		if($goods[0]['uid']!=session('uid') && is_admin(session('uid'),session('username'))==0)
        {
        	$this->error('不能编辑非本人发布的需求信息');
        	return false;
        }
		if(count($goods)!=1)
			$r['code']=-1;
		else
		{
			$m=explode(',',$goods[0]['photo']);
			foreach ($m as $key => $value) 
			{
				if($value !='')
				{
					$val=substr($value,1);
					if($val==$_POST['src'])	$m[$key]='';					
				}			
			}
			$str='';
			foreach ($m as $key => $value)
			{
				if($value!='')
					$str=$str.$value.',';
			}
			$data['photo']=$str;
			$result=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->save($data);
			if($result==1)
				$r['code']=1;
			else
				$r['code']=-1;
			//删除图片
			$m=explode('/', $_POST['src']);
            $photo_lit=$m[0].'/'.$m[1].'/'.'thumb_'.$m[2]; 
            $photo_cut=$m[0].'/'.$m[1].'/'.'Cut_'.$m[2]; 
			unlink("./Uploads".$photo_lit);
			unlink("./Uploads".$photo_cut);

		}
		$this->ajaxReturn($r);
	}
}