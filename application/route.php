<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
//return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '[hello]'     => [
//        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//        ':name' => ['index/hello', ['method' => 'post']],
//    ],
//
//];
// 客户相关路由
Route::controller('/custom','Custom/Custom');
// 投票相关路由
Route::controller('/vote','Vote/Vote');
// 投票候选人相关路由
Route::controller('/candidate','Candidate/Candidate');
// 用户相关路由
Route::controller('/user','User/User');
// 所有路由匹配不到的情况下触发该路由
Route::miss('Api/Exception/miss');