<?php
$dateTime = (date("Y-m-d H:i:s"));
$filePath = __DIR__ . "/paynet_log.txt";
$fp = @fopen($filePath, "a");
fputs($fp, "" . $dateTime . " : " . print_r($_REQUEST, true) . " \n");
@fclose($fp);

if(empty($_REQUEST)){
    echo json_encode([
            'status' => 'false',
            'message' => 'Parameters missing'
        ]);
    exit;
}

$merchantdata = isset($_REQUEST['merchantdata']) ? $_REQUEST['merchantdata'] : '';
$mData = explode('_', $merchantdata);

$brandId = isset($mData[0]) ? $mData[0] : 0;
$customerId = isset($mData[1]) ? $mData[1] : 0;
$tranManagerId = isset($mData[2]) ? $mData[2] : 0;
$reqToken = isset($_REQUEST['stoken']) ? $_REQUEST['stoken'] : '';

$sysToken = md5($brandId.'_'.$customerId.'_'.$tranManagerId.'_'.$_REQUEST['time']);
if($sysToken != $reqToken){
    echo json_encode([
            'status' => 'false',
            'message' => 'Invalid token'
        ]);
    exit;
}

if(isset($_REQUEST['currency']) && strtolower($_REQUEST['currency']) == 'eur'){
    $_REQUEST['amount'] = $_REQUEST['amount'] * 1.22;
}

// Here call curl //
$cUrl = 'https://leads8.com/dmn/paynet_callback/';
//echo $cUrl;exit;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cUrl);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response, use for testing
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Cache-Control: max-age=604800",
    'Content-type: multipart/form-data'
        )
);

// turning off the server and peer verification(TrustManager Concept).
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
if(!empty($_REQUEST)){
    curl_setopt($ch, CURLOPT_POST, 1);
    // setting the NVP $my_api_str as POST FIELD to curl
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_REQUEST);
} else {
    curl_setopt($ch, CURLOPT_POST, 0);
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$httpResponse = curl_exec($ch);
if (!$httpResponse) {
    // $info = curl_getinfo($ch);
    $response['Status code'] = '401';
    $response['message'] = 'Somethings went wrong.';    
    curl_close($ch);
    echo json_encode($response);
}
curl_close($ch);
echo $httpResponse;
