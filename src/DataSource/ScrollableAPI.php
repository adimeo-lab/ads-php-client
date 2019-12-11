<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Exception\DataSourceExecutionException;
use Adimeo\DataSuite\Model\DataSource;
use GuzzleHttp\Client;

class ScrollableAPI extends DataSource
{
    public function getDisplayName(): string
    {
        return 'Scrollable API';
    }

    public function getOutputFields(): array
    {
        return ['doc'];
    }

    public function getSettingFields(): array
    {
        return [
            'apiUrl' => [
                'label' => 'API Url (use !from, !limit for scrolling parameters)',
                'type' => 'string',
                'required' => true
            ],
            'method' => [
                'label' => 'Method (GET, POST, etc)',
                'type' => 'string',
                'required' => true
            ],
            'parameters' => [
                'label' => 'Request body (use !from, !limit for scrolling parameters)',
                'type' => 'textarea',
                'required' => false
            ],
            'start' => [
                'label' => 'Start at',
                'type' => 'integer',
                'required' => true
            ],
            'batchSize' => [
                'label' => 'batch size (limit parameter)',
                'type' => 'integer',
                'required' => true
            ],
            'explodingCode' => [
                'label' => 'Exploding code (PHP used to explode content into documents [$content is the string content returned by the API]. Must return an array.)',
                'type' => 'textarea',
                'required' => true
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [
            'arg1' => [
                'label' => 'Argument 1',
                'type' => 'string',
                'required' => false,
            ],
            'arg2' => [
                'label' => 'Argument 2',
                'type' => 'string',
                'required' => false,
            ],
            'arg3' => [
                'label' => 'Argument 3',
                'type' => 'string',
                'required' => false,
            ]
        ];
    }

    public function execute(array $args = [])
    {
        $apiUrl = $this->getSettings()['apiUrl'];
        $method = $this->getSettings()['method'];
        $parameters = $this->getSettings()['parameters'];
        $start = $this->getSettings()['start'];
        $batchSize = $this->getSettings()['batchSize'];
        $explodingCode = $this->getSettings()['explodingCode'];
        while (!empty($docs = $this->getContentFromAPI($apiUrl, $method, $parameters, $start, $batchSize, $explodingCode, $args))) {
            foreach ($docs as $doc) {
                $this->index([
                    'doc' => $doc
                ]);
            }
            $start += $batchSize;
        }
    }

    private function getContentFromAPI($apiUrl, $method, $parameters, $start, $batchSize, $explodingCode, $args)
    {
        $placeholders = [
            '!from' => $start,
            '!limit' => $batchSize
        ];
        foreach ($args as $k => $arg) {
            $placeholders['!'.$k] = $arg;
        }
        $this->getOutputManager()->writeLn('Getting content from '.$start);
        $url = $this->injectScrollingParameters($apiUrl, $placeholders);
        $params = $this->injectScrollingParameters($parameters, $placeholders);
        $client = new Client();
        $res = $client->request($method, $url, [
            'body' => $params
        ]);
        $callable = function ($content, $code) {
            return eval($code);
        };
        if ($res->getStatusCode() < 400) {
            $content = (string) $res->getBody();
            return $callable($content, $explodingCode);
        }
        throw new DataSourceExecutionException('Call failed (status code '.$res->getStatusCode().')'.PHP_EOL.$res->getBody());
    }

    private function injectScrollingParameters($string, $params)
    {
        foreach ($params as $k => $v) {
            $string = str_replace($k, $v, $string);
        }

        return $string;
    }
}
