<?php
/**
 * 特定配置
 * @author zjy
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Validate;

class GivenConfigController extends AdminBaseController
{
    public function defaultPage(){
        exit('暂未开放');
    }
    /**
     * 社区设置
     */
    public function setCommunity(){
        $option = cmf_get_option('set_community');
        $this->assign("option", $option);
        return $this->fetch();
    }


    /**
     * 社区设置提交
     */
    public function setCommunityPost(){
        if ($this->request->isPost()) {
            $param = $this->request->post('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            if (empty($params)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('set_community', $params)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 主播等级配置
     */
    public function AnchorLevel(){
        $option = cmf_get_option('anchor_level');
        $this->assign("option", $option);
        return $this->fetch('given_config/anchor_level/index');
    }

    /**
     * 主播等级配置提交
     */
    public function AnchorLevelPost(){
        if($this->request->isAjax()){
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            if(empty($params['AnchorLevel'])){
                $this->error('服务器出错');
            }else{
                $condition = [];
                //验证范围值是否符合规范
                foreach ($params['AnchorLevel'] as $key => $val){
                    $arr = explode(',',$val);
                    if(!empty($arr) && count($arr) > 1 && is_numeric($arr[1]) && is_numeric($arr[0]) && ($arr[1] > $arr[0])){
                        $condition[$key] = $arr;
                        switch ($key){
                            case 'goddess':
                                if(!isset($condition['model']) || ($condition['goddess'][0] <= $condition['model'][1])){
                                    $this->error('女神范围值必须大于模特范围值');
                                }
                                break;
                            case 'queen':
                                if(!isset($condition['goddess']) || ($condition['queen'][0] <= $condition['goddess'][1])){
                                    $this->error('女王范围值必须大于女神范围值');
                                }
                                break;
                            default:
                                break;
                        }
                    }else{
                        $this->error('不能为空或范围值不正确');
                    }
                }
                //保存配置
                if (cmf_set_option('anchor_level', $params)) {
                    cmf_clear_cache();
                    $this->success('保存成功！');
                } else {
                    $this->error('保存失败，请重新操作！');
                }

            }
        }else{
            $this->error('无权访问');
        }
    }
}
