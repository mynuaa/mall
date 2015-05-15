<?php
namespace Common\Model;
use Think\Model;
class NewgoodsModel extends Model 
{
	//1新增数据时验证 2编辑数据时候验证  3全部情况下验证（默认） 
	//新增，4：新增出售信息时验证
	protected $_validate = array
	(     
		array('goods_name','require','商品名必须填写！！'), 
		array('goods_name','1,15','商品名超出长度！！',0,'length'),


		array('need_type','require','商品类型必须填写！！'),
		array('need_type',array(0,1),'非法输入！！',0,'in'),
		array('location','require','所在地必须填写！！'),
		array('location',array(0,1,2),'非法输入！！',0,'in'),
		array('classify','require','分类信息必须填写！！'),
		array('classify','0,11','非法输入！！',0,'between'),

		array('price','require','出售物品时价格必须填写！！',0,'',4),
		array('price',array(0,9999),'价格不在允许范围内！！',0,'between'), 
		array('contact','require','联系方式必须填写！！'),  
		array('contact','1,30','联系方式超出长度！！',0,'length'),
	);

	public function get_num($condition='')	//获取记录总数
	{
		return $this->where($condition)->count();
	}

	public function check_exist($goods_id)
	{
		return $this->where("goods_id=%d",$goods_id)->count();
	}

	public function is_mygoods($uid,$goods_id)
	{
		return $this->where('uid=%d and goods_id=%d',$uid,$goods_id)->count();
	}

	public function get_goods($goods_id)
	{
		$goods=$this->where("goods_id=%d",$goods_id)->select();
		$goods[0]['photo']=explode(',',$goods[0]['photo']);
        foreach ($goods[0]['photo'] as $key => $value)
        {
        	$goods[0]['photo'][$key]=substr($value,1);
        }

        foreach ($goods[0] as $key => $value) 	//xss处理
        {
            if($key!="photo")
                $goods[0][$key]=htmlspecialchars($value);
        }

        //检测是否为商铺商品
        if($goods[0]['Concern_shopid'] != 0 )   //如果属于商铺商品
        {
            $shop_name=get_shop_name($goods[0]['Concern_shopid']);
            $shop_avator=get_shop_avator($goods[0]['Concern_shopid']);
            if($shop_avator && $shop_name)
            {
                $goods[0]['username']=$shop_name;
                $goods[0]['shop_avator']=$shop_avator;
            }
            else
            {
                $goods[0]['Concern_shopid']=0;
            }
        }

        return $goods[0];
	}

	public function get_page($condition='',$page)	//获取某一页的所有数据
	{
		$list = $this->page($page,20)->where($condition)->order('data desc')->select();    
        foreach ($list as $key => $value) 
        {
            if($value['photo'] !='')//默认显示第一张图片
            {
                $x=explode(',',$value['photo']);
                $list[$key]['photo']=substr($x[0], 1);
            } 
            /*if(strlen($value['goods_name']) > 28)	//限制题目长度
            {
                $list[$key]['goods_name']=mb_substr($value['goods_name'],0,12,'utf-8').'...';
            }
            if(strlen($value['description']) > 280)	//限制内容长度
            {
                $list[$key]['description']=mb_substr($value['description'],0,140,'utf-8').'...';
            }*/
            if($value['Concern_shopid'] != 0)   //如果属于商铺商品
            {
                $shop_name=get_shop_name($value['Concern_shopid']);
                $shop_avator=get_shop_avator($value['Concern_shopid']);
                if($shop_avator && $shop_name)
                {
                    $list[$key]['username']=$shop_name;
                    $list[$key]['shop_avator']=$shop_avator;
                }
                else
                    $list[$key]['Concern_shopid']=0;
            }
        }

        return $list;
	}

	public function get_goods_name($goods_id)
	{
		$result=$this->where('goods_id=%d',$goods_id)->select();

		return $result[0]['goods_name'];
	}

	public function get_search_num($search_content)	//分类检索
	{
        $map['goods_name'] = array('like','%'.$search_content.'%');

        return $this->where($map)->count();
	}

	public function get_search_result($search_content,$page)
	{
		$map['goods_name'] = array('like','%'.$search_content.'%');
		$list=$this->where($map)->order('data desc')->page($page,10)->select();

		foreach ($list as $key => $value) 
        {
            $x=explode(',',$value['photo']);//默认显示第一张图片
            $list[$key]['photo']=substr($x[0], 1);

            /*if(strlen($value['description']) > 280)	//商品描述长度限制
            {
                $list[$key]['description']=mb_substr($value['description'],0,140,'utf-8').'...';
            }*/
            if($value['Concern_shopid'] != 0)   //如果属于商铺商品
            {
                $list[$key]['username']=get_shop_name($value['Concern_shopid']);
                $list[$key]['shop_avator']=get_shop_avator($value['Concern_shopid']);
            }
        }

        return $list;
	}	

	public function save_goods($data)
	{
		return $this->where('goods_id=%d',$data['goods_id'])->save($data);
	}

	public function get_allgoods($uid)
	{
		$goods=$this->where('uid=%d',$uid)->limit(15)->order('data desc')->select();
		foreach ($goods as $key => $value) 
        {
            /*if(strlen($value['goods_name']) > 24)
            {
                $panel_goods[$key]['goods_name']=mb_substr($value['goods_name'],0,12,'utf-8').'...';
            }*/
            foreach ($value as $keys => $values) 
            {
                $panel_goods[$key][$keys]=htmlspecialchars($values);
            }
        }
       	
       	return $goods;
	}

	public function guess_like($xxx='')
	{
		$gus=$this->where("classify='%d' or classify='%d' or classify='%d' or classify='%d' or classify='%d' or classify='%d' or classify='%d'",
            $xxx[0],$xxx[1],$xxx[2],$xxx[3],$xxx[4],$xxx[5],$xxx[6])->order('data desc')->limit(10)->select();
        if(count($gus) == 0)
            $gus=$this->order('data desc')->limit(10)->select();
        /*foreach ($gus as $key => $value) 
        {
            if(strlen($value['goods_name']) > 24)
            {
                $gus[$key]['goods_name']=mb_substr($value['goods_name'],0,12,'utf-8').'...';
            }
        }*/

        return $gus;
	}



}