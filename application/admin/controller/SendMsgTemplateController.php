<?php
/**
 * 消息，通知模板管理
 */
namespace app\admin\controller;

use app\admin\model\MsgTemplateModel;
use app\admin\service\SendMsgTemplateService;
use cmf\controller\AdminBaseController;
use think\Exception;
use think\Validate;

class SendMsgTemplateController extends AdminBaseController
{
    /**
     * 消息模版列表
     * @throws Exception
     */
    public function msgTemplateList()
    {
      /* $param = $this->request->param();

        $where = [];
        $field = 'm.*';
        if (!empty($param['content'])) {
            $where['m.content'] = ['LIKE', '%'.$param['content'].'%'];
        }
        if (!empty($param['tmp_code'])) {
            $where['m.tmp_code'] = $param['tmp_code'];
        }

        $msgModel = new MsgTemplateModel();
        $data = $msgModel->alias('m')->field($field)
            ->where($where)
            ->order('m.id', 'DESC')
            ->paginate(20);

        $codeList = $msgModel->getMsgCodeList();
        $this->assign('msg_code_list', $codeList);
        $this->assign('search', $param);
        $this->assign('list', $data->items());
        $this->assign('page', $data->render());

        return $this->fetch('msg_template_list');*/

        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => !isset($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = SendMsgTemplateService::RList($condition);
            //var_dump($result);die;
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $item)
                {
                    //操作
                    $opera = '<button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$item['id'].')"><i class="icon wb-edit" aria-hidden="true" ></i> 编辑</button>';

                    $filed =  [
                        'id' => $item['id'],
                        'type' => SendMsgTemplateService::typeList($item['type']),
                        'tmp_code' => $item['tmp_code'],
                        'content' => $item['content'],
                        'note' => $item['note'],
                        'create_time' => !empty($item['create_time']) ? date("Y-m-d H:i:s",$item['create_time']) : '空',
                        'update_time' => !empty($item['update_time']) ? date("Y-m-d H:i:s",$item['update_time']) : '空',
                        'status' => SendMsgTemplateService::statusList($item['status']),
                        'opera' => $opera
                    ];
                    array_push($data,$filed);
                }
            }
            return json_encode([
                "pageIndex"=> $params['pageIndex'],//分页索引
                "pageSize"=> $params['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "sortField"=> 'id',//排序字段
                "sortType"=> 'desc',//排序类型
                "total"=> $result['total'],//总记录数
                'pageList'=>$data,//分页数据
                "data"=> $params['data']//表单参数
            ]);
        }
        return $this->fetch('list');
    }

    /**
     * 添加消息模板
     * @return false|mixed|string
     * @throws Exception
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $validate = new Validate([
                'tmp_code'  => 'require|max:100',
                'content' => 'require|max:255',
            ]);

            $validate->message([
                'content.require' => '请输入消息模版内容!',
                'content.max' => '模版内容不能超过255个字符!',
                'tmp_code.require'  => '请输入消息模版编码!',
                'tmp_code.max'  => '模版编码不能超过100个字符!'
            ]);

            $param = $this->request->param();

            if (!$validate->check($param)) {
                return json_encode(['code'=> 0, 'msg' => $validate->getError()]);
            }
            $condition = [
                'tmp_code' =>$param['tmp_code'],
                'content' =>$param['content'],
                'type' =>$param['type'],
                'note' =>$param['note'],
                'status' =>$param['status'],
                'create_time' => time(),
            ];
            $insert_id = SendMsgTemplateService::AddInfo($condition);
            if(!empty($insert_id)){
                return json_encode(['code'=>200, 'msg' => '添加成功']);
            }else{
                return json_encode(['code'=> 0, 'msg' => '添加失败']);
            }
        }
        $type = SendMsgTemplateService::typeList();
        $this->assign('type', $type);
        return $this->fetch('add');
    }

    /**
     * 编辑消息模板
     * @return mixed
     * @throws Exception
     */
    public function EditInfo(){
        if($this->request->isAjax()){
            $validate = new Validate([
                'tmp_code'  => 'require|max:100',
                'content' => 'require|max:255',
                'id' => 'require',
            ]);

            $validate->message([
                'id.require' => 'id不能为空!',
                'content.require' => '请输入消息模版内容!',
                'content.max' => '模版内容不能超过255个字符!',
                'tmp_code.require'  => '请输入消息模版编码!',
                'tmp_code.max'  => '模版编码不能超过100个字符!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                return json_encode(['code'=> 0, 'msg' => $validate->getError()]);
            }
            $condition = [
                'tmp_code' =>$param['tmp_code'],
                'content' =>$param['content'],
                'type' =>$param['type'],
                'note' =>$param['note'],
                'status' =>$param['status'],
                'update_time' => time(),
            ];
            $res = SendMsgTemplateService::UpdateInfo(['id'=>$param['id']],$condition);
            if(!empty($res)){
                return json_encode(['code'=>200, 'msg' => '修改成功']);
            }else{
                return json_encode(['code'=> 0, 'msg' => '修改失败']);
            }
        }
        $param = $this->request->param();
        if(!isset($param['id'])){
            $this->error('参数错误');
        }
        $info = SendMsgTemplateService::ToInfo(['id'=>$param['id']]);
        if(empty($info)){
            $this->error('数据错误');
        }
        $type = SendMsgTemplateService::typeList();
        $this->assign('type', $type);
        $this->assign('info', $info);
        return $this->fetch('edit');
    }

    /**
     * 消息模版新增
     * @throws
     */
    public function msgTemplateAdd()
    {
        $msgModel = new MsgTemplateModel();

        if ($this->request->isPost()) {
            $validate = new Validate([
                'content' => 'require',
                'tmp_code'  => 'require',
            ]);

            $validate->message([
                'content.require' => '请输入模版内容!',
                'tmp_code.require'  => '请输入模版编码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param['post'])) {
                $this->error($validate->getError());
            }

            # 插入新数据
            $insert = $param['post'];
            $insert['create_time'] = time();
            $autoId = $msgModel->insertGetId($insert);
            if ($autoId) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }

        $codeList = $msgModel->getMsgCodeList();
        $this->assign('msg_code_list', $codeList);

        return $this->fetch();
    }

    /**
     * 消息模版修改
     * @throws
     */
    public function msgTemplateEdit()
    {
        $msgModel = new MsgTemplateModel();

        if ($this->request->isPost()) {
            $validate = new Validate([
                'id' => 'require|integer',
                'content' => 'require',
                'tmp_code'  => 'require',
            ]);

            $validate->message([
                'content.require' => '请输入模版内容!',
                'tmp_code.require'  => '请输入模版编码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param['post'])) {
                $this->error($validate->getError());
            }

            # 更新数据
            $insert = $param['post'];
            $insert['update_time'] = time();
            $autoId = $msgModel->update($insert);
            if ($autoId) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }

        $id = $this->request->param('id', 0, 'int');
        $data = $msgModel->where('id', $id)->find();
        if (empty($data)) {
            $this->error('数据为空！');
        }
        $this->assign('data', $data);
        $codeList = $msgModel->getMsgCodeList();
        $this->assign('msg_code_list', $codeList);

        return $this->fetch();
    }

    /**
     * 给男用户发送单聊消息
     * @adminMenu(
     *     'name'   => '任务管理',
     *     'parent' => 'admin/AdminChatTask/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '任务列表',
     *     'param'  => ''
     * )
     */
    public function sendSingleMsg2ManConfig()
    {
        $sendSingleMsg2ManSettings = cmf_get_option('send_single_msg_2_man_config');
        $this->assign("send_single_msg_2_man", $sendSingleMsg2ManSettings);

        return $this->fetch('single_msg_2_man');
    }

    /**
     * 给男用户发送单聊消息提交
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     */
    public function sendSingleMsg2ManConfigPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('send_single_msg_2_man_config', $post)) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

}
