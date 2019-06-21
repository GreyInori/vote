<?php
	//设置返回头方法
	namespace app\api\controller;

	use think\Controller;
	use think\Request;
	/**
	 * 返回数据方法
	 */
	trait Send
	{
		/**
		 * 返回成功
		 *
		 * @param $code,$message,$data
		 * @return $data
		 */
		public static function returnMsg($code = 200,$message = '',$data = [])
		{
			//设置返回头
			http_response_code($code);
			
			$return['code'] = $code;
			$return['message'] = $message;
			$return['data'] = $data;
			//发送返回头
			$access = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:'*';
			header("Access-Control-Allow-Origin: {$access}");
			// header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Credentials: true");
			header('Access-Control-Allow-Method:POST,GET');

			// $return['data'] = request();

			exit(json_encode($return));
		}

	}