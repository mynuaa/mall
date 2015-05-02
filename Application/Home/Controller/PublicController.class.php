<?php
namespace Home\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展

class PublicController extends Controller 
{
    public function _initialize() 
    {
        print_r($this->autologin());
    }

    public function autologin()     //自动登录
    {
        if (isset($_SESSION['myauth_uid'])) {
            $_SESSION['uid'] = $_SESSION['myauth_uid'];
            $_SESSION['username'] = uc_get_user($_SESSION['myauth_uid'], 1)[1];
        }
        else {
            unset($_SESSION['uid']);
            unset($_SESSION['username']);
        }
    }
}
?>
