<?php
class CurlClient
{
    private static $instance;
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function request($method, $absUrl, $headers, $params)
    {
        $curl = curl_init();
        $method = strtolower($method);
        $curlOptions = array();
        
        // Request Method
        if ($method == 'get') {
            $curlOptions[CURLOPT_HTTPGET] = 1;
            if (count($params) > 0) {
                $encoded = self::encodeAsUrl($params);
                $absUrl = "$absUrl"."/"."$encoded";
            }
        } else if ($method == 'post') {
            $curlOptions[CURLOPT_POST] = 1;
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($params);  
        } else if ($method == 'put') {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($params);
        } else {
            throw new Error("Unrecognized method {$method}");
        }
        // echo("<br><br>ABSOLUTE URL: " . $absUrl . "<br><br>");
        $absUrl = $absUrl;
        $curlOptions[CURLOPT_URL] = $absUrl;
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_CONNECTTIMEOUT] = 30;
        $curlOptions[CURLOPT_TIMEOUT] = 80;
        $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        
        curl_setopt_array($curl, $curlOptions);
        $httpBody = curl_exec($curl);
        
        $errorNum = curl_errno($curl);
        
        if ($httpBody === false) {
            $errorNum = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            $this->handleCurlError($errorNum, $message);
        }
        
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
       
        return array(
            $httpBody,
            $httpStatus
        );
    }
    /**
     * @param array $arr An map of param keys to values.
     * @returns string A querystring, essentially.
     */
    public static function encode($arr, $prefix = null)
    {
        if (!is_array($arr))
            return $arr;
        
        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v))
                continue;
            
            if ($prefix && $k && !is_int($k))
                $k = $prefix . "[" . $k . "]";
            else if ($prefix)
                $k = $prefix . "[]";
            
            if (is_array($v)) {
                $r[] = self::encode($v, $k, true);
            } else {
                $r[] = urlencode($k) . "=" . urlencode($v);
            }
        }
        
        return implode("&", $r);
    }
/**
     * @param array $arr An map of param keys to values.
     * @returns string A querystring, essentially.
     */
    public static function encodeAsUrl($arr)
    {
        if (!is_array($arr))
            return $arr;
        
        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v))
                continue;
            
            
            if (is_array($v)) {
                $r[] = self::encodeAsUrl($v);
            } else {
                $r[] = urlencode($v);
            }
        }
        
        return implode("/", $r);
    }
    /**
     * @param number $errno
     * @param string $message
     */
    public function handleCurlError($errno, $message)
    {
        $apiBase = Shippo::$apiBase;
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to FLCL. ";
                break;
            default:
                $msg = "Unexpected error communicating with FLCL.  " . "If this problem persists,";
        }
        $msg .= " let us know by contacting us through our contact form.";
        $msg .= "\n\n(Network error [errno $errno]: $message)";
        throw new $msg;
    }
}
