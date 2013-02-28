## 1，SDK简介

本SDK是基于ThinkPHP开发类库扩展，因此只能在ThinkPHP平台下使用（ThinkPHP版本要求2.0以上）。DEMO中用到了控制器分层，因此运行DEMO需使用ThinkPHP3.1.2版本。

## 2，目前支持的平台
目前可用登录平台为：腾讯QQ，腾讯微博，新浪微博，网易微博，人人网，360，豆瓣，Github，Google，MSN。

## 3，包含的文件

ThinkSDK/ThinkOauth.class.php    SDK基类，主要用于Oauth的认证，所有平台的SDK均需要继承此类    
ThinkSDK/sdk/DoubanSDK.class.php （豆瓣SDK）    
ThinkSDK/sdk/GithubSDK.class.php （Github SDK）    
ThinkSDK/sdk/GoogleSDK.class.php （Google SDK）    
ThinkSDK/sdk/MsnSDK.class.php （MSN SDK）    
ThinkSDK/sdk/QqSDK.class.php （腾讯QQ SDK）    
ThinkSDK/sdk/RenrenSDK.class.php （人人网SDK）    
ThinkSDK/sdk/SinaSDK.class.php （新浪微博SDK）    
ThinkSDK/sdk/T163SDK.class.php （网易微博SDK）    
ThinkSDK/sdk/TencentSDK.class.php （腾讯微博SDK）    
ThinkSDK/sdk/X360SDK.class.php （360 SDK）

## 4，配置格式

SDK的配置格式如下（可参考DEMO中的配置）

	//将一下(TYPE)换成你对应的SDK类型
	'THINK_SDK_(TYPE)' => array(
		'APP_KEY'    => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK'   => '', //注册应用填写的callback
	)

## 5，使用方法

a，添加ThinkPHP扩展，将整个ThinkSDK目录放入到ThinkPHP的扩展目录下~Extend/Library/ORG/~。    
b，添加SDK配置，按以上配置格式在项目配置中添加对应的SDK配置。（可参考DEMO中的配置文件）    
c，跳转到授权页面，导入SDK基类~import("ORG.ThinkSDK.ThinkOauth");~，获取SDK实例~$sns=ThinkOauth::getInstance($type)~，跳转到授权页面~redirect($sns->getRequestCodeURL())~。（可参考DEMO中的Index/login方法）