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
// | 2012-4-25  IndexAction.class.php
// +----------------------------------------------------------------------

class IndexAction extends Action{
	/* 这个是登录或授权按钮所在页面，可以是你的应用的任何页面
	 * 点击登录按钮连接到的页面必须是该模块的oauth操作
	* 如果你的引用接入了多个SNS，则还需要传递参数 sns_type参数
	* sns_type参数的取值为：腾讯微博：tencent；新浪微博：sina；网易微博：163
	* 跳转的路径最好通过U函数生成，如下：
	*/
	public function index(){
		/* 腾讯微博登录连接 */
		$this->tencent = U('Oauth/oauthCode', 'sns_type=tencent');
	
		/* 腾讯微博登录连接 */
		$this->sina = U('Oauth/oauthCode', 'sns_type=sina');
	
		/* 腾讯微博登录连接 */
		$this->t163 = U('Oauth/oauthCode', 'sns_type=163');
	
		/* 调用模板显示登录按钮 */
		$this->display();
	}
}