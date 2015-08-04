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
        openssl_public_encrypt($str, $encrypted, $public_key) || die('failed');
        return $encrypted;
    }
    // 通过本应用的私钥解密
    public function my_decrypt($str) {
        $encrypted = base64_decode($str);
        $private_key = openssl_pkey_get_private(PRIVATE_KEY);
        openssl_private_decrypt($encrypted, $str, $private_key) || die('failed');
        return $str;
    }
    public function autologin()     //自动登录
    {
        if (isset($_COOKIE['myauth_uid'])) {
            $uid = json_decode($this->my_decrypt($_COOKIE['myauth_uid']), true);
            $uid = intval($uid['uid']);
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
