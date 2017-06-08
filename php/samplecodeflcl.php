<?php

class FLCLService
{

    public $config;
    public $rateUrl = "";
    public $shipmentUrl = "";
    public $returnShipmentUrl = "";
    public $dutyUrl = "";
    public $trackingUrl = "";
    private $_samplingData;
    private $_errors = "";


    public function init()
    {
        parent::init();
        $this->_loadClient();
    }

    private function _loadClient()
    {
        try
        {

            if (!isset($this->config['appID']) || !isset($this->config['restApiKey']) || !isset($this->config['host']))
            {
                throw new Exception("Error initiating flavorcloud service client", 1);
            }
            else
            {
                $this->rateUrl = $this->config['host'] . '/Rates';
                $this->shipmentUrl = $this->config['host'] . '/Shipments';
                $this->returnShipmentUrl = $this->config['host'] . '/ReturnShipment';
                $this->dutyUrl = $this->config['host'] . '/LandedCost';
                $this->trackingUrl = $this->config['host'] . '/Tracking';
                $this->_samplingData = $this->_loadSamplingData();
            }
        }
        catch (CException $e)
        {
            Yii::log('Error initiating algolia search client' . print_r($e, true), CLogger::LEVEL_ERROR, 'application.component.flavorcloudservice');
        }
    }

    public function getAppId()
    {
        return $this->config['appID'];
    }

    public function getAdminApiKey()
    {
        return $this->config['restApiKey'];
    }

    public function getRates($products, $destinationCountry, $originCountry)
    {
        Yii::log("FLCLService getRates Invoked", CLogger::LEVEL_INFO, 'application.component.FLCLService');
        $params = $this->_createRateRequestParams($products, $destinationCountry, $originCountry);
        Yii::log("FLCLService params :- " . print_r($params, true), CLogger::LEVEL_INFO, 'application.component.FLCLService');
        $response = $this->_doPost($params, $this->rateUrl);
        return $response;
    }

    public function getShipment($params)
    {
        $response = $this->_doPost($params, $this->shipmentUrl);
        return $response;
    }

    public function getReturnShipment($params)
    {
        $response = $this->_doPost($params, $this->returnShipmentUrl);
        return $response;
    }

    public function getDuty($products, $designerCountryISOCode, $shippingCountryISOCode, $shippingCost, $insuranceCost, $defaultCurrencyIsoCode, $isRetailPrice = true, $province)
    {
        Yii::log("FLCLService getDuty Invoked", CLogger::LEVEL_INFO, 'application.component.FLCLService');
        $params = $this->_createDutyRequestParams($products, $designerCountryISOCode, $shippingCountryISOCode, $shippingCost, $insuranceCost, $defaultCurrencyIsoCode, $isRetailPrice, $province);
        Yii::log("FLCLService params :- " . print_r($params, true), CLogger::LEVEL_INFO, 'application.component.FLCLService');
        $response = $this->_doPost($params, $this->dutyUrl);
        Yii::log("FLCLService params response:- " . print_r($response, true), CLogger::LEVEL_INFO, 'application.component.FLCLService');
        return $response;
    }

    public function getTracking($params)
    {
        $response = $this->_doPost($params, $this->trackingUrl);
        return $response;
    }

    private function _loadSamplingData()
    {
        $sampleObj = SampleAddress::model()->findAllByAttributes(array('isSupported' => 0));
        $data = array();
        foreach ($sampleObj as $key => $val)
        {
            $data[$val->country->IsoCode] = array('city' => $val->City, 'zip' => $val->Zip, 'State' => $val->State);
        }

        return $data;
    }

