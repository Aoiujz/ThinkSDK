<?php
	return array(
		'URL_CASE_INSENSITIVE' => true, //URL不区分大小写
			
		/**
		 * 以下是当前支持的微博的配置文件，成功创建应用后即会获得
		 * 根据自己的需求选择相应的配置 不需要的配置可以删除
		 */
		
		/* 腾讯微博配置 */
		'SNS_SDK_TENCENT'=> array(
			'APP_KEY'    => '', //应用注册成功后分配的 app_key
			'APP_SECRET' => '', //应用注册成功后分配的app_secret
			'CALLBACK'   => 'http://servername/index.php/Oauth/callback/sns_type/tencent', //应用授权后的回调地址，必须和注册应用时填写一致
			'SUCCESS'    => 'complete', //成功获取到access_token后跳转到的方法（留空则跳转到点击授权的页面）
		),
		
		/* 新浪微博配置 */
		'SNS_SDK_SINA' => array(
			'APP_KEY'     => '', //应用注册成功后分配的 app_key
			'APP_SECRET'  => '', //应用注册成功后分配的app_secret
			'CALLBACK'    => 'http://servername/index.php/Oauth/callback/sns_type/sina', //应用授权后的回调地址，必须和注册应用时填写一致
			'SUCCESS'     => 'complete', //成功获取到access_token后跳转到的方法（留空则跳转到点击授权的页面）
		),
		
		/* 网易微博配置 */
		'SNS_SDK_163' => array(
			'APP_KEY'     => '', //应用注册成功后分配的 Consumer Key
			'APP_SECRET'  => '', //应用注册成功后分配的 Consumer Secret
			'CALLBACK'    => 'http://servername/index.php/Oauth/callback/sns_type/163', //应用授权后的回调地址，必须和注册应用时填写一致
			'SUCCESS'     => 'complete', //成功获取到access_token后跳转到的方法（留空则跳转到点击授权的页面）
		),
	);
?>