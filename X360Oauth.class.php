<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Aoiujz <zuojiazi@vip.qq.com>
// +----------------------------------------------------------------------
// | X360Oauth.class.php 2012-6-10
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class X360Oauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://openapi.360.cn/oauth2/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://openapi.360.cn/oauth2/access_token';
	
	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'X360';
	
	/**
	 * 获取接口调用的http方法 并验证接口的正确性
	 +------------------------------------------------------------------------------
	 * @param  string $api
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	protected function getMethod($api){
		/* 以POST方式提交的接口列表 */
		static $post = array(
		);
		
		/* 以GET方式提交的接口列表 */
		static $get  = array(
			'user/me'
		);
		
		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("360开放平台暂无接口：{$api}");
	}
	
	/**
	 * 组装接口调用参数 并调用接口
	 +------------------------------------------------------------------------------
	 * @param  string $api    360开放平台API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @return json
	 +------------------------------------------------------------------------------
	 */
	protected function call($api, $param, $method = 'GET'){
		$url = 'https://openapi.360.cn/';
		
		/* 360开放平台调用公共参数 */
		$params = array(
			'access_token' => $this->accessToken(),
		);
		
		return $this->http($url . $api . '.json', $this->param($params, $param), $method);
	}
	
	/**
	 * 解析access_token方法请求后的返回值
	 +------------------------------------------------------------------------------
	 * @param string $result 获取access_token的方法的返回值
	 +------------------------------------------------------------------------------
	 */
	protected function parseToken($result){
		$data = json_decode($result, true);
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token']){
			session("SNS_ACCESS_X360", $data);
		}else{
			throw_exception("获取360开放平台ACCESS_TOKEN出错：{$data['error']}");
		}
	}
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		if(isset($this->Token['openid']))
			return $this->Token['openid'];
		
		$data = $this->userMe();

		if(!empty($data['id']))
			return $data['id'];
		else
			throw_exception('没有获取到360开放平台用户ID！');
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->userMe();
		if($data['error_code'] == 0){
			$userInfo['type'] = 'X360';
			$userInfo['name'] = $data['name'];
			$userInfo['nick'] = $data['name'];
			$userInfo['head'] = $data['avatar'];
			return $userInfo;
		} else {
			throw_exception($data['error']);
		}
	}
}