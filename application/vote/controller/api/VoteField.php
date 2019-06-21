<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/19
 * Time: 9:54
 */

namespace app\vote\controller\api;

use think\Controller;
use think\Db;

/**
 * Class VoteField
 * @package app\vote\controller\api
 */
class VoteField extends Controller
{
    /**
     * 投票候选人字段插入方法
     * @param $fieldArr
     * @param $voteId
     * @return array|false
     * @throws \Exception
     */
    public function CreatField($fieldArr, $voteId)
    {

        if (empty($fieldArr)) {

            return false;
        }
        $insertField = array();
        /* 循环查询数组，进行插入数组添加操作 */
        foreach ($fieldArr as $key => $field) {
            array_push($insertField, ['vote_id' => $voteId, 'vote_field' => $field]);
        }

        $insertNum = Db::table('vo_vote_field')->insertAll($insertField, false);

        return $insertNum;
    }
}