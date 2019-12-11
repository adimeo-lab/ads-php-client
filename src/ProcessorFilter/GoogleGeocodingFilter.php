<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;
use GuzzleHttp\Client;

class GoogleGeocodingFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Google Geocoding Filter";
    }

    public function getFields()
    {
        return ['location'];
    }

    public function getSettingFields()
    {
        return [
            'api_key' => [
                'label' => 'API key',
                'type' => 'string',
                'required' => false
            ]
        ];
    }

    public function getArguments()
    {
        return [
            'address' => 'Address',
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        try {
            $settings = $this->getSettings();
            $apiKey = isset($settings['api_key']) ? $settings['api_key'] : '';
            $address = $this->getArgumentValue('address', $document);

            if (!empty($address)) {
                $google_url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address);
                if (!empty($apiKey))
                    $google_url .= '&key='.$apiKey;

                $client = new Client();
                $response = $client->request('GET', $google_url);
                $json = json_decode($response->getBody(), TRUE);
                if (isset($json['error_message']) && !empty($json['error_message'])) {
                    throw new \Exception('Google maps error : '.$json['error_message']);
                }
                if (isset($json['status']) && $json['status'] == 'OK' && isset($json['results'][0])) {
                    usleep(100000);//Sleep for 100ms
                    return ['location' => $json['results'][0]];
                }
            }
            return ['value' => null];
        } catch (\Exception $ex) {
            $datasource->getOutputManager()->writeLn('Exception ==> '.$ex->getMessage());
            return ['value' => null];
        }
    }
}
