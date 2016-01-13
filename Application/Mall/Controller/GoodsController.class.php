<?php
namespace Mall\Controller;
use Think\Controller;
class GoodsController extends PublicController
{
	public function index()
	{
        $Newgoods = D("Newgoods");
        $Soldout = D('Soldout');
        $Collection = D("Collection");
        $Message = D('Message');
        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');
        $Config = D('Config');
      
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));   //初始化未读消息数目
        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);

        //处理商品编号
		$id=-1;
		if(is_numeric($_GET['id']) && $_GET['id'] > 0 && ($Newgoods->check_exist($_GET['id']) || $Soldout->check_exist($_GET['id'])) )//如果商品编号为数字且数字正确
        {
            $id = $_GET['id'];
        }
        else
        {
            $this->error('对应的需求信息不存在，3秒后链接到上一页');
        }

        $goods=$Newgoods->get_goods($id);
        
        $goods['type']="new";
        if(empty($goods['uid']))
        {
            $goods=$Soldout->get_goods($id);
            $goods['type']="soldout";
        }

        //处理图片信息
        $image = new \Think\Image();
        foreach ($goods['photo'] as $key => $value) 
        {
            if($value!='')
            {
                $m=explode('/', $value);
                $goods['photo_lit'][$key]=$m[0].'/'.$m[1].'/'.'thumb_'.$m[2]; 
                $goods['photo_cut'][$key]=$m[0].'/'.$m[1].'/'.'Cut_'.$m[2]; 
                $image->open('./Uploads'.$goods['photo_lit'][$key]);
                $size[$key] = $image->size();
            }
        }    
        $this->assign('size',$size);
        $this->assign('photo_num',count($size)<=4 ? count($size):4);

        //处理讨论信息
        $mess=$Message->get_message($id);
        $this->assign('mess',re_xss($mess));
        $this->assign('mess_count',empty($mess[0]['from_uid'])?0:count($mess));
        //处理收藏信息
        $goods=$Collection->get_one_collection($goods);

        //右侧该用户出售的其他商品
        if($goods['Concern_shopid'] == 0)
        {
            $panel_con['Concern_shopid']=0;
            $panel_con['uid']=$goods['uid'];
            $panel_goods=$Newgoods->get_page($panel_con,1);
            $this->assign("panel_goods",$panel_goods);
            $this->assign("panel_goods_num",empty($panel_goods[0]['uid'])?0:count($panel_goods));
        }
        else    //右侧该商铺出售的商品
        {
            $panel_con['Concern_shopid']=$goods['Concern_shopid'];
            $panel_goods=$Newgoods->get_page($panel_con,1);
            $this->assign("panel_goods",$panel_goods);
            $this->assign("panel_goods_num",empty($panel_goods[0]['uid'])?0:count($panel_goods));
        }
        
        //右侧谁还喜欢这个商品
        $panel_likes=$Collection->get_alllikes($id);
        $this->assign("panel_likes",re_xss($panel_likes));
        $this->assign("panel_likes_num",empty($panel_likes[0]['uid'])?0:count($panel_likes));
        //右侧猜你喜欢
        $gus=$Newgoods->guess_like($Config->get_likes(session('uid')));
        $this->assign('guess',re_xss($gus)); 

        $classify = array("手机","电子","书籍","车辆","服饰","化妆","日用","乐器","房屋","其他","虚拟","食品");
        $location = array("本部","江宁","不限");
        $this->assign('goods',$goods);
        $this->assign('classify',$classify[$goods['classify']]);
        $this->assign('location',$location[$goods['location']]);

        $app_title = $goods['goods_name'].' - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('username',session('username'));
        $this->assign('uid',session('uid'));
        $this->assign('m','mall');
        $this->display('Index:info');
	}

    public function talk_add()      //ajax增加留言
    {
        if(!IS_AJAX || session('?uid')==0)
        {
            $r['code']=0;
        }
        else
        {
            $result=send_message(session('uid'),session('username'),$_POST['to_uid'],$_POST['to_username'],$_POST['content'],$_POST['goods_id'],$_POST['goods_name'],0);   
            if($result>0)
            {
                $r['code']=1;
                $r['time']=date("Y-m-d H:i:s",time());
            }    
            else
                $r['code']=-1;
        }
        $this->ajaxReturn($r);
    }

    public function read()  //ajax阅读留言，并将留言移动到另一个数据表
    {
        if(!IS_AJAX || session('?uid')==0)
        {
            $r['code']=-1;
            return false;
        }   
       
        $Message=D('Message');
        $mess=$Message->where('message_id=%d',$_POST['message_id'])->select();
        $r['code']=$Message->read_mess(session('uid'),$mess[0]['goods_id']);
        
        $this->ajaxReturn($r);
    }

    public function get_mess()  //ajax获取已读信息
    {
        if(!IS_AJAX)
        {
            $this->error('非法操作！');
            $r['code']=-1;
        }   
        if(session('?uid')==0)
        {
            $this->error('尚未登录，非法操作');
            $r['code']=-1;
        }
        $message=M('Message');
        $result=$message->where('to_uid=%d',session('uid'))->page($_POST['mess_page'],10)->order('data desc')->select();
        foreach ($result as $key => $value) 
        {
            foreach ($value as $keys => $values) 
            {
                $result[$key][$keys]=htmlspecialchars($values);
            }
        }
        $r['code']=count($result);
        $r['data']=$result;
        $this->ajaxReturn($r);
    }

    public function delete_goods()  //ajax删除商品信息
    {
        if(!IS_AJAX)
            $r['code']=-1;
        else
        {
            if(!session('?uid'))  
            {
                $r['code']=-1;
            }
            else
            {
                $Newgoods=D("Newgoods");
                $goods=$Newgoods->where("goods_id=%d",$_POST['goods_id'])->select();
                if($goods[0]['uid']!=session('uid'))
                {
                    $this->error('不能删除非本人发布的需求信息');
                    $r['code']=-1;
                }
                else
                {
                    $ress=colle_over($_POST['goods_id'],1);
                    $dele_res=colle_delete($_POST['goods_id']);
                    $result=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->delete();
                    $r['ress']=$ress;
                    $r['dele_res']=$dele_res;
                    $r['result']=$result;
                    if($result && $ress && $dele_res)
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

    public function trade_over()    //ajax交易完成
    {
        if(!IS_AJAX)
        {
            $r['code']=-1;
            $this->ajaxReturn($r);
        }  
        if(!session('?uid'))  
        {
            $r['code']=-1;
            $this->ajaxReturn($r);
        }

        $Newgoods=D("Newgoods");
        $data=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->select();
        $qx=is_admin(session('uid'),session('username'));
        if($data[0]['uid']!=session('uid') && $qx==0)
        {
            $this->error('不能操作非本人发布的需求信息');
            $r['code']=-1;
            return false;
        }
        else
        {
            if(count($data)!=1)
                $r['code']=-1;
            else
            {
                //$res=send_message(session('uid'),session('username'),$data[0]['uid'],$data[0]['username'],'',$_POST['goods_id'],$data[0]['goods_name'],2);
                $ress=colle_over($_POST['goods_id'],0);   //给收藏过该商品用户的发信息
                $soldout=M('Soldout');
                $data[0]['close_time']=date("Y-m-d H:i:s",time());
                $result=$soldout->data($data[0])->add();
                if($result >= 0 && $ress==1)
                {
                    $res=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->delete();
                    if($res==1)
                        $r['code']=1;
                    else
                        $r['code']=-1;
                }    
                else
                    $r['code']=-1;
            }
        }

        $this->ajaxReturn($r);
    }

    public function goods_up()  //置顶
    {
        if(!IS_AJAX)
            $r['code']=-1;
        else
        {
            if(!session('?uid'))  
            {
                $r['code']=-1;
            }
            else
            {
                $Newgoods=D("Newgoods");
                $goods=$Newgoods->where("goods_id=%d",$_POST['goods_id'])->select();
                if($goods[0]['uid']!=session('uid'))
                {
                    $r['code']=-1;  //不能置顶非本人发布的需求信息
                }
                else
                {
                    $data['data']=date("Y-m-d H:i:s",time());
                    $r['code']=$Newgoods->where('goods_id=%d',$_POST['goods_id'])->save($data);
                }
                
            }
        }
        $this->ajaxReturn($r);
    }
}