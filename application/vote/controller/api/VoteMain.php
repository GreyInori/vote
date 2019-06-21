<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/18
 * Time: 17:56
 */

namespace app\vote\controller\api;

use think\Controller;
use think\Db;
use app\api\controller\Send;
use app\vote\controller\api\VoteField as Field;

class VoteMain extends Controller
{
    use Send;

    /**
     * 投票信息插入操作
     * @return mixed|string|void
     * @throws \Exception
     */
    public function createVote()
    {
        $fieldArr = array();
        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('add');

        if (!is_array($data)) {

            return self::returnMsg(500, 'fail', $data);
        }

        if (isset($data['vote_field'])) {
            $fieldArr = explode('|', $data['vote_field']);
        }

        /* 实例化模型类，进行投票添加操作 */
        $request = new \app\vote\model\VoteModel();

        $save = $request->save($data);

        if ($save !== 1) {

            return 'fail';

        }
        /* 如果传递了需要显示的字段信息，就进行数据字段插入操作 */
        if (isset($data['vote_field'])) {

            $field = new Field();

            $field->CreatField($fieldArr, $request->vote_id);
        }

        return $request->vote_id;
    }

    /**
     * 获取投票信息方法
     * @return \app\vote\model\VoteModel|void|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findVote()
    {
        /* 根据传递的参数获取查询条件 */
        $where = $this->paramCheck('find');

        if (!is_array($where)) {

            return self::returnMsg(500, 'fail', $where);
        }

        $request = new \app\vote\model\VoteModel();

        $result = $request::get($where['vote_id'])->toArray();

        if(empty($result)){

            return null;
        }

        $candidate = new \app\candidate\controller\api\CandidateMain();

        $result['candidate'] = $candidate->fetchCandidate($where['vote_id'], $result['vote_field']);

        /* 计算投票候选人所占的百分比 */
        $count = new \app\vote\controller\api\VoteCount();

        $result = $count->candidateCount($result);

        return $result;
    }

    /**
     * 投票修改方法
     * @return false|int|void
     */
    public function updateVote()
    {
        $update = $this->paramCheck('edit');

        $request = new \app\vote\model\VoteModel();

        $alreadyHas = $request::where('vote_id', $update['vote_id'])->column('vote_id');

        if (empty($alreadyHas)) {

            return self::returnMsg(500, 'fail', '不存在当前投票，请检查传递的id');
        }

        $where = array(
            'vote_id' => $update['vote_id']
        );

        unset($update['vote_id']);

        $save = $request->save($update, $where);

        return $save;
    }

    /**
     * 投票删除方法
     * @return string|void
     */
    public function deleteVote()
    {
        $del = $this->paramCheck('del');

        if (!is_array($del)) {

            return self::returnMsg(500, 'fail', $del);
        }

        Db::startTrans();

        try{

            /* 执行投票插入操作 */
            Db::table('vo_vote')->where('vote_id', $del['vote_id'])->where('custom_id', $del['custom_id'])->delete();

            $this->deleteVoteMain($del['vote_id']);

            Db::commit();
            return 'success';

        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return 'fail';
        }
    }

    /**
     * 投票相关项删除方法
     * @param $vote
     * @return bool
     */
    public function deleteVoteMain($vote)
    {
        Db::table('vo_vote_candidate')->where('vote_id', $vote)->delete();
        Db::table('vo_vote_list')->where('vote_id', $vote)->delete();
        Db::table('vo_vote_intro')->where('vote_id', $vote)->delete();
        Db::table('vo_vote_private')->where('vote_id', $vote)->delete();
    }

    /**
     * @param string $code
     * @return array|mixed|string|true|void
     */
    private function paramCheck($code = 'find')
    {
        /* 检测传递的参数是否符合规范 */
        $requestCheck = new \app\vote\controller\VoteAutoLoad;

        $data = $requestCheck->checkData($code);

        if (!is_array($data)) {            // 如果不符合规范就返回错误信息

            return $data;
        }
        return $data;
    }
}