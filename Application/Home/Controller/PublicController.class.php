<?php
namespace Home\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展

class PublicController extends Controller 
{
    public function _initialize() 
    {
        echo $this->autologin();
    }

    public function autologin()     //自动登录
    {
        if (isset($_COOKIE['myauth_uid'])) {
            $uid = explode("\t", uc_authcode($_COOKIE['myauth_uid'], 'DECODE', 'myauth'));
            $uid = intval($uid[1]);
            $_SESSION['uid'] = $uid;
            $_SESSION['username'] = uc_get_user($uid, 1)[1];
        }
        else {
            unset($_SESSION['uid']);
            unset($_SESSION['username']);
        }
    }
}
?>
