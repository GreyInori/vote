<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/19
 * Time: 17:57
 */

namespace app\vote\controller\api;

use think\Controller;

/**
 * Class VoteCount
 * @package app\vote\controller\api
 */
class VoteCount extends Controller
{
    /**
     * 计算投票候选人选票所占百分比方法
     * @param array $voteMain
     * @return array
     */
    public function candidateCount($voteMain = array())
    {
        $result = $voteMain;

        foreach ($result['candidate'] as $k => $v) {

            if ($result['vote_num'] == 0) {
                $result['candidate'][$k]['percent'] = '0%';
            } else {

                $result['candidate'][$k]['percent'] = number_format(($v['vote_num'] / $result['vote_num']*100), 2) . '%';
            }

        }

        return $result;
    }
}