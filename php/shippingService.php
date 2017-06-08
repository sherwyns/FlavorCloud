
<?php
require_once curlClient.php;

class ShippingService{

    const appID = "6a7d75ed471a52f7bb81f8506ae78f13";
    const restAPIKey = "3ea182891f031ca339adf11c4336a68df9f800bd";
    const FLCLShippingServiceURL = "http://developers.flavorcloud.com/api/Shipments";


    // Create the shipment request and make the service call
    public function createShipment(){
        $client = curlClient::instance();

        $requestParams = _buildCreateShipmentRequestParams();
        $response = $this->_callService($jsonRequest, $this->FLCLShippingServiceURL);
        return $response;
    }

    // get shipment by id
    public function getShipment()
    {
        $shipmentID = 123456;
        $url = "/v1/shipments/" .;

    }

    // build out the json shipment request
    private function _buildCreateShipmentRequestParams()
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
            $params['is_return'] = 'N';
            // use ISO standard currency code
            $params['currency'] = 'USD';

 
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
            
            // If you want insurance. We default to no insurance if this is not provided
            $params['insurance'] = 'Y';

            return $params;
            
        }
        catch (Exception $e)
        {
            echo 'Caught exception: ',  $e->getMessage();
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