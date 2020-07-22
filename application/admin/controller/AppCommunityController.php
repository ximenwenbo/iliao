<?php
/**
 * app社区
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use app\admin\service\AppCommunityService;
use app\admin\service\ForumReplyService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;


class AppCommunityController extends AdminBaseController
{
    /**
     * app社区首页
     * @author zjy
     * @throws
     */
    public function index()
    {
        $param = $this->request->param();
        $where = [
            'users' => empty($param['users']) ? '' : $param['users'],
            'keywords' => empty($param['keywords']) ? '' : $param['keywords'],
            'start_time' => empty($param['startDate']) ? '' : strtotime($param['startDate']),
            'end_time' => empty($param['endDate']) ? '' : strtotime($param['endDate'])+86399,
            'y_level' => !isset($param['y_level']) ? '' : $param['y_level'],
            'status' => isset($param['status']) ? intval($param['status']) : '' ,
            'pageSize' => isset($param['pageSize']) ? intval($param['pageSize']) : 6 ,
            'current_page' => empty($param['current_page']) ? 0 : intval($param['current_page']),
        ];
        //var_dump($where);die;
        $data = AppCommunityService::UList($where);

        if(!empty($data['data'])){
            foreach ($data['data'] as $key=>&$item){
                if (preg_match('/^http/', $item['avatar']) == 0) {
                    $item['avatar'] = MaterialService::getFullUrl($item['avatar']);
                }
                $item['sex_type'] = $item['sex']==1 ? '/assets/admin/sex_man.ico' : '/assets/admin/sex_woman.ico';
                $item['img_list'] = !empty($item['picture']) ? explode(',',$item['picture']) : '';
                $item['media_list'] = !empty($item['video']) ? MaterialService::getFullUrl($item['video']): '';
                $item['cj_status_text'] = AppCommunityService::statusList($item['status']);
                /*评论区*/
                $reply_id = 'SELECT reply_id FROM t_forum_opt_record WHERE object_id = '.$item['id'];
                $review = Db::name('forum_reply')->where("id in({$reply_id}) and status = 2")->select()->toArray();
                //var_dump($review);die;
                if(!empty($review)){
                    foreach ($review as &$val){
                        $val['cj_status_text'] = $val['status'] == 2 ? '正常' : '已删除';
                        $account = UserMemberService::ToInfo(['id'=>$val['user_id']],'sex,user_nickname,avatar');
                        if($account){
                            $val['account']['avatar'] = preg_match('/^http/', $account['avatar']) == 0 ? MaterialService::getFullUrl($account['avatar']) : $account['avatar'];
                            $val['account']['nickname'] = $account['user_nickname'];
                            $val['account']['sex_type'] = $account['sex']==1 ? '/assets/admin/sex_man.ico' : '/assets/admin/sex_woman.ico';
                        }else{
                            $val['account']['avatar'] = '';
                            $val['account']['nickname'] = '';
                            $val['account']['sex_type'] = '';
                        }

                    }
                    $item['review'] = $review;
                }else{
                    $item['review'] = [];
                }

            }

        }

        //var_dump($data);die;
        $post_status = AppCommunityService::statusList(-1);
        $total_pages = ceil($data['total']/$where['pageSize']);
        $y_level = [1,2,3,4,5,6,7,8,9,10];
        $where['start_time'] = !empty($param['startDate']) ? $param['startDate'] : '';
        $where['end_time'] = !empty($param['endDate']) ? $param['endDate'] : '';
        $this->assign('y_level',$y_level);
        $this->assign('post_status',$post_status);
        $this->assign('param',$where);
        $this->assign('pageTotal',$data['total']);
        $this->assign('data',$data['data']);
        $this->assign('total_pages',$total_pages);
        return $this->fetch('index');
    }

    /**
     * (删除)动态
     * @throws
     */
    public function adoptPost(){
        $id = $this->request->param('id');
        $res = AppCommunityService::UpdateB(['id'=>$id],['status'=>0]);
        if($res){
            return json_encode(['code'=>200, 'msg'=> '删除成功']);
        }else{
            return json_encode(['code'=>0, 'msg'=> '删除失败']);
        }
    }

    /**
     * (删除)动态下的回复
     * @throws
     */
    public function adoptReview(){
        $id = $this->request->param('id');
        $res = ForumReplyService::UpdateB(['id'=>$id],['status'=>0]);
        if($res){
            return json_encode(['code'=>200, 'msg'=> '删除成功']);
        }else{
            return json_encode(['code'=>0, 'msg'=> '删除失败']);
        }
    }

    /**
     * 涉黄等级修改
     * @return false|mixed|string
     * @throws
     */
    public function yellowEdit(){

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

            $res = AppCommunityService::UpdateB(['id'=>$params['id']],['y_level'=>$params['y_level']]);
            if($res){
                return json_encode(['msg'=>'修改成功','code'=>200]);
            }else{
                return json_encode(['code'=>0,'msg'=> '修改失败']);
            }

        }
        $id = $this->request->param('id');
        $info = AppCommunityService::ToInfo(['id'=>$id]);
        //var_dump($info);die;
        $y_level = [1,2,3,4,5,6,7,8,9,10];
        $this->assign('info',$info);
        $this->assign('y_level',$y_level);
        return $this->fetch();
    }


    /**
     * 用户详情
     * @throws
     */
    public function UserDetails(){
        $user_id = $this->request->param('user_id');
        //var_dump($user_id);die;
        $user_info = UserMemberService::ToInfo(['id'=>$user_id]);
        if(!empty($user_info)){
            $user_info['avatar'] = preg_match('/^http/', $user_info['avatar']) == 0 ? MaterialService::getFullUrl($user_info['avatar']) : $user_info['avatar'];
            //$user_info['img_list'] = empty($user_info['album']) ? '' : json_decode($user_info['album'],true);
            //$user_info['video_intro'] = empty($user_info['video']) ? '' : json_decode($user_info['video'],true);
            $user_info['cj_status'] = UserMemberService::statusList($user_info['daren_status']);
            $sex = ['未知','男','女'];
            $user_info['sex_type'] = isset($sex[$user_info['sex']]) ? $sex[$user_info['sex']] : '无';
            $user_info['personal_profile'] = isset($user_info['signature']) ? $user_info['signature'] : '无';
            $user_info['age'] = isset($user_info['age']) ? $user_info['age'] : '无';
            $user_info['city'] = isset($user_info['address']) ? $user_info['address'] : '无';
            $user_info['nickname'] = isset($user_info['user_nickname']) ? $user_info['user_nickname'] : '无';
            // array_push(json_decode($info['album'],true),$album);
            $user_info['img_list'] = [];
            $user_info['video_intro'] = [];
            if(!empty($user_info['album'])){
                $album = json_decode(html_entity_decode($user_info['album']));
                if(count($album) > 0){
                    foreach ($album as $key=>$item){
                        $user_info['img_list'][$key] = MaterialService::getFullUrl($item);
                    }
                }
            }
            if(!empty($user_info['video'])){
                $video = json_decode(html_entity_decode($user_info['video']));
                if(count($video) > 0){
                    foreach ($video as $k=>$value){
                        $user_info['video_intro'][$k] = MaterialService::getFullUrl($value);
                    }
                }
            }
            //var_dump($user_info);die;
            $this->assign('user_info',$user_info);
            return $this->fetch();
        }else{
            echo '没有改用户信息';
        }
    }
}
