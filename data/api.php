<?php
libxml_disable_entity_loader(false);
$function_name = htmlspecialchars($_POST['function_name']);
foreach ($_POST as $key => $value){
    if($key != 'function_name'){
        $array[$key] = $value;
    }
}
//var_dump($array);
//die;
$soap=new SoapClient('http://59.110.215.209:8181/ws/n_webservice.asmx?WSDL');
$result=$soap->$function_name($array);
$result = (array)$result;

//if($type = 'xml'){
//    $str = json_decode(json_encode(simplexml_load_string($result['as_data'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
//    $result['as_data'] = $str;
//}
die(json_encode($result));