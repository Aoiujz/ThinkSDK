### SDK简介

本SDK是基于ThinkPHP开发类库扩展，因此只能在ThinkPHP平台下使用（ThinkPHP版本要求2.0以上）。DEMO中用到了控制器分层，因此运行DEMO需使用ThinkPHP3.1.2版本。

### 目前支持的平台
目前可用登录平台为：腾讯QQ，腾讯微博，新浪微博，网易微博，人人网，360，豆瓣，Github，Google，MSN。

### 包含的文件

ThinkSDK/ThinkOauth.class.php    SDK基类，主要用于Oauth的认证，所有平台的SDK均需要继承此类。    
ThinkSDK/sdk/DoubanSDK.class.php 豆瓣SDK 

**************** 项目文件 ********************
/项目目录/Lib/OauthAction.class.php 


使用方法
1，配置SDK，在ThinkPHP项目配置文件配置以下信息【腾讯，网易，新浪三个微博需要那个就填写那个配置】

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

2，授权地址 U('Oauth/oauthCode', 'sns_type=tencent') 
   这里的sns_type=tencent 中的tencent为SNS类型，腾讯 - tencent,新浪 - sina, 网易 - 163
   可参考示例 IndexAction.class.php

3, 调用接口，授权之后就可以正常调用接口，如下：
   import('ORG.SNS_SDK.TencentWeiBo'); //引入接口类
   $tencent = new TencentWeiBo(); //实例化接口对象
   $tencent->tShow(); //调用接口

4，接口命名规范
   所有接口方法的名称为SNS平台提供的接口名称去掉‘/’，紧跟‘/’后的字母大写，如下：
   接口名：t/add                   对应的方法为   tAdd()
   接口名：statuses/home_timeline  对应的方法为   statusesHome_timeline()
   具体接口请参考SNS开放平台

5，接口参数规范
   提供两种传递参数的方式
   a，URL查询字符串：a=xxx&b=xxx
   b，数组方式： array(a=>'xxx',b=>'xxx')
   具体接口参数请参考SNS开放平台
   注：各平台返回数据格式默认为 json 可以不传递此参数
       腾讯微博需要传递用户IP，系统已经自动传递，调用接口时可以不用传递此参数
       出以上两个参数以外，其他只要是开放平台规定的必选参数即为SDK方法的必选参数
 
   
本周末提交详细文档及其他平台开发规范