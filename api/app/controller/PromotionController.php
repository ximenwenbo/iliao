<?php
/**
 * User: coase
 * Date: 2019-03-21
 */
namespace api\app\controller;

use api\app\module\promotion\InviteModule;
use cmf\controller\RestBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\module\ConfigModule;
use api\app\module\MaterialModule;

/**
 * #####推广的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.
 * ``````````````````
 */
class PromotionController extends RestBaseController
{
    /**
     * 获取推广数据
     */
    public function getPromData()
    {
        try {
            $userId = $this->getUserId();

            // 获取oss资源访问域名的配置
            $shareLink = url('apph5/share/shareinstall', 'from_uid='. $userId .'&r='.time(), 'html', true);
//            $shareLink = $this->sinaDUrlAPI($shareLink);

            $option = cmf_get_option('share_config');
            $website = $option['share_config'];
            $data = [
                'share_link' => [
                    'title' => array_key_exists('share_title', $website) ? $website['share_title'] : '分享标题', // 标题
                    'desc' => array_key_exists('share_desc', $website) ? $website['share_desc'] : '分享描述', // 描述
                    'icon_url' => array_key_exists('share_logo_file', $website) ? MaterialModule::getFullUrl($website['share_logo_file']) : '', // icon url
                    'link' => $shareLink, // 分享链接
                ],
                'share_qr' => [
                    'qr_url' => url('app/promotion/createQrcode', ['url' => $shareLink], 'html', true), // 二维码图片url
                ]
            ];

            $this->success("OK", $data);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 生成二维码
     * @throws \Endroid\QrCode\Exception\InvalidWriterException
     */
    public function createQrcode()
    {
        try {
            $validate = new Validate([
                'url' => 'require',
            ]);

            $validate->message([
                'url.require' => '请输入url',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $qrCode = new \Endroid\QrCode\QrCode($param['url']);

            ob_clean();
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();die;

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 将被推广者添加到推广者【该接口需要app集成 shallinstall SDK】-- 废弃不用 20190802 Coase
     */
    public function addInviteUser()
    {
        try {
            $validate = new Validate([
                'from_uid' => 'require|integer', // 推广者用户id
            ]);

            $validate->message([
                'from_uid.require' => '请输入from_uid!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->success($validate->getError());
            }

            $userId = $this->getUserId(); // 被邀请者用户id

            if ($userId == $param['from_uid']) {
                $this->success('参数非法，自己不能邀请自己');
            }

            $inviteUserInfo = Db::name("user")->find($param['from_uid']);
            if (! $inviteUserInfo) {
                $this->success('参数非法，邀请者不存在');
            }

            if (Db::name("prom_invite_rela")->where('user_id', $userId)->count()) {
                $this->success('该用户被邀请过了');
            }

            DB::startTrans();
            try {
                // 更新用户邀请者
                Db::name('user')->where('id', $userId)->update(['from_uid' => $param['from_uid']]);

                // 新增邀请关系
                Db::name("prom_invite_rela")->insert([
                    'user_id' => $userId,
                    'parent_uid' => $param['from_uid'],
                    'level' => InviteModule::getInvitedLevel($param['from_uid'])
                ]);

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw new Exception('数据库执行错误,' . $e->getMessage(), 9901);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 生成sina短链
     * @param string $longRrl
     * @return bool|string
     */
    private function sinaDUrlAPI($longRrl)
    {
        $source = '3271760578';
        $longRrl = urlencode($longRrl);
        $url = "http://api.t.sina.com.cn/short_url/shorten.json?source={$source}&url_long={$longRrl}";
        $result = file_get_contents($url);
        $aResult = json_decode($result, true);
        if (!empty($aResult[0]['url_short'])) {
            return $aResult[0]['url_short'];
        }

        return false;
    }

    /**
     * 我的推广成绩总揽
     */
    public function getMyInviteData()
    {
        $this->error(['code' => 0, 'msg' => '关闭']);
        $userId = $this->getUserId();

        try {
            // 获取推广的总奖励
            $userFind = Db::name('user')->where('id', $userId)->field('bonus_coin,bonus_frozen_coin,bonus_used_coin')->find();
            $totalCoin = $userFind['bonus_coin'] + $userFind['bonus_frozen_coin'] + $userFind['bonus_used_coin'];

            // 获取推广的总用户数
            $totalUser = Db::name('prom_invite_rela')->where(['parent_uid' => $userId])->count();

            $data = [
                'total_user' => $totalUser . '人', // 总的用户数
                'total_money' => ConfigModule::coin2money($totalCoin) / 100 . '元', // 总的奖励金，单位元
                'regular_zh' => ConfigModule::getTips4Promotion()
            ];

            $this->success("OK", $data);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 我的推营销广钱包
     */
    public function myWallet()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $userRow = Db::name("user")->find($userId);

            $totalBonusCoin = $userRow['bonus_coin'] + $userRow['bonus_frozen_coin'] + $userRow['bonus_used_coin'];


            $publicConfig = cmf_get_option('public_config');
            $minQuota = $publicConfig['public_config']['Withdraw']['quota'];

            $this->success("OK", [
                'bonus_coin' => $userRow['bonus_coin'],
                'bonus_money' => sprintf('%.2f', ConfigModule::coin2money($userRow['bonus_coin']) / 100),
                'total_bonus_coin' => $totalBonusCoin,
                'coin_note' => sprintf('注：1元=%d'.\dctool\Cgf::getCoinNickname(), ConfigModule::getCoinRate()),
                'proportion_desc' => "1、满{$minQuota}元才可提现\\n2、预计1~2工作日到账，节假日顺延\\n3、请输入与实名信息一致的支付宝账号",
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 邀请奖励排行榜
     */
    public function getInviteBonusRanking()
    {
        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;
            if ($iPage > 1) {
                $this->success("OK", ['list' => []]);
            }

            $result = Db::name('prom_invite_bonus')
                ->alias('b')
                ->join('user u', 'u.id = b.user_id')
                ->group('b.user_id')
                ->field('SUM(b.coin) as total,u.id as user_id,u.user_nickname,u.avatar')
                ->order('total desc')
                ->page($iPage, $iPageSize)
                ->select()
                ->toArray();

            foreach ($result as &$row) {
                $row['avatar'] = MaterialModule::getFullUrl($row['avatar']);
                $row['total_zh'] = $row['total'] . \dctool\Cgf::getCoinNickname();
            }

            $this->success("OK", ['list' => $result]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 邀请人数排行榜
     */
    public function getInviteUserRanking()
    {
        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;
            if ($iPage > 1) {
                $this->success("OK", ['list' => []]);
            }

            $result = Db::name('prom_invite_rela')
                ->alias('b')
                ->join('user u', 'u.id = b.parent_uid')
                ->group('b.parent_uid')
                ->field('COUNT(b.id) as total,u.id as user_id,u.user_nickname,u.avatar')
                ->order('total desc')
                ->page($iPage, $iPageSize)
                ->select()
                ->toArray();

            foreach ($result as &$row) {
                $row['avatar'] = MaterialModule::getFullUrl($row['avatar']);
                $row['total_zh'] = $row['total'] . '人';
            }

            $this->success("OK", ['list' => $result]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 我的徒弟奖励数据
     */
    public function getMyLevel1Invite()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;
            $divideInto = cmf_get_option('divide_into');
            $manL1Rate = isset($divideInto['RechargeShare']['one']) ? sprintf('%.2f',100 / $divideInto['RechargeShare']['one']) : 0;

            $result = Db::name('prom_invite_bonus')
                ->alias('b')
                ->join('user u', 'u.id = b.from_uid')
                ->where("b.user_id={$userId}")
                ->where('b.invite_level', 1)
                ->group('b.from_uid')
                ->field('SUM(b.coin) as total,u.id as user_id,u.user_nickname,u.avatar')
                ->order('total desc')
                ->page($iPage, $iPageSize)
                ->select()
                ->toArray();

            foreach ($result as &$row) {
                $row['avatar'] = MaterialModule::getFullUrl($row['avatar']);
                $row['recharge_zh'] = ($row['total']*$manL1Rate) . \dctool\Cgf::getCoinNickname();
                $row['total_zh'] = $row['total'] . \dctool\Cgf::getCoinNickname();
            }

            $this->success("OK", ['list' => $result]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 我的徒孙奖励数据
     */
    public function getMyLevel2Invite()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;

            $result = Db::name('prom_invite_bonus')
                ->alias('b')
                ->join('user u', 'u.id = b.from_uid')
                ->where("b.user_id={$userId}")
                ->where('b.invite_level', 2)
                ->group('b.from_uid')
                ->field('SUM(b.coin) as total,u.id as user_id,u.user_nickname,u.avatar')
                ->order('total desc')
                ->page($iPage, $iPageSize)
                ->select()
                ->toArray();

            foreach ($result as &$row) {
                $row['avatar'] = MaterialModule::getFullUrl($row['avatar']);
                $row['total_zh'] = $row['total'] . \dctool\Cgf::getCoinNickname();
            }

            $this->success("OK", ['list' => $result]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
