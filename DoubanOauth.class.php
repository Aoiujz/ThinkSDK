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

class DoubanOauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://www.douban.com/service/auth2/auth';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://www.douban.com/service/auth2/token';

	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'DOUBAN';

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
			'shuo/v2/statuses/','v2/user/:name'
		);

		/* 以GET方式提交的接口列表 */
		static $get  = array(
			'v2/user','v2/user/~me','shuo/v2/statuses/home_timeline'
		);

		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("豆瓣网暂无接口：{$api}");
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
		$url = 'https://api.douban.com/';

		/* 豆瓣调用公共参数 */
		$params = array(
			'source'=>$this->AppKey
		);
		$header = array('Authorization: Bearer '.$this->accessToken());
		return $this->http($url . $api, $this->param($params, $param), $method,$header);
	}
	

	/**
	 * 方法名转换为API名称
	 +----------------------------------------------------------
	 * @param string $method 方法名
	 * @return string
	 +----------------------------------------------------------
	 */
	protected function getApi($method){
		$method = (substr($method,-1) == '_') ? substr_replace($method, '/', strlen($method)-1, 1): $method;
		$method = $method=="v2User_me" ? str_replace("_", '/~',$method) : $method;
		return parent::getApi($method);
	}

	/**
	 * 解析access_token方法请求后的返回值
	 +------------------------------------------------------------------------------
	 * @param string $result 获取access_token的方法的返回值
	 +------------------------------------------------------------------------------
	 */
	protected function parseToken($result){
		$data = json_decode($result, true);
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token'] && $data['douban_user_id']){
			$data['openid'] = $data['douban_user_id'];
			session("SNS_ACCESS_DOUBAN", $data);
		} else
			throw_exception("获取豆瓣网ACCESS_TOKEN出错：{$data['error']}");
	}
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		$data = $this->Token ? $this->Token : session('SNS_ACCESS_DOUBAN');
		
		if(!empty($data['openid']))
			return $data['openid'];
		else
			throw_exception('没有获取到豆瓣网用户ID！');
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->v2User_me();
		if(!isset($data['error_code'])){
			$userInfo['type'] = 'DOUBAN';
			$userInfo['name'] = $data['name'];
			$userInfo['nick'] = $data['name'];
			$userInfo['head'] = $data['avatar'];
            $userInfo['sex']  = 0; //不能返回性别
			return $userInfo;
		} else {
			throw_exception("获取豆瓣网用户信息失败：{$data['error_msg']}");
		}
	}
}