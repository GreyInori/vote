<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/18
 * Time: 15:21
 */

namespace app\vote\validate;

use think\Validate;

/**
 * Class VoteValidate
 * @package app\vote\validate
 */
class VoteValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/',
    );

    protected $rule = array(
        'vote' => 'require',
        'isPublic' => 'require|max:1',
        'start' => 'require',
        'end' => 'require',
        'min' => 'require',
        'max' => 'require',
        'file' => 'require',
        'field' => 'require',
        'custom' => 'require',
        'title' => 'require'
    );

    protected $message = array(
        'vote.require' => '请传递投票id',
        'isPublic.require' => '请传递是否为保密',
        'start.require' => '请输入开始时间',
        'end.require' => '请输入结束时间',
        'min.require' => '请传递最小参与人数',
        'max.require' => '请输入最大参与人数',
        'file.require' => '请输入投票图片',
        'custom.require' => '请传递客户id'
    );

    protected $scene = array(
        'add' => ['start', 'end', 'max','title','field','custom'],
        'edit' => ['vote'],
        'del' => ['vote','custom'],
        'find' => ['vote']
    );

}