    private function _createRateRequestParams($products, $destinationCountry, $originCountry)
    {
        try
        {
            $params = array();
            $params['appId'] = $this->config['appID'];
            $params['restApiKey'] = $this->config['restApiKey'];
            $params['reference'] = $products->ProductID . "-" . $destinationCountry;
            $params['service_code'] = 'EXPRESS';
            $params['weight_unit'] = 'LB';
            $params['dimension_unit'] = 'CM';
            $params['is_return'] = 'N';
            $params['currency'] = $products->designerCollection->designer->currency->IsoCode;
            $params['shipto_name'] = 'shipto name';
            $params['shipto_attention_name'] = 'shipto_attensionname';
            $params['shipfrom_name'] = 'ship from name';
            $params['shipfrom_attention_name'] = 'shipfrom_attensionname';

            $params['shipto_address'] = array(
                'city' => $this->_samplingData[$destinationCountry]['city'],
                'state' => $this->_samplingData[$destinationCountry]['State'],
                'zip' => $this->_samplingData[$destinationCountry]['zip'],
                'country' => $destinationCountry,
                'address_line_1' => 'destinationAddressLine1',
                'address_line_2' => '',
                'address_line_3' => '',
                'phone' => '1234567895',
                'email' => 'R2Steam@enqos.com'
            );

            $params['shipfrom_address'] = array(
                'city' => $this->_samplingData[$originCountry]['city'],
                'state' => $this->_samplingData[$originCountry]['State'],
                'zip' => $this->_samplingData[$originCountry]['zip'],
                'country' => $originCountry,
                'address_line_1' => 'address line 1',
                'address_line_2' => '',
                'address_line_3' => '',
                'phone' => '9876543210',
                'email' => 'R2Steam@enqos.com'
            );

            if (is_object($products) && $products->ProductDimensionID !== NULL)
            {
                $package['quantity'] = "1";
                $package['sale_price'] = $products->RetailPrice;
                $package['weight'] = $products->productDimension->Weight;
                $package['description'] = (isset($products->Description) && $products->Description != "")?strpos(strip_tags($products->Description), 350):'calculation of shipping cost';
                $params['pieces'][] = $package;
                if ($products->RetailPrice > 100)
                    $params['insurance'] = 'Y';
                else
                    $params['insurance'] = 'N';
                return json_encode($params);
            }else
            {
                throw new CException("Product is not object or Product Dimension not available");
            }
        }
        catch (CException $e)
        {
            Yii::app()->rerror->logError(102001, $e->getMessage(), CLogger::LEVEL_ERROR, 'application.component.FLCLService');
        }
    }

    private function _createDutyRequestParams($products, $designerCountryISOCode, $shippingCountryISOCode, $shippingCost, $insuranceCost, $defaultCurrencyIsoCode, $isRetailPrice, $province)
    {
        $params = array();
        $params['appId'] = $this->config['appID'];
        $params['restApiKey'] = $this->config['restApiKey'];
        $params['reference'] = $products[0]['ProductID'] . "-" . $designerCountryISOCode;
        $params['shipto'] = $shippingCountryISOCode;
        $params['currency'] = $defaultCurrencyIsoCode;
        if (count($products) > 0)
        {
            foreach ($products as $key => $value)
            {
                $price = $value["RetailPrice"];
                $package['quantity'] = 1;
                $package['sale_price'] = $price;
                $package['hsc'] = $value["HScode"];
                $package['currency'] = $defaultCurrencyIsoCode;
                $package['shipping_cost'] = $shippingCost;
                $package['insurance'] = $insuranceCost;
                $package['origin_country'] = $value["Origin"];
                $package['description'] = (isset($value["Description"]) && $value["Description"] != "")?$value["Description"]:'calculation of shipping cost';
                $package['material'] = $value["Material"];
                $package['category'] = $value["Category"];
                $package['sku'] = $value['SKU'];
                $params['pieces'][] = $package;
            }
        }
        return json_encode($params);
    }

    private function _doPost($params, $endPoint)
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
        Yii::log("FLCLServcie API endpoint :- " . $endPoint, CLogger::LEVEL_INFO, 'application.component.FLCLService');
        Yii::log("FLCLServcie API response :- " . print_r($response, true), CLogger::LEVEL_INFO, 'application.component.FLCLService');
        if (curl_errno($ch))
        {
            Yii::log("FLCLServcie API errors :- " . curl_error($ch), CLogger::LEVEL_INFO, 'application.component.FLCLService');
            Yii::log("FLCLServcie API errors respnse :- " . $response, CLogger::LEVEL_INFO, 'application.component.FLCLService');
        }
        else
        {
            curl_close($ch);
        }

        return json_decode($response, true);
    }

}
?>
