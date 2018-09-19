<?php
/*
Plugin Name: 微信公众号涨粉
Plugin URI: http://www.huoduan.com/wechat-fans.html
Description: 本插件可以隐藏文章中的任意部分内容，当访客关注您的微信公众后，可获取验证码查看隐藏内容
Version: 1.1
Author: 火端网络
Author URI: http://www.huoduan.com/
Note: 请勿修改或删除以上信息
*/
error_reporting(E_ERROR | E_PARSE); 
ob_start();
 register_activation_hook(__FILE__,'wechatfans_install');    

 register_deactivation_hook( __FILE__, 'wechatfans_remove' );   
 function wechatfans_install() {   
    add_option("wechatfans", "", '', 'yes');
    
 }
 function wechatfans_remove() { 
    delete_option('wechatfans'); 

}  

 if( is_admin() ) {   

 add_action('admin_menu', 'wechatfans_menu');   
}   
 function wechatfans_menu() {   

    add_options_page('Wechat fans', '微信公众号涨粉设置','administrator','wechatfans', 'wechatfans_html_page');
 }    
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'wechatfans_plugin_actions');
function wechatfans_plugin_actions ($links) {
    $new_links = array();
    $new_links[] = '<a href="admin.php?page=wechatfans">设置</a>';
    return array_merge($new_links, $links);
}
function wechatfans_html_page() {   
?>   
<div>   
<h2>微信公众号设置</h2>   
<form method="post" action="options.php">   
 
<?php wp_nonce_field('update-options'); 
$wechatfans = get_option('huoduan_wechatfans');
?>  
<p>
<strong>微信公众号名称：</strong><br />
　　名称：<input name="huoduan_wechatfans[wechat_name]" type="text" value="<?php echo isset($wechatfans['wechat_name'])?$wechatfans['wechat_name']:'火端网络';?>" /> <span>微信公众号平台→公众号设置→名称，例如：火端网络</span>

</p> 
<p>
<strong>微信公众号：</strong><br />
　微信号：<input name="huoduan_wechatfans[wechat_account]" type="text" value="<?php echo isset($wechatfans['wechat_account'])?$wechatfans['wechat_account']:'huoduan';?>" /> <span>微信公众号平台→公众号设置→微信号，例如：huoduan</span>

</p> 
<p>
<strong>回复以下关键词获取验证码：</strong><br />
　关键词：<input name="huoduan_wechatfans[wechat_keyword]" type="text" value="<?php echo isset($wechatfans['wechat_keyword'])?$wechatfans['wechat_keyword']:'微信验证码';?>" /> 例如：<span style="color:#F00;">微信验证码</span>，访客回复这个关键词就可以获取到验证码

</p> 
<p>
<strong>自动回复的验证码：</strong><br />
　验证码：<input name="huoduan_wechatfans[wechat_code]" type="text" value="<?php echo isset($wechatfans['wechat_code'])?$wechatfans['wechat_code']:'168168';?>" /> 该验证码要和微信公众号平台自动回复的内容一致，最好定期两边都修改下
</p>
 <p>
<strong>微信公众号二维码地址：</strong><br />
图片地址：<input name="huoduan_wechatfans[wechat_qrimg]" style="width:400px;" type="text" value="<?php echo isset($wechatfans['wechat_qrimg'])?$wechatfans['wechat_qrimg']:'http://www.huoduan.com/static/images/wechat150.gif';?>" /> <button class='custom_media_upload button'>上传</button> 填写您的微信公众号的二维码图片地址，建议150X150像素
</p>
<p>
<strong>Cookie有效期：</strong><br />
有效天数：<input name="huoduan_wechatfans[wechat_day]" type="text" value="<?php echo isset($wechatfans['wechat_day'])?$wechatfans['wechat_day']:'30';?>" />天， 在有效期内，访客无需再获取验证码可直接访问隐藏内容
</p>
<p>
<strong>加密密钥：</strong><br />
　　密钥：<input name="huoduan_wechatfans[wechat_key]" type="text" value="<?php echo isset($wechatfans['wechat_key'])?$wechatfans['wechat_key']:md5('huoduan.com'.time().rand(10000,99999));?>" /> 用于加密Cookie，默认是自动生成，一般无需修改，如果修改，所有访客需要重新输入验证码才能查看隐藏内容
</p>

  
<p>   

<input type="hidden" name="action" value="update" />   
<input type="hidden" name="page_options" value="huoduan_wechatfans" />
<input type="submit" value="保存设置"class="button-primary" />   
</p>   
</form>   
<p>  

如有疑问可以点击<a href="http://www.huoduan.com/wechat-fans.html" target="_blank">这里</a>咨询作者，火端网络官方网站：<a href="http://www.huoduan.com" target="_blank">http://www.huoduan.com</a><br />
</p>
</div>   
<?php   

}   
add_filter('the_content', 'huoduan_wechat_fans');
function huoduan_wechat_fans($content){
	$cookie_name = 'huoduan_wechat_fans';
	
	if (preg_match_all('/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i', $content, $hide_words))
	{
	    $wechatfans = get_option('huoduan_wechatfans');
		$cv = md5($wechatfans['wechat_key'].$cookie_name.'huoduan.com');
		$vtips='';
		if(isset($_POST['huoduan_verifycode'])){
			if($_POST['huoduan_verifycode']==$wechatfans['wechat_code']){
				setcookie($cookie_name, $cv ,time()+(int)$wechatfans['wechat_day']*86400, "/");
				$_COOKIE[$cookie_name] = $cv;
			}else{
				$vtips='<script>alert("验证码错误！请输入正确的验证码！");</script>';
			}
		}
		$cookievalue = isset($_COOKIE[$cookie_name])?$_COOKIE[$cookie_name]:'';

		if($cookievalue==$cv){
			$content = str_replace($hide_words[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_words[0][0].'</div>', $content);	
		}else{
			
			$hide_notice = '<div class="huoduan_hide_box" style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;"><img class="wxpic" align="right" src="'.$wechatfans['wechat_qrimg'].'" style="width:150px;height:150px;margin-left:20px;display:inline;border:none" width="150" height="150"  alt="'.$wechatfans['wechat_name'].'" /><span style="font-size:18px;">此处内容已经被作者隐藏，请输入验证码查看内容</span><form method="post" style="margin:10px 0;"><span class="yzts" style="font-size:18px;float:left;">验证码：</span><input name="huo'.'duan_verifycode" id="verifycode" type="text" value="" style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" /><input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="提交查看" /></form><div style="clear:left;"></div><span style="color:#00BF30">请关注本站微信公众号，回复“<span style="color:blue">'.$wechatfans['wechat_keyword'].'</span>”，获取验证码。在微信里搜索“<span style="color:blue">'.$wechatfans['wechat_name'].'</span>”或者“<span style="color:blue">'.$wechatfans['wechat_account'].'</span>”或者微信扫描右侧二维码都可以关注本站微信公众号。</span><div class="cl"></div></div>'.$vtips;
			$content = str_replace($hide_words[0], $hide_notice, $content);
		}
		
	}
	return $content;
}

add_action('admin_footer', 'huoduan_wechat_fans_toolbar');
function huoduan_wechat_fans_toolbar() {
    if ( !strpos($_SERVER['SCRIPT_NAME'], 'post.php') && !strpos($_SERVER['SCRIPT_NAME'], 'post-new.php')) {
		return '';
	}
	global $wp_version;
	$wechatfans_271_hacker = ($wp_version == '2.7.1') ? ".lastChild.lastChild" : "";
?>	
<script type="text/javascript">
jQuery(document).ready(function($) {
		<?php if ( version_compare( $GLOBALS['wp_version'], '3.3alpha', '>=' ) ) : ?>
		edButtons[edButtons.length] = new edButton(
			// id, display, tagStart, tagEnd, access_key, title
			"wechatfans", "插入微信隐藏标签", "<!--wechatfans start-->", "<!--wechatfans end-->", "h", "插入微信隐藏标签"
		);
		<?php else : ?>
	if(s2v_toolbar = document.getElementById("ed_toolbar")<?php echo $wechatfans_271_hacker ?>){
		wechatfansNr = edButtons.length;
		edButtons[wechatfansNr] = 
		new edButton('wechatfans','插入微信隐藏标签','<!--wechatfans start-->','<!--wechatfans end-->','h', "插入微信隐藏标签"
		);
		var wechatfansBut = s2v_toolbar.lastChild;
	
		while (wechatfansBut.nodeType != 1){
			wechatfansBut = wechatfansBut.previousSibling;
		}

		wechatfansBut = wechatfansBut.cloneNode(true);
		wechatfansBut.value = "wechatfans";
		wechatfansBut.title = "插入微信隐藏标签";
		wechatfansBut.onclick = function () {edInsertTag(edCanvas,parseInt(wechatfansNr));}
		s2v_toolbar.appendChild(wechatfansBut);
		wechatfansBut.id = "wechatfans";
	}
	<?php endif; ?>
	});
</script>
<?php } ?>