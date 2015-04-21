<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');  //载入UcApi扩展

class PublicController extends Controller 
{
    public function _initialize() 
    {
       // dump($_COOKIE);
        print_r($this->autologin());

    }

    public function autologin()     //自动登录
    {    
        if(!session('?uid') && isset($_COOKIE['yvDU_2132_auth']) && !empty($_COOKIE['yvDU_2132_auth'])) 
        { 
			$AUTH_KEY = '935ce1aQbWaGvivb';   //解密钥匙，存放在discuz目录的config的config_global.php文件中的$_config['security']['authkey']
            $key = md5($AUTH_KEY . $_COOKIE['yvDU_2132_saltkey']); //解密钥匙		
            $userMsg = explode("\t", uc_authcode($_COOKIE['yvDU_2132_auth'], 'DECODE', $key)); //得到加了密的password和uid
            $userInfo = uc_get_user($userMsg[1], 1);   //获取用户信息 (通过ID ，获取name)    
            // "竟然自动登录了！";
            if($userMsg[0]!='')    //如果不为空的话
            {
                //echo "尼玛！竟然赋值了@_@";
                $_SESSION['uid'] = $userMsg[1];
                $_SESSION['username'] = $userInfo[1];
            }
        }     
    }
}
?>
