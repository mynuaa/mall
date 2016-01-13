<?php
namespace Mall\Controller;
use Think\Controller;
Vendor('ThinkphpUcenter.UcApi');
class TestController extends Controller {
    public function index()
    {
        var_dump(session('?uid'));
    }

}
