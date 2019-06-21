<?php
/**
 * Created by PhpStorm.
 * User: Grey
 * Date: 2019/6/18
 * Time: 9:25
 */

namespace app\custom\controller\api;

use think\Controller;
use app\api\controller\Send;

/**
 * Class Main
 * @package app\custom\controller\api
 */
class CustomMain extends Controller
{
    use Send;

    /**
     * 客户新增方法
     * @return string|void
     */
    public function createCustom()
    {
        /* 检测传参返回错误信息 */
        $data = $this->paramCheck('add');

        if (!is_array($data)) {

            return self::returnMsg(500, 'fail', $data);
        }

        /* 创建用户id，并执行客户数据创建操作 */
        $data['custom_id'] = self::CreateId();

        $request = new \app\custom\model\CustomModel();

        $alreadyHas = $request::where('custom_mobile', $data['custom_mobile'])->column('custom_id');

        if (!empty($alreadyHas)) {

            return self::returnMsg(500, 'fail', '当前手机号已经存在');
        }

        $save = $request->save($data);

        if ($save === 1) {

            return $data['custom_id'];
        } else {

            return 'fail';
        }
    }

    /**
     * 查找获取用户信息方法
     * @return \app\custom\model\CustomModel|void|null
     * @throws \think\exception\DbException
     */
    public function findCustom()
    {
        /* 根据传递的参数获取查询条件 */
        $where = $this->paramCheck('find');

        if (!is_array($where)) {

            $where = $this->paramCheck('del');

            if (!is_array($where)) {

                return self::returnMsg(500, 'fail', '请传递需要查询的电话号码或者id');
            }
        }
        $request = new \app\custom\model\CustomModel();

        $result = $request::get($where);

        return $result;
    }

    /**
     * 客户修改方法
     * @return false|int|void
     */
    public function updateCustom()
    {
        $update = $this->paramCheck('edit');

        $request = new \app\custom\model\CustomModel();

        $alreadyHas = $request::where('custom_id', $update['custom_id'])->column('custom_id');

        if (empty($alreadyHas)) {

            return self::returnMsg(500, 'fail', '不存在当前用户，请检查传递的id');
        }

        $where = array(
            'custom_id' => $update['custom_id']
        );

        unset($update['custom_id']);

        $save = $request->save($update, $where);

        return $save;
    }

    /**
     * 客户删除方法
     * @return array|int|mixed|string|true|void
     */
    public function deleteCustom()
    {
        $del = $this->paramCheck('del');

        if (!is_array($del)) {

            return self::returnMsg(500, 'fail', $del);
        }

        $request = new \app\custom\model\CustomModel();

        $del = $request::destroy($del['custom_id']);

        return $del;
    }

    /**
     * 创建客户id方法
     * @return string
     */
    public function CreateId()
    {
        $time = substr(time(), 0, 5);
        $rand = mt_rand(100, 999);

        return $time . $rand;
    }

    /**
     * @param string $code
     * @return array|mixed|string|true|void
     */
    private function paramCheck($code = 'find')
    {
        /* 检测传递的参数是否符合规范 */
        $requestCheck = new \app\custom\controller\CustomAutoLoad;

        $data = $requestCheck->checkData($code);

        if (!is_array($data)) {            // 如果不符合规范就返回错误信息

            return $data;
        }
        return $data;
    }
}