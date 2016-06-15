<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');
class IndexController extends PublicController {
    public function index()
    {
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

        //如果$_get['load']==1 被赋值，则为登陆页面
        $this->assign('load_is',isset($_GET['load'])?'1':'0');
        //初始化未读消息数目
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));    

        //页码操作
        $num=$Newgoods->get_num();
        $page = 1;  //如果没有设定页数的值，默认显示第一页。
        $page_num=($num%20==0)?(int)($num/20):(int)($num/20)+1;
       // echo $page_num;
        if(isset($_GET['page']) && is_numeric($_GET['page']) && ($num/20+1 >= $_GET['page']))
            $page=$_GET['page'];
        $this->assign('page_this',(int)$page);
        $this->assign('page_num',$page_num);
        $this->assign('page_start',$page>4?$page-4:1);
        if($page >= 5)
            $this->assign('page_end',($page+5 < $page_num)?(int)$page+5:$page_num+1);
        else
            $this->assign('page_end',$page_num>10?10:$page_num+1);
        

        //初始化主体数据
        $list = $Newgoods->get_page('',$page); //数据提取
        foreach ($list as $key => $value) 
        {
            $list[$key]['mess_goods']=$Message->get_goods_mess($list[$key]['goods_id']);    //查询消息记录
        }
        $list = $Collection->get_collection($list);  //为数据增加收藏记录，同时查询当前用户是否已收藏
        $this->assign('data',re_xss($list));
        $this->assign('length',count($list));

       // print_r($list);

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = '集市 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid')); 
        $this->assign('username',session('username'));
        $this->assign('m','mall');
        $this->display('Index:plat_mall');
    }

    public function load_ajax() //ajax登陆
    {
        if(!session('?uid') && IS_AJAX)
        {
            list($uid, $username, $password, $email) = uc_user_login($_POST['user_name'], $_POST['password']);
            if($uid > 0) 
            {
                session('username',$username);
                session('uid',$uid);
                setcookie('Example_auth', uc_authcode($uid."\t".$username, 'ENCODE'));
                $r['load']=uc_user_synlogin($uid);
                $r['code']=$uid;
            }
            else
            {
                $r['load']=' ';
                $r['code']=-1;
            }
        }
        else
            $r['code']=-1;
        $this->ajaxReturn($r);
    }

    public function logout() 
    {
        session(null);
        echo uc_user_synlogout();
        $this->success('退出成功','?m=mall');
    }

    public function coll_ajax()  //收藏
    {
        if(IS_AJAX && session('?uid'))
        {
            $Collection=D("Collection");
            $Newgoods=D('Newgoods');
            if(!$Collection->exist(session('uid'),$_POST['goods_id'])) //如果数据表中木有数据，那么插入记录
            {
                $goods_name=$Newgoods->get_goods_name($_POST['goods_id']);
                $r['code']=$Collection->add_colle(session('uid'),session('username'),$_POST['goods_id'],$goods_name);
            } 
            else 
                $r['code']=-1;
        }
        else
            $r['code']=0;

        $this->ajaxReturn($r);
    }

    public function team()
    {
        $Message=D('Message');
        $Shop = D('Shop');
        $Notice = D('Notice');
        $Ad = D('Ad');

        //初始化未读消息数目
        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));    

        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = '关于我们 - 南航mall';
        $this->assign('uid',session('uid'));
        $this->assign('username',session('username'));


        $this->assign('m','mall');
        $this->assign('app_title',$app_title);
        $this->display('Index:team');
    }

    public function save_bug()  //保存bug信息
    {
        if(!IS_AJAX || !session('?uid'))
        {
            $r['code']=-1;
        }
        else
        {
            $Bug=D('Bug');
            $r['code']=($Bug->add_bug(session('uid'),session('username'),$_POST['content'],$_POST['email'])>0)?1:-2;
        }
        $this->ajaxReturn($r);
    }

    public function search()    //搜索
    {
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
        //初始化未读消息数目
        if(session('?uid'))  $this->assign('mess_id',M('Message')->get_user_mess(session('uid')));

        //分页载入
        $num=$Newgoods->get_search_num($_GET['search_content']);
        $page = 1;
        if(isset($_GET['page']) && is_numeric($_GET['page']) && ($num/10+1 >= $_GET['page']))
            $page=$_GET['page'];  
        $this->assign('page_this',(int)$page);
        $this->assign('page_num',($num%10==0)?(int)($num/10):(int)($num/10)+1);

        //信息查询以及处理
        $list=$Newgoods->get_search_result($_GET['search_content'],$page);
        foreach ($list as $key => $value) 
        {
            $list[$key]['mess_goods']=$Message->get_goods_mess($list[$key]['goods_id']);    //查询消息记录
        }
        $this->assign('result',re_xss($list));

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺   

        //页面特殊项
        $this->assign('result_num',$num);
        $this->assign('search_type',re_xss($_GET['search_type']));
        $this->assign('search_content',re_xss($_GET['search_content']));

        $app_title = '搜索 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid'));
        $this->assign('username',session('username'));
        $this->assign('m','mall');
        $this->display('Index:search');
    }

    public function scan() //分类浏览
    {
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

        //变量 类别lb 地点dd 类型lx
        $lb=-1;$dd=-1;$lx=-1;
        if(is_numeric($_GET['lb']) && $_GET['lb'] <= 11 && $_GET['lb'] >= 0) $lb=$_GET['lb'];
        if(is_numeric($_GET['dd']) && ($_GET['dd']>=0 && $_GET['dd']<=2)) $dd=$_GET['dd'];
        if(is_numeric($_GET['lx']) && ($_GET['lx']==0 || $_GET['lx']==1))    $lx=$_GET['lx'];

        //构造查询数组
        if($lx!=-1)
            $condition['need_type']=$lx;
        if($lb!=-1)
            $condition['classify']=$lb;
        if($dd!=-1)
            $condition['location']=$dd;

        //页码设定
        $num=$Newgoods->get_num($condition);
        $page = 1;  //如果没有设定页数的值，默认显示第一页。
        $page_num=($num%20==0)?(int)($num/20):(int)($num/20)+1;
        if(isset($_GET['page']) && is_numeric($_GET['page']) && ($num/20+1 >= $_GET['page']))
            $page=$_GET['page'];
        $this->assign('page_this',(int)$page);
        $this->assign('page_num',$page_num);
        $this->assign('page_start',$page>4?$page-4:1);
        if($page >= 5)
            $this->assign('page_end',($page+5 < $page_num)?(int)$page+5:$page_num+1);
        else
            $this->assign('page_end',$page_num>10?10:$page_num+1);

        //echo $page;

        //初始化主体数据
        $list = $Newgoods->get_page($condition,$page); //数据提取
        foreach ($list as $key => $value) 
        {
            $list[$key]['mess_goods']=$Message->get_goods_mess($list[$key]['goods_id']);    //查询消息记录
        }
        $list = $Collection->get_collection($list);  //为数据增加收藏记录，同时查询当前用户是否已收藏
        $this->assign('data',re_xss($list));
        $this->assign('length',count($list));

        $this->assign('lb',$lb);
        $this->assign('lx',$lx);
        $this->assign('dd',$dd);

        //初始化右侧
        $this->assign("notice",$Notice->get_notice()); //初始化公告
        $this->assign("ad",$Ad->get_ad());   //初始化广告
        $this->assign("new_shop",$Shop->get_new_shop());   //初始化最新店铺

        $app_title = '分类检索 - 纸飞机';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid'));
        $this->assign('username',session('username'));
        $this->assign('m','mall');
        $this->display('Index:scan');
    }

}
