<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展

class PublicController extends Controller 
{
    public function _initialize() 
    {
        echo $this->autologin();
    }
    // 通过本应用的公钥加密
    public function my_encrypt($str) {
        $public_key = openssl_pkey_get_public(PUBLIC_KEY);
        if (!openssl_public_encrypt($str, $encrypted, $public_key)) return false;
        return $encrypted;
    }
    // 通过本应用的私钥解密
    public function my_decrypt($str) {
        $encrypted = base64_decode($str);
        $private_key = openssl_pkey_get_private(PRIVATE_KEY);
        if (!openssl_private_decrypt($encrypted, $str, $private_key)) return false;
        return $str;
    }
    public function getuid() {
        if (!($uid = $this->my_decrypt($_COOKIE['myauth_uid']))) {
            setcookie('myauth_uid', '', time() - 3600);
            return false;
        }
        if (!$uid) return false;
        $uid = json_decode($uid, true);
        return $uid['uid'];
    }
    public function autologin()     //自动登录
    {
        if (isset($_COOKIE['myauth_uid'])) {
            $uid = $this->getuid();
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
