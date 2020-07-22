<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class MsgTemplateModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 获取消息模版code
     * @param null $code
     * @return array|mixed|null
     */
    public function getMsgCodeList($code = null)
    {
        $ret = [
            'AUTO_SEND_TO_MAN' => [
                'code' => 'AUTO_SEND_TO_MAN',
                'desc' => '系统自动给男性用户发送单聊消息'
            ]
        ];

        if ($code) {
            return isset($ret[$code]) ? $ret[$code] : null;
        } else {
            return  $ret;
        }
    }
}