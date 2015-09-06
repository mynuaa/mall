<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展

class PublicController extends Controller 
{
    public function _initialize() 
    {
        $this->checkwechat();
        $this->autologin();
    }
    public function checkwechat() {
        if (!preg_match('/micromessenger/i', $_SERVER['HTTP_USER_AGENT'])) return;
        if (isset($_COOKIE['myauth_uid'])) return;
        if (isset($_GET['code'])) {
            // 获取openid
            $result = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxa04c7656484a07d2&secret=66fe85f09de7ce2fac6d11e075886686&code={$_GET['code']}&grant_type=authorization_code");
            $result = json_decode($result, true);
            $openid = json_encode($result['openid']);
            $openid = preg_replace('/"/', '', $openid);
            // 处理绑定/登录
            $user = file_get_contents("http://my.nuaa.edu.cn/sso/?action=getuserbyopenid&openid=" . $openid);
            $user = json_decode($user, true);
            $arr = ['uid' => $user['uid']];
            setcookie('myauth_uid', $this->my_encrypt(json_encode($arr)), time() + 3600 * 10000, '/', NULL, NULL, true);
            echo PUBLIC_KEY;
            die($this->my_encrypt(json_encode($arr)));
        }
        else {
            // 跳到授权页面
            header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxa04c7656484a07d2&redirect_uri=' . rawurlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") . '&response_type=code&scope=snsapi_base&state=mynuaa#wechat_redirect');
        }
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
