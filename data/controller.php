<?php
/**
 * Created by PhpStorm.
 * User: zcg
 * Date: 2017/9/14
 * Time: 12:00
 */
$array = array();
$function_name = htmlspecialchars($_POST['function_name']);
foreach ($_POST as $key => $value){
    if($key != 'function_name'){
        $array[$key] = $value;
        if(is_array($value)){
            $array[$key] = json_encode($value);
        }
    }
}

//var_dump($array);die;

$url = "http://59.110.215.209/tp3/index.php/home/".$function_name;
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
die($output);