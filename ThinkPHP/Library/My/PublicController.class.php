<?php

/**
 * 方法类
 *
 * @author YJQ
 */
class PublicController extends Controller {

    public function _initialize() {
        //dump($_SESSION);
        dump($_COOKIE);
        $this->autologin();
    }

	//自动登录
    public function autologin() {
        if (isset($_COOKIE['myauth_uid'])) {
            $uid = $this->getuid();
            session('uid', $uid);
            session('username', uc_get_user($uid, 1)[1]);
        }
        else {
            session('uid', null);
            session('username', null);
        }
    }
}
?>
