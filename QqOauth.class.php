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
// | QqOauth.class.php 2012-6-3
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class QqOauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://graph.qq.com/oauth2.0/authorize';
	
	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://graph.qq.com/oauth2.0/token';
	
	/**
	 * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
	 * @var srting
	 */
	protected $Authorize = 'scope=get_user_info,add_share,add_idol';
	
	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'QQ';
	
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
			'shuoshuo/add_topic', 'blog/add_one_blog', 'photo/add_album', 'photo/upload_pic', 'share/add_share',
			't/add_t', 't/add_pic_t', 't/del_t', 'relation/add_idol', 'relation/del_idol','cft_info/get_tenpay_addr',
		);
		
		/* 以GET方式提交的接口列表 */
		static $get  = array(
			'user/get_user_info', 'photo/list_album', 'user/check_page_fans','relation/get_app_friends',
			't/get_repost_list', 'user/get_info', 'user/get_other_info', 'relation/get_fanslist',
			'relation/get_idollist', 
		);
		
		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("腾讯微博暂无接口：{$api}");
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
		$url = 'https://graph.qq.com/';
		
		/* 腾讯微博调用公共参数 */
		$params = array(
			'oauth_consumer_key' => $this->AppKey,
			'access_token'       => $this->accessToken(),
			'openid'             => $this->getOpenid(),
			'format'             => 'json'
		);
		return $this->http($url . $api, $this->param($params, $param), $method);
	}
	
	/**
	 * 解析access_token方法请求后的返回值 
	 +------------------------------------------------------------------------------
	 * @param string $result 获取access_token的方法的返回值
	 +------------------------------------------------------------------------------
	 */
	protected function parseToken($result){
		parse_str($result, $data);
		if($data['access_token'] && $data['expires_in']){
			$data['openid'] = $this->getOpenid($data);
			session("SNS_ACCESS_QQ", $data);
		} else{
			throw_exception("获取腾讯QQ ACCESS_TOKEN出错：{$result}");
		}
	}
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		$data = is_null($data) ? ($this->Token ? $this->Token : session('SNS_ACCESS_QQ')) : $data;
		if(isset($data['openid']))
			return $data['openid'];
		elseif($data['access_token']){
			$data = $this->http('https://graph.qq.com/oauth2.0/me', array('access_token' => $data['access_token']));
			$data = json_decode(trim(substr($data, 9), " );\n"), true);
			if(isset($data['openid']))
				return $data['openid'];
			else
				throw_exception("获取用户openid出错：{$data['error_description']}");
		} else {
			throw_exception('没有获取到openid！');
		}
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->userGet_user_info();
		if($data['ret'] == 0){
			$userInfo['type'] = 'QQ';
			$userInfo['name'] = $data['nickname'];
			$userInfo['nick'] = $data['nickname'];
			$userInfo['head'] = $data['figureurl_2'];
			$userInfo['sex'] = $data['gender']=="男" ? 1 : 0;
			return $userInfo;
		} else {
			throw_exception($data['msg']);
		}
	}
}