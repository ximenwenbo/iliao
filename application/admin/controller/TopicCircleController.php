<?php
/**
 * 话题圈子管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\model\TopicCircleModel;
use app\admin\service\forum\CjDataService;
use app\admin\service\TopicCircleService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Validate;

class TopicCircleController extends AdminBaseController
{
    /**
     * 列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $param = $this->request->param();
        $get = $_GET;
        if (isset($get['status']) && $get['status'] != $param['status']) {
            $param['status'] = $get['status'];
            $param['current_page'] = 0;
        }
        $where = [
            'keywords' => empty($param['keywords']) ? '' : $param['keywords'],
            'xchat_y_level' => empty($param['xchat_y_level']) ? '' : $param['xchat_y_level'],
            'cj_status' => isset($param['status']) ? $param['status'] : '' ,
            'pageSize' => isset($param['pageSize']) ? intval($param['pageSize']) : 10 ,
            'current_page' => empty($param['current_page']) ? 0 : intval($param['current_page']),
        ];

        $data = TopicCircleService::PostList($where);
        //var_dump($data);die;
        if(!empty($data['data'])){
            foreach ($data['data'] as $key=>&$item){
                if (preg_match('/^http/', $item['avatar']) == 0) {
                    $item['avatar'] = 'https://avatar01.jiaoliuqu.com/'.$item['avatar'];
                }
                $item['sex_type'] = $item['sex_type'] == 1 ? '/assets/admin/sex_man.ico' : '/assets/admin/sex_woman.ico';
                $item['img_list'] = !empty($item['img_list']) ? json_decode($item['img_list'],true) : '';
                $item['media_list'] = !empty($item['media_list']) ? json_decode($item['media_list'],true) : '';
                $item['cj_status_text'] = TopicCircleService::statusList($item['cj_status']);


                $review = TopicCircleService::ReviewList("post_uuid = '{$item['uuid']}' and cj_status >= 0") ;

                if(!empty($review)){
                    foreach ($review as &$val){
                        $val['cj_status_text'] = TopicCircleService::statusList($val['cj_status']);
                        $account = TopicCircleModel::TableFind('t_jiaoliuqu_account',['account_uuid'=>$val['account_uuid']],'avatar,nickname,sex_type');
                        if($account){
                            $val['account']['avatar'] = preg_match('/^http/', $account['avatar']) == 0 ? 'https://avatar01.jiaoliuqu.com/'.$account['avatar'] : $account['avatar'];
                            $val['account']['nickname'] = $account['nickname'];
                            $val['account']['sex_type'] = $account['sex_type'] == 1 ? '/assets/admin/sex_man.ico' : '/assets/admin/sex_woman.ico';
                        }else{
                            $val['account']['avatar'] = '';
                            $val['account']['nickname'] = $val['nickname'];
                            $val['account']['sex_type'] = $val['sex_type'] == 1 ? '/assets/admin/sex_man.ico' : '/assets/admin/sex_woman.ico';
                        }

                    }
                    $item['review'] = $review;
                }else{
                    $item['review'] = [];
                }

            }
        }
        $page_status = isset($param['status']) ? $param['status'] : '';
        $post_status = TopicCircleService::statusList(-999);
        $total_pages = ceil($data['total']/$where['pageSize']);
        $xchat_y_level = [1,2,3,4,5,6,7,8,9,10];
        $this->assign('xchat_y_level',$xchat_y_level);
        $this->assign('page_status',$page_status);
        $this->assign('post_status',$post_status);
        $this->assign('param',$where);
        $this->assign('pageTotal',$data['total']);
        $this->assign('data',$data['data']);
        $this->assign('total_pages',$total_pages);
        return $this->fetch('index');
    }

    /**
     * (采用/删除)动态
     * @throws
     */
    public function adoptPost(){
        $uuid = $this->request->param('uuid');
        $type = $this->request->param('type');
        $tqDb = CjDataService::connectCjtaqu();
        switch ($type){
            case 1:
                $statusRes = $tqDb->table('t_jiaoliuqu_post')->where(['uuid'=>$uuid])->update(['cj_status'=>1]);
                if($statusRes){
                    return json_encode(['msg'=>'采用成功','code'=> 200]);
                }else{
                    return json_encode(['msg'=>'采用失败','code'=> 0]);
                }
                break;
            case 2:
                //先删除该动态
                $statusRes = $tqDb->table('t_jiaoliuqu_post')->where(['uuid'=>$uuid])->update(['cj_status'=>-99]);
                if(!$statusRes){
                    return json_encode(['msg'=>'删除失败','code'=>0]);
                }
                //在删除该动态下的回复
                $re_statusRes = $tqDb->table('t_jiaoliuqu_review')->where(['post_uuid'=>$uuid])->update(['cj_status'=>-99]);
                if(!$re_statusRes){
                    return json_encode(['msg'=>'删除失败','code'=>0]);
                }
                return json_encode(['msg'=>'删除成功','code'=>200]);
                break;
            default:
                return json_encode(['msg'=>'操作无效','code'=>0]);
                break;
        }
    }

    /**
     * 同步动态
     * @throws
     */
    public function synchroPost(){
        $uuid = $this->request->param('uuid');
        $tqDb = CjDataService::connectCjtaqu();
        Db::startTrans();
        try{
            //更改为采用状态
            $status = $tqDb->table('t_jiaoliuqu_post')->where(['uuid'=>$uuid])->value('cj_status');
            if($status && $status != 1) {
                $statusRes = $tqDb->table('t_jiaoliuqu_post')->where(['uuid' => $uuid])->update(['cj_status' => 1]);
                if (!$statusRes) {
                    throw new Exception('采用动态失败');
                }
            }
            //同步到数据库
            $postRes = CjDataService::translatePostData($uuid);
            if(!$postRes){
                //失败原因
                $trans_error = $tqDb->table('t_jiaoliuqu_post')->where(['uuid'=>$uuid])->value('trans_error');
                throw new Exception($trans_error?$trans_error:'同步动态失败');
            }
            Db::commit();
            return json_encode(['msg'=>'同步成功','code'=>200]);
        }catch (Exception $exception)
        {
            Db::rollback();
            return json_encode(['code'=>0,'msg'=>$exception->getMessage()]);
        }

    }

    /**
     * (采用/删除)动态下的回复
     * @throws
     */
    public function adoptReview(){
        $uuid = $this->request->param('uuid');
        $type = $this->request->param('type');
        $tqDb = CjDataService::connectCjtaqu();
        switch ($type){
            case 1:
                $statusRes = $tqDb->table('t_jiaoliuqu_review')->where(['uuid'=>$uuid])->update(['cj_status'=>1]);
                if($statusRes){
                    return json_encode(['msg'=>'采用成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'采用失败','code'=>0]);
                }
                break;
            case 2:
                $statusRes = $tqDb->table('t_jiaoliuqu_review')->where(['uuid'=>$uuid])->update(['cj_status'=>-99]);
                if($statusRes){
                    return json_encode(['msg'=>'删除成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'删除失败','code'=>0]);
                }
                break;
            default:
                return json_encode(['msg'=>'操作无效','code'=>0]);
                break;
        }
    }

    /**
     * 同步动态
     * @throws
     */
    public function synchroReview(){
        $uuid = $this->request->param('uuid');
        $tqDb = CjDataService::connectCjtaqu();
        //return json_encode(['msg'=>'同步回复成功','code'=>200,'data'=>$uuid]);
        Db::startTrans();
        try{
            //更改为采用状态
            $status = $tqDb->table('t_jiaoliuqu_review')->where(['uuid'=>$uuid])->value('cj_status');
            if($status != 1){
                $statusRes = $tqDb->table('t_jiaoliuqu_review')->where(['uuid'=>$uuid])->update(['cj_status'=>1]);
                if(!$statusRes){
                    throw new Exception('采用动态失败'.$statusRes);
                }
            }

            //同步到数据库
            $postRes = CjDataService::translateReviewData($uuid);
            if(!$postRes){
                //失败原因
                $trans_error = $tqDb->table('t_jiaoliuqu_review')->where(['uuid'=>$uuid])->value('trans_error');
                throw new Exception($trans_error?$trans_error:'同步回复失败');
            }
            Db::commit();
            return json_encode(['msg'=>'同步回复成功','code'=>200]);
        }catch (Exception $exception)
        {
            Db::rollback();
            return json_encode(['code'=>0,'msg'=>$exception->getMessage()]);
        }

    }

    /**
     * (采用/删除)动态下所有的回复
     * @throws
     */
    public function adoptReviewAll(){
        $uuid = $this->request->param('uuid');
        $type = $this->request->param('type');
        $tqDb = CjDataService::connectCjtaqu();
        switch ($type){
            case 1:
                $ids = $tqDb->table('t_jiaoliuqu_review')->where(['post_uuid'=>$uuid,'cj_status'=>0])->field('id')->select();
                if(!empty($ids)){
                   foreach ($ids as $id){
                       $statusRes = $tqDb->table('t_jiaoliuqu_review')->where("id = {$id['id']}")->update(['cj_status'=>1]);
                       if(!$statusRes){
                           return json_encode(['msg'=>'采用失败','code'=>0,'data'=>$statusRes]);
                       }
                   }
                    return json_encode(['msg'=>'采用成功','code'=>200,'data'=>$id]);
                }else{
                    return json_encode(['msg'=>'该评论下没有待采用回复','code'=>0]);
                }
                break;
            case 2:
                $ids = $tqDb->table('t_jiaoliuqu_review')->where("`post_uuid`='{$uuid}' and `cj_status`in(0,1)")->field('id')->select();
                if(!empty($ids)){
                    foreach ($ids as $id){
                        $statusRes = $tqDb->table('t_jiaoliuqu_review')->where("id = {$id['id']}")->update(['cj_status'=>-99]);
                        if(!$statusRes){
                            return json_encode(['msg'=>'采用失败','code'=>0,'data'=>$statusRes]);
                        }
                    }
                    return json_encode(['msg'=>'删除成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'该评论下没有回复,无法删除','code'=>0]);
                }
                break;
            default:
                return json_encode(['msg'=>'操作无效','code'=>0]);
                break;
        }
    }

    /**
     * 用户详情
     * @throws
     */
    public function UserDetails(){
        $account_uuid = $this->request->param('account_uuid');
        $tqDb = CjDataService::connectCjtaqu();
        $user_info = $tqDb->name('t_jiaoliuqu_account')->where(['account_uuid'=>$account_uuid])->find();
        if(!empty($user_info)){
            $user_info['img_list'] = empty($user_info['img_list']) ? '' : json_decode($user_info['img_list'],true);
            $user_info['video_intro'] = empty($user_info['video_intro']) ? '' : json_decode($user_info['video_intro'],true);
            $user_info['cj_status'] = TopicCircleService::statusList($user_info['cj_status']);
            $user_info['trans_time'] = empty($user_info['trans_time']) ? '' : date("Y-m-d H:i:s", $user_info['trans_time']);
            $sex = ['未知','男','女'];
            $user_info['sex_type'] = isset($sex[$user_info['sex_type']]) ? $sex[$user_info['sex_type']] : '无';
            //var_dump($user_info);die;
            $this->assign('user_info',$user_info);
            return $this->fetch();
        }else{
            echo '没有改用户信息';
        }
    }

    /**
     * @return false|mixed|string
     * @throws
     */
    public function yellowEdit(){
        $tqDb = CjDataService::connectCjtaqu();
        if($this->request->isAjax()){
            $params = $this->request->param();
            $validate = new Validate([
                'uuid' => 'require',//昵称
                'xchat_y_level' => 'require|number|between:1,10', //验证
            ]);
            $validate->message([
                'id.require' => 'uuid不能为空',
                'xchat_y_level.between' => '等级必须在1-10之间',
                'xchat_y_level.require' => '涉黄等级不能为空',
                'xchat_y_level.number' => '涉黄等级必须为正整数',
            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            $res = $tqDb->table('t_jiaoliuqu_post')->where(['uuid'=>$params['uuid']])->update(['xchat_y_level'=>$params['xchat_y_level']]);
            if($res){
                return json_encode(['msg'=>'修改成功','code'=>200]);
            }else{
                return json_encode(['code'=>0,'msg'=> '修改失败']);
            }

        }
        $uuid = $this->request->param('uuid');
        $info = $tqDb->table('t_jiaoliuqu_post')->where(['uuid'=>$uuid])->field('uuid,xchat_y_level')->find();
        //var_dump($info);die;
        $this->assign('info',$info);
        $y_level = [1,2,3,4,5,6,7,8,9,10];
        $this->assign('y_level',$y_level);
        return $this->fetch();
    }


    /**
     * 用户涉黄等级修改
     * @return false|mixed|string
     * @throws
     */
    public function userYellowEdit(){
        $tqDb = CjDataService::connectCjtaqu();
        if($this->request->isAjax()){
            $params = $this->request->param();
            $validate = new Validate([
                'id' => 'require',//昵称
                'y_level' => 'require|number|between:1,10', //验证
            ]);
            $validate->message([
                'id.require' => 'id不能为空',
                'y_level.between' => '等级必须在1-10之间',
                'y_level.require' => '涉黄等级不能为空',
                'y_level.number' => '涉黄等级必须为正整数',
            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            $res = $tqDb->table('t_jiaoliuqu_account')->where(['account_uuid'=>$params['y_level']])->update(['xchat_y_level'=>$params['xchat_y_level']]);
            if($res){
                return json_encode(['msg'=>'修改成功','code'=>200]);
            }else{
                return json_encode(['code'=>0,'msg'=> '修改失败']);
            }

        }
    }

//    public function downTaquAllPostByUuid()
//    {
//        $accountUuid = $this->request->param('account_uuid');
//        $oGeneralApi = new \cjdata\cjtaqu\GeneralApi;
//        $res = $oGeneralApi->getOneUserAllDataByUuid($accountUuid);
//        if ($res['code'] == 'ok') {
//            return json_encode([
//                'code' => 200,
//                'msg' => sprintf('新增动态%d条，重复动态%d条.', $res['data']['new_post'], $res['data']['repeat_post'])
//            ]);
//        } else {
//            return json_encode([
//                'code' => 0,
//                'msg' => $res['msg']
//            ]);
//        }
//    }
}
