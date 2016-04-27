<?php

namespace Common\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展

class LoginController extends Controller
{
    public function _initialize() {
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
            session('uid', $user['uid']);
            session('username', $user['username']);
        }
        else {
            // 跳到授权页面
            header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxa04c7656484a07d2&redirect_uri=' . rawurlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") . '&response_type=code&scope=snsapi_base&state=mynuaa#wechat_redirect');
        }
    }
    function my_encrypt($str, $appid = '') {
        if ($appid) $appid .= '_';
        $public_key = openssl_pkey_get_public(file_get_contents(MYAUTH_CERT_PATH . "/{$appid}public_key.pem"));
        if (!openssl_public_encrypt($str, $encrypted, $public_key)) return false;
        return base64_encode($encrypted);
    }
    function my_decrypt($str, $appid = '') {
        if ($appid) $appid .= '_';
        $encrypted = base64_decode($str);
        $private_key = openssl_pkey_get_private(file_get_contents(MYAUTH_CERT_PATH . "/{$appid}private_key.pem"));
        if (!openssl_private_decrypt($encrypted, $str, $private_key)) return false;
        return $str;
    }
    function get_pubkey_for_js() {
        return trim(file_get_contents(MYAUTH_CERT_PATH . "/js_public_key.dat"));
    }
    function getuid() {
        if (!($uid = $this->my_decrypt($_COOKIE['myauth_uid']))) {
            setcookie('myauth_uid', '', time() - 3600);
            return false;
        }
        if (!$uid) return false;
        $uid = json_decode($uid, true);
        $uid = intval($uid['uid']);
        return $uid;
    }
    public function autologin()     //自动登录
    {
        if (isset($_COOKIE['myauth_uid']) && $uid = $this->getuid()) {
            session('uid', $uid);
            session('username', uc_get_user($uid, 1)[1]);
        }
        else {
            session('uid', null);
            session('username', null);
        }
    }
}
