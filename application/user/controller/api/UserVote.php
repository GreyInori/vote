<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/20
 * Time: 14:17
 */

namespace app\user\controller\api;

use think\Controller;
use think\Db;
use app\api\controller\Send;

class UserVote extends Controller
{
    use Send;

    /**
     * 投票id
     * @var int
     */
    private $vote;

    /**
     * 进行投票操作
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function insertVote()
    {
        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('vote');

        if (!is_array($data)) {

            return self::returnMsg(500, 'fail', $data);
        }

        $check = self::voteFieldCheck($data);
        /* 进行投票检查 */
        if (!is_array($check)) {

            return self::returnMsg(500, 'fail', $check);
        }

        Db::startTrans();
        try{

             /* 执行投票插入操作 */
            $this->doVote($check);
            $this->voteChange($check['candidate_id']);
            Db::commit();
            return 'success';

        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return self::returnMsg(500,'fail','投票失败，请稍后重试');
        }
    }

    /**
     * 判断用户投票传递的参数是否正确
     * @param $data
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function voteFieldCheck($data)
    {
        /* 投票候选人是数组，拼接where IN查询条件 */
        $candidateStr = '';

        foreach ($data['candidate_id'] as  $v) {

            $candidateStr .= "{$v},";
        }
        $candidateStr = rtrim($candidateStr, ',');

        /* 拼接查询条件，检查是否在是否有指定投票活动在可投票的时间范围内 */
        $where = array(
            'vo.vote_id' => $data['vote_id'],
            'vo.vote_start' => ['<=', time()],
            'vo.vote_end' => ['>=', time()],
            'vc.candidate_id' => ['IN', $candidateStr]
        );

        $voteList = Db::table('vo_vote')
            ->alias('vo')
            ->join('vo_vote_candidate vc', "vc.vote_id = vo.vote_id")
            ->where($where)
            ->field(['vote_isPublic'])
            ->select();

        if (empty($voteList) || count($voteList) != count($data['candidate_id'])) {
            return '投票失败，可能未在指定时间内投票，或传递投票或候选人id有误';
        }
        /* 如果当前投票是保密投票，检查投票人是否符合条件 */
        if ($voteList[0]['vote_isPublic'] == 1) {

            $public = Db::table('vo_vote_private')->where(['vote_id' => $data['vote_id'], 'vote_mobile' => $data['vote_mobile']])->field(['vote_id'])->select();

            if (empty($public)) {

                return '投票失败，当前投票尚未把您添加进可投票人中，请检查';
            }
        }
        /* 检查当前用户是否已经投过票了 */
        $isVote = Db::table('vo_vote_list')->where(['vote_id' => $data['vote_id'], 'vote_mobile' => $data['vote_mobile']])->field(['vote_id'])->select();

        if (!empty($isVote)) {
            return '当前已经投过票了，不能多次投票';
        }

        $this->vote = $data['vote_id'];

        return $data;
    }

    /**
     * 执行投票数据插入操作
     * @param $data
     * @return string
     */
    private function doVote($data)
    {
        /* 拼接选票插入数组 */
        $voteArr = array();

        foreach ($data['candidate_id'] as $k => $v) {

            $voteArr[$k] = array(
                'candidate_id' => $v,
                'vote_id' => $data['vote_id'],
                'vote_mobile' => $data['vote_mobile'],
                'vote_time' => time()
            );
        }

        $insert = Db::table('vo_vote_list')->insertAll($voteArr);
        /*判断是否插入成功 */
        if ($insert == 0) {

            return '投票失败';
        }

        return $data['candidate_id'];
    }

    /**
     * 选票数据信息修改方法
     * @param $candidateArr
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private function voteChange($candidateArr)
    {

        /* 投票候选人是数组，拼接where IN查询条件 */
        $candidateStr = '';

        foreach ($candidateArr as $k => $v) {

            $candidateStr .= "{$v},";
        }

        $candidateStr = rtrim($candidateStr, ',');

        $candidateArr = Db::table('vo_vote_candidate')->where('candidate_id', 'IN', $candidateStr)->where('vote_id', $this->vote)->field(['candidate_id', 'vote_num'])->select();
        $voteNum = Db::table('vo_vote')->where('vote_id', $this->vote)->where('vote_id', $this->vote)->field(['vote_num'])->select();

        /* 总票数修改 */
        $voteNum = $voteNum[0]['vote_num'] + count($candidateArr);

        /* 执行数据修改操作 */
        Db::execute($this->getUpdateCode($candidateArr, $this->vote, 'plus'));
        Db::table('vo_vote_candidate')->where('candidate_id', 'IN', $candidateStr)->where('vote_id',$this->vote)->update(['vote_update_time'=>time()]);
        Db::table('vo_vote')->where('vote_id',$this->vote)->update(['vote_num'=>$voteNum]);
    }


    /**
     * 投票数量减少方法
     * @param $vote
     * @param $candidate
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function changeVote($vote, $candidate)
    {
        /* 把人员数据列表处理成whereIN数据 */

        $candidateStr = '';

        foreach($candidate as $v){

            $candidateStr .= "{$v['candidate_id']},";
        }

        $candidateStr = rtrim($candidateStr, ',');

        /* 获取需要修改的列表，用来进行删除操作 */
        $voteNum = Db::table('vo_vote')->where('vote_id', $vote)->field(['vote_num'])->select();
        $candidateNum = Db::table('vo_vote_candidate')->where('candidate_id', 'IN',$candidateStr)->where('vote_id', $vote)->field(['candidate_id', 'vote_num'])->select();

        $voteNum = $voteNum[0]['vote_num'] - count($candidateNum);

        /* 执行数据修改操作 */
        Db::table('vo_vote')->where('vote_id', $vote)->update(['vote_num' => $voteNum]);
        Db::execute($this->getUpdateCode($candidateNum, $vote, 'minus '));
    }

    /**
     * 投票候选人票数修改sql语句获取
     * @param $candidate
     * @param string $token
     * @return string
     */
    private function getUpdateCode($candidate, $vote, $token = 'plus')
    {

        $update = "UPDATE vo_vote_candidate
							SET vote_num = CASE candidate_id";
        $candidateStr = '';
        /* 循环候选人数组，拼接成详细修改sql语句 */
        foreach($candidate as $v){

            if($token !== 'plus'){                     // 判断是投票，还是删除操作

                $num = $v['vote_num'] - 1;
            }else{

                $num = $v['vote_num'] + 1;
            }

            $update .= " WHEN {$v['candidate_id']} THEN {$num} ";

            $candidateStr .= "{$v['candidate_id']},";
        }
        $candidateStr = rtrim($candidateStr, ',');

        $update .= "END WHERE candidate_id IN ({$candidateStr}) AND vote_id = {$vote}";

        return $update;
    }

    /**
     * @param string $code
     * @return array|mixed|string|true|void
     */
    private function paramCheck($code = 'find')
    {
        /* 检测传递的参数是否符合规范 */
        $requestCheck = new \app\user\controller\UserAutoLoad;

        $data = $requestCheck->checkData($code);

        if (!is_array($data)) {            // 如果不符合规范就返回错误信息

            return $data;
        }
        return $data;
    }
}