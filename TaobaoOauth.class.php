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
// | TAOBAOOauth.class.php 2012-6-9
// +----------------------------------------------------------------------

require_once 'Oauth.class.php';

class TaobaoOauth extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $GetRequestCodeURL = 'https://oauth.taobao.com/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string 
	 */
	protected $GetAccessTokenURL = 'https://oauth.taobao.com/token';

	/**
	 * 微博类型（目前不可修改）
	 * @var string
	 */
	protected $Type = 'TAOBAO';

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
			 'taobao.user.get','taobao.user.seller.get'
		);

		/* 以GET方式提交的接口列表 */
		static $get  = array(
             'taobao.user.get','taobao.user.seller.get','taobao.user.buyer.get'
		);

		if(in_array($api, $post))
			return 'POST';
		elseif(in_array($api, $get))
			return 'GET';
		else
			throw_exception("淘宝网暂无接口：{$api}");
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
		$url = 'http://gw.api.taobao.com/router/rest';

		/* 淘宝调用公共参数
		 * 数组有一定顺序不要随意调换
		 *  */
		$params = array(
			'app_key' => $this->AppKey,
			'fields' => 'user_id,nick,sex,uid,nick,sex,buyer_credit,avatar,has_shop,vip_info',
			'format' => 'json',
			'method' => $api,
			// 'nick'         =>'乡村小男孩99',
			'sign_method' => 'md5',
			'timestamp' => date('Y-m-d H:i:s'),
			'v' => '2.0',
			'partner_id'=>'top-apitools',
			'session' => '610290041f3184bc5462aceb4275407daab3a67737c4071573855961',
		);

		return $this->http($url , $this->param($params, $param), $method);
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
			$param[] = "{$key}{$value}";
		}
		$sign = $this->AppSecret.implode('', $param).$this->AppSecret;
		$params['sign'] = strtoupper(md5($sign));
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
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token'] && $data['taobao_user_id']){
			$data['openid'] = $data['taobao_user_id'];
			unset($data['taobao_user_id']);
			session("SNS_ACCESS_TAOBAO", $data);
		} else{
			throw_exception("获取淘宝网ACCESS_TOKEN出错：{$data['error_description']}");
		}
	}
	
	/**
	 * 获取当前授权应用的openid
	 +------------------------------------------------------------------------------
	 * @return string
	 +------------------------------------------------------------------------------
	 */
	public function getOpenid($data = null){
		$data = $this->Token ? $this->Token : session('SNS_ACCESS_TAOBAO');
		
		if(!empty($data['openid']))
			return $data['openid'];
		else
			throw_exception('没有获取到淘宝网用户ID！');
	}
	
	/**
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function getUserInfo($id){
		$data = $this->taobao_user_buyer_get();

		if(!isset($data['error_response'])){
			$userInfo['type'] = 'TAOBAO';
			$userInfo['name'] = $data['user_buyer_get_response']['user']['nick'];
			$userInfo['nick'] = $data['user_buyer_get_response']['user']['nick'];
			$userInfo['head'] = $data['user_buyer_get_response']['user']['avatar'];
            $userInfo['sex']  = $data['user_buyer_get_response']['user']['sex']=='m' ? 1 : 0;
			//是否通过买家认证  $data['user_buyer_get_response']['user']['seller_credit']['level']<2
			if($data['user_buyer_get_response']['user']['buyer_credit']['level']>2){
				$userInfo['buy']['isBuyerAccess'] = 1;
			}
			return $userInfo;
		} else {
			throw_exception("获取淘宝网用户信息失败：{$data}");
		}
	}
}