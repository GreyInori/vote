<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/18
 * Time: 15:28
 */

namespace app\vote\controller;

use think\Controller;


/**
 * Class VoteAutoLoad
 * @package app\custom\controller
 */
class VoteAutoLoad extends Controller
{
    protected static $fieldArr = array(
        'vote' => 'vote_id',
        'isPublic' => 'vote_isPublic',
        'start' => 'vote_start',
        'end' => 'vote_end',
        'max' => 'vote_max',
        'min' => 'vote_min',
        'custom' => 'custom_id',
        'title' => 'vote_title',
        'field' => 'vote_field',
    );

    /**
     * requestCheck
     * @param string $control
     * @return array|mixed|string|true
     */
    public function checkData($control = '')
    {
        $CustomValidate = new \app\vote\validate\VoteValidate();

        $request = request()->param();
        $check = true;
        /* 根据传递的请求判断传递的参数信息 */
        if ($control == 'edit') {

            $check = $CustomValidate->scene('edit')->check($request);

        }

        if ($control == 'add') {

            $check = $CustomValidate->scene('add')->check($request);
        }

        if ($control == 'del') {

            $check = $CustomValidate->scene('del')->check($request);
        }

        if ($control == 'find') {

            $check = $CustomValidate->scene('find')->check($request);
        }
        /* 如果验证不通过，就烦会错误信息，否则处理传递数据信息 */
        if ($check === false) {
            return $CustomValidate->getError();
        }

        $request = self::buildRequestField($request);

        return $request;
    }

    /**
     * 把传递的参数拼接成和数据库数组对应的参数
     * @param $data
     * @return array 对应数据库字段格式的数组
     */
    private static function buildRequestField($data)
    {
        $result = array();

        foreach ($data as $k => $v) {

            if ($k === 'field') {                // 如果传递的是字段数组的话，用 | 拼接成字符串

                $v = implode('|', $v);
            }

            if ($k === 'start' || $k == 'end') {                // 如果传递的是字段数组的话，用 | 拼接成字符串

                $v = strtotime($v);
            }

            isset(self::$fieldArr[$k]) ? $result[self::$fieldArr[$k]] = $v : false;
        }

        return $result;
    }
}