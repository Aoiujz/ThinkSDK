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
// | T163Oauth.class.php 2012-4-24
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class T163Oauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://api.t.163.com/oauth2/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://api.t.163.com/oauth2/access_token';

	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'T163';

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
			/* 微博写入接口 */
			'statuses/update', 'statuses/reply','statuses/retweet','statuses/destroy','statuses/upload',
			'users/modify_user_groups',
			'friendships/create','friendships/destroy',
			'direct messages/new','direct messages/destroy','direct messages/destroy',
			'audio_messages/upload','audio messages/new',
			'account/activate','account/update_profile','account/update_profile_image',
			'favorites/create','favorites/destroy',
			'blocks/create','blocks/destroy',
		);

		/* 以GET方式提交的接口列表 */
		static $get  = array(
			/* 微博读取接口 */
			'statuses/home_timeline', 'statuses/public_timeline','statuses/mentions','statuses/user_timeline',
			'statuses/retweets_of_me','statuses/comments_by_me','statuses/comments_to_me','statuses/group_timeline',
			'statuses/user_column_timeline','statuses/column_timeline','statuses/show','statuses/comments',
			'statuses/retweets','statuses/id/retweeted_by',
			'users/show','users/suggestions','users/suggestions_i_followers','users/groups','column/info',
			'friendships/show','statuses/friends','statuses/followers','friends/names','statuses/followers/names',
			'statuses/topRetweets','statuses/topFollowRetweets',
			'trends/recommended','trends/recommended',
			'direct_messages','direct_messages','direct_messages','direct_messages','audio_messages/download',
			'account/verify_credentials','reminds/message/latest','account/rate_limit_status',
			'favorites',
			'blocks/exists','blocks/blocking','blocks/blocking/ids',
			'location/venues','statuses/location_timeline','location/report','location/search_neighbors',
			'search','statuses search','users/search',
			'oauth/request_token','oauth/authenticate','oauth/authorize','oauth/access_token',
			'oauth2/authorize','oauth2/access_token',
		);

		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("网易微博暂无接口：{$api}");
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
		$url = 'https://api.t.163.com/';

		/* 新浪微博调用公共参数 */
		$params = array(
			'oauth_token' => $this->accessToken(),
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
			session("SNS_ACCESS_T163", $data);
		}else{
			throw_exception("获取网易微博ACCESS_TOKEN出错：{$data['error']}");
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
		
		$data = $this->usersShow();
		if(!empty($data['screen_name']))
			return $data['screen_name'];
		else
			throw_exception('没有获取到网易微博用户ID！');
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->usersShow();
		if($data['error_code'] == 0){
			$userInfo['sex'] = $data['gender']==1 ? 1 : 0 ;
			$userInfo['type'] = 'T163';
			$userInfo['name'] = $data['name'];
			$userInfo['nick'] = $data['screen_name'];
			$userInfo['head'] = str_replace('w=48&h=48', 'w=180&h=180', $data['profile_image_url']);
			return $userInfo;
		} else {
			throw_exception($data['error']);
		}
	}
	
	public function focus($accont) {
		$data = $this->friendshipsCreate("screen_name={$accont}");
		if ($data['errcode'] != 0) {
			throw_exception($data['error']);
		}
	}
}