<?php
/**
 * User: coase
 * Date: 2019-03-06
 */
namespace api\app\controller;

use think\Db;
use think\Validate;
use think\Exception;
use cmf\controller\RestBaseController;
use api\app\module\MaterialModule;

/**
 * #####广告位模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.社区首页顶部广告位
 * ``````````````````
 */
class AdController extends RestBaseController
{
    /**
     * app启动广告
     */
    public function adLaunchScreen()
    {
        try {
            // 获取banner
            $bannerFind = Db::name('banner')->where(['type' => 3, 'status' => 1])->order('sort')->find();

            if ($bannerFind) {
                $bannerList[] = [
                    'img' => MaterialModule::getFullUrl($bannerFind['img_url']),
                    'link' => $bannerFind['a_url']
                ];
            } else {
                $bannerList = [];
            }

            $this->success("OK", [
                'list' => $bannerList
            ]);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 社区首页顶部广告位
     */
    public function adForumIndexTop()
    {
        try {
            // 获取banner
            $bannerFind = Db::name('banner')->where(['type' => 2, 'status' => 1])->order('sort')->find();

            if ($bannerFind) {
                $bannerList = [
                    'img' => MaterialModule::getFullUrl($bannerFind['img_url']),
                    'link' => $bannerFind['a_url']
                ];
            } else {
                $bannerList = [];
            }

            $this->success("OK", ['ad' => $bannerList]);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取banner--通用
     */
    public function getBanner()
    {
        try {
            $validate = new Validate([
                'scene_id' => 'require|number',
            ]);

            $validate->message([
                'scene_id.require'  => '场景错误!'
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $sceneId = $param['scene_id'];

            // 获取banner
            $bannerSelect = Db::name('banner')->where(['type' => $sceneId, 'status' => 1])->order('sort')->select();

            $bannerList = [];
            foreach ($bannerSelect as $item) {
                $bannerList[] = [
                    'img' => MaterialModule::getFullUrl($item['img_url']),
                    'link' => $item['a_url']
                ];
            }

            $this->success("OK", ['list' => $bannerList]);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取公告--通用
     */
    public function getNotice()
    {
        try {
            $validate = new Validate([
                'scene_id' => 'require|number', // 场景 1：直播列表页顶部 2：直播房间页顶部
            ]);

            $validate->message([
                'scene_id.require'  => '场景错误!'
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $sceneId = $param['scene_id'];

            # 获取配置参数
            $publicConfig = cmf_get_option('public_config');
            $noticeText = !empty($publicConfig['public_config']['Notice']['text']) ? $publicConfig['public_config']['Notice']['text'] : '';

            $aList = [
                '1' => [ // 直播列表页顶部
                    'type' => 1, // 类型 1：文字,不可点击
                    'content' => [ // 内容
                        'text' => $noticeText,
                        'link' => ''
                    ]
                ],
                '2' => [ // 直播房间页顶部
                    'type' => 1, // 类型 1：文字,不可点击
                    'content' => [ // 内容
                        'text' => $noticeText,
                        'link' => ''
                    ]
                ]
            ];

            $aRet = isset($aList[$sceneId]) ? $aList[$sceneId] : [];

            $this->success("OK", $aRet);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
