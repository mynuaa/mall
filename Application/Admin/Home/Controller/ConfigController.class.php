<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class ConfigController extends PublicController 
{
	public function index()
	{
		if(session('?uid')==0)
        {
            $this->error('请先登录！！');
        }

        $Newgoods = D("Newgoods");
        $Message = D('Message');
        $Config=D('Config');
        $Shop=D('Shop');

        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));
        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);

        $this->assign('data',$Config->get_config(session('uid')));

        $this->assign('id0',x);	//配合前台IN标签,由于IN标签不支持0，所以用x代替0
        $this->assign('id1',1);
        $this->assign('id2',2);
        $this->assign('id3',3);
        $this->assign('id4',4);
        $this->assign('id5',5);
        $this->assign('id6',6);
        $this->assign('id7',7);
        $this->assign('id8',8);
        $this->assign('id9',9);
        $this->assign('id10',10);
        $this->assign('id11',11);
  
        $con=$Config->where('uid=%d',session('uid'))->select();
        if(count($con)==1)
        {
            $xxx=explode(",",$con[0]['attention']);
            foreach ($xxx as $key => $value) 
            {
                if($value == 'x')
                    $xxx[$key]=0;
                $num[$xxx[$key]] = get_classify_num($xxx[$key]);
            }
            $this->assign('attention_daynum',$num);
            $this->assign('attention_panel',$xxx);
            $this->assign('attention_num',($xxx[0]==='')?0:count($xxx));
        }
        else
            $this->assign('attention_num',0);
        $a=array("手机","电子","书籍","车辆","服饰","化妆","日用","乐器","房屋","其他","虚拟","食物"); 
        $this->assign('attention_array',$a);
        //接上 右侧 猜你喜欢
        $gus=$Newgoods->guess_like($Config->get_likes(session('uid')));
        $this->assign('guess',re_xss($gus)); 

        $this->assign('username',session('username'));
        $this->assign('uid',session('uid'));
		$app_title = '个人设置 - 南航mall';
        $this->assign('app_title',$app_title);
		$this->assign('m','home');
    	$this->display('Index:config');
	}

	public function config()
	{
		if(session('?uid')==0)
		{
			$this->error('尚未登录，不可进行保存操作');
        	return false;
		}
		if(!IS_AJAX)
		{
			$this->error('非法请求');
			return false;
		}
		$Config=D('Config');
		$list=$Config->where("uid=%d",session('uid'))->select();

		$data['uid']=session('uid');
		$data['contact']=$_POST['contact'];
		$data['location']=$_POST['location'];
        $data['data']=date("Y-m-d H:i:s",time());
        if($_POST['attention'] != '')
		  $data['attention']=$_POST['attention'];
		if (!$Config->create($data))
		{
			$this->error($Config->getError());
			return false;
		}
		else
		{
			$r['code']=-1;
			if(count($list)==0)	//没有保存记录
			{	
				$result=$Config->add();
				if($result>=0)
					$r['code']=1;
			}
			else if(count($list)==1)
			{
				$result=$Config->where('uid=%d',session('uid'))->save($data);
				if($result==1)
					$r['code']=1;
			}
			$this->ajaxReturn($r);	
		}
	}
}