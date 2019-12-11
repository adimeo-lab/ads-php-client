<?php

namespace Adimeo\DataSuite\Index;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;

class StatIndexManager
{
    const APP_INDEX_NAME = '.adimeo_data_suite_stat';

    /**
     * @var Client
     */
    private $client;

    private $indexNumberOfShards;

    private $indexNumberOfReplicas;

    public function __construct($elasticsearchServerUrl, $numberOfShards = 1, $numberOfReplicas = 1)
    {
        $clientBuilder = new ClientBuilder();
        if (!defined('JSON_PRESERVE_ZERO_FRACTION')) {
            $clientBuilder->allowBadJSONSerialization();
        }
        $clientBuilder->setHosts([$elasticsearchServerUrl]);
        $this->client = $clientBuilder->build();

        $this->indexNumberOfShards = $numberOfShards;
        $this->indexNumberOfReplicas = $numberOfReplicas;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function saveStat($target, $facets = [], $text, $keywords = [], $rawKeyWords = [], $apiUrl = '', $resultCount = 0, $responseTime = 0, $remoteAddress = '', $tag = '', $hits = [])
    {
        try {
            $this->getClient()->search([
                'index' => static::APP_INDEX_NAME,
                'type' => 'stat',
                'body' => [
                    'query' => [
                        'match_all' => [
                            'boost' => 1
                        ]
                    ]
                ]
            ]);
        } catch (Missing404Exception $ex) {
            //stat index does not exist
            $this->client->indices()->create([
                'index' => static::APP_INDEX_NAME,
                'body' => [
                    'settings' => [
                        'number_of_shards' => $this->indexNumberOfShards,
                        'number_of_replicas' => $this->indexNumberOfReplicas,
                    ]
                ]
            ]);
            $json = json_decode(file_get_contents(__DIR__.'/../Resources/stat_structure.json'), TRUE);
            $this->putMapping(static::APP_INDEX_NAME, 'stat', $json);
        }
        $indexName = strpos($target, '.') === 0 ? ('.'.explode('.', $target)[1]) : explode('.', $target)[0];
        $params = [
            'index' => static::APP_INDEX_NAME,
            'type' => 'stat',
            'body' => [
                'date' => date('Y-m-d\TH:i:s'),
                'index' => $indexName,
                'mapping' => $target,
                'remote_addr' => $remoteAddress,
                'log' => $tag,
                'facets' => $facets,
                'keywords' => $keywords,
                'keywords_raw' => $rawKeyWords,
                'api_url' => $apiUrl,
                'result_count' => $resultCount,
                'response_time' => $responseTime,
                'text' => $text,
                'hits' => $hits
            ]
        ];
        $r = $this->getClient()->index($params);
        $this->getClient()->indices()->flush();
        unset($params);
        return $r;
    }

    private function putMapping($indexName, $mappingName, $mapping)
    {
        $body = [
            'properties' => $mapping
        ];
        $this->client->indices()->putMapping([
            'index' => $indexName,
            'type' => $mappingName,
            'body' => $body
        ]);
    }

    public function search($indexName, $query, $from = 0, $size = 20, $type = null)
    {
        $params = [
            'index' => $indexName,
            'body' => $query
        ];
        if ($type != null) {
            $params['type'] = $type;
        }
        $params['body']['from'] = $from;
        $params['body']['size'] = $size;
        return $this->client->search($params);
    }
}
