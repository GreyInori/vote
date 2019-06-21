<?php

namespace app\custom\validate;

use think\Validate;

/**
 * Class CustomValidate
 * @package app\custom\validate
 */
class CustomValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/',
    );

    protected $rule = array(
        'custom' => 'require|max:8',
        'mobile' => 'require|mobile',
        'address' => 'require',
        'linkman' => 'require'
    );

    protected $message = array(
        'custom.require' => '请传递客户id',
        'mobile.require' => '请传递客户手机号',
        'mobile.mobile' => '输入手机号不符合规范',
        'address.require' => '请传递客户地址',
        'linkman.require' => '请传递客户联系人'
    );

    protected $scene = array(
        'add' => ['mobile', 'address', 'linkman'],
        'edit' => ['custom'],
        'del' => ['custom'],
        'find' => ['mobile']
    );
}