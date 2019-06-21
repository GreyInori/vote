<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/19
 * Time: 16:13
 */

namespace app\candidate\validate;

use think\Validate;

/**
 * Class CandidateValidate
 * @package app\candidate\validate
 */
class CandidateValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/',
    );

    protected $rule = array(
        'vote' => 'require|max:8',
        'candidate' => 'require',
        'photo' => 'require',
        'num' => 'require',
        'main' => 'require'
    );

    protected $message = array(
        'vote.require' => '请传递投票id',
        'vote.max:8' => '传递投票id不符合规范',
        'candidate' => '请传递候选人id',
        'photo.require' => '请传递投票候选人头像',
        'num.require' => '请传递候选人选票数量',
        'main.require' => '请传递候选人详细信息'
    );

    protected $scene = array(
        'add' => ['vote','main'],
        'edit' => ['custom'],
        'del' => ['custom'],
        'find' => ['mobile']
    );
}