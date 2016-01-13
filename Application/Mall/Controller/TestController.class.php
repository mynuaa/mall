<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');
class IndexController extends PublicController {
    public function index()
    {
        var_dump(session('uid'));
    }

}
