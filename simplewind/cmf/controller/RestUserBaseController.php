<?php
namespace cmf\controller;

class RestUserBaseController extends RestBaseController
{

    public function _initialize()
    {

        if (empty($this->user)) {
            $this->error(['code' => 1001, 'msg' => '登录已失效!']);
        }

    }

}