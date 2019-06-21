<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/19
 * Time: 14:30
 */

namespace app\candidate\controller\api;

use think\Controller;
use app\api\controller\Send;
use app\api\controller\Picture;
use think\Request;

/**
 * 投票候选人相关方法
 * Class CandidateMain
 * @package app\candidate\controller\api
 */
class CandidateMain extends Controller
{
    use Picture;
    use Send;

    protected static $url;

    /**
     * CandidateMain constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        self::$url = request()->domain();
    }

    /**
     * 投票候选人添加方法
     * @return mixed|string
     */
    public function insertCandidate()
    {
        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('add');

        if (!is_array($data)) {

            return self::returnMsg(500, 'fail', $data);
        }
        /* 进行投票候选人头像上传，并创建插入数据数组 */
        $candidateAdd = array(
            'candidate_profile_photo' => self::toImgUp('candidate', 'photo'),
            'vote_id' => $data['vote_id'],
        );

        /* 进行投票候选人详细信息的数据插入数组创建 */
        foreach ($data['main'] as $k => $v) {

            $tag = $k + 1;
            $candidateAdd["candidate_t{$tag}"] = $v;
        }

        $request = new \app\candidate\model\CandidateModel();

        $save = $request->save($candidateAdd);

        if ($save === 1) {

            return $request->candidate_id;
        } else {

            return 'fail';
        }
    }

    /**
     * 根据投票id获取投票候选人
     * @param $vote
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fetchCandidate($vote, $field)
    {
        $vote = array(
            'vote_id' => $vote
        );

        $fieldArr = explode('|', $field);

        $whereField = array('candidate_id', 'candidate_profile_photo', 'vote_num', 'vote_update_time');
        /* 根据投票填写的字段名数据，来获取需要查询的字段 */
        foreach ($fieldArr as $k => $v) {

            $tag = $k + 1;
            array_push($whereField, "candidate_t{$tag} as {$v}");
        }

        $result = $this->findList($vote, $whereField);

        foreach ($result as $resultK => $resultV) {

                $result[$resultK]['candidate_profile_photo'] = self::$url.$resultV['candidate_profile_photo'];
        }
        return $result;
    }

    /**
     * 获取列表方法
     * @param $where
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findList($where, $field)
    {
        $model = new \app\candidate\model\CandidateModel();

        return $model->where($where)->field($field)->select();
    }

    /**
     * @param string $code
     * @return array|mixed|string|true|void
     */
    private function paramCheck($code = 'find')
    {
        /* 检测传递的参数是否符合规范 */
        $requestCheck = new \app\candidate\controller\CandidateLoad();

        $data = $requestCheck->checkData($code);

        if (!is_array($data)) {            // 如果不符合规范就返回错误信息

            return $data;
        }

        return $data;
    }
}