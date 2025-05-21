<?php

/**
 * User Class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class LeadsApi {

    /**
     * Business ID
     * @var Int
     */
    private $brandId = '454';

    /**
     * Leads8 Api Url
     * @var Array
     */
    private $apiUrl = 'https://leads8.com/api/';

    /**
     * Leads8 access token
     * @var String
     */
    private $token = '716b684cfcbe77f7575db1005932403b';
    //private $token = 'd7124fb6f0d04dd8a1648586b437f6c4';

    /*
     * Get business Id
     */

    public function getBusinessId() {
        return $this->brandId;
    }

    /*
     * Call curl for leads8
     */
    public function callCurl($methodName = '', $params = []){
        if($methodName != ''){
            $cUrl = $this->apiUrl.$methodName.'/?token='.$this->token.'&brand='.$this->brandId;
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
            if(!empty($params)){
                curl_setopt($ch, CURLOPT_POST, 1);
                // setting the NVP $my_api_str as POST FIELD to curl
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } else {
                curl_setopt($ch, CURLOPT_POST, 0);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
            $httpResponse = curl_exec($ch);
            if (!$httpResponse) {
                // $info = curl_getinfo($ch);
                $response = "API_method failed: " . curl_error($ch) . "(" . curl_errno($ch) . ")";
                curl_close($ch);
                return $response;
            }
            curl_close($ch);
            $httpResponse = json_decode($httpResponse, true);

            return $httpResponse;
        }
    }
}
