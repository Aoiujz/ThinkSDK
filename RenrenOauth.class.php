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
// | RenrenOauth.class.php 2012-6-9
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class RenrenOauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://graph.renren.com/oauth/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://graph.renren.com/oauth/token';

	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'RENREN';

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
			'users.getInfo','like.like','like.unlike','share.share','feed.publish','feed.publishFeed'
		);

		/* 以GET方式提交的接口列表 */
		static $get  = array(
			
		);

		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("人人网暂无接口：{$api}");
	}

	/**
	 * 组装接口调用参数 并调用接口
	 +------------------------------------------------------------------------------
	 * @param  string $api    微博API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @return json
	 +------------------------------------------------------------------------------
	 */
	protected function call($api, $param, $method = 'GET'){
		$url = 'http://api.renren.com/restserver.do';

		/* 新浪微博调用公共参数 */
		$params = array(
			'method'       => $api,
			'access_token' => $this->accessToken(), 
			'v'            => '1.0',
			'format'       => 'json',
		);
		return $this->http($url, $this->param($params, $param), $method);
	}
	
	/**
	 * 合并默认参数和额外参数
	 +----------------------------------------------------------
	 * @param array $params  默认参数
	 * @param array/string $param 额外参数
	 * @return array:
	 +----------------------------------------------------------
	 */
	protected function param($params, $param){
		$params = parent::param($params, $param);
		
		/* 签名 */
		ksort($params);
		$param = array();
		foreach ($params as $key => $value){
			$param[] = "{$key}={$value}";
		}
		$sign = implode('', $param).$this->AppSecret;
		$params['sig'] = md5($sign);

		return $params;
	}
	
	/**
	 * 方法名转换为API名称
	 +----------------------------------------------------------
	 * @param string $method 方法名
	 * @return string
	 +----------------------------------------------------------
	 */
	protected function getApi($method){
		return str_replace('_', '.', $method);
	}

	/**
	 * 解析access_token方法请求后的返回值
	 +------------------------------------------------------------------------------
	 * @param string $result 获取access_token的方法的返回值
	 +------------------------------------------------------------------------------
	 */
	protected function parseToken($result){
		$data = json_decode($result, true);
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token'] && $data['user']['id']){
			$data['openid'] = $data['user']['id'];
			unset($data['user']);
			session("SNS_ACCESS_RENREN", $data);
		}else{
			throw_exception("获取人人网ACCESS_TOKEN出错：{$data['error_description']}");
		}
	}
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		$data = $this->Token ? $this->Token : session('SNS_ACCESS_RENREN');
		
		if(!empty($data['openid']))
			return $data['openid'];
		else
			throw_exception('没有获取到人人网用户ID！');
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->users_getInfo();
		if(!isset($data['error_code'])){
			$userInfo['type'] = 'RENREN';
			$userInfo['name'] = $data[0]['name'];
			$userInfo['nick'] = $data[0]['name'];
			$userInfo['head'] = $data[0]['headurl'];
			$userInfo['sex'] = $data[0]['sex'];
			return $userInfo;
		} else {
			throw_exception("获取人人网用户信息失败：{$data['error_msg']}");
		}
	}
	//关注官方
	public function focus($accont) {
		//$data = $this->like_like("url={$accont}");
		if ($data['error_code']) {
			throw_exception($data['error_msg']);
		}
	}
}