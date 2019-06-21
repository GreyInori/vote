<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/20
 * Time: 9:55
 */

namespace app\user\controller\api;

use think\Controller;
use think\Db;


/**
 * Class UserMain
 * @package app\user\controller\api
 */
class UserMain extends Controller
{
    /**
     * 根据用户手机号获取投票列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fetchVote()
    {
        $voteList = array();

        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('find');

        if (!is_array($data)) {

            return self::returnMsg(500, 'fail', $data);
        }

        array_push($voteList, $this->classifyVote($this->fetchPublicVote($data['vote_mobile'])));
        array_push($voteList, $this->classifyVote($this->fetchPrivateVote($data['vote_mobile'])));

        return $voteList;
    }

    /**
     * 获取公开投票列表数据
     * @param $mobile
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function fetchPublicVote($mobile)
    {
        $voteList = Db::table('vo_vote')
            ->alias('vo')
            ->join('vo_vote_list vl', "vl.vote_id = vo.vote_id AND vl.vote_mobile = {$mobile}", 'left')
            ->where('vo.vote_isPublic', 0)
            ->field(['vo.vote_id', 'vote_start', 'vote_end', 'vote_max', 'vote_min', 'vote_title', 'vote_num', 'vl.vote_mobile'])
            ->select();

        return $voteList;
    }

    /**
     * 获取非公开投票列表数据
     * @param $mobile
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function fetchPrivateVote($mobile)
    {
        $voteList = Db::table('vo_vote')
            ->alias('vo')
            ->join('vo_vote_list vl', "vl.vote_id = vo.vote_id AND vl.vote_mobile = {$mobile}", 'left')
            ->join('vo_vote_private vp', "vp.vote_id = vo.vote_id AND vp.vote_mobile = {$mobile}")
            ->field(['vo.vote_id', 'vote_start', 'vote_end', 'vote_max', 'vote_min', 'vote_title', 'vote_num', 'vl.vote_mobile'])
            ->select();

        return $voteList;
    }

    /**
     * 把数据分类成已投和未投状态
     * @param $list
     * @return array
     */
    private function classifyVote($list)
    {
        $result = array(
            0 => array(),
            1 => array()
        );
        /* 循环投票数据，如果已投表内没有数据的话就为未投 */
        foreach ($list as $k => $v) {

            if ($v['vote_mobile'] == null) {

                array_push($result[0], $v);
            } else {

                array_push($result[1], $v);
            }
        }

        return $result;
    }

    /**
     * 执行保密手机号码添加方法
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function doInsertPrivate()
    {
        $insertList = array();

        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('add');

        if (!is_array($data)) {

            return $data;
        }
        /* 检测当前投票是否存在 */
        if ($this->filterVote($data['vote_id']) != 'success') {

            return '不存在当前保密投票，请检查传递的投票id';
        }

        if (!is_array($data['mobileList'])) {

            explode(',', $data['mobileList']);
        }
        /* 过滤掉已经存在的用户手机号码 */
        $data['mobileList'] = $this->filterMobile($data['mobileList'], $data['vote_id']);

        /* 循环隐藏用户手机号码数组，拼接插入数据 */
        foreach ($data['mobileList'] as $k => $v) {

            $insertList[$k] = array(
                'vote_mobile' => $v,
                'vote_id' => $data['vote_id']
            );
        }
        /* 执行插入操作，返回插入结果 */
        $num = Db::table('vo_vote_private')->insertAll($insertList);

        if ($num) {

            return 'success';
        } else {

            return '投票添加失败，请稍后再试';
        }
    }

    /**
     * 已经存在的用户过滤方法
     * @param array $mobileArr
     * @param int $vote
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function filterMobile($mobileArr = array(), $vote = 0)
    {
        $result = array();
        $mobileFilter = array();
        /* 查询表内已经存在的用户数据，添加到需要过滤的列表中 */
        $mobileList = Db::table('vo_vote_private')->where('vote_id', $vote)->field(['vote_mobile'])->select();

        foreach ($mobileList as $v) {

            array_push($mobileFilter, $v);
        }
        /* 进行过滤，返回过滤后的手机号数组 */
        foreach ($mobileArr as $v) {

            if (!in_array($v, $mobileFilter)) {

                array_push($result, $v);
            }
        }
        return $result;
    }

    /**
     * 判断当前考试是否存在以及是否保密方法
     * @param $vote
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function filterVote($vote)
    {

        $list = Db::table('vo_vote')->where('vote_id', $vote)->where('vote_isPublic', 1)->select();

        if (empty($list)) {

            return 'fail';
        } else {

            return 'success';
        }
    }

    /**
     * 删除投票内的一个电话号码
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delPrivateMobile()
    {
        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('add');

        if (!is_array($data)) {

            return $data;
        }

        Db::startTrans();
        try{

            /* 执行投票删除操作 */
            Db::table('vo_vote_private')->where('vote_id', $data['vote_id'])->where('vote_mobile', $data['vote_mobile'])->delete();
            $voteMain = Db::table('vo_vote_list')->where('vote_id', $data['vote_id'])->where('vote_mobile', $data['vote_mobile'])->field(['candidate_id', 'vote_id'])->select();
            Db::table('vo_vote_list')->where('vote_id', $data['vote_id'])->where('vote_mobile', $data['vote_mobile'])->field(['candidate_id', 'vote_id'])->delete();
            /* 如果该电话号码进行过投票，就把改投票的票数减少 */
            if (!empty($voteMain)) {

                $voteDel = new \app\user\controller\api\UserVote();

                $voteDel->changeVote($voteMain[0]['vote_id'], $voteMain);
            }
            Db::commit();
            return 'success';

        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return '用户删除失败，请稍后再试';
        }
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