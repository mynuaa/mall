<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');
class TestController extends PublicController {
    public function index()
    {
        var_dump(session('?uid'));
    }

}
