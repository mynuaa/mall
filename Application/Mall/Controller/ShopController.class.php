<?php
namespace Mall\Controller;
use Think\Controller;
class ShopController extends Controller {
    public function index()    //店铺页面
    {
        $Shop=D('Shop');
        $Newgoods = D("Newgoods");
        $Message = D('Message');
        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');
        $Collection = D("Collection");

        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid'))); 

        $id=-1;
        if(is_numeric($_GET['id']) && $_GET['id'] > 0)    $id = $_GET['id'];
        if($id == -1)  $this->error('要查看的商品不存在'); 

        //检索店铺信息
        $shop_content=$Shop->get_shop($id);
        if($shop_content['admin_id']=='')
            $this->error("您查看的商铺不存在或尚未开放");
        $this->assign("shop",$shop_content);

        //初始我的出售分页
        $page=1;
        $condition['Concern_shopid']=$id;
        $num = $Newgoods->get_num($condition);
        if(isset($_GET['page']) && is_numeric($_GET['page']) && ($num/10+1 >= $_GET['page']))
            $page=$_GET['page'];
        $this->assign('page_this',(int)$page);
        $this->assign('page_num',($num%10==0)?(int)($num/10):(int)($num/10)+1);
        $this->assign('num',$num);

        //初始化商品
        $list = $Newgoods->get_page($condition,$page);
        foreach ($list as $key => $value) //查询消息记录
        {
            $list[$key]['mess_goods']=$Message->get_goods_mess($list[$key]['goods_id']);    
        }
        $list = $Collection->get_collection($list);  //为数据增加收藏记录，同时查询当前用户是否已收藏
        $this->assign('data',re_xss($list));

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = $shop_content['shop_name'].' - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid')); 
        $this->assign('username',session('username'));
        $this->assign('m','mall');
        $this->display('Index:shop');
    }

    public function shop_list()	//店铺列表
    {
        $Newgoods = D("Newgoods");
        $Collection = D("Collection");
        $Message = D('Message');
        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));
        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id); 

        $page=1;
        $condition['is_open']=1;
        $num = $Shop->get_num($condition);
        if(isset($_GET['page']) && is_numeric($_GET['page']) && ($num/10+1 >= $_GET['page']))
            $page=$_GET['page'];
        $this->assign('page_this',(int)$page);
        $this->assign('page_num',($num%10==0)?(int)($num/10):(int)($num/10)+1);
        $this->assign('num',$num);

        $a=array("手机","电子","书籍","车辆","服饰","化妆","日用","乐器","房屋","其他","虚拟","食品"); 
    	$res=$Shop->where('is_open=%d',1)->order('create_time')->select();
        foreach ($res as $key => $value) //处理封面图片
        {
            $res[$key]['cover']=substr($value['cover'], 1);
            $res[$key]['num']=$Newgoods->where('uid=%d and Concern_shopid=%d',$value['admin_id'],$value['shop_id'])->count();
            $m=explode(",",$value['shop_type']);
            unset($res[$key]['shop_type']);
            foreach ($m as $keys => $values) 
            {
                if(!empty($values))
                {
                    if($values == 'x')
                        $res[$key]['shop_type']=$a[0]." , ".$res[$key]['shop_type'];
                    else
                        $res[$key]['shop_type']=$a[$values]." , ".$res[$key]['shop_type'];
                }
            }    
            //循环商品图片
            $x=$Newgoods->where('uid=%d and Concern_shopid=%d',$value['admin_id'],$value['shop_id'])->order('data desc')->select();
            $photo_num=0;
            foreach ($x as $ke => $va) 
            {
                if($va['photo']!='' && $photo_num < 4)
                {
                    $mm=explode(',',$va['photo']);
                    $res[$key]['photo'][$photo_num]=substr($mm[0], 1);
                    $photo_num++;   //计数
                }
            }
            $res[$key]['photo_num']=$photo_num;
        }
        $this->assign("shop",re_xss($res));
        //print_r(re_xss($res));

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = '店铺列表 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('username',session('username'));
        $this->assign('uid',session('uid'));
    	$this->assign('m','mall');
        $this->display('Index:shop_list');
    }

    public function edit()//编辑商铺信息
    {
        if(!session('?uid'))
        {
            $this->error("您尚未登录，不能访问该页面",'./?m=mall');
        }

        $Newgoods = D("Newgoods");
        $Collection = D("Collection");
        $Message = D('Message');
        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');
        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid'))); 

        $id=-1;
        if(is_numeric($_GET['id']) && $_GET['id'] > 0) $id = $_GET['id'];
        if($id == -1)  $this->error('要查看的商铺不存在'); 

        //检索店铺信息
        $condition['shop_id']=$id;
        $condition['admin_id']=session('uid');
        $shop_content=$Shop->get_shop($condition);
        if(!empty($shop_content['admin_id']))
        {
            $this->assign('id0',x); //配合前台IN标签,由于IN标签不支持0，所以用x代替0
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

            $this->assign("shop",re_xss($shop_content));
        } 
        else
        {
            $this->error('您没有访问本页的权限!');
        }

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = '编辑商铺 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('username',session('username'));
        $this->assign('uid',session('uid'));
        $this->assign('m','mall');
        $this->display('Index:shop_edit');
    }

    public function save_change()   //保存编辑之后的信息
    {
        if(!session('?uid'))
        {
            $this->error("发布新需求需要先登录",'./?m=mall');
        }
        if(!IS_POST)
        {
            $this->error('非法请求');
        }

        $Shop=M('Shop');
        $shop_num=$Shop->where('shop_id=%d and admin_id=%d',$_POST['shop_id'],session('uid'))->count();
        if($shop_num == 1)
        {
            $shop_content=$Shop->where('shop_id=%d and admin_id=%d',$_POST['shop_id'],session('uid'))->select();   
            $this->assign("shop",re_xss($shop_content[0]));
        }
        else
        {
            $this->error('您没有保存该商铺信息的权限!');
            return false;
        }

        $upload = new \Think\Upload();
        $upload->maxSize   =     10242880 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath  =      './';

        $Shop=M('Shop');
        $data['shop_name']=$_POST['shop_name'];
        $data['contact']=$_POST['contact'];
        $data['description']=$_POST['description'];
        $info  =  $upload->upload();

        foreach ($_POST['classify1'] as $key => $value) 
        {
            if($value!='')
                $data['shop_type']=$value.','.$data['shop_type'];
        }

        if(!$info && $upload->getError()!="没有文件被上传！")
        {
            $this->error($upload->getError());    
        }
        else
        {
            if($upload->getError()!="没有文件被上传！")
            {
                $image = new \Think\Image(); 
                foreach($info as $file)
                {        
                    $data['cover']=$file['savepath'].$file['savename'];    
                }
                $image->open('./Uploads'.substr($data['cover'], 1));
                $image->thumb(150, 150,\Think\Image::IMAGE_THUMB_SCALE)->save('./Uploads'.substr($data['cover'], 1));
            }
            $Shop=M('Shop');
            $result=$Shop->where('admin_id=%d',session('uid'))->save($data);
            if($result)
                $this->success('编辑成功~','./?m=mall');
            else
                $this->error('编辑失败~~');
        }
    }

    public function new_shop()  //申请新商铺
    {
        if(!session('?uid'))
        {
            $this->error("您尚未登录，不能访问该页面",'./?m=mall');
        }
        $app_title = '申请新商铺 - 纸飞机';
        $this->assign('app_title',$app_title);

        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');
        $Message=D('Message');
        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid'))); 

         //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺
        
        $this->assign('username',session('username'));
        $this->assign('uid',session('uid'));
        $this->assign('m','mall');
        $this->display('Index:new_shop');
    }

    public function add_shop()  //添加新商铺
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

        $upload = new \Think\Upload();
        $upload->maxSize   =     10242880 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath  =      './';

        $Shop=M('Shop');
        $data['shop_name']=$_POST['shop_name'];
        $data['create_time']=date("Y-m-d H:i:s",time());
        $data['admin_id']=session('uid');
        $data['admin_name']=session('username');
        $data['contact']=$_POST['contact'];
        $data['description']=$_POST['description'];
        $data['is_open']=0;
        $info  =  $upload->upload();

        foreach ($_POST['classify1'] as $key => $value) 
        {
            if($value!='')
                $data['shop_type']=$value.','.$data['shop_type'];
        }
        if(!$info)
        {
            $this->error($upload->getError());          
            return false;  
        }  
        else
        {
            foreach($info as $file)
            {        
                $data['cover']=$file['savepath'].$file['savename'];    
            }
            $image = new \Think\Image(); 
            $image->open('./Uploads'.substr($data['cover'], 1));
            $image->thumb(150, 150,\Think\Image::IMAGE_THUMB_SCALE)->save('./Uploads'.substr($data['cover'], 1));

            $result=$Shop->data($data)->add();
            if($result)
                $this->success('申请新店铺成功，请等待后台验证（1-3个工作日内）','./?m=mall');
            else
                $this->error('申请失败，一个用户只能申请一个店铺');
        }
    }

    public function permit()    //商铺通过申请
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
            $r['code']=-2;      //管理员权限不够
        }
        else
        {
            $Shop=M('Shop');
            $data['is_open']=1;
            $num=$Shop->where('shop_id=%d',$_POST['shop_id'])->save($data);
            if($num > 0)
                $r['code']=1;
            else
                $r['code']=-1;
        }
        $this->ajaxReturn($r);
    }

    public function delete_shop()   //删除商铺
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
            $Shop=M('Shop');
            $num=$Shop->where('shop_id=%d',$_POST['shop_id'])->delete();
            if($num > 0)
                $r['code']=1;
            else
                $r['code']=-1;
        }
        $this->ajaxReturn($r);
    }

    public function all_shops() //ajax获取所有的商铺名称
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
            $r['code']=-2;      //管理员权限不够
        }
        else
        {
            $Shop=M('Shop');
            $num=$Shop->where('is_open=%d',1)->order('shop_id')->select();
            $r['data']=$num;
            $r['code']=1;
        }
        $this->ajaxReturn($r);
    }
}