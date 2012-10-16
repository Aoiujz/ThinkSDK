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
// | IndexAction.class.php 2012-4-23
// +----------------------------------------------------------------------

class OauthAction extends SnsSdkAction{	
	/* 
	 * 获取到正确的access_token后跳转到的页面，你可以在这里获取并记录用户信息 
	 * 到这里则标志着授权完全成功而且获取到了 access_token
	 * 获取到的access_token已经记录到了session中，通过ThinkPHP的session()函数获取
	 * 获取新浪的access_token: session('SINA_WEIBO_ACCESS')
	 * 获取腾讯的access_token: session('TENCENT_WEIBO_ACCESS')
	 * 获取网易的access_token: session('163_WEIBO_ACCESS')
	 */
	public function complete(){
		/* 登录成功，获取用户信息 */
		$userInfo = array();
		switch($this->_get('sns_type')){
			case 'tencent':
				$tencent = new TencentWeiBo();
				$data = $tencent->userInfo();
				if($data['errorcode'] == 0){
					$userInfo['sns_type'] = 'tencent';
					$userInfo['name']     = $data['data']['name'];
					$userInfo['nick']     = $data['data']['nick'];
					$userInfo['head']     = $data['data']['head'] . '/50';
				} else {
					$this->error($data['msg']);
				}
				break;
			case 'sina':
				$sina = new SinaWeiBo();
				$access = session('SINA_WEIBO_ACCESS');
				$data = $sina->usersShow("uid={$access['uid']}");
				if($data['error_code'] == 0){
					$userInfo['sns_type'] = 'sina';
					$userInfo['name']     = $data['name'];
					$userInfo['nick']     = $data['screen_name'];
					$userInfo['head']     = $data['profile_image_url'];
				} else {
					$this->error($data['error']);
				}
				break;
			 case '163':
				$t163 = new T163WeiBo();
				$access = session('163_WEIBO_ACCESS');
				$data = $t163->usersShow();
				if($data['error_code'] == 0){
					$userInfo['sns_type'] = '163';
					$userInfo['name']     = $data['name'];
					$userInfo['nick']     = $data['screen_name'];
					$userInfo['head']     = $data['profile_image_url'];
				} else {
					$this->error($data['error']);
				}
				break;
		}
		session('sns_user_info', $userInfo);
		$url = cookie('click_sns_login_url');
		cookie('click_sns_login_url', null);
		header('Location: ' . $url);
	}
}