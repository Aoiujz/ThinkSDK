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
// | Oauth.class.php 2012-4-23
// +----------------------------------------------------------------------

abstract class Oauth{
	/**
	 * oauth版本
	 * @var string
	 */
	protected $Version = '2.0';
	
	/**
	 * 申请应用时分配的app_key
	 * @var string
	 */
	protected $AppKey = '';
	
	/**
	 * 申请应用时分配的 app_secret
	 * @var string
	 */
	protected $AppSecret = '';
	
	/**
	 * 授权类型 response_type 目前只能为code
	 * @var string
	 */
	protected $ResponseType = 'code';
	
	/**
	 * grant_type 目前只能为 authorization_code
	 * @var string 
	 */
	protected $GrantType = 'authorization_code';
	
	/**
	 * 回调页面URL  可以通过配置文件配置
	 * @var string
	 */
	protected $Callback = '';
	
	/**
	 * 获取request_code的额外参数 URL查询字符串格式
	 * @var srting
	 */
	protected $Authorize = '';
	
	/**
	 * 调用接口类型
	 * @var string
	 */
	protected $Type = '';
	
	/**
	 * 获取request_code请求的URL
	 * @var string
	 */
	protected $GetRequestCodeURL = '';
	
	/**
	 * 获取access_token请求的URL
	 * @var string
	 */
	protected $GetAccessTokenURL = '';
	
	/**
	 * 授权后获取到的TOKEN信息
	 +----------------------------------------------------------
	 * @var array
	 +----------------------------------------------------------
	 */
	protected $Token = null;
	
	/**
	 * 构造方法，配置应用信息
	 +----------------------------------------------------------
	 * @param array $token 
	 +----------------------------------------------------------
	 */
	public function __construct($token = null){
		//获取应用配置 存放表格SettingSns
		$map['TYPE'] = $this->Type;
		$map['status']=1;
		$config = D('SettingSns')->where($map)->limit(1)->select();
		//$config = C("THINK_SDK.{$this->Type}");
		//$config['APP_KEY'] && $config['APP_SECRET']
		if($config){
			$this->AppKey    = $config[0]['APP_KEY'];
			$this->AppSecret = $config[0]['APP_SECRET'];
			$this->Token     = $token; //设置获取到的TOKEN
		} else {
			throw_exception('请配置您在微博申请的APP_KEY和APP_SECRET');
		}
	}
	
	/**
	 * 初始化配置
	 */
	private function config(){
		$this->Callback  = U(MODULE_NAME."/callback{$this->Type}",'',true,false,true);
		$config = C("THINK_SDK.{$this->Type}");
		if(isset($config['AUTHORIZE']) && !empty($config['AUTHORIZE']))
			$this->Authorize = $config['AUTHORIZE'];
	}
	
	/**
	 * 请求code 
	 */
	public function getRequestCodeURL($extend=null){
		$this->config();
		//Oauth 标准参数
		$params = array(
			'client_id'     => $this->AppKey,
			'redirect_uri'  => $this->Callback,
			'response_type' => $this->ResponseType,
		);
		if($extend){
			$params = array_merge($params,$extend);
		}
		//获取额外参数
		if($this->Authorize){
			parse_str($this->Authorize, $_param);
			if(is_array($_param)){
				$params = array_merge($params, $_param);
			} else {
				throw_exception('AUTHORIZE配置不正确！');
			}
		}
		return $this->GetRequestCodeURL . '?' . http_build_query($params);
	}
	
	/**
	 * 获取access_token
	 +------------------------------------------------------------------------------
	 * @param string $code 上一步请求到的code
	 * @param string $extend  扩展数据
	 +------------------------------------------------------------------------------
	 */
	public function getAccessToken($code, $extend = null){
		$this->config();
		$params = array(
				'client_id'     => $this->AppKey,
				'client_secret' => $this->AppSecret,
				'grant_type'    => $this->GrantType,
				'code'          => $code,
				'redirect_uri'  => $this->Callback,
		);
		$data = $this->http($this->GetAccessTokenURL, $params, 'POST');
		$this->parseToken($data);
	}

	/**
	 * 获取access_token
	 */
	protected function accessToken(){
		$data = $this->Token ? $this->Token : session("SNS_ACCESS_{$this->Type}");
		if($data['access_token'])
			return $data['access_token'];
		else
			throw_exception('您还没有授权！');
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
		if(is_string($param))
			parse_str($param, $param);
		return array_merge($params, $param);
	}
	
	/**
	 * 发送HTTP请求方法，目前只支持CURL发送请求
	 +------------------------------------------------------------------------------
	 * @param  string $url    请求URL
	 * @param  array  $params 请求参数
	 * @param  string $method 请求方法GET/POST
	 * @return array  $data   响应数据
	 +------------------------------------------------------------------------------
	 */
	protected function http($url, $params, $method = 'GET',$header=''){
		$vars = http_build_query($params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		if(is_array($header))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		switch(strtoupper($method)){
			case 'GET':
				curl_setopt($ch, CURLOPT_URL, $url . '?' . $vars);
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
				break;
			default:
				throw_exception('不支持的请求方式！');
		}
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error)
			throw_exception('请求发生错误：' . $error);
		return  $data;
	}
	
	/**
	 * 方法名转换为API名称
	 +----------------------------------------------------------
	 * @param string $method 方法名
	 * @return string 
	 +----------------------------------------------------------
	 */
	protected function getApi($method){
		return strtolower(preg_replace('/[A-Z]/', '/\0', $method));
	}
	
	/**
	 * 魔术方法，调用API接口
	 +------------------------------------------------------------------------------
	 * @param  string $method api接口
	 * @param  string $args 传递的参数
	 * @return array
	 +------------------------------------------------------------------------------
	 */
	public function __call($method, $args){
		/* 方法名转换为API名称 */
		$api = $this->getApi($method);
		$param = empty($args[0]) ? array() : $args[0];
		return json_decode($this->call($api, $param, $this->getMethod($api)), true);
	}
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 获取接口调用的http方法 并验证接口的正确性
	 +------------------------------------------------------------------------------
	 */
	abstract protected function getMethod($api);
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 组装接口调用参数 并调用接口
	 +------------------------------------------------------------------------------
	 */
	abstract protected function call($api, $param, $method = 'GET');
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 解析access_token方法请求后的返回值
	 +------------------------------------------------------------------------------
	 */
	abstract protected function parseToken($result);
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 获取当前授权用户的SNS标识
	 +------------------------------------------------------------------------------
	 */
	abstract public function getOpenid($data = null);
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 获取当前授权用户的详细信息
	 +------------------------------------------------------------------------------
	 */
	abstract public function getUserInfo($id);
	
}