<?php
namespace Chan\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index()
    {
    	$app_title = '换客天地 - 南航mall';
        $this->assign('app_title',$app_title);
        $this->assign('uid',session('uid')); 
        $this->assign('username',session('username'));
        $this->assign('m','chan');
        $this->display('Index:plat_chan');
    }
}