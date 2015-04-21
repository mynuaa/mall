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
        if (isset($_COOKIE['1qlM_2132_auth']) && !empty($_COOKIE['1qlM_2132_auth'])) {
            Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展
			// 'AUTH_KEY' => '1aa83cCPc7hUxHdy' //解密钥匙，存放在discuz目录的config的config_global.php文件中的$_config['security']['authkey']
			//这里我把这个值写到配置文件去了。所以是用 C 读出来
            $key = md5(C('AUTH_KEY') . $_COOKIE['1qlM_2132_saltkey']); //解密钥匙		
            $userMsg = explode("\t", uc_authcode($_COOKIE['1qlM_2132_auth'], 'DECODE', $key)); //得到加了密的password和uid
            $userInfo = uc_get_user($userMsg[1], 1);   //获取用户信息 (通过ID ，获取name)         
            //dump($userMsg);
            //dump($userInfo);
			$_SESSION['user_id'] = $userMsg[1];
            $_SESSION['username'] = $userInfo[1];
            //dump($_SESSION);
        } else {    //论坛没有登录
            Session::destroy();
        }
    }
}
?>
