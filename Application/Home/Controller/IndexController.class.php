<?php
namespace Home\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');
header("Content-Type: text/html; charset=utf8");
class IndexController extends PublicController
{
    public function index()
    { 
        if(session('?uid')==0) $this->error('请先登录！！','/sso/?page=login');

        $Newgoods = D("Newgoods");
        $Soldout = D("Soldout");
        $Collection = D("Collection");
        $Message = D('Message');
        $Shop = D('Shop');
        $Config = D('Config');


        if(is_numeric($_GET['lb']) && $_GET['lb'] <= 4 && $_GET['lb'] > 0)
            $this->assign('tap_s',$_GET['lb']);     //设置显示标签的类别 1 消息 2 正在出售 3 已售出 4 收藏
        else
            $this->assign('tap_s','1');

        if(session('?uid'))  $this->assign('mess_id',$Message->get_user_mess(session('uid')));

        //初始化我的信息
        $mess=$Message->get_ten_mess(session('uid'));
        $this->assign('mess',re_xss($mess));
        $this->assign('mess_num',count($mess));
 
        //初始我的出售分页
        $p2=1;
        $condition_1['uid']=session('uid');
        $num = $Newgoods->get_num($condition_1);
        if(isset($_GET['p2']) && is_numeric($_GET['p2']) && ($num/10+1 >= $_GET['p2'])) 
            $p2=$_GET['p2'];    
        $this->assign('p2_this',(int)$p2);
        $this->assign('p2_num',($num%10==0)?(int)($num/10):(int)($num/10+1));

        $list=$Newgoods->get_page($condition_1,$p2);
        $this->assign('num',$num);
        $this->assign('data',re_xss($list));

        //初始化已出售
        $p3=1;
        $num = $Soldout->get_num($condition_1);
        if(isset($_GET['p3']) && is_numeric($_GET['p3']) && ($num/10+1 >= $_GET['p3']))
                $p3=$_GET['p3'];    
        $this->assign('p3_this',(int)$p3);
        $this->assign('p3_num',($num%10==0)?(int)($num/10):(int)($num/10+1));

        $sold = $Soldout->get_page($condition_1,$p3); 
        $this->assign('num_sold',$num);
        foreach ($sold as $key => $value)
        {
            $sold[$key]['mess_goods']=$Message->get_goods_mess($sold[$key]['goods_id']);    //查询消息记录
        }
        $this->assign('soldout',re_xss($sold));


        //查询收藏信息
        $type=1;    //type用来记录商品状态，0已关闭 1尚未出售
        $p4=1;
        $num = $Collection->get_user_colle_num(session('uid'));
        if(isset($_GET['p4']) && is_numeric($_GET['p4']) && ($num/10+1 >= $_GET['p4']))
            $p4=$_GET['p4'];     
        $this->assign('p4_this',(int)$p4); 
        $this->assign('p4_num',($num%10==0)?(int)($num/10):(int)($num/10+1));
        $this->assign('num_colle',$num);

        $colle = $Collection->get_user_collection(session('uid'),$p4);
        foreach ($colle as $key => $value)
        {
            $goods = $Newgoods->get_goods($value['goods_id']);
            if($goods['uid']!='')
                $goods['is_sold']=0;
            else
            {
                $type=0;
                $goods = $Soldout->get_goods($value['goods_id']);
                if($goods['uid']!='')
                    $goods['is_sold']=1;
            }
            if(isset($goods['is_sold']))  //如果检索到了商品
            {
                $x=explode(',',$goods['photo']);
                $colle[$key]['photo']=substr($x[0], 1);
                $colle[$key]['uid']=$goods['uid'];
                $colle[$key]['username']=$goods['username'];
                $colle[$key]['data']=$goods['data'];
                $colle[$key]['need_type']=$goods['need_type'];
                $colle[$key]['price']=$goods['price'];
                $colle[$key]['description']=$goods['description'];
                $colle[$key]['location']=$goods['location'];
                $colle[$key]['classify']=$goods['classify'];
                $colle[$key]['goods_name']=$goods['goods_name'];
                $colle[$key]['contact']=$goods['contact'];
                $colle[$key]['is_sold']=$goods['is_sold'];
                $colle[$key]['shop_avator']=$goods['shop_avator'];
                $colle[$key]['Concern_shopid']=$goods['Concern_shopid'];
                if($type == 0)
                {
                    $colle[$key]['sale_type']=$goods['sale_type'];
                    $colle[$key]['close_time']=$goods['close_time'];
                }
            }   
            $type=1;
        }
        $this->assign('colle',re_xss($colle));
       // print_r($colle);

        //右侧 我的关注
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

        //店铺信息
        $shop_id=$Shop->get_shopid(session('uid'));
        if($shop_id>0)  //如果有店铺
            $this->assign("shop_id",$shop_id);
        
        $app_title = '个人中心 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid'));
        $this->assign('username',session('username'));
        $this->assign('m','home');
        $this->display('Index:plat_home');
    }

    public function del_col()
    {
        if(!IS_AJAX)
            $this->error('非法操作！');
        else
        {
            if(!session('?uid'))  
            {
                $this->error('尚未登录！！','/sso/?page=login');
                return false;
            }
            else
            {
                $Colle=D("Collection");
                $result=$Colle->where('uid=%d and collect_id=%d',session('uid'),$_POST['collect_id'])->delete();
                if($result==1)
                    $r['code']=1;
                else if($return==0)
                    $r['code']=-1;
                $this->ajaxReturn($r);
            }
        }
    }
}