<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 15:41
 */
$back_url = urldecode($_GET['url']);

if($back_url){
    $referer = urlEncode($back_url);
}else{
    $referer = urlEncode("http://www.yibuaishop.com/yibumobile/index.html");
}

$app_id = 'wxead41370cd5744f8';
$app_secret = 'a670f02b7615ec5eb73c5aa55a4817a0';

$redirect_uri = urlEncode('http://www.yibuaishop.com/data/wx_back.php?referer='.$referer.'&scope=snsapi_base');
$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_id."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base#wechat_redirect";

header("Location: $url");