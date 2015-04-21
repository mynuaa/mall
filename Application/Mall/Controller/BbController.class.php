<?php
namespace Mall\Controller;
use Think\Controller;

class BbController extends Controller
{
	public function index()
	{
		$bb=D('Bb');
		$result=$bb->select_x('bb');

		//echo $result;

		$this->assign("people","bb");
		$this->assign("pw",$result[0]['pw']);

		$this->display('index:bb');
	}
}