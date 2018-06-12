<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 15:50
 */

$app_id = 'wxead41370cd5744f8';
$app_secret = 'a670f02b7615ec5eb73c5aa55a4817a0';

$scope = isset($_GET['scope'])?$_GET['scope']:"";
$result = getResult($app_id, $app_secret, 'code', isset($_GET['code']) ? $_GET['code'] : '');
if ($result['error'] == 0) {
    $oauth_id = $result['return']['openid'];

    $platform = 'wechat';

    $url = urldecode($_GET['referer']);

    if($oauth_id){
        if(strpos($url,'?') !== false){
            $urls =  $url.'&open_id='.$oauth_id;
        }else{
            $urls =  $url.'?open_id='.$oauth_id;
        }
        header("Location: $urls");
    }
    else{
        header("Location: $url");
    }

//    if($scope == "snsapi_userinfo"){
//        $access_token = $result['return']['access_token'];
//        $userinfo = getUserByWeixin($access_token, $oauth_id);
//    }
//
//    $user_number = checkBind($oauth_id);
//    if (!$user_number) {
//        header("Location: http://www.yibuaishop.com/yibumobile/mobileLogin.html?open_id=$oauth_id");
//    }else{
//        $urls =  $url.'?open_id='.$oauth_id.'&user_id='.$user_number['id'].'&token='.$user_number['token'].'&name='.$user_number['name'].'&balance='.$user_number['balance'];
//        header("Location: $urls");
//    }
//    if($scope == "snsapi_userinfo"){
//        if (!$userinfo) {
//            return false;
//        }
//
//        $user_number = checkBind($oauth_id);
//        if (!$user_number) {
//            return false;
//        }
//    }
//    else{
//        $user_number = checkBind($oauth_id);
//        if (!$user_number) {
//            return false;
//        }
//    }
}

function getResult($appid, $secret, $type = 'code', $key) {
    $params = array();
    $params['appid'] = $appid;
    $params['secret'] = $secret;
    if ($type === 'token') {
        $uri = 'sns/oauth2/refresh_token';
        $params['appid'] = $appid;
        $params['grant_type'] = 'refresh_token';
        $params['refresh_token'] = $key;
    }elseif($type === 'code') {
        $uri = 'sns/oauth2/access_token';
        $params['appid'] = $appid;
        $params['secret'] = $secret;
        $params['code'] = $key;
        $params['grant_type'] = 'authorization_code';
    }else{
        return array('error'=>"wrong auth type");
    }
    $return = request('https://api.weixin.qq.com/'.$uri, 'GET', $params);
    if(!is_array($return) || !$return)
        return array('error'=>"get access token failed".$return);
    if (!isset($return['errcode'])){
        // $this->access_token = $return['access_token'];
        // $this->refresh_token = $return['refresh_token'];
        // $this->expires_in = $return['expires_in'];
        // $this->openid = $return['openid'];
        // $this->unionid = isset($return['unionid']) ? $return['unionid'] : null;
        return array('error'=>0 ,'return'=>$return);
    }else{
        return array('error'=>"get access token failed: " . $return['errmsg']);

    }
}

function request($url, $method, $parameters) {
    return curl_request($url, $method, $parameters);
}

function getUserByWeixin($access_token, $open_id)
{
    $api = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}";
    $res = curl_request($api);
    if (isset($res['errcode'])) {
        return false;
    }

    return [
        'nickname' => $res['nickname'],
        'gender' => $res['sex'],
        'prefix' => 'wx',
        'avatar' => $res['headimgurl']
    ];
}

function curl_request($api, $method = 'GET', $params = array(), $headers = [])
{
    $curl = curl_init();

    switch (strtoupper($method)) {
        case 'GET' :
            if (!empty($params)) {
                $api .= (strpos($api, '?') ? '&' : '?') . http_build_query($params);
            }
            curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
            break;
        case 'POST' :
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            break;
        case 'PUT' :
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
        case 'DELETE' :
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
    }

    curl_setopt($curl, CURLOPT_URL, $api);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, 0);

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);

    if ($response === FALSE) {
        $error = curl_error($curl);
        curl_close($curl);
        return FALSE;
    }else{
        // 解决windows 服务器 BOM 问题
        $response = trim($response,chr(239).chr(187).chr(191));
        $response = json_decode($response, true);
    }

    curl_close($curl);

    return $response;
}

function checkBind($open_id)
{
    $function_name = 'user/wx_user';
    $arr = array(
        'open_id'=>$open_id
    );
    $id = getCurl($function_name,$arr);
    $id = (array)json_decode($id);

    if(empty($id)){
        return false;
    }
    return $id;
}

function createAuthUser($open_id,$tel, $nickname='', $gender='', $avatar = '')
{

}

function updateAuthUser($vendor, $open_id, $nickname, $gender, $prefix = 'ec', $avatar = '')
{
    $user_sql = "SELECT * FROM ".$GLOBALS['ecs']->table('users')." WHERE `open_id` = '$open_id'";
    $res = $GLOBALS['db']->query($user_sql);
    $user = $GLOBALS['db']->fetchRow($res);
    if($user['alias']){
        $nickname = $user['alias'];
    }
    if($user['sex']){
        $gender = $user['sex'];
    }
    if ($user['user_id'])
    {
        $nickname = strip_tags($nickname);
        $sql = "UPDATE " . $GLOBALS['ecs']->table('users') .
            " SET sex = '$gender', alias = '$nickname',headimgurl = '$avatar' WHERE open_id = '$open_id' ";
        $res = $GLOBALS['db']->query($sql);
        if ($res)
        {
            return $user['user_number'];
        }
        return false;
    }
}

function getCurl($function_name,$array){
    $url = "http://116.62.132.51/tp3/index.php/home/".$function_name;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// post数据
    curl_setopt($ch, CURLOPT_POST, 1);
// post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
    $output = curl_exec($ch);
    curl_close($ch);
//打印获得的数据
    return $output;
}

