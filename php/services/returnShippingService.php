
<?php

require_once("curlClient.php");

class ReturnShippingService
{

    const appID = "6a7d75ed471a52f7bb81f8506ae78f13";
    const restAPIKey = "3ea182891f031ca339adf11c4336a68df9f800bd";
    const FLCLShippingServiceURL = "http://api-int.flavorcloud.com/api/ReturnShipment";

    // Create the shipment request and make the service call
    public function createReturnShipment()
    {
        $client = CurlClient::instance();
        $requestParams = $this->_buildCreateReturnShipmentRequestParams();
        $response = $client->request("POST", self::FLCLShippingServiceURL, array('Content-Type:application/json'), $requestParams);
        return $response[0];
    }

    // get shipment by id
    public function getReturnShipment()
    {
        $returnShipmentID = 18;
	$client = CurlClient::instance();
        $requestParams = $this->_buildGetReturnShipmentRequestParams($returnShipmentID);
        $response = $client->request("GET", self::FLCLShippingServiceURL, array('Content-Type:application/json'), $requestParams);
        print_r($response);
        return $response[0];
        $client = curlClient::instance();
    }

    // build out the json shipment request
    private function _buildCreateReturnShipmentRequestParams()
    {
        try
        {
            $params = array();
            $params['appId'] = self::appID;
            $params['restApiKey'] = self::restAPIKey;
            // create a unique reference id to associate the response with your request
            $params['reference'] = '123456';
            // Valid service codes we support - EXPRESS | 
            $params['service_code'] = 'EXPRESS';
            // Use standard weight units - LB, KG
            $params['weight_unit'] = 'LB';
            // Use standard dimension unit - CM, ??
            $params['dimension_unit'] = 'CM';
            $params['is_return'] = 'Y';
            // use ISO standard currency code
            $params['currency'] = 'USD';
            // onward shipment id
	    $params['shipment_id'] = '145';		

            // Ship to information
            $params['shipto_name'] = 'Sheila Haque';
            $params['shipto_attention_name'] = 'Runway2Street';
            $params['shipto_address'] = array(
                'city' => 'Melborne',
                'state' => 'VIC',
                'zip' => '3805',
                'country' => 'Australia',
                'address_line_1' => '1 Honeyeater Grove',
                'address_line_2' => '',
                'address_line_3' => '',
                'phone' => '111-111-1111',
                'email' => 'global@test.com'
            );

            //ship from information
            $params['shipfrom_name'] = 'Globe Trotter';
            $params['shipfrom_attention_name'] = 'FlavorCloud';

            $params['shipfrom_address'] = array(
                'city' => 'Seattle',
                'state' => 'WA',
                'zip' => '98109',
                'country' => 'US',
                'address_line_1' => '1740 Aurora Ave N 401',
                'address_line_2' => '',
                'address_line_3' => '',
                'phone' => '9876543210',
                'email' => 'globetrotter@flcl.com'
            );

            // Package information
            $package['quantity'] = "1";
            $package['sale_price'] = '99.99';
            $package['weight'] = '2';
            $package['description'] = 'Silk scarf';
            $params['pieces'][] = $package;
            $params['terms_of_trade'] = 'DDP';
            $params['invoice_date'] = '2018-01-01 01:01:01';
            $params['reason_for_export'] = 'Return';
            $params['return_reason'] =  'fit';
            $params['carrier'] = 'UPS';
            // If you want insurance. We default to no insurance if this is not provided
            $params['insurance'] = 'Y';

            return $params;
        }
        catch (Exception $e)
        {
            echo 'Caught exception: ', $e->getMessage();
        }
    }

    private function _buildGetReturnShipmentRequestParams($returnShipmentID)
    {
        try
        {
            $params = array();
            $params['appId'] = self::appID;
            $params['restApiKey'] = self::restAPIKey;
            $params['return_shipment_id'] = $returnShipmentID;
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
