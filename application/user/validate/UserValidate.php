<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/20
 * Time: 9:43
 */

namespace app\user\validate;

use think\Validate;

/**
 * Class UserValidate
 * @package app\user\validate
 */
class UserValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/',
    );

    protected $rule = array(
        'mobile' => 'require|mobile',
        'vote' => 'require',
        'candidate' => 'require',
        'mobileList' => 'require'
    );

    protected $message = array(
        'mobile.require' => '请传递用户手机号',
        'mobile.mobile' => '传递用户手机号不符合规范',
        'vote.require' => '传递投票号不能为空',
        'candidate.require' => '传递候选人数据不能为空',
        'mobileList.require' => '请传递需要添加的电话列表',
    );

    protected $scene = array(
        'add' => ['mobileList','vote'],
        'edit' => ['mobile'],
        'del' => ['mobile'],
        'find' => ['mobile'],
        'vote' => ['mobile','vote','candidate']
    );
}