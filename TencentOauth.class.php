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
// | TencentOauth.class.php 2012-4-23
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class TencentOauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';
	
	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';
	
	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'TENCENT';
	
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
			't/add', 't/del', 't/re_add', 't/reply', 't/add_pic', 't/comment', 't/add_music', 't/add_video',
			't/getvideoinfo', 't/add_emotion', 't/add_pic_url', 't/add_multi',
			'user/update', 'user/update_head', 'user/update_edu', 'user/verify', 'user/emotion',
			'friends/add', 'friends/del', 'friends/addspecial', 'friends/delspecial', 'friends/addblacklist', 'friends/delblacklist',
			'private/add', 'private/del', 
			'fav/addt', 'fav/delt', 'fav/addht', 'fav/delht', 
			'tag/add', 'tag/del', 
			'list/list_followers', 'list/myfollowlist', 'list/create', 'list/delete', 'list/edit', 'list/get_list', 
			'list/check_user_in_list', 'list/list_attr', 'list/follow', 'list/undo_follow', 'list/add_to_list', 
			'list/del_from_list', 'list/get_other_in_list', 'list/listusers', 'list/list_info',
			'weiqun/add', 
			'lbs/update_pos', 'lbs/del_pos', 'lbs/get_poi', 'lbs/get_around_new', 'lbs/get_around_people',
		);
		
		/* 以GET方式提交的接口列表 */
		static $get  = array(
			't/show', 't/re_count', 't/re_list', 't/list', 't/sub_re_count',
			'user/info', 'user/other_info', 'user/infos',
			'statuses/home_timeline', 'statuses/public_timeline', 'statuses/user_timeline', 'statuses/mentions_timeline',
			'statuses/broadcast_timeline', 'statuses/special_timeline', 'statuses/area_timeline', 'statuses/home_timeline_ids',
			'statuses/user_timeline_ids', 'statuses/broadcast_timeline_ids', 'statuses/mentions_timeline_ids', 'statuses/users_timeline',
			'statuses/users_timeline_ids', 'statuses/ht_timeline_ext', 'statuses/home_timeline_vip', 'statuses/sub_re_list',
			'friends/fanslist', 'friends/fanslist_name', 'friends/idollist_name', 'friends/idollist', 'friends/blacklist',
			'friends/speciallist', 'friends/check', 'friends/user_fanslist', 'friends/user_idollist', 'friends/user_speciallist',
			'friends/fanslist_s', 'friends/idollist_s', 'friends/mutual_list', 'friends/match_nick_tips',
			'private/recv', 'private/send',
			'search/user', 'search/t', 'search/userbytag', 'search/ht',
			'trends/ht', 'trends/t', 'trends/famouslist',
			'info/update',
			'fav/delt',	'fav/list_t', 'fav/list_ht',
			'ht/ids', 'ht/info',
			'weiqun/apply4group', 'weiqun/bindqqgroup', 'weiqun/del', 'weiqun/home_timeline', 'weiqun/multihome_timeline',
			'weiqun/quitgroup', 'weiqun/setbulletin', 'weiqun/setmemattr',
			'lbs/rgeoc', 'lbs/geoc', 'other/kownperson',
			'other/shorturl', 'other/videokey', 'other/get_emotions', 'other/gettopreadd', 'other/follower_trans_conv',
			'other/quality_trans_conv',	'other/vip_trans_conv', 'other/url_converge', 'other/gettopiczbrank'
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
		$url = 'https://open.t.qq.com/api/';
		
		/* 腾讯微博调用公共参数 */
		$params = array(
			'oauth_consumer_key' => $this->AppKey,
			'access_token'       => $this->accessToken(),
			'openid'             => $this->getOpenid(),
			'clientip'           => get_client_ip(),
			'oauth_version'      => '2.a',
			'scope'              => 'all',
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
		$data = array_merge($data, session('SNS_ACCESS_TOKEN_EXTEND'));
		session('SNS_ACCESS_TOKEN_EXTEND', null); //清空零时数据
		if($data['access_token'] && $data['expires_in'] && $data['name'] && $data['openid'] && $data['openkey']){
			session("SNS_ACCESS_TENCENT", $data);
		}else{
			throw_exception("获取腾讯微博ACCESS_TOKEN出错：{$result}");
		}
	}
	/**
	 * 通过微博url获取 微博id
	 +------------------------------------------------------------------------------
	 */
	public function getText($url){
		$arr = explode('/', $url);
		$id = $arr[(count($arr)-1)];
		$data = $this->tShow("id={$id}");
		if ($data) {
			$text = $data['data']['text'] . "<br/>";
			if (file_exists($data['data']['image'])) {
				$text .= '<img width="100" src="' . $data['data']['image'] . '" />';
			}
		}
		return $text;
	}
	public function getUrlId($url){
            	$arr = explode('/', $url);
		$id = $arr[(count($arr)-1)];
                return $id;
        }
	/**
	 * 获取access_token
	 +------------------------------------------------------------------------------
	 * @param string $code 上一步请求到的code
	 * @param string $extend  扩展数据
	 +------------------------------------------------------------------------------
	 */
	public function getAccessToken($code, $extend){
		session('SNS_ACCESS_TOKEN_EXTEND', $extend);
		parent::getAccessToken($code);
	}
	
	/**
	 * 获取当前授权用户的用户名
	 +------------------------------------------------------------------------------
	 */
	public function getUserKey(){
		$data = session("SNS_ACCESS_TENCENT");
		if($data['name'])
			return $data['name'];
		else
			throw_exception('没有获取到腾讯微博用户名！');
	}
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		$data = $this->Token ? $this->Token : session('SNS_ACCESS_TENCENT');;
		if(isset($data['openid']))
			return $data['openid'];
		else
			throw_exception('没有获取到openid！');
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->userInfo();
		if($data['errcode'] == 0){
			$userInfo['type'] = 'TENCENT';
			$userInfo['name'] = $data['data']['name'];
			$userInfo['nick'] = $data['data']['nick'];
			$userInfo['email'] = $data['data']['email'];
			$userInfo['weibo_num'] = $data['data']['tweetnum']; //微博总数
			$userInfo['sex'] = $data['data']['sex'];
			$userInfo['head'] = $data['data']['head'] . '/180';
			$userInfo['fans']= $data['data']['fansnum'];
			$userInfo['active_fans']= $data['data']['fansnum'];
			return $userInfo;
		} else {
			throw_exception($data['msg']);
		}
	}

	public function focus($accont) {
		$data = $this-> friendsAdd("name={$accont}");
		if ($data['errcode'] != 0) {
			throw_exception($data['msg']);
		}
	}

}