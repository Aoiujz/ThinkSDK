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
// | SinaOauth.class.php 2012-4-23
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class SinaOauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://api.weibo.com/oauth2/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $GetAccessTokenURL = 'https://api.weibo.com/oauth2/access_token';
	
	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'SINA';
	
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
			'statuses/repost', 'statuses/destroy', 'statuses/update', 'statuses/upload', 'statuses/upload_url_text',
			'comments/create', 'comments/destroy', 'comments/destroy_batch', 'comments/reply',
			'friendships/create', 'friendships/destroy', 'friendships/remark/update',
			'favorites/create', 'favorites/destroy', 'favorites/destroy_batch', 'favorites/tags/update',
			'favorites/tags/update_batch', 'favorites/tags/destroy_batch',
			'trends/follow', 'trends/destroy',
			'tags/create', 'tags/destroy', 'tags/destroy_batch',
			'suggestions/users/not_interested',
			'remind/set_count',
			'location/pois/add', 'location/mobile/get_location'
		);
		
		/* 以GET方式提交的接口列表 */
		static $get  = array(
			'statuses/public_timeline', 'statuses/friends_timeline', 'statuses/home_timeline',
			'statuses/friends_timeline/ids', 'statuses/user_timeline', 'statuses/user_timeline/ids',
			'statuses/repost_timeline', 'statuses/repost_timeline/ids', 'statuses/repost_by_me',
			'statuses/mentions', 'statuses/mentions/ids', 'statuses/bilateral_timeline', 'statuses/show', 'statuses/querymid',
			'statuses/queryid', 'statuses/hot/repost_daily', 'statuses/hot/repost_weekly', 'statuses/hot/comments_daily',
			'statuses/hot/comments_weekly',
			'statuses/count', 'emotions',
			'comments/show', 'comments/by_me', 'comments/to_me', 'comments/timeline', 'comments/mentions', 'comments/show_batch',
			'users/show', 'users/domain_show', 'users/counts',
			'friendships/friends', 'friendships/friends/in_common', 'friendships/friends/bilateral',
			'friendships/friends/bilateral/ids', 'friendships/friends/ids', 'friendships/followers', 'friendships/followers/ids',
			'friendships/followers/active', 'friendships/friends_chain/followers',
			'friendships/show',
			'account/get_privacy', 'account/profile/school_list', 'account/rate_limit_status',
			'account/get_uid', 'account/end_session',
			'favorites', 'favorites/ids', 'favorites/show', 'favorites/by_tags', 'favorites/tags', 'favorites/by_tags/ids',
			'trends', 'trends/is_follow', 'trends/hourly', 'trends/daily', 'trends/weekly',
			'tags', 'tags/tags_batch', 'tags/suggestions',
			'register/verify_nickname',
			'search/suggestions/users', 'search/suggestions/statuses', 'search/suggestions/schools',
			'search/suggestions/companies', 'search/suggestions/apps', 'search/suggestions/at_users', 'search/topics',
			'suggestions/users/hot', 'suggestions/users/may_interested', 'suggestions/users/by_status',
			'suggestions/statuses/hot', 'suggestions/statuses/reorder', 'suggestions/statuses/reorder/ids',
			'suggestions/favorites/hot',
			'remind/unread_count',
			'short_url/shorten', 'short_url/expand', 'short_url/clicks', 'short_url/referers',
			'short_url/locations', 'short_url/share/counts', 'short_url/share/statuses',
			'short_url/comment/counts', 'short_url/comment/comments', 'short_url/info',
			'common/code_to_location', 'common/get_city', 'common/get_province', 'common/get_country', 'common/get_timezone',
			'location/base/get_map_image', 'location/geo/ip_to_geo', 'location/geo/address_to_geo',
			'location/geo/geo_to_address', 'location/geo/gps_to_offset', 'location/geo/is_domestic',
			'location/pois/show_batch', 'location/pois/search/by_location',
			'location/pois/search/by_geo', 'location/pois/search/by_area', 'location/line/drive_route',
			'location/line/bus_route', 'location/line/bus_line', 'location/line/bus_station'
		);
		
		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("新浪微博暂无接口：{$api}");
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
		$url = 'https://api.weibo.com/2/';
		
		/* 新浪微博调用公共参数 */
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
		if($data['access_token'] && $data['expires_in'] && $data['remind_in'] && $data['uid']){
			session("SNS_ACCESS_SINA", $data);
		}else{
			throw_exception("获取新浪微博ACCESS_TOKEN出错：{$data['error']}");
		}
	}
	/**
	 * 通过微博url获取 微博id
	 +------------------------------------------------------------------------------
	 */
	public function getText($url){
		$arr = explode('/', $url);
		$urlId = $arr[(count($arr)-1)];
		$id = $this->sinaWburl2ID($urlId);
		$data = $this->statusesShow("id={$id}");
		if($data){
			$text = $data['text']."<br />";
			if($data['original_pic']){
			$text .= '<img width="300" src="'.$data['original_pic'].'" />';
			}
		}
		return $text;
	}
        public function getUrlId($url){
            	$arr = explode('/', $url);
		$urlId = $arr[(count($arr)-1)];
		$id = $this->sinaWburl2ID($urlId);
                return $id;
        }

        /**
	 * 通过微博url id 获取 微博id（int64）
	 +------------------------------------------------------------------------------
	 */
	public function sinaWburl2ID($url) {  
		$surl[2] = $this->str62to10(substr($url, strlen($url) - 4, 4));  
		$surl[1] = $this->str62to10(substr($url, strlen($url) - 8, 4));  
		$surl[0] = $this->str62to10(substr($url, 0, strlen($url) - 8));  
		$int10 = $surl[0] . $surl[1] . $surl[2];  
		return ltrim($int10, '0');  
	}  
	private function str62to10($str62) { //62进制到10进制  
		$strarry = str_split($str62);  
		$str = 0;  
		for ($i = 0; $i < strlen($str62); $i++) {  
			$vi = Pow(62, (strlen($str62) - $i -1));  
			$str += $vi * ($this->str62keys($strarry[$i]));  
		}  
		$str = str_pad($str, 7, "0", STR_PAD_LEFT);  
		return $str;  
	}  

	private function str62keys($ks) //62进制字典  
	{  
		$str62keys = array (  
			"0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q",  
			"r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q",  
			"R","S","T","U","V","W","X","Y","Z"  
		);  
		return array_search($ks, $str62keys);  
	}  
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		$data = $this->Token ? $this->Token : session('SNS_ACCESS_SINA');
		if(isset($data['uid'])){
                        $_SESSION["SNS_ACCESS_SINA"]['openid'] = $data['uid'];
			return $data['uid'];
                }else{
			throw_exception('没有获取到新浪微博用户ID！');
	}       }
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->usersShow("uid={$id}");
		$data2 = $this->friendshipsFollowersActive("uid={$id}");
		$data3 = $this->usersCounts("uid={$id}");
		if($data['error_code'] == 0 && $data2['error_code']==0){
			$userInfo['type'] = 'SINA';
			$userInfo['name'] = $data['name'];
			$userInfo['nick'] = $data['screen_name'];
			$userInfo['head'] = $data['avatar_large'];
			$userInfo['fans'] = $data['followers_count'];
			$userInfo['active_fans'] = count($data2['users']);
			$userInfo['weibo_num'] = $data['statuses_count'];//微博总数
			$userInfo['sex'] = $data['gender']=="m" ? 1 : 0;
			return $userInfo;
		} else {
			throw_exception($data['error']);
		}
	}
	public function focus($accont){
		$data = $this->friendshipsCreate("screen_name={$accont}");
		if($data['error'] != 0){
			throw_exception($data['error']);
		}
	}
}