
<?php

require_once("curlClient.php");

class TrackingService
{

    const appID = "6a7d75ed471a52f7bb81f8506ae78f13";
    const restAPIKey = "3ea182891f031ca339adf11c4336a68df9f800bd";
    const FLCLShippingServiceURL = "http://api-int.flavorcloud.com/api/Tracking";

    // Get the hscode for product
    public function getTracking()
    {
        $client = CurlClient::instance();
        $requestParams = $this->_buildTrackingRequestParams();
        $response = $client->request("POST", self::FLCLShippingServiceURL, array('Content-Type:application/json'), $requestParams);
        return $response[0];
    }

    
    // build out the json hscode request
    private function _buildTrackingRequestParams()
    {
        try
        {
            $params = array();
            $params['appId'] = self::appID;
            $params['restApiKey'] = self::restAPIKey;
            // create a unique reference id to associate the response with your request
            $params['reference'] = '123456';
            $params['carrier'] = 'DHL';
            $params['tracking_number'] = '731699839';
            return $params;
        }
        catch (Exception $e)
        {
            echo 'Caught exception: ', $e->getMessage();
        }
    }



    // call the service
    private function _callService($params, $endPoint)
    {
        //setting the curl parameters. 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        //turning off the server and peer verification(TrustManager Concept). 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //setting the params as POST FIELD to curl 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        //getting response from server 
        $response = curl_exec($ch);

        if (curl_errno($ch))
        {
            echo "FLCLServcie API errors :- ", curl_error($ch);
            echo "FLCLServcie API errors response :- ", $response;
        }
        else
        {
            curl_close($ch);
        }

        return json_decode($response, true);
    }

}

?>
