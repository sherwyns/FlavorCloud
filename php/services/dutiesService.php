
<?php

require_once("curlClient.php");

class DutiesService
{

    const appID = "6a7d75ed471a52f7bb81f8506ae78f13";
    const restAPIKey = "3ea182891f031ca339adf11c4336a68df9f800bd";
    const FLCLShippingServiceURL = "http://api-int.flavorcloud.com/api/LandedCost";

    // Get the duty for product
    public function getLandedCost()
    {
        $client = CurlClient::instance();
        $requestParams = $this->_buildDutiesRequestParams();
        $response = $client->request("POST", self::FLCLShippingServiceURL, array('Content-Type:application/json'), $requestParams);
        return $response[0];
    }

    

    // build out the json duty request
    private function _buildDutiesRequestParams()
    {
        try
        {
            $params = array();
            $params['appId'] = self::appID;
            $params['restApiKey'] = self::restAPIKey;
            // create a unique reference id to associate the response with your request
            $params['reference'] = '123456';
            // use ISO standard currency code
            $params['currency'] = 'USD';

            // Package information
            $package['quantity'] = '1';
            $package['sale_price'] = '200.00';
            $package['hs_code'] = '640359';
            $package['shipping_cost'] = '10.00';
            $package['insurance'] = '2.00';
            $package['origin_country_code'] = 'US';
            $package['description'] = 'Silk scarf';
            $package['material"'] = 'Kid Suede upper, Kid leather lining';
            $package['category'] = array('Womens Shoes','Boots');
            $package['sku'] = '300101860';
            $params['pieces'][] = $package;
	
            $params['shipto'] = 'GB';
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
