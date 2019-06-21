<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/20
 * Time: 9:51
 */

namespace app\user\controller;

use think\Controller;

/**
 * Class UserAutoLoad
 * @package app\user\controller
 */
class UserAutoLoad extends Controller
{
    protected static $fieldArr = array(
        'mobile' => 'vote_mobile',
        'candidate' => 'candidate_id',
        'vote' => 'vote_id',
        'mobileList' => 'mobileList'
    );

    /**
     * requestCheck
     * @param string $control
     * @return array|mixed|string|true
     */
    public function checkData($control = '')
    {
        $CustomValidate = new \app\user\validate\UserValidate();

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

        if ($control == 'vote') {

            $check = $CustomValidate->scene('vote')->check($request);
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

            if ($k === 'main' && !is_array($v)) {
                $v = explode(',', $v);
            }

            if ($k === 'candidate' && !is_array($v)) {
                $v = explode(',', $v);
            }

            isset(self::$fieldArr[$k]) ? $result[self::$fieldArr[$k]] = $v : false;
        }

        return $result;
    }
